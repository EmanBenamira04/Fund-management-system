<?php
require_once '../config/Database.php';
require_once '../models/Member.php';

// 🔍 Debug Logger
function debug_log($message) {
    $timestamp = date("Y-m-d H:i:s");
    file_put_contents(__DIR__ . '/../../debug.log', "[$timestamp] $message\n", FILE_APPEND);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'create') {
    debug_log("🟢 Member 'create' action started");

    $fullName = trim($_POST['full_name']);
    debug_log("Full Name: $fullName");

    $member = new Member();
    $result = $member->create($fullName);

    debug_log("Create result: " . var_export($result, true));

    if ($result) {
        debug_log("✅ Member created successfully");
        header("Location: ../../public/members.php?success=1");
    } else {
        debug_log("❌ Failed to create member");
        header("Location: ../../public/add-member.php?error=1");
    }
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'update') {
    debug_log("🟡 Member 'update' action started");

    $id = $_POST['id'];
    $fullName = trim($_POST['full_name']);
    debug_log("ID: $id, Full Name: $fullName");

    $member = new Member();
    $result = $member->update($id, $fullName);

    debug_log("Update result: " . var_export($result, true));

    if ($result) {
        debug_log("✅ Member updated successfully");
        header("Location: ../../public/members.php?updated=1");
    } else {
        debug_log("❌ Failed to update member");
        header("Location: ../../public/edit-member.php?id=$id&error=1");
    }
    exit;
}
