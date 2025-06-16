<?php
session_start();
$error = '';
if (isset($_SESSION['login_error'])) {
    $error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - POS System</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css">
  <script src="https://kit.fontawesome.com/a076d05399.js"></script>

  <style>
    html, body {
      height: 100%;
    }

    body {
      background: linear-gradient(135deg, #f1f4f9, #dff1ff);
      display: flex;
      flex-direction: column;
    }

    .main-content {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding-top: 40px;
      padding-bottom: 40px;
    }

    .login-box {
      background: #ffffff;
      border-radius: 20px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.1);
      padding: 30px;
    }

    .login-heading {
      font-weight: 700;
      color: #2c3e50;
    }

    .login-subtext {
      font-size: 14px;
      color: #7f8c8d;
    }

    .form-control {
      height: 45px;
      border-radius: 10px;
    }

    .btn-login {
      border-radius: 10px;
      padding: 10px;
      font-size: 16px;
    }

    .footer {
      background-color: #2c3e50;
      color: #ffffff;
    }

    .brand-logo {
      font-size: 40px;
      font-weight: bold;
      color: #2980b9;
      text-shadow: 1px 1px 2px #bdc3c7;
    }

    .form-links {
      font-size: 13px;
    }

    .img-container {
      max-width: 90%;
    }

    .input-icon {
      position: relative;
    }

    .input-icon i {
      position: absolute;
      top: 13px;
      left: 15px;
      color: #aaa;
    }

    .input-icon input {
      padding-left: 40px;
    }

    .note-box {
      font-size: 14px;
      color: #555;
      background-color: #f9f9f9;
      border-left: 4px solid #2980b9;
      padding: 10px 15px;
      margin-top: 10px;
      border-radius: 5px;
    }
  </style>
</head>
<body>

<div class="main-content">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-md-6 text-center mb-4 mb-md-0">
        <div class="img-container mb-3">
          <img src="images/ad3.svg" class="img-fluid" alt="POS System">
        </div>
        <h1 class="brand-logo">POS SYSTEM</h1>
        <p class="login-subtext">Smart Sales. Smarter Management.</p>
      </div>

      <div class="col-md-6">
        <div class="login-box">
          <h3 class="login-heading text-center mb-3">Welcome Back</h3>
          <div class="note-box text-left">
          This is the Cashier Login Page. After logging in, you can perform sales transactions and view your individual sales reports.
          </div>

          <?php if (!empty($error)): ?>
            <div class="alert alert-danger mt-3">
              <?= htmlspecialchars($error) ?>
            </div>
          <?php endif; ?>

          <form action="pos-logincode.php" method="POST" class="mt-4" novalidate>
            <div class="form-group input-icon">
              <i class="fas fa-envelope"></i>
              <input type="email" id="email" name="email" class="form-control" placeholder="Email address" required>
            </div>

            <div class="form-group input-icon">
              <i class="fas fa-lock"></i>
              <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
            </div>

            <button type="submit" name="login_btn" class="btn btn-primary btn-block btn-login">Login</button>
          </form>

          <p class="text-center form-links mt-4">Don’t have an account? <a href="#">Contact Admin</a></p>
        </div>
      </div>
    </div>
  </div>
</div>

<footer class="footer text-center py-3">
  <div class="container">
    &copy; 2025 POS System. All rights reserved.
  </div>
</footer>

</body>
</html>
