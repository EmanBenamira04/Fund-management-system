<?php
require_once '../config/Database.php';
require_once '../models/Department.php';

function logDebug($message) {
    $logFile = __DIR__ . '/../../debug.log';
    $timestamp = date('Y-m-d H:i:s');
    error_log("[$timestamp] $message\n", 3, $logFile);
}

$db = new Database();
$conn = $db->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    logDebug("Action received: $action");

    try {
        $department = new Department();

if ($action === 'create') {
    $name = trim($_POST['name']);
    $amount = floatval($_POST['amount']);
    $percentage = floatval($_POST['percentage']);
    $year = intval($_POST['year']);            

    logDebug("Creating department: Name=$name, Amount=$amount, Percentage=$percentage, Year=$year");

    $created = $department->create($name, $amount, $percentage, $year);

    if ($created) {
        logDebug("Department successfully created.");
        header("Location: ../../public/departments.php?success=1");
        exit;
    } else {
        logDebug("Failed to create department in model.");
        throw new Exception("Create method returned false");
    }
}

if ($action === 'update') {
    $id = $_POST['id'];
    $name = trim($_POST['name']); // ✅ Add this
    $amount = floatval($_POST['amount']);
    $percentage = floatval($_POST['percentage']);
    $year = intval($_POST['year']);

    logDebug("Updating department: ID=$id, Name=$name, Amount=$amount, Percentage=$percentage, Year=$year");

    // ✅ Pass name to update()
    $updated = $department->update($id, $name, $amount, $percentage, $year);

    if ($updated) {
        logDebug("Department updated successfully.");
        header("Location: ../../public/departments.php?updated=1");
        exit;
    } else {
        throw new Exception("Update method failed");
    }
}



    } catch (Exception $e) {
        logDebug("Error occurred: " . $e->getMessage());
        header("Location: ../../public/departments.php?error=1");
        exit;
    }
}
