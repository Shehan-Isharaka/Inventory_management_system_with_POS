<?php
include('../includes/dbconfig.php');

$term = $_GET['term'];
$query = "SELECT Product_ID, Product_Name FROM products WHERE Product_Name LIKE '%$term%' LIMIT 10";
$result = mysqli_query($conn, $query);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = [
        'id' => $row['Product_ID'],
        'text' => $row['Product_Name']
    ];
}

echo json_encode($data);
?>
