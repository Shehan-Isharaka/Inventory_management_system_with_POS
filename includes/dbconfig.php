<?php
// Enable MySQLi error reporting with exceptions
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Database credentials
$host = "localhost";
$user = "root";
$password = "";
$database = "inventory_pos";

try {
    // Create connection
    $conn = mysqli_connect($host, $user, $password, $database);

    // Set character set to UTF-8
    mysqli_set_charset($conn, "utf8mb4");

} catch (mysqli_sql_exception $e) {
    // Log the error and show a friendly message
    error_log("Database Connection Failed: " . $e->getMessage());
    die("We're currently experiencing technical difficulties. Please try again later.");
}

?>