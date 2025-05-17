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
    <h4>Member List</h4>

    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
      <div class="d-flex justify-content-end mb-3">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMemberModal">
          + Add Member
        </button>
      </div>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
      <div class="alert alert-success fade-message">Member added successfully.</div>
    <?php elseif (isset($_GET['updated'])): ?>
      <div class="alert alert-info fade-message">Member updated successfully.</div>
    <?php endif; ?>

    <div class="table-responsive">
      <table id="members-table" class="table table-bordered table-striped nowrap" style="width:100%;">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Joined</th>
            <?php if ($_SESSION['user']['role'] === 'admin'): ?><th>Actions</th><?php endif; ?>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </form>
</div> 
</div> <!-- end #main-content -->
</div><!-- end layout -->
<!-- Scripts -->
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
    setTimeout(() => {
      form.classList.add("visible");
    }, 90);
  }, 300);

    // Fade alerts
  const alert = document.querySelector('.fade-message');
  if (alert) {
    setTimeout(() => {
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 500);
    }, 2500);
  }

  // Load table data
  $.fn.dataTable.ext.errMode = 'none'; // suppress DataTable alerts

  fetch('/church-fund-manager/api/members/index.php', {
    headers: { 'Authorization': 'Bearer <?= $token ?>' }
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      const rows = data.data.map(m => {
        const joined = new Date(m.created_at).toLocaleDateString();
        let actions = '';
        <?php if ($_SESSION['user']['role'] === 'admin'): ?>
          actions = `<button type="button" class="btn btn-sm btn-warning edit-member-btn"
                     data-id="${m.id}" data-name="${m.full_name}">Edit</button>`;
        <?php endif; ?>
        return [m.id, m.full_name, joined, actions];
      });

      $('#members-table').DataTable({
        data: rows,
        responsive: true,
        destroy: true,
        lengthMenu: [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]]
      });
    }
  })
  .catch(err => console.error('Error fetching members:', err));
});

document.addEventListener('click', function (e) {
  if (e.target.classList.contains('edit-member-btn')) {
    const id = e.target.getAttribute('data-id');
    const name = e.target.getAttribute('data-name');
    document.getElementById('edit-member-id').value = id;
    document.getElementById('edit-member-name').value = name;
    new bootstrap.Modal(document.getElementById('editMemberModal')).show();
  }
});

// Cleanup query string
if (window.location.search.includes('success') || window.location.search.includes('updated')) {
  const url = new URL(window.location);
  url.searchParams.delete('success');
  url.searchParams.delete('updated');
  window.history.replaceState({}, document.title, url.pathname);
}
</script>

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>

<?php include '../form/add-modals.php'; ?>
<?php include '../form/edit-modals.php'; ?>
<?php require_once '../views/footer.php'; ?>
