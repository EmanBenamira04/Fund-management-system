<?php
require_once '../src/config/Database.php';
require_once '../src/models/Member.php';

$db = new Database();
$conn = $db->connect();

$memberModel = new Member();
$members = $memberModel->all();

// Get only current year departments
$currentYear = date('Y');
$stmt = $conn->prepare("
    SELECT d.id, d.name, da.year, da.amount
    FROM departments d
    INNER JOIN department_allocations da 
        ON d.id = da.department_id
    WHERE da.year = :year
      AND da.id = (
        SELECT MAX(da2.id)
        FROM department_allocations da2
        WHERE da2.department_id = d.id AND da2.year = :year
    )
    ORDER BY d.name
");

$stmt->bindParam(':year', $currentYear);
$stmt->execute();
$departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!-- Add Member Modal -->
<div class="modal fade" id="addMemberModal" tabindex="-1" aria-labelledby="addMemberModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST" action="../src/controllers/MemberController.php" class="modal-content">
      <input type="hidden" name="action" value="create">
      <div class="modal-header">
        <h5 class="modal-title">Add Member</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input type="text" name="full_name" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-success" type="submit">Add</button>
      </div>
    </form>
  </div>
</div>

<!-- Add Contribution Modal -->
<div class="modal fade" id="addContributionModal" tabindex="-1" aria-labelledby="addContributionLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" style="max-width: 48%;">
    <form method="POST" action="../src/controllers/ContributionController.php" class="modal-content">
      <input type="hidden" name="action" value="create">
      <div class="modal-header">
        <h5 class="modal-title">Add Contribution</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Date</label>
            <input type="date" name="date" class="form-control" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Member</label>
            <select name="member_id" id="member-select" class="form-select" required>
              <option value="">-- Select Member --</option>
              <?php foreach ($members as $m): ?>
                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['full_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="row">
          <?php
          $fields = [
            'tithe' => 'Tithe',
            'hope_channel' => 'Hope Channel',
            'clc_building' => 'CLC Building',
            'cb' => 'CB',
            'cf' => 'CF',
            'pfm' => 'PFM',
          ];
          foreach ($fields as $name => $label): ?>
            <div class="col-md-4 mb-3">
              <label class="form-label"><?= $label ?></label>
              <input type="number" step="0.01" name="<?= $name ?>" class="form-control" value="0">
            </div>
          <?php endforeach; ?>

          <div class="col-md-4 mb-3">
            <label class="form-label">One Offering (Total)</label>
            <input type="number" step="0.01" name="one_offering_total" class="form-control" value="0">
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label">Local Church / Others</label>
            <select name="department_id" id="department-select" class="form-select">
              <option value="">-- Select Department --</option>
              <?php foreach ($departments as $dept): ?>
                <option value="<?= $dept['id'] ?>">
                  <?= htmlspecialchars($dept['name']) . " ({$dept['year']})" ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-6 mb-3" id="dept-amount-box" style="display: none;">
            <label class="form-label">Amount to Add to Department</label>
            <input type="number" step="0.01" name="local_church_others" id="dept-amount" class="form-control" value="0">
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Remarks</label>
          <textarea name="remarks" class="form-control"></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-success">Save Contribution</button>
      </div>
    </form>
  </div>
</div>


<!-- Add Department Modal -->
<div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-labelledby="addDepartmentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST" action="../src/controllers/DepartmentController.php" class="modal-content">
      <input type="hidden" name="action" value="create">
      <div class="modal-header">
        <h5 class="modal-title">Add Department</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body row g-3">
        <div class="col-md-6">
          <label>Department</label>
          <input type="text" name="name" class="form-control" required>
        </div>
        <div class="col-md-3">
          <label>Amount</label>
          <input type="number" step="0.01" name="amount" class="form-control" required>
        </div>
        <div class="col-md-3">
          <label>Percentage</label>
          <input type="number" step="0.01" name="percentage" class="form-control" required>
        </div>
        <div class="col-md-3">
          <label>Year</label>
          <select name="year" class="form-select" required>
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
        <button type="submit" class="btn btn-success">Add</button>
      </div>
    </form>
  </div>
</div>



<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-labelledby="addExpenseModalLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" style="max-width: 48%;">
    <form method="POST" action="../src/controllers/DepartmentExpenseController.php" class="modal-content">
      <input type="hidden" name="action" value="create">
      <div class="modal-header">
        <h5 class="modal-title">Add Expense</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <div class="col-md-3">
          <label>Department</label>
<select name="department_name" id="modal-dept-select" class="form-select" required>
  <option value="">-- Select --</option>
  <?php foreach ($departments as $d): ?>
    <option 
      value="<?= htmlspecialchars(trim($d['name'])) ?>" 
      data-balance="<?= htmlspecialchars($d['amount']) ?>">
      <?= htmlspecialchars($d['name']) ?>
    </option>
  <?php endforeach; ?>
</select>

        </div>
        <div class="col-md-3">
          <label>Current Balance</label>
          <input type="text" id="modal-dept-balance" class="form-control" readonly>
        </div>
        <div class="col-md-3">
          <label>Expenses Date</label>
          <?php
            $minDate = "$currentYear-01-01";
            $maxDate = "$currentYear-12-31";
          ?>
          <input type="date" name="expense_date" class="form-control" required min="<?= $minDate ?>" max="<?= $maxDate ?>">
        </div>
        <div class="col-md-3">
          <label>Member Name</label>
          <select name="member_id" class="form-select" required>
            <option value="">-- Select --</option>
            <?php foreach ($members as $m): ?>
              <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['full_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label>Purpose</label>
          <input type="text" name="purpose" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label>Amount</label>
          <input type="number" step="0.01" name="amount" id="modal-amount-input" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label>Remaining Balance</label>
          <input type="text" id="modal-remaining-balance" class="form-control text-danger fw-bold" readonly>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-success">Add Expense</button>
      </div>
    </form>
  </div>
</div>


<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 38%;">
    <form method="POST" action="../src/controllers/UserController.php" class="modal-content">
      <input type="hidden" name="action" value="create">
      <div class="modal-header">
        <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body row g-3">
        <div class="col-md-6">
          <label>Username</label>
          <input type="text" name="username" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label>Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label>Password</label>
          <input type="text" name="password" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label>Role</label>
          <select name="role" class="form-select" required>
            <option value="">-- Select Role --</option>
            <option value="admin">admin</option>
            <option value="treasurer">treasurer</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-success">Add User</button>
      </div>
    </form>
  </div>
</div>



<!-- Scripts for Select2 and dynamic department field -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const deptSelect = document.getElementById('department-select');
  const deptAmountBox = document.getElementById('dept-amount-box');

  // Show/hide department amount input
  deptSelect?.addEventListener('change', function () {
    deptAmountBox.style.display = this.value ? 'block' : 'none';
  });

  // Initialize Select2 when modal is shown
  const addModal = document.getElementById('addContributionModal');
  addModal.addEventListener('shown.bs.modal', function () {
    $('#member-select').select2({
      placeholder: "-- Select Member --",
      width: '100%',
      dropdownParent: $('#addContributionModal') // prevent z-index issues
    });
  });
});

// expenses 

document.addEventListener('DOMContentLoaded', function () {
  const modalDeptSelect = document.getElementById('modal-dept-select');
  const modalBalanceInput = document.getElementById('modal-dept-balance');
  const modalAmountInput = document.getElementById('modal-amount-input');
  const modalRemainingInput = document.getElementById('modal-remaining-balance');

  function updateModalRemainingBalance() {
    const balance = parseFloat(modalBalanceInput.value) || 0;
    const expense = parseFloat(modalAmountInput.value) || 0;
    modalRemainingInput.value = (balance - expense).toFixed(2);
  }

  modalDeptSelect?.addEventListener('change', function () {
    const selected = this.selectedOptions[0];
    modalBalanceInput.value = parseFloat(selected.getAttribute('data-balance') || 0).toFixed(2);
    updateModalRemainingBalance();
  });

  modalAmountInput?.addEventListener('input', updateModalRemainingBalance);
});

</script>
