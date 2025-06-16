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
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Inventory Login</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css">

    <!-- Font Awesome for icons -->
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>

    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa, #dff1ff);
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

        .col-md-6.text-center img {
            max-width: 500px;
            height: auto; 
            margin-bottom: 30px;
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

        .footer a {
            color: #ffffff;
        }

        .brand-logo {
            font-size: 40px;
            font-weight: bold;
            color: #2980b9;
            text-shadow: 1px 1px 2px #bdc3c7;
        }

        .note-box {
            font-size: 14px;
            background-color: #f0f3f5;
            padding: 10px 15px;
            border-left: 4px solid #2980b9;
            border-radius: 6px;
            margin-bottom: 20px;
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
    </style>
</head>
<body>

<section class="vh-100 d-flex align-items-center justify-content-center">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-md-6 text-center">
                <img src="images/login.svg" class="img-fluid mb-4" alt="Inventory Login">
                <h1 class="brand-logo">INVENTORY SYSTEM</h1>
                <p class="login-subtext">Efficient Stock. Easy Management.</p>
            </div>
            <div class="col-md-6">
                <div class="login-box">
                    <h3 class="login-heading text-center mb-3">Inventory Login</h3>
                    <div class="note-box">
                        This is the <strong>Inventory Management Login Page</strong>. Please log in to access the inventory system, manage products, and track stock efficiently.
                    </div>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form action="logincode.php" method="POST">
                        <div class="form-group input-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" class="form-control" placeholder="Enter Email" required>
                        </div>

                        <div class="form-group input-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" class="form-control" placeholder="Password" required>
                        </div>

                        <div class="form-group d-flex justify-content-between align-items-center">
                            <a href="pos-login.php" class="text-primary font-weight-bold">Login as Cashier</a>
                        </div>

                        <button type="submit" name="login_btn" class="btn btn-primary btn-block btn-login">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="footer text-center py-3 fixed-bottom">
    <div class="container">
        &copy; 2025 Inventory Management System. All rights reserved.
    </div>
</footer>

</body>
</html>
