<?php
session_start();
require_once '../config/Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $email = $_SESSION['otp_email'] ?? null;

    if (!$email) {
        header("Location: ../../public/login.php");
        exit;
    }

    if (strlen($password) < 6) {
        header("Location: ../../public/reset-password.php?error=Password too short");
        exit;
    }

    if ($password !== $confirm) {
        header("Location: ../../public/reset-password.php?error=Passwords do not match");
        exit;
    }

    $db = new Database();
    $conn = $db->connect();

    // ❌ NOT hashed for demo
    $plainPassword = $password;

    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->execute([$plainPassword, $email]);

    unset($_SESSION['otp'], $_SESSION['otp_email'], $_SESSION['otp_expires']);

    header("Location: ../../public/reset-password.php?success=Password updated successfully");
    exit;
}
