<?php
session_start();
if (!isset($_SESSION['jwt_token'])) {
    header('Location: login.php');
    exit;
}
$token = $_SESSION['jwt_token'];

require_once '../src/auth/middleware.php';
require_once '../views/header.php';
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

<!-- Main Content -->
<div id="main-content" style="visibility: hidden;">
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

  <form class="page-form">
    <h4 class="mb-3">Department Allocations</h4>

    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
    <div class="d-flex justify-content-end mb-3">
      <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
        + Add Department
      </button>
    </div>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
      <div id="success-alert" class="alert alert-success">Department added successfully.</div>
    <?php elseif (isset($_GET['updated'])): ?>
      <div id="update-alert" class="alert alert-info">Department updated successfully.</div>
    <?php endif; ?>

    <div class="table-responsive">
      <table id="departments-table" class="table table-bordered table-striped text-center align-middle nowrap" style="width:100%;">
        <thead class="table-dark">
          <tr>
            <th>Dept.</th>
            <th>Amount</th>
            <th>%</th>
            <?php if ($_SESSION['user']['role'] === 'admin'): ?><th>Action</th><?php endif; ?>
          </tr>
        </thead>
        <tbody></tbody>
        <tfoot class="fw-bold table-primary">
          <tr>
            <td>Total</td>
            <td id="total-amount">0.00</td>
            <td id="total-percentage">0.00%</td>
            <?php if ($_SESSION['user']['role'] === 'admin'): ?><td></td><?php endif; ?>
          </tr>
        </tfoot>
      </table>
    </div>
  </form>
</div>
</div>
</div>
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>

<script>
window.addEventListener("load", function () {
  const loader = document.getElementById("loader");
  const main = document.getElementById("main-content");
  const form = document.querySelector(".page-form");

  loader.style.opacity = "0";
  loader.style.transition = "opacity 0.5s ease-out";

  setTimeout(() => {
    loader.style.display = "none";
    main.style.visibility = "visible";
    setTimeout(() => form.classList.add("visible"), 90);
  }, 300);

  const alert = document.querySelector('.alert');
  if (alert) {
    setTimeout(() => {
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 500);
    }, 2000);
  }

  $.fn.dataTable.ext.errMode = 'none';

  const table = $('#departments-table').DataTable({
    ajax: {
      url: '/church-fund-manager/api/departments/index.php',
      headers: { 'Authorization': 'Bearer <?= $token ?>' },
      dataSrc: function (json) {
        if (!json.success) {
          console.error('API Error:', json.error);
          return [];
        }
        updateTotals(json.data);
        return json.data;
      }
    },
    columns: [
      { data: 'name' },
      { data: 'amount', render: $.fn.dataTable.render.number(',', '.', 2, '') },
      { data: 'percentage', render: data => data + '%' },
      <?php if ($_SESSION['user']['role'] === 'admin'): ?>
      {
        data: null,
        render: function (row) {
          return `
            <button type="button" class="btn btn-sm btn-warning edit-dept-btn"
              data-id="${row.id}"
              data-name="${row.name}"
              data-amount="${row.amount}"
              data-percentage="${row.percentage}"
              data-year="${row.year}">
              Edit
            </button>`;
        }
      }
      <?php endif; ?>
    ],
    responsive: true,
    destroy: true,
    lengthMenu: [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]]
  });

  function updateTotals(data) {
    let totalAmount = 0, totalPercent = 0;
    data.forEach(row => {
      totalAmount += parseFloat(row.amount || 0);
      const pct = parseFloat(row.percentage);
      if (!isNaN(pct)) totalPercent += pct;
    });
    document.getElementById('total-amount').innerText = totalAmount.toFixed(2);
    document.getElementById('total-percentage').innerText = totalPercent.toFixed(2) + '%';
  }

  // Clean URL
  if (window.location.search.includes('success') || window.location.search.includes('updated')) {
    const url = new URL(window.location);
    url.searchParams.delete('success');
    url.searchParams.delete('updated');
    window.history.replaceState({}, document.title, url.pathname);
  }
});
</script>

<!-- Modal Includes -->
<?php include '../form/add-modals.php'; ?>
<?php include '../form/edit-modals.php'; ?>
<?php require_once '../views/footer.php'; ?>
