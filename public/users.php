<?php
session_start();
if (!isset($_SESSION['jwt_token'])) {
    header('Location: login.php'); // Force login
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
      <h3>Users</h3>
      <div class="d-flex justify-content-end mb-3">
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
              + Add User
          </button>
      </div>

      <?php if (isset($_GET['created'])): ?>
          <div id="flash-message" class="alert alert-success">User added successfully.</div>
      <?php elseif (isset($_GET['updated'])): ?>
          <div id="flash-message" class="alert alert-info">User updated successfully.</div>
      <?php endif; ?>

      <div class="table-responsive">
          <table id="users-table" class="table table-bordered table-striped text-center align-middle nowrap" style="width:100%;">
              <thead class="table-dark">
                  <tr>
                      <th>User</th>
                      <th>Email</th>
                      <th>Password</th>
                      <th>Role</th>
                      <th>Action</th>
                  </tr>
              </thead>
              <tbody></tbody>
          </table>
      </div>
  </form>
</div>
</div>
</div>
<!-- Include modals -->
<?php include '../form/add-modals.php'; ?>
<?php include '../form/edit-modals.php'; ?>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
window.addEventListener("load", function () {
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

  const flash = document.getElementById('flash-message');
  if (flash) {
    setTimeout(() => {
      flash.style.transition = "opacity 0.8s";
      flash.style.opacity = "0";
      setTimeout(() => flash.remove(), 800);
    }, 2500);
  }

  $.fn.dataTable.ext.errMode = 'none';

  fetch('/church-fund-manager/api/users/index.php', {
      headers: { 'Authorization': 'Bearer <?= $token ?>' }
  })
  .then(res => res.json())
  .then(res => {
      $('#users-table').DataTable({
          data: res.success ? res.data : [],
          destroy: true,
          responsive: true,
          pageLength: 5,
          lengthMenu: [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
          columns: [
              { data: 'username' },
              { data: 'email' },
              { data: 'password' },
              { data: 'role' },
              {
                  data: null,
                  render: function(row) {
                      return `
                        <button type="button" class="btn btn-sm btn-warning edit-user-btn"
                          data-id="${row.id}"
                          data-username="${row.username}"
                          data-email="${row.email}"
                          data-password="${row.password}"
                          data-role="${row.role}">
                          Edit
                        </button>`;
                  }
              }
          ]
      });
  })
  .catch(err => {
      console.error('Unauthorized or network error:', err);
      $('#users-table').DataTable({
          data: [],
          destroy: true,
          responsive: true
      });
  });

  if (window.location.search.includes('created') || window.location.search.includes('updated')) {
    const url = new URL(window.location);
    url.searchParams.delete('created');
    url.searchParams.delete('updated');
    window.history.replaceState({}, document.title, url.pathname);
  }
});
</script>

<!-- DataTables CSS/JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>

<!-- Select2 (optional, if used in modals) -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<?php require_once '../views/footer.php'; ?>
