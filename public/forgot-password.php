<?php
session_start();
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password - Church Fund</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #f2f6fa;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      font-family: 'Segoe UI', sans-serif;
    }
    .container {
      max-width: 350px;
      background: linear-gradient(0deg, white 0%, #f4f7fb 100%);
      border-radius: 40px;
      padding: 25px 35px;
      border: 5px solid #fff;
      box-shadow: rgba(133, 189, 215, 0.8) 0 30px 30px -20px;
    }
    .heading {
      text-align: center;
      font-weight: 900;
      font-size: 26px;
      color: #1089d3;
    }
    .input {
      width: 100%;
      background: white;
      border: none;
      padding: 15px 20px;
      border-radius: 20px;
      margin-top: 15px;
      box-shadow: #cff0ff 0 10px 10px -5px;
    }
    .input:focus {
      outline: none;
      border: 2px solid #12b1d1;
    }
    .login-button,
    .back-button {
      display: block;
      width: 100%;
      font-weight: bold;
      padding: 15px;
      margin-top: 10px;
      border-radius: 20px;
      border: none;
      transition: 0.2s ease-in-out;
    }
    .login-button {
      background: linear-gradient(45deg, #1089d3 0%, #12b1d1 100%);
      color: white;
    }
    .back-button {
      background: transparent;
      border: 1px solid #1089d3;
      color: #1089d3;
    }
    .login-button:hover,
    .back-button:hover {
      transform: scale(1.03);
    }
    .alert {
      font-size: 14px;
      padding: 10px;
      margin-top: 10px;
    }
  </style>
</head>
<body>
<div class="container">
  <div class="heading">Forgot Password</div>

  <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
  <?php elseif (isset($_GET['success'])): ?>
    <div class="alert alert-success">OTP sent to your email.</div>
  <?php endif; ?>

  <form method="POST" action="../src/controllers/ForgotPasswordController.php">
    <input type="hidden" name="action" value="send_otp">
    <input type="email" name="email" placeholder="Enter your email" class="input" required>
    <button type="submit" class="login-button">Send OTP</button>
  </form>

  <a href="login.php"><button class="back-button">Back to Login</button></a>
</div>
</body>
</html>
