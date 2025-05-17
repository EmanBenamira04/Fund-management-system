<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Church Fund Manager</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">

  <style>
    .nav-link.active {
      background-color: #495057;
      color: #ffffff;
      border-color: rgba(255,255,255,0.5);
    }
  </style>
</head>

<body class="fade-in">

<?php
// Access user info from session
$role = $_SESSION['user']['role'] ?? '';
$username = $_SESSION['user']['username'] ?? 'Unknown';
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!-- Header -->
<div class="main-header d-flex align-items-center justify-content-between px-4 py-2">
  <a href="index.php" class="d-flex align-items-center gap-3 text-decoration-none">
    <img src="../assets/images/sdalogo.png" alt="Logo" class="header-logo">
    <h5 class="mb-0 text-white">Seventh Day Adventist</h5>
  </a>
<div class="header-user d-flex align-items-center gap-3">
  <span class="text-white"><?= htmlspecialchars($username) ?></span>
  <!-- Old logout button replaced -->
  <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#logoutModal">Logout</button>
</div>

</div>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  <!-- Navigation -->
  <ul class="nav flex-column nav-menu">
    <li class="nav-item" title="Dashboard">
      <a class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>" href="index.php">🏠 <span>Dashboard</span></a>
    </li>
    <li class="nav-item" title="Members">
      <a class="nav-link <?= $currentPage === 'members.php' ? 'active' : '' ?>" href="members.php">👥 <span>Members</span></a>
    </li>
    <li class="nav-item" title="Contributions">
      <a class="nav-link <?= $currentPage === 'contributions.php' ? 'active' : '' ?>" href="contributions.php">📋 <span>Contributions</span></a>
    </li>
    <li class="nav-item" title="Expenses">
      <a class="nav-link <?= $currentPage === 'expenses.php' ? 'active' : '' ?>" href="expenses.php">💸 <span>Expenses</span></a>
    </li>
    <li class="nav-item" title="Reports">
      <a class="nav-link <?= $currentPage === 'reports.php' ? 'active' : '' ?>" href="reports.php">📄 <span>Reports</span></a>
    </li>
<li class="nav-item" title="Departments">
      <a class="nav-link <?= $currentPage === 'departments.php' ? 'active' : '' ?>" href="departments.php">🏛 <span>Departments</span></a>
    </li>
    <?php if ($role === 'admin'): ?>
      <li class="nav-item" title="Users">
        <a class="nav-link <?= $currentPage === 'users.php' ? 'active' : '' ?>" href="users.php">👥 <span>Users</span></a>
      </li>
    <?php endif; ?>
  </ul>

  <!-- Footer card OUTSIDE the UL -->
  <div class="sidebar-card text-center mt-auto">
    <h6>Adventist &copy; <?= date('Y')?></h6>
  </div>
</div>
<!-- Content -->
<div class="content">
  <div class="container-fluid bg-transparent">

    <!-- JS Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

    <script>
      document.addEventListener("DOMContentLoaded", function () {
        $('table').DataTable({
          paging: true,
          pageLength: 5,
          lengthMenu: [5, 10, 25, 50],
          ordering: true,
          responsive: true
        });

        $(document).on('hidden.bs.modal', function () {
          $('body').removeClass('modal-open');
          $('.modal-backdrop').remove();
        });
      });
    </script>
