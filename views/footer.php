</div> <!-- /.content -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to logout?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
        <a href="logout.php" class="btn btn-danger">Yes, Logout</a>
      </div>
    </div>
  </div>
</div>


</style>
<div class="modal fade" id="viewContributionModal" tabindex="-1" aria-labelledby="viewContributionModalLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" style="max-width: 68%;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Contribution Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="view-contribution-body" class="row g-4 flex-wrap"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- View Contribution Modal SCRIPT -->
<script>
$(document).on('click', '.view-contrib-btn', function () {
  const row = $('#contributions-table').DataTable().row($(this).parents('tr')).data();

  if (!row) {
    $('#view-contribution-body').html('<div class="alert alert-danger">Contribution not found.</div>');
    return;
  }

const format = (label, value, icon = '') => `
  <div class="col-md-3">
    <label class="form-label fw-semibold">${label}</label>
    <div class="input-group rounded shadow-sm bg-white border border-1">
      <span class="input-group-text bg-transparent border-0">${icon}</span>
      <input type="text" class="form-control border-0 bg-transparent" readonly value="${value}">
    </div>
  </div>
`;


  const html = `
    ${format("Date", row.date, '📅')}
    ${format("Member", row.full_name, '👤')}
    ${format("Or Number", row.or_number, '#')}
    ${format("Hope Channel", "₱" + parseFloat(row.hope_channel).toFixed(2))}
    ${format("Tithe", "₱" + parseFloat(row.tithe).toFixed(2))}
    ${format("CLC Building", "₱" + parseFloat(row.clc_building).toFixed(2))}
    ${format("One Offering CLC", "₱" + parseFloat(row.one_offering_clc).toFixed(2))}
    ${format("One Offering Church", "₱" + parseFloat(row.one_offering_church).toFixed(2))}
    ${format("CB", "₱" + parseFloat(row.cb).toFixed(2))}
    ${format("CF", "₱" + parseFloat(row.cf).toFixed(2))}
    ${format("PFM", "₱" + parseFloat(row.pfm).toFixed(2))}
    ${format("Others", "₱" + parseFloat(row.local_church_others).toFixed(2))}
    ${format("Department", row.department_name || '-', '🏢')}
    <div class="col-md-12">
      <label class="form-label fw-semibold">Remarks</label>
      <textarea class="form-control bg-white border rounded shadow-sm" readonly>${row.remarks || ''}</textarea>
    </div>
  `;

  $('#view-contribution-body').html(html);
  new bootstrap.Modal(document.getElementById('viewContributionModal')).show();
});
</script>


</body>
</html>
