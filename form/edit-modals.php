
<!-- Edit Member Modal -->
<div class="modal fade" id="editMemberModal" tabindex="-1" aria-labelledby="editMemberModalLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered">
    <form method="POST" action="../src/controllers/MemberController.php" class="modal-content">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id" id="edit-member-id">
      <div class="modal-header">
        <h5 class="modal-title" id="editMemberModalLabel">Edit Member</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input type="text" name="full_name" class="form-control" id="edit-member-name" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Update</button>
      </div>
    </form>
  </div>
</div>



<!-- Edit Contribution Modal -->
<?php
require_once '../src/models/Member.php';
$members = (new Member())->all();
?>

<div class="modal fade" id="editContributionModal" tabindex="-1" aria-labelledby="editContributionModalLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" style="max-width: 68%;">
    <form method="POST" action="../src/controllers/ContributionController.php" class="modal-content">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id" id="edit-contribution-id">
      <input type="hidden" name="member_id" id="edit-contribution-member-id">
      <input type="hidden" name="prev_local_amount" id="edit-prev-local">
      <input type="hidden" name="prev_department_id" id="edit-prev-dept">

      <div class="modal-header">
        <h5 class="modal-title">Edit Contribution</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body row g-3">
        <div class="col-md-6">
          <label>Date</label>
          <input type="date" name="date" id="edit-date" class="form-control" required>
        </div>
<div class="col-md-6">
  <label>Member</label>
  <select name="member_id" id="edit-member-select" class="form-select" required>
    <option value="">-- Select Member --</option>
    <?php foreach ($members as $m): ?>
      <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['full_name']) ?></option>
    <?php endforeach; ?>
  </select>
</div>


        <?php
        $fields = [
          'tithe' => 'Tithe',
          'hope_channel' => 'Hope Channel',
          'clc_building' => 'CLC Building',
          'cb' => 'CB',
          'cf' => 'CF',
          'pfm' => 'PFM',
        ];
        foreach ($fields as $key => $label): ?>
          <div class="col-md-4">
            <label><?= $label ?></label>
            <input type="number" step="0.01" name="<?= $key ?>" id="edit-<?= $key ?>" class="form-control">
          </div>
        <?php endforeach; ?>

        <div class="col-md-4">
          <label>One Offering (Total)</label>
          <input type="number" step="0.01" name="one_offering_total" id="edit-one-offering" class="form-control">
        </div>

        <div class="col-md-6">
          <label>Department</label>
          <select name="department_id" id="edit-department-id" class="form-select">
            <option value="">-- Select Department --</option>
            <?php foreach ($departments as $d): ?>
              <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-6" id="edit-dept-amount-box" style="display: none;">
          <label>Amount to Add to Department</label>
          <input type="number" step="0.01" name="local_church_others" id="edit-local-church" class="form-control">
        </div>

        <div class="col-md-12">
          <label>Remarks</label>
          <textarea name="remarks" id="edit-remarks" class="form-control"></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-success">Update</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Department Modal -->
<div class="modal fade" id="editDepartmentModal" tabindex="-1" aria-labelledby="editDepartmentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST" action="../src/controllers/DepartmentController.php" class="modal-content">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id" id="edit-dept-id">

      <div class="modal-header">
        <h5 class="modal-title">Edit Department</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <div class="col-md-6">
          <label>Department</label>
          <input type="text" name="name" id="edit-dept-name" class="form-control" required>
        </div>
        <div class="col-md-3">
          <label>Amount</label>
          <input type="number" step="0.01" name="amount" id="edit-dept-amount" class="form-control" required>
        </div>
        <div class="col-md-3">
          <label>Percentage</label>
          <input type="number" step="0.01" name="percentage" id="edit-dept-percentage" class="form-control" required>
        </div>
        <div class="col-md-3">
          <label>Year</label>
          <select name="year" id="edit-dept-year" class="form-select" required>
            <?php
            $currentYear = date('Y');
            for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
              echo "<option value=\"$y\">$y</option>";
            }
            ?>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-success" type="submit">Update</button>
      </div>
    </form>
  </div>
</div>


