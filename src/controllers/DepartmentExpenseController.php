<?php
require_once '../config/Database.php';
require_once '../models/DepartmentExpense.php';

// 🔍 Debug Logger
function debug_log($message) {
    $timestamp = date("Y-m-d H:i:s");
    file_put_contents(__DIR__ . '/../../debug.log', "[$timestamp] $message\n", FILE_APPEND);
}

$db = new Database();
$conn = $db->connect();
$currentYear = date('Y');

// CREATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'create') {
    debug_log("🟢 Starting department expense 'create'...");

    $departmentName = trim(rtrim($_POST['department_name'], ','));
    $amount = floatval($_POST['amount']);

    debug_log("Department: $departmentName, Amount: $amount");

    // Get department ID
    $stmt = $conn->prepare("SELECT id FROM departments WHERE name = ?");
    $stmt->execute([$departmentName]);
    $deptId = $stmt->fetchColumn();

    if (!$deptId) {
        debug_log("❌ Department not found: $departmentName");
        die("Department not found: '$departmentName'");
    }

    $data = [
        'department_name' => $departmentName,
        'member_id' => $_POST['member_id'],
        'purpose' => trim($_POST['purpose']),
        'amount' => $amount,
        'expense_date' => $_POST['expense_date']
    ];

    $expenseModel = new DepartmentExpense();
    $expenseModel->create($data);
    debug_log("✅ Expense created for $departmentName");

    // Deduct from department_allocations
    $stmt = $conn->prepare("
        UPDATE department_allocations
        SET amount = amount - ?
        WHERE department_id = ? AND year = ?
    ");
    $stmt->execute([$amount, $deptId, $currentYear]);
    debug_log("💸 Deducted $amount from allocation for $departmentName (ID $deptId)");

    header('Location: ../../public/expenses.php?success=1');
    exit;
}

// UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'update') {
    debug_log("🟡 Starting department expense 'update'...");

    $id = $_POST['id'];
    $newDept = trim($_POST['department_name']);
    $memberId = $_POST['member_id'];
    $purpose = trim($_POST['purpose']);
    $newAmount = floatval($_POST['amount']);
    $date = $_POST['expense_date'];

    debug_log("Update ID: $id, New Dept: $newDept, New Amount: $newAmount");

    // Get previous data
    $stmt = $conn->prepare("SELECT department_name, amount FROM department_expenses WHERE id = ?");
    $stmt->execute([$id]);
    $old = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$old) {
        debug_log("❌ Expense record not found for ID $id");
        die("Original expense not found.");
    }

    $oldDept = $old['department_name'];
    $oldAmount = floatval($old['amount']);

    // Get department IDs
    $stmt = $conn->prepare("SELECT id FROM departments WHERE name = ?");
    $stmt->execute([$oldDept]);
    $oldDeptId = $stmt->fetchColumn();

    $stmt->execute([$newDept]);
    $newDeptId = $stmt->fetchColumn();

    if (!$oldDeptId || !$newDeptId) {
        debug_log("❌ One of the departments not found.");
        die("One of the departments not found.");
    }

    // Update the expense entry
    $stmt = $conn->prepare("
        UPDATE department_expenses 
        SET department_name = ?, member_id = ?, purpose = ?, amount = ?, expense_date = ?
        WHERE id = ?
    ");
    $stmt->execute([$newDept, $memberId, $purpose, $newAmount, $date, $id]);
    debug_log("✅ Expense record updated");

    // Adjust allocations
    if ($oldDeptId !== $newDeptId) {
        // Return amount to old dept
        $stmt = $conn->prepare("
            UPDATE department_allocations
            SET amount = amount + ?
            WHERE department_id = ? AND year = ?
        ");
        $stmt->execute([$oldAmount, $oldDeptId, $currentYear]);
        debug_log("↩️ Returned $oldAmount to $oldDept");

        // Deduct from new dept
        $stmt = $conn->prepare("
            UPDATE department_allocations
            SET amount = amount - ?
            WHERE department_id = ? AND year = ?
        ");
        $stmt->execute([$newAmount, $newDeptId, $currentYear]);
        debug_log("↪️ Deducted $newAmount from $newDept");
    } else {
        // Same dept: adjust difference
        $diff = $oldAmount - $newAmount;
        $stmt = $conn->prepare("
            UPDATE department_allocations
            SET amount = amount + ?
            WHERE department_id = ? AND year = ?
        ");
        $stmt->execute([$diff, $newDeptId, $currentYear]);
        debug_log("🔁 Adjusted $diff for $newDept");
    }

    header('Location: ../../public/expenses.php?updated=1');
    exit;
}
