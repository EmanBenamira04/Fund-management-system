<?php
require_once '../config/Database.php';

// 🔍 Debug Logger
function debug_log($message) {
    $timestamp = date("Y-m-d H:i:s");
    file_put_contents(__DIR__ . '/../../debug.log', "[$timestamp] $message\n", FILE_APPEND);
}

$db = new Database();
$conn = $db->connect();

$action = $_POST['action'] ?? null;

if (!$action) {
    debug_log("❌ No action provided.");
    die("Invalid request.");
}

debug_log("✅ Action received: $action");

if ($action === 'create') {
    debug_log("🟢 Starting user 'create'...");

    $username = $_POST['username'];
    $email = $_POST['email']; // Added
    $password = $_POST['password'];
    $role = $_POST['role'];

    debug_log("Username: $username, Role: $role");

    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
    $result = $stmt->execute([$username, $email, $password, $role]);

    debug_log("Create result: " . var_export($result, true));

    header("Location: ../../public/users.php?created=1");
    exit;
}

if ($action === 'update') {
    debug_log("🟡 Starting user 'update'...");

    $id = $_POST['id'];
    $username = $_POST['username'];
    $email = $_POST['email']; // Added
    $password = $_POST['password'];
    $role = $_POST['role'];

    debug_log("ID: $id, Username: $username, Role: $role");
    
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ?, role = ? WHERE id = ?");
    $result = $stmt->execute([$username, $email, $password, $role, $id]);

    debug_log("Update result: " . var_export($result, true));

    header("Location: ../../public/users.php?updated=1");
    exit;
}
