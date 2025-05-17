<?php
require_once '../src/auth/middleware.php';
require_once '../src/config/Database.php';
require_once '../views/header.php';

$db = new Database();
$conn = $db->connect();
$departments = $conn->query("SELECT id, name FROM departments ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Loader -->
<div id="loader" style="
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
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
<div id="main-content" style="visibility:hidden;">
  <style>
    .page-form {
      opacity: 0;
      transform: translateY(20px);
      transition: opacity 0.5s ease, transform 0.5s ease;
    }
    .page-form.visible {
      opacity: 1;
      transform: translateY(0);
    }
  </style>

  <form class="page-form" method="GET" action="#">
    <div class="container">
      <h4 class="mb-3">Generate Reports</h4>
      <div class="row g-3 mb-4">
        <div class="col-md-3">
          <label>Report Type</label>
          <select name="report_type" id="report-type" class="form-select" required>
            <option value="">-- Select --</option>
            <option value="contribution">Contribution</option>
            <option value="expenses">Expenses</option>
            <option value="department">Department</option>
          </select>
        </div>

        <div class="col-md-3 d-none" id="department-select-container">
          <label>Department</label>
          <select name="department_id" id="department-select" class="form-select">
            <option value="">-- Select Department --</option>
            <?php foreach ($departments as $dept): ?>
              <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-3">
          <label>Date From</label>
          <input type="date" name="from" class="form-control" required>
        </div>

        <div class="col-md-3">
          <label>Date To</label>
          <input type="date" name="to" class="form-control" required>
        </div>

        <div class="col-md-3 d-flex align-items-end">
          <button type="submit" class="btn btn-primary w-100" id="generate-btn">Generate</button>
          <button type="button" class="btn btn-danger w-100 d-none ms-2" id="download-pdf-btn">Download PDF</button>
        </div>
      </div>

      <div id="report-output"></div>
    </div>
  </form>
</div>
</div>

<!-- DataTables CSS + JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>

<script>
window.addEventListener("load", () => {
  const loader = document.getElementById("loader");
  const content = document.getElementById("main-content");
  const form = document.querySelector(".page-form");

  loader.style.opacity = "0";
  loader.style.transition = "opacity 0.5s ease-out";
  setTimeout(() => {
    loader.style.display = "none";
    content.style.visibility = "visible";
    setTimeout(() => form.classList.add("visible"), 90);
  }, 300);
});

const token = '<?= $_SESSION['jwt_token'] ?>';
const reportType = document.getElementById("report-type");
const departmentContainer = document.getElementById("department-select-container");
const departmentSelect = document.getElementById("department-select");
const generateBtn = document.getElementById("generate-btn");
const downloadBtn = document.getElementById("download-pdf-btn");
const reportOutput = document.getElementById("report-output");

reportType.addEventListener("change", () => {
  const selected = reportType.value;
  departmentContainer.classList.toggle("d-none", selected !== "department");
  downloadBtn.classList.add("d-none");
  generateBtn.classList.remove("d-none");
  reportOutput.innerHTML = "";
});

document.querySelector(".page-form").addEventListener("submit", function (e) {
  e.preventDefault();

  const type = reportType.value;
  const from = document.querySelector('[name="from"]').value;
  const to = document.querySelector('[name="to"]').value;
  const deptId = departmentSelect.value;

  if (!type || !from || !to) return alert("All fields are required.");
  if (type === "department" && !deptId) return alert("Please select a department.");

  let apiURL = `/church-fund-manager/api/reports/index.php?report_type=${type}&from=${from}&to=${to}`;
  if (type === "department") apiURL += `&department_id=${deptId}`;

  fetch(apiURL, {
    headers: { Authorization: `Bearer ${token}` },
  })
  .then(res => res.json())
  .then(json => {
    if (!json.success) return alert("No data found.");
    renderTable(type, json.data);
    downloadBtn.classList.remove("d-none");
    generateBtn.classList.add("d-none");
  })
  .catch(err => console.error("Fetch error:", err));
});

downloadBtn.addEventListener("click", () => {
  const type = reportType.value;
  const from = document.querySelector('[name="from"]').value;
  const to = document.querySelector('[name="to"]').value;
  const deptId = departmentSelect.value;

  let downloadURL = `/church-fund-manager/api/reports/download.php?report_type=${type}&from=${from}&to=${to}&token=${token}`;
  if (type === "department") {
    const deptText = departmentSelect.options[departmentSelect.selectedIndex].text;
    downloadURL += `&department=${encodeURIComponent(deptText)}`;
  }

  window.open(downloadURL, "_blank");
});

function renderTable(type, data) {
  reportOutput.innerHTML = "";

  if (type === "contribution") {
    let html = `<h5>Contribution Report</h5>
    <table id="report-table" class="table table-bordered display nowrap" style="width:100%">
    <thead class="table-dark"><tr>
    <th>Date</th><th>Tithe</th><th>Hope</th><th>CLC</th><th>1O CLC</th><th>1O Church</th><th>CB</th><th>CF</th><th>PFM</th><th>Local</th></tr></thead><tbody>`;

    let totals = { tithe: 0, hope: 0, clc: 0, clc_offering: 0, church_offering: 0, cb: 0, cf: 0, pfm: 0, local_church: 0 };
    data.forEach(row => {
      html += `<tr><td>${row.date}</td><td>${fmt(row.tithe)}</td><td>${fmt(row.hope)}</td><td>${fmt(row.clc)}</td>
      <td>${fmt(row.clc_offering)}</td><td>${fmt(row.church_offering)}</td><td>${fmt(row.cb)}</td><td>${fmt(row.cf)}</td>
      <td>${fmt(row.pfm)}</td><td>${fmt(row.local_church)}</td></tr>`;
      for (let key in totals) totals[key] += parseFloat(row[key] || 0);
    });

    html += `</tbody><tfoot class="fw-bold table-primary"><tr><td>Total</td>
      <td>${fmt(totals.tithe)}</td><td>${fmt(totals.hope)}</td><td>${fmt(totals.clc)}</td><td>${fmt(totals.clc_offering)}</td>
      <td>${fmt(totals.church_offering)}</td><td>${fmt(totals.cb)}</td><td>${fmt(totals.cf)}</td><td>${fmt(totals.pfm)}</td><td>${fmt(totals.local_church)}</td>
      </tr></tfoot></table>`;
    reportOutput.innerHTML = html;
  }

  else if (type === "expenses") {
    let total = 0;
    let html = `<h5>Expenses Report</h5><table id="report-table" class="table table-bordered display nowrap" style="width:100%">
    <thead class="table-dark"><tr><th>Date</th><th>Member</th><th>Purpose</th><th>Amount</th><th>Department</th></tr></thead><tbody>`;
    data.forEach(row => {
      total += parseFloat(row.amount);
      html += `<tr><td>${row.date}</td><td>${row.full_name}</td><td>${row.purpose}</td><td>${fmt(row.amount)}</td><td>${row.department_name}</td></tr>`;
    });
    html += `</tbody><tfoot class="fw-bold table-primary"><tr><td>Total</td><td></td><td></td><td>${fmt(total)}</td><td></td></tr></tfoot></table>`;
    reportOutput.innerHTML = html;
  }

  else if (type === "department") {
    let html = `<h5>Department Report</h5><table id="report-table" class="table table-bordered display nowrap" style="width:100%">
    <thead class="table-dark"><tr><th>Department</th><th>Amount</th><th>Percentage</th><th>Year</th></tr></thead><tbody>`;
    data.forEach(row => {
      html += `<tr><td>${row.name}</td><td>${fmt(row.amount)}</td><td>${row.percentage}%</td><td>${row.year}</td></tr>`;
    });
    html += `</tbody></table>`;
    reportOutput.innerHTML = html;
  }

  $('#report-table').DataTable({ responsive: true });
}

function fmt(num) {
  return (+num).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>

<?php require_once '../views/footer.php'; ?>
