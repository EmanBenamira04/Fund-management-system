<?php
session_start();
if (!isset($_SESSION['jwt_token'])) {
    header('Location: login.php');
    exit;
}

$token = $_SESSION['jwt_token'];

require_once '../src/auth/middleware.php';
require_once '../views/header.php';

$role = $_SESSION['user']['role'] ?? '';
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
      <h4 class="mb-0">Department Expenses</h4>
      <div class="d-flex justify-content-end mb-3">
      <?php if ($role === 'admin'): ?>
      <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
        + Add Expense
      </button>
      <?php endif; ?>
    </div>

    <?php if (isset($_GET['success'])): ?>
      <div class="alert alert-success fade-message">Expense added successfully.</div>
    <?php elseif (isset($_GET['updated'])): ?>
      <div class="alert alert-info fade-message">Expense updated successfully.</div>
    <?php endif; ?>

    <div class="table-responsive">
      <table class="table table-bordered table-striped align-middle text-center nowrap" id="expenses-table" style="width:100%;">
        <thead class="table-dark">
          <tr>
            <th>Expenses Date</th>
            <th>Member Name</th>
            <th>Purpose</th>
            <th>Amount</th>
            <th>Department</th>
            <?php if ($role === 'admin'): ?><th>Action</th><?php endif; ?>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </form>
</div>
</div>
</div>
<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">
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

  const alert = document.querySelector('.fade-message');
  if (alert) {
    setTimeout(() => {
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 500);
    }, 2500);
  }

  $.fn.dataTable.ext.errMode = 'none';

  $('#expenses-table').DataTable({
    ajax: {
      url: '/church-fund-manager/api/expenses/index.php',
      headers: { 'Authorization': 'Bearer <?= $token ?>' },
      dataSrc: function (json) {
        if (!json || !json.success) return [];
        return json.data;
      },
      error: function () {
        $('#expenses-table').DataTable().clear().draw();
      }
    },
    columns: [
      { data: 'expense_date' },
      { data: 'full_name' },
      { data: 'purpose' },
      { data: 'amount', render: $.fn.dataTable.render.number(',', '.', 2, '', ' php') },
      { data: 'department_name' },
      <?php if ($role === 'admin'): ?>
      {
        data: null,
        render: function (row) {
          return `<button 
                    type="button"
                    class="btn btn-sm btn-warning edit-expense-btn"
                    data-id="${row.id}"
                    data-member="${row.member_id}"
                    data-date="${row.expense_date}"
                    data-purpose="${row.purpose}"
                    data-amount="${row.amount}"
                    data-dept="${row.department_name}">
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

  $(document).on('click', '.edit-expense-btn', function () {
    const btn = $(this);
    $('#edit-expense-id').val(btn.data('id'));
    $('#edit-expense-date').val(btn.data('date'));
    $('#edit-expense-amount').val(btn.data('amount'));
    $('#edit-expense-purpose').val(btn.data('purpose'));
    $('#edit-expense-member').val(btn.data('member')).trigger('change');
    $('#edit-expense-department').val(btn.data('dept')).trigger('change');

    new bootstrap.Modal(document.getElementById('editExpenseModal')).show();
  });

  // Cleanup URL
  if (window.location.search.includes('success') || window.location.search.includes('updated')) {
    const url = new URL(window.location);
    url.searchParams.delete('success');
    url.searchParams.delete('updated');
    window.history.replaceState({}, document.title, url.pathname);
  }
});
</script>

<?php include '../form/add-modals.php'; ?>
<?php include '../form/edit-modals.php'; ?>
<?php require_once '../views/footer.php'; ?>
