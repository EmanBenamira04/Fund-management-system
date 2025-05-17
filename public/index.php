<?php
require_once '../src/auth/middleware.php';
require_once '../views/header.php';
require_once '../src/config/Database.php';

$db = new Database();
$conn = $db->connect();

$currentYear = $_GET['year'] ?? date('Y');

// --- Income
$incomeStmt = $conn->prepare("
    SELECT 
        SUM(tithe + hope_channel + clc_building + one_offering_clc + one_offering_church + cb + cf + pfm + local_church_others) AS total_month,
        MONTH(date) as month
    FROM contributions
    WHERE YEAR(date) = ?
    GROUP BY month
");
$incomeStmt->execute([$currentYear]);
$monthlyIncome = $incomeStmt->fetchAll(PDO::FETCH_ASSOC);

$totalIncomeYear = 0;
$totalIncomeThisMonth = 0;
$monthlyTotals = array_fill(1, 12, 0);
foreach ($monthlyIncome as $row) {
    $monthlyTotals[(int)$row['month']] = (float)$row['total_month'];
    $totalIncomeYear += (float)$row['total_month'];
    if ((int)$row['month'] === (int)date('n')) {
        $totalIncomeThisMonth = (float)$row['total_month'];
    }
}

// --- Expenses
$expenseStmt = $conn->prepare("
    SELECT SUM(amount) AS total_month, MONTH(expense_date) as month
    FROM department_expenses
    WHERE YEAR(expense_date) = ?
    GROUP BY month
");
$expenseStmt->execute([$currentYear]);
$monthlyExpenses = $expenseStmt->fetchAll(PDO::FETCH_ASSOC);

$totalExpenseYear = 0;
$totalExpenseThisMonth = 0;
foreach ($monthlyExpenses as $row) {
    $totalExpenseYear += (float)$row['total_month'];
    if ((int)$row['month'] === (int)date('n')) {
        $totalExpenseThisMonth = (float)$row['total_month'];
    }
}

// --- Members
$memberStmt = $conn->prepare("
    SELECT COUNT(*) AS total_month, MONTH(created_at) as month
    FROM members
    WHERE YEAR(created_at) = ?
    GROUP BY month
");
$memberStmt->execute([$currentYear]);
$monthlyMembers = $memberStmt->fetchAll(PDO::FETCH_ASSOC);

$totalMembersYear = 0;
$totalMembersThisMonth = 0;
foreach ($monthlyMembers as $row) {
    $totalMembersYear += (int)$row['total_month'];
    if ((int)$row['month'] === (int)date('n')) {
        $totalMembersThisMonth = (int)$row['total_month'];
    }
}

// --- Contribution Breakdown
$breakdownStmt = $conn->prepare("
    SELECT SUM(tithe) as tithe, SUM(one_offering_clc) as clc, SUM(one_offering_church) as church, SUM(local_church_others) as others
    FROM contributions
    WHERE YEAR(date) = ?
");
$breakdownStmt->execute([$currentYear]);
$breakdown = $breakdownStmt->fetch(PDO::FETCH_ASSOC);

// --- Department Highlights
$highlightStmt = $conn->prepare("
    SELECT d.name, da.amount
    FROM departments d
    INNER JOIN department_allocations da ON d.id = da.department_id
    WHERE da.year = ? AND MONTH(da.created_at) = ?
    ORDER BY d.name ASC
");
$highlightStmt->execute([$currentYear, date('n')]);
$departmentsThisMonth = $highlightStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Loader -->
<div id="loader" style="
  position: fixed;
  top: 0; left: 0;
  width: 100%;
  height: 100%;
  background: white;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 24px;
">
  Loading...
</div>

<!-- Content Wrapper -->
<div id="main-content" style="visibility: hidden;">
<style>
.card-box {
    background: rgba(255, 255, 255, 0.7);
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 0 10px rgba(0,0,0,0.08);
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.5s ease, transform 0.5s ease;
}
.card-box.visible {
    opacity: 1;
    transform: translateY(0);
}
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}
canvas {
    width: 100% !important;
    height: auto !important;
}
.year-select {
    width: 100px;
}
#dept-highlight {
    position: relative;
    height: 60px;
}
.dept-slide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    opacity: 0;
    transform: translateY(-100%);
    transition: all 0.5s ease;
}
.dept-slide.active {
    opacity: 1;
    transform: translateY(0);
}
</style>

<form method="GET" action="#">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3>Dashboard Overview</h3>
      <select name="year" class="form-select year-select" onchange="this.form.submit()">
        <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
          <option value="<?= $y ?>" <?= $y == $currentYear ? 'selected' : '' ?>><?= $y ?></option>
        <?php endfor; ?>
      </select>
    </div>
  </div>
</form>

<div class="container-fluid">
  <div class="dashboard-grid">
    <div class="card-box"> 
      <div>Total Collection</div>
      <h2>₱<?= number_format($totalIncomeYear / 1000, 2) ?>k</h2>
      <div class="text-success">₱<?= number_format($totalIncomeThisMonth, 2) ?> this month</div>
    </div>
    <div class="card-box">
      <div>Total Expenses</div>
      <h2>₱<?= number_format($totalExpenseYear / 1000, 2) ?>k</h2>
      <div class="text-success">₱<?= number_format($totalExpenseThisMonth, 2) ?> this month</div>
    </div>
    <div class="card-box">
      <div>Total Members</div>
      <h2><?= $totalMembersYear ?></h2>
      <div class="text-success"><?= $totalMembersThisMonth ?> this month</div>
    </div>
    <div class="card-box">
      <div>Department amount</div>
      <div id="dept-highlight">
        <?php foreach ($departmentsThisMonth as $index => $dept): ?>
          <div class="dept-slide <?= $index === 0 ? 'active' : '' ?>">
            <h2>₱<?= number_format($dept['amount'], 2) ?></h2>
            <p><?= htmlspecialchars($dept['name']) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="dashboard-grid">
    <div class="card-box">
      <h6>Monthly Collection (<?= $currentYear ?>)</h6>
      <canvas id="lineChart"></canvas>
    </div>
    <div class="card-box">
      <h6>Contribution Breakdown</h6>
      <canvas id="barChart"></canvas>
    </div>
  </div>
</div>
</div> 
</div><!-- END #main-content -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
window.addEventListener("load", () => {
  const loader = document.getElementById("loader");
  const main = document.getElementById("main-content");

  loader.style.opacity = "0";
  loader.style.transition = "opacity 0.5s ease-out";

  setTimeout(() => {
    loader.style.display = "none";
    main.style.visibility = "visible";

    // Animate cards with stagger
    const cards = document.querySelectorAll('.card-box');
    cards.forEach((card, index) => {
      setTimeout(() => card.classList.add('visible'), index * 150);
    });
  }, 500);
});

// Dept slider
let currentDept = 0;
const slides = document.querySelectorAll("#dept-highlight .dept-slide");
if (slides.length > 1) {
  setInterval(() => {
    slides[currentDept].classList.remove("active");
    currentDept = (currentDept + 1) % slides.length;
    slides[currentDept].classList.add("active");
  }, 3000);
}

// Charts
const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
const incomeData = <?= json_encode(array_values($monthlyTotals)) ?>;
const barData = {
  labels: ["Tithes", "Others", "50% CLC", "50% Church"],
  datasets: [{
    label: "₱",
    data: [
      <?= $breakdown['tithe'] ?? 0 ?>,
      <?= $breakdown['others'] ?? 0 ?>,
      <?= $breakdown['clc'] ?? 0 ?>,
      <?= $breakdown['church'] ?? 0 ?>
    ],
    backgroundColor: ["#ffb3ba", "#ffe3a9", "#b3d1ff", "#a0f0ff"]
  }]
};

new Chart(document.getElementById('lineChart'), {
  type: 'line',
  data: {
    labels: months,
    datasets: [{
      label: "Income (₱)",
      data: incomeData,
      fill: false,
      borderColor: "green",
      backgroundColor: "green",
      tension: 0
    }]
  },
  options: {
    responsive: true,
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          callback: value => new Intl.NumberFormat().format(value)
        }
      }
    }
  }
});

new Chart(document.getElementById('barChart'), {
  type: 'bar',
  data: barData,
  options: {
    responsive: true,
    indexAxis: 'y',
    scales: {
      x: { beginAtZero: true }
    }
  }
});
</script>

<?php require_once '../views/footer.php'; ?>
