<?php
session_start();
if (!isset($_SESSION['jwt_token'])) {
    header('Location: login.php');
    exit;
}
$token = $_SESSION['jwt_token'];

require_once '../src/auth/middleware.php';
require_once '../src/models/Contribution.php';
require_once '../src/config/Database.php';
require_once '../views/header.php';

$db = new Database();
$conn = $db->connect();
$contributionModel = new Contribution();
$departments = $conn->query("SELECT id, name FROM departments ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
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
    <h4>All Contributions</h4>

    <?php if ($role === 'admin'): ?>
      <div class="d-flex justify-content-end mb-3">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addContributionModal">
          + Add Contribution
        </button>
      </div>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
      <div class="alert alert-success fade-message">Contribution added successfully.</div>
    <?php elseif (isset($_GET['updated'])): ?>
      <div class="alert alert-info fade-message">Contribution updated successfully.</div>
    <?php endif; ?>

    <div class="table-responsive">
      <table id="contributions-table" class="table table-bordered table-hover nowrap" style="width:100%;">
        <thead class="table-dark">
          <tr>
            <th>Date</th>
            <th>Member</th>
            <th>Tithe</th>
            <th>CLC Bldg</th>
            <th>1O CLC</th>
            <th>1O Church</th>
            <th>Others</th>
            <th>Department</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </form>
</div> 
</div>
</div><!-- end main-content -->
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
    setTimeout(() => form.classList.add("visible"), 00);
  }, 300);

  // Fade alerts
  const alert = document.querySelector('.fade-message');
  if (alert) {
    setTimeout(() => {
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 500);
    }, 2500);
  }

  $.fn.dataTable.ext.errMode = 'none';

  const table = $('#contributions-table').DataTable({
    ajax: {
      url: '/church-fund-manager/api/contributions/index.php',
      headers: { 'Authorization': 'Bearer <?= $token ?>' },
      dataSrc: json => json.success ? json.data : []
    },
    columns: [
      { data: 'date' },
      { data: 'full_name' },
      { data: 'tithe', render: $.fn.dataTable.render.number(',', '.', 2) },
      { data: 'clc_building', render: $.fn.dataTable.render.number(',', '.', 2) },
      { data: 'one_offering_clc', render: $.fn.dataTable.render.number(',', '.', 2) },
      { data: 'one_offering_church', render: $.fn.dataTable.render.number(',', '.', 2) },
      { data: 'local_church_others', render: $.fn.dataTable.render.number(',', '.', 2) },
      { data: 'department_name', defaultContent: '-' },
      {
        data: null,
        render: function (row) {
          return `
            <button type="button" class="btn btn-sm btn-info text-white me-1 view-contrib-btn" data-id="${row.id}">View</button>
            <?php if ($role === 'admin'): ?>
              <button 
                type="button"
                class="btn btn-sm btn-warning edit-contrib-btn"
                data-id="${row.id}"
                data-date="${row.date}"
                data-member="${row.full_name}"
                data-memberid="${row.member_id}"
                data-tithe="${row.tithe}"
                data-hope="${row.hope_channel}"
                data-clc="${row.clc_building}"
                data-cb="${row.cb}"
                data-cf="${row.cf}"
                data-pfm="${row.pfm}"
                data-clc1="${row.one_offering_clc}"
                data-church1="${row.one_offering_church}"
                data-others="${row.local_church_others}"
                data-remarks="${row.remarks || ''}"
                data-dept="${row.department_id || ''}">
                Edit
              </button>
            <?php endif; ?>
          `;
        }
      }
    ],
    responsive: true,
    destroy: true,
    lengthMenu: [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]]
  });

  // Edit modal population
  $(document).on('click', '.edit-contrib-btn', function () {
    const btn = $(this);
    $('#edit-contribution-id').val(btn.data('id'));
    $('#edit-date').val(btn.data('date'));
    $('#edit-member-id').val(btn.data('memberid'));
    $('#edit-member-name').val(btn.data('member'));
    $('#edit-tithe').val(btn.data('tithe'));
    $('#edit-hope').val(btn.data('hope'));
    $('#edit-clc').val(btn.data('clc'));
    $('#edit-cb').val(btn.data('cb'));
    $('#edit-cf').val(btn.data('cf'));
    $('#edit-pfm').val(btn.data('pfm'));
    $('#edit-one-offering').val((parseFloat(btn.data('clc1') || 0) + parseFloat(btn.data('church1') || 0)).toFixed(2));
    $('#edit-local-church').val(btn.data('others'));
    $('#edit-department-id').val(btn.data('dept') || '');
    $('#edit-remarks').val(btn.data('remarks') || '');

    const box = $('#edit-dept-amount-box');
    btn.data('dept') ? box.show() : box.hide();

    new bootstrap.Modal(document.getElementById('editContributionModal')).show();
  });
});

// Clean up URL
if (window.location.search.includes('success') || window.location.search.includes('updated')) {
  const url = new URL(window.location);
  url.searchParams.delete('success');
  url.searchParams.delete('updated');
  window.history.replaceState({}, document.title, url.pathname);
}
</script>

<!-- DataTables + jQuery -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>

<!-- Modal Includes -->
<?php include '../form/add-modals.php'; ?>
<?php include '../form/edit-modals.php'; ?>
<?php require_once '../views/footer.php'; ?>
