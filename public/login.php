<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../src/config/Database.php';
require_once '../helpers/jwt.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../src/auth/Auth.php';

    $username = $_POST['username'];
    $password = $_POST['password'];

    if (login($username, $password)) {
        $db = new Database();
        $conn = $db->connect();
        $stmt = $conn->prepare("SELECT id, username, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $payload = [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role']
        ];
        $_SESSION['jwt_token'] = generate_jwt($payload);
        $_SESSION['user'] = $user;
        $_SESSION['login_success'] = true;

        header("Location: login.php");
        exit;
    } else {
        header("Location: login.php?error=Invalid credentials");
        exit;
    }
}

if (isset($_SESSION['user']) && !isset($_SESSION['login_success'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Login - Church Fund</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <style>
    * { box-sizing: border-box; }
    body {
      margin: 0; padding: 0;
      height: 100vh;
      font-family: "times new roman", sans-serif;
      background: #dbeafe;
      display: flex; justify-content: center; align-items: center;
    }
    .wrapper {
      display: flex; width: 900px; max-width: 95%;
      background: linear-gradient(0deg, #fff, #f4f7fb);
      border-radius: 30px;
      box-shadow: rgba(0, 0, 0, 0.5) 0 30px 40px -20px;
      overflow: hidden;
    }
    .login-box {
      flex: 1;
      padding: 40px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      background-image: url('../assets/images/222.png');
      background-size: cover;
      background-position: center;
      color: white;
      position: relative;
    }
    .login-box::before {
      content: '';
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      z-index: 0;
    }
    .login-box > * { position: relative; z-index: 1; }
    .login-box h1, .login-box h2, .login-box h6 {
      text-align: center;
      color: rgb(16, 137, 211);
    }
    .login-box h1 { font-size: 32px; font-weight: 900; }
    .login-box h6 { font-size: 15px; font-weight: 900; margin-bottom: 30px; }
    .login-box h2 { font-size: 32px; font-weight: 900; margin-bottom: 30px; }
    .form .input {
      width: 100%;
      background: rgba(255, 255, 255, 0.72);
      border: none;
      padding: 15px 20px;
      border-radius: 20px;
      margin-top: 15px;
      color: black;
      border-inline: 2px solid rgba(255, 255, 255, 0.5);
    }
    .form .input::placeholder {
      color: rgba(52, 51, 51, 0.7);
    }
    .form .input:focus {
      outline: none;
      border-inline: 2px solid #12b1d1;
      background: rgba(205, 199, 199, 0.3);
    }
    .form .forgot-password {
      display: block; margin-top: 10px; font-size: 12px; text-align: left;
    }
    .form .forgot-password a {
      color: #0099ff; text-decoration: none;
    }
    .form .login-button {
      display: block; width: 100%; font-weight: bold;
      background: linear-gradient(45deg, rgb(16, 137, 211), rgb(18, 177, 209));
      color: white; padding: 15px; margin: 25px 0;
      border-radius: 20px; border: none;
      box-shadow: rgba(133, 189, 215, 0.5) 0 20px 10px -15px;
      transition: all 0.2s ease-in-out;
    }
    .form .login-button:hover { transform: scale(1.03); }
    .copyright {
      text-align: center;
      font-size: 11px;
      color: #888;
    }
    .image-box {
      flex: 1;
      background: url('../assets/images/3d.png') no-repeat center;
      background-size: cover;
      min-height: 400px;
    }
    @media (max-width: 768px) {
      .wrapper { flex-direction: column; width: 95%; }
      .image-box { height: 200px; background-position: center top; }
    }
    .fade-out { opacity: 0; transition: opacity 1s ease-out; }
  </style>
</head>
<body>

<div class="wrapper">
  <div class="login-box">
    <h1>Seventh Day Adventist</h1>
    <h6>Fund Management System</h6>
    <h2>Sign In</h2>

    <?php if (isset($_GET['error'])): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <form class="form" method="POST" action="">
      <input type="text" name="username" placeholder="Username" class="input" required />
      <input type="password" name="password" placeholder="Password" class="input" required />
      <span class="forgot-password"><a href="forgot-password.php">Forgot Password?</a></span>
      <input type="submit" value="Sign In" class="login-button" />
    </form>

    <div class="copyright">Seventh Day Adventist © 2025</div>
  </div>

  <div class="image-box"></div>
</div>

<!-- ✅ Login Success Modal -->
<?php if (isset($_SESSION['login_success']) && isset($_SESSION['user'])): ?>
  <div class="modal fade show" id="loginSuccessModal" tabindex="-1" style="display: block; background-color: rgba(0, 0, 0, 0.5);" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow" style="animation: fadeIn 0.4s ease-out;">
        <div class="modal-body text-center p-5">
          <div class="text-success mb-3">
            <i class="bi bi-check-circle-fill" style="font-size: 3rem;"></i>
          </div>
          <h4 class="mb-3 fw-bold">Welcome, <?= htmlspecialchars($_SESSION['user']['username']) ?>!</h4>
          <p class="text-muted mb-0">You have successfully logged in.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap Icons CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>

  <script>
    setTimeout(() => {
      window.location.href = 'index.php';
    }, 1500);
  </script>
  <?php unset($_SESSION['login_success']); ?>
<?php endif; ?>


<script>
  window.addEventListener('DOMContentLoaded', () => {
    const alertBox = document.querySelector('.alert-danger');
    if (alertBox) {
      setTimeout(() => alertBox.classList.add('fade-out'), 3000);
      setTimeout(() => alertBox.remove(), 4000);
    }
  });
</script>
</body>
</html>
