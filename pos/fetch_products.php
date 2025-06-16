<?php
include('../includes/dbconfig.php');

$searchTerm = isset($_POST['searchTerm']) ? $_POST['searchTerm'] : '';

$query = "SELECT Product_ID, Product_Name, Selling_Price FROM products WHERE Product_Name LIKE ? LIMIT 15";

$stmt = $conn->prepare($query);
$likeTerm = "%$searchTerm%";
$stmt->bind_param("s", $likeTerm);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

echo json_encode($products);
?>
