<?php
header('Content-Type: application/json');

// Simulate DB connection (replace with your own)
$conn = new mysqli("localhost", "root", "", "your_database");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'DB connection failed']);
    exit;
}

$term = isset($_GET['term']) ? $_GET['term'] : '';

$sql = "SELECT id, product_name FROM products WHERE product_name LIKE ? LIMIT 10";
$stmt = $conn->prepare($sql);
$likeTerm = "%$term%";
$stmt->bind_param("s", $likeTerm);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = [
        'id' => $row['id'],
        'text' => $row['product_name']
    ];
}

echo json_encode($products);
$conn->close();
