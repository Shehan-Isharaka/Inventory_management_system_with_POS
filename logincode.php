<?php
session_start();
include('includes/dbconfig.php');

$error = '';

if (isset($_POST['login_btn'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) && empty($password)) {
        $error = "Email and password are required.";
    } elseif (empty($email)) {
        $error = "Please enter your email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (empty($password)) {
        $error = "Please enter your password.";
    } else {
        $email_safe = mysqli_real_escape_string($conn, $email);

        $sql = "SELECT * FROM users WHERE email='$email_safe' LIMIT 1";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);

            if ($user['user_type'] === 'cashier') {
                // Restrict cashier from logging in here
                $error = "Cashiers must login through the POS system. Please use the POS login page.";
            } elseif (!in_array($user['user_type'], ['admin', 'stock_keeper'])) {
                // Restrict other user types
                $error = "Access denied. Your user role is not authorized to login here.";
            } elseif (password_verify($password, $user['password'])) {
                // Login success for admin and stock_keeper
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['username'] = $user['username'];

                // Redirect based on role
                switch ($user['user_type']) {
                    case 'admin':
                        header("Location: inventory/index.php");
                        break;
                    case 'stock_keeper':
                        header("Location: stock/index.php");
                        break;
                }
                exit();
            } else {
                $error = "The password you entered is incorrect.";
            }
        } else {
            $error = "No account found with that email address.";
        }
    }

    if (!empty($error)) {
        $_SESSION['login_error'] = $error;
        header("Location: login.php");
        exit();
    }
}
