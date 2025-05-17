<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputOtp = $_POST['otp'] ?? '';
    $sessionOtp = $_SESSION['otp'] ?? null;
    $otpExpiry = $_SESSION['otp_expires'] ?? 0;

    if (!$sessionOtp || time() > $otpExpiry) {
        header("Location: ../../public/verify-otp.php?error=OTP expired");
        exit;
    }

    if ($inputOtp != $sessionOtp) {
        header("Location: ../../public/verify-otp.php?error=Invalid OTP");
        exit;
    }

    // OTP is valid — redirect to reset password form
    header("Location: ../../public/reset-password.php");
    exit;
}
