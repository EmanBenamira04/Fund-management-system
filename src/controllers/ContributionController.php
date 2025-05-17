<?php
require_once '../config/Database.php';
require_once '../models/Contribution.php';

// 🔍 Debug Logger
function debug_log($message) {
    $timestamp = date("Y-m-d H:i:s");
    file_put_contents(__DIR__ . '/../../debug.log', "[$timestamp] $message\n", FILE_APPEND);
}

function sanitizeMoney($value) {
    return is_numeric($value) ? floatval($value) : 0;
}

$db = new Database();
$conn = $db->connect();
$contribution = new Contribution();

$action = $_POST['action'] ?? null;
if (!$action) {
    debug_log("❌ No action provided in request.");
    die("Invalid action");
}

debug_log("✅ Action received: $action");

// ➕ CREATE
if ($action === 'create') {
    debug_log("🟢 Starting 'create' action...");

    $total = sanitizeMoney($_POST['one_offering_total'] ?? 0);
    $split = $total / 2;

    $data = [
        'member_id' => $_POST['member_id'],
        'date' => $_POST['date'],
        'or_number' => $_POST['or_number'] ?? null,
        'tithe' => sanitizeMoney($_POST['tithe']),
        'hope_channel' => sanitizeMoney($_POST['hope_channel']),
        'clc_building' => sanitizeMoney($_POST['clc_building']),
        'one_offering_clc' => $split,
        'one_offering_church' => $split,
        'cb' => sanitizeMoney($_POST['cb']),
        'cf' => sanitizeMoney($_POST['cf']),
        'pfm' => sanitizeMoney($_POST['pfm']),
        'local_church_others' => sanitizeMoney($_POST['local_church_others']),
        'department_id' => !empty($_POST['department_id']) ? intval($_POST['department_id']) : null,
        'remarks' => $_POST['remarks'] ?? ''
    ];

    $result = $contribution->create($data);
    debug_log("📝 Contribution create result: " . var_export($result, true));

    // ✅ Update department_allocations instead of departments.amount
    if (!empty($data['department_id']) && $data['local_church_others'] > 0) {
        $year = date('Y');
        $stmt = $conn->prepare("UPDATE department_allocations SET amount = amount + ? WHERE department_id = ? AND year = ?");
        $stmt->execute([$data['local_church_others'], $data['department_id'], $year]);
        debug_log("✅ Updated department_allocations (dept_id={$data['department_id']}, year=$year) with +{$data['local_church_others']}");
    }

    debug_log("✅ 'create' action complete. Redirecting...");
    header("Location: ../../public/contributions.php?success=1");
    exit;
}

// 🔁 UPDATE
if ($action === 'update') {
    debug_log("🟡 Starting 'update' action...");

    $id = $_POST['id'] ?? null;
    if (!$id) {
        debug_log("❌ Missing contribution ID.");
        die("Missing contribution ID.");
    }

    $newAmount = sanitizeMoney($_POST['local_church_others']);
    $newDeptId = !empty($_POST['department_id']) ? intval($_POST['department_id']) : null;

    $oldAmount = sanitizeMoney($_POST['prev_local_amount']);
    $oldDeptId = !empty($_POST['prev_department_id']) ? intval($_POST['prev_department_id']) : null;

    $year = date('Y');

    // ✅ Deduct from old department_allocations
    if ($oldDeptId && $oldAmount > 0) {
        $stmt = $conn->prepare("UPDATE department_allocations SET amount = amount - ? WHERE department_id = ? AND year = ?");
        $stmt->execute([$oldAmount, $oldDeptId, $year]);
        debug_log("↘️ Deducted $oldAmount from department_allocations (dept_id=$oldDeptId, year=$year)");
    }

    // ✅ Add to new department_allocations
    if ($newDeptId && $newAmount > 0) {
        $stmt = $conn->prepare("UPDATE department_allocations SET amount = amount + ? WHERE department_id = ? AND year = ?");
        $stmt->execute([$newAmount, $newDeptId, $year]);
        debug_log("↗️ Added $newAmount to department_allocations (dept_id=$newDeptId, year=$year)");
    }

    $one_offering_total = sanitizeMoney($_POST['one_offering_total'] ?? 0);
    $split = $one_offering_total / 2;

    $data = [
        'id' => $id,
        'member_id' => $_POST['member_id'],
        'date' => $_POST['date'],
        'tithe' => sanitizeMoney($_POST['tithe']),
        'hope_channel' => sanitizeMoney($_POST['hope_channel']),
        'clc_building' => sanitizeMoney($_POST['clc_building']),
        'one_offering_clc' => $split,
        'one_offering_church' => $split,
        'cb' => sanitizeMoney($_POST['cb']),
        'cf' => sanitizeMoney($_POST['cf']),
        'pfm' => sanitizeMoney($_POST['pfm']),
        'local_church_others' => $newAmount,
        'department_id' => $newDeptId,
        'remarks' => $_POST['remarks'] ?? ''
    ];

    $result = $contribution->update($data);
    debug_log("📝 Contribution update result: " . var_export($result, true));

    if ($result) {
        debug_log("✅ 'update' action complete. Redirecting...");
        header("Location: ../../public/contributions.php?updated=1");
    } else {
        debug_log("❌ Failed to update contribution.");
        echo "Failed to update contribution.";
    }

    exit;
}
