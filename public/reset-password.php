<?php
session_start();
if (!isset($_SESSION['otp_email'])) {
    header("Location: login.php");
    exit;
}
$error = $_GET['error'] ?? null;
$success = $_GET['success'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password</title>
  <style>
    body {
      background: #e6f0fa;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      font-family: 'Segoe UI', sans-serif;
    }
    .container {
      background: linear-gradient(to top, #ffffff, #f4f7fb);
      border-radius: 30px;
      padding: 40px;
      width: 350px;
      box-shadow: 0 25px 50px -20px rgba(0, 0, 0, 0.1);
      text-align: center;
    }
    h2 {
      font-size: 26px;
      font-weight: 800;
      margin-bottom: 20px;
      color: #1179e0;
    }
    input[type="password"] {
      width: 100%;
      padding: 14px;
      border: none;
      border-radius: 15px;
      box-shadow: 0 5px 10px rgba(0, 174, 255, 0.15);
      margin-bottom: 10px;
      font-size: 16px;
    }
    button, .back-button {
      background: linear-gradient(45deg, #168fd9, #14c0dd);
      color: white;
      font-weight: bold;
      border: none;
      padding: 14px;
      border-radius: 15px;
      width: 100%;
      margin-top: 10px;
      cursor: pointer;
    }
    .back-button {
      background: transparent;
      border: 1px solid #1179e0;
      color: #1179e0;
    }
    .alert {
      padding: 10px;
      margin-bottom: 10px;
      border-radius: 8px;
      font-size: 14px;
    }
    .alert-error {
      background: #f8d7da;
      color: #842029;
    }
    .alert-success {
      background: #d1e7dd;
      color: #0f5132;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Reset Password</h2>
    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="POST" action="../src/controllers/ResetPasswordController.php">
      <input type="password" name="password" placeholder="New Password" required>
      <input type="password" name="confirm_password" placeholder="Confirm Password" required>
      <button>Reset</button>
    </form>
    <a href="login.php"><button class="back-button">Back to Login</button></a>
  </div>
</body>
</html>
