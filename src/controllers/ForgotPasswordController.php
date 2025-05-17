<?php
session_start();
require_once '../config/Database.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php'; // Make sure PHPMailer is installed via Composer

function logDebug($message) {
    $logFile = __DIR__ . '/../../debug.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    if (!$email) {
        header("Location: ../../public/forgot-password.php?error=Email is required");
        exit;
    }

    // 🔍 Look up user by email
    $db = new Database();
    $conn = $db->connect();
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        logDebug("Attempted password reset for non-existent email: $email");
        header("Location: ../../public/forgot-password.php?error=Email not found");
        exit;
    }

    // 🔐 Generate OTP and store in session
    $otp = rand(100000, 999999);
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_email'] = $email;
    $_SESSION['otp_expires'] = time() + (5 * 60); // 5 minutes

    // 📧 Send OTP via email
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'hermanbenamira33@gmail.com';         // Replace with your Gmail
        $mail->Password   = 'meft awnf rinm czyl';            // Replace with Gmail App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('your_email@gmail.com', 'Church Fund');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code - Church Fund';
        $mail->Body    = "<p>Your OTP code is:</p><h2 style='color:#007bff;'>$otp</h2><p>This code will expire in 5 minutes.</p>";

        $mail->send();

        logDebug("OTP sent successfully to $email: $otp");

        header("Location: ../../public/verify-otp.php");
        exit;

    } catch (Exception $e) {
        logDebug("Email failed to $email: " . $mail->ErrorInfo);
        header("Location: ../../public/forgot-password.php?error=Failed to send OTP");
        exit;
    }
}