<!-- Edit Expense Modal -->
<div class="modal fade" id="editExpenseModal" tabindex="-1" aria-labelledby="editExpenseModalLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" style="max-width: 38%;">
    <form method="POST" action="../src/controllers/DepartmentExpenseController.php" class="modal-content">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id" id="edit-expense-id">
      <div class="modal-header">
        <h5 class="modal-title">Edit Expense</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <div class="col-md-6">
          <label>Department</label>
          <select name="department_name" id="edit-expense-dept" class="form-select" required>
            <?php foreach ($departments as $d): ?>
              <option value="<?= htmlspecialchars($d['name']) ?>"><?= htmlspecialchars($d['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label>Member</label>
<select name="member_id" id="edit-expense-member" class="form-select" required>
  <?php foreach ($members as $m): ?>
    <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['full_name']) ?></option>
  <?php endforeach; ?>
</select>

        </div>
        <div class="col-md-6">
          <label>Purpose</label>
          <input type="text" name="purpose" id="edit-expense-purpose" class="form-control" required>
        </div>
        <div class="col-md-3">
          <label>Amount</label>
          <input type="number" step="0.01" name="amount" id="edit-expense-amount" class="form-control" required>
        </div>
        <div class="col-md-3">
          <label>Expenses Date</label>
          <input type="date" name="expense_date" id="edit-expense-date" class="form-control" 
                 required min="<?= $currentYear ?>-01-01" max="<?= $currentYear ?>-12-31">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-success">Update</button>
      </div>
    </form>
  </div>
</div>


<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" style="max-width: 38%;">
    <form method="POST" action="../src/controllers/UserController.php" class="modal-content">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id" id="edit-user-id">
      <div class="modal-header">
        <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <div class="col-md-6">
          <label>Username</label>
          <input type="text" name="username" id="edit-user-username" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label>Email</label>
          <input type="email" name="email" id="edit-user-email" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label>Password</label>
          <input type="text" name="password" id="edit-user-password" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label>Role</label>
          <select name="role" id="edit-user-role" class="form-select" required>
            <option value="admin">admin</option>
            <option value="treasurer">treasurer</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-success">Update</button>
      </div>
    </form>
  </div>
</div>

<!-- JS to open modal and fill in user values -->
<script>
  $(document).on('click', '.edit-user-btn', function () {
    const btn = $(this);
    $('#edit-user-id').val(btn.data('id'));
    $('#edit-user-username').val(btn.data('username'));
    $('#edit-user-email').val(btn.data('email'));
    $('#edit-user-password').val(btn.data('password'));
    $('#edit-user-role').val(btn.data('role'));
    new bootstrap.Modal(document.getElementById('editUserModal')).show();
  });
</script>



<script>
  // This must be AFTER the table has rendered
  $(document).on('click', '.edit-dept-btn', function () {
    const btn = $(this);

    $('#edit-dept-id').val(btn.data('id'));
    $('#edit-dept-name').val(btn.data('name'));
    $('#edit-dept-amount').val(btn.data('amount'));
    $('#edit-dept-percentage').val(btn.data('percentage'));
    $('#edit-dept-year').val(btn.data('year'));

    new bootstrap.Modal(document.getElementById('editDepartmentModal')).show();
  });
</script>

<!-- Department Toggle -->
<script>
  document.getElementById('edit-department-id')?.addEventListener('change', function () {
    const box = document.getElementById('edit-dept-amount-box');
    box.style.display = this.value ? 'block' : 'none';
  });
</script>

<!-- Member Edit Modal Trigger -->
<script>
  document.addEventListener('click', function (e) {
    if (e.target.classList.contains('edit-member-btn')) {
      const id = e.target.getAttribute('data-id');
      const name = e.target.getAttribute('data-name');
      document.getElementById('edit-member-id').value = id;
      document.getElementById('edit-member-name').value = name;
      const modal = new bootstrap.Modal(document.getElementById('editMemberModal'));
      modal.show();
    }
  });
</script>

<!-- Contribution Edit Modal Trigger -->
<script>
  $(document).on('click', '.edit-contrib-btn', function () {
    const btn = $(this);
    $('#edit-contribution-id').val(btn.data('id'));
    $('#edit-date').val(btn.data('date'));
    $('#edit-contribution-member-id').val(btn.data('memberid'));
    $('#edit-member-select').val(btn.data('memberid')).trigger('change');
    $('#edit-tithe').val(btn.data('tithe'));
    $('#edit-hope_channel').val(btn.data('hope'));
    $('#edit-clc_building').val(btn.data('clc'));
    $('#edit-cb').val(btn.data('cb'));
    $('#edit-cf').val(btn.data('cf'));
    $('#edit-pfm').val(btn.data('pfm'));
    $('#edit-one-offering').val((parseFloat(btn.data('clc1') || 0) + parseFloat(btn.data('church1') || 0)).toFixed(2));
    $('#edit-local-church').val(btn.data('others'));
    $('#edit-department-id').val(btn.data('dept') || '');
    $('#edit-remarks').val(btn.data('remarks') || '');
    $('#edit-prev-local').val(btn.data('others'));
    $('#edit-prev-dept').val(btn.data('dept') || '');

    if (btn.data('dept')) {
      $('#edit-dept-amount-box').show();
    } else {
      $('#edit-dept-amount-box').hide();
    }

    new bootstrap.Modal(document.getElementById('editContributionModal')).show();
  });
</script>
<!-- Select2 JS & CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function () {
  $('#edit-member-select').select2({
    dropdownParent: $('#editContributionModal'),
    placeholder: "-- Select Member --",
    width: '100%'
  });
});
</script>
<script>
$(document).ready(function () {
  $('#edit-expense-member').select2({
    dropdownParent: $('#editExpenseModal'),
    placeholder: "-- Select Member --",
    width: '100%'
  });
});
</script>
