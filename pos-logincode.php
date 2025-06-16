<?php
session_start();
include('includes/dbconfig.php');

if (isset($_POST['login_btn'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = "Please enter email and password.";
        header("Location: pos-login.php");
        exit();
    }

    // Fetch user by email
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user || !password_verify($password, $user['password'])) {
        $_SESSION['login_error'] = "Invalid email or password.";
        header("Location: pos-login.php");
        exit();
    }

    // Check user type and active status
    if (strtolower($user['user_type']) !== 'cashier' || (int)$user['status'] !== 1) {
        $_SESSION['login_error'] = "Access denied. Only active cashiers can log in.";
        header("Location: pos-login.php");
        exit();
    }

    // Login successful - set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_type'] = $user['user_type'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];

    header("Location: pos/index.php");
    exit();
}
?>
