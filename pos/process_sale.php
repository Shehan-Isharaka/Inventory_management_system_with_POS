<?php
include('../includes/dbconfig.php');
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['products']) || !is_array($data['products']) || count($data['products']) === 0) {
    echo json_encode(["status" => "error", "message" => "Invalid or incomplete request data."]);
    exit;
}

$query = "SELECT MAX(id) AS last_id FROM sales_invoices";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$last_id = $row['last_id'] ?? 0;
$new_id = $last_id + 1;
$invoice_number = 'POS-' . str_pad($new_id, 6, '0', STR_PAD_LEFT); // <-- formatted invoice number

$sale_date = date('Y-m-d H:i:s');
$created_at = $sale_date;
$created_by = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

$paid_amount = floatval($data['paid_amount'] ?? 0);
$total_amount = floatval($data['total_amount'] ?? 0);
$balance = floatval($data['balance'] ?? 0);

$products = $data['products'];
$total_discount = 0;

foreach ($products as $p) {
    if (!isset($p['product_id'], $p['quantity'], $p['price'], $p['discount'], $p['subtotal'])) {
        echo json_encode(["status" => "error", "message" => "Product data incomplete."]);
        exit;
    }
    $total_discount += floatval($p['discount']);
}

$conn->begin_transaction();

try {
    // Insert into sales_invoices
    $stmt = $conn->prepare("INSERT INTO sales_invoices 
        (invoice_number, sale_date, total_discount, total_amount, paid_amount, balance, created_by, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) throw new Exception("Sales invoice prepare failed: " . $conn->error);

    $stmt->bind_param("ssddddss", $invoice_number, $sale_date, $total_discount, $total_amount, $paid_amount, $balance, $created_by, $created_at);
    if (!$stmt->execute()) throw new Exception("Sales invoice execute failed: " . $stmt->error);

    $stmt->close();

    // ✅ Use formatted $invoice_number as sales_invoice_id (VARCHAR)
    $detailStmt = $conn->prepare("INSERT INTO sales_invoice_details 
        (sales_invoice_id, product_id, quantity, unit_price, discount, subtotal, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");

    $stockCheckStmt = $conn->prepare("SELECT Stock_Quantity FROM products WHERE Product_ID = ?");
    $stockUpdateStmt = $conn->prepare("UPDATE products SET Stock_Quantity = Stock_Quantity - ? WHERE Product_ID = ?");

    if (!$detailStmt || !$stockCheckStmt || !$stockUpdateStmt) {
        throw new Exception("Preparation of statements failed.");
    }

    foreach ($products as $p) {
        $product_id = $p['product_id'];
        $quantity = (int)$p['quantity'];
        $unit_price = floatval($p['price']);
        $discount = floatval($p['discount']);
        $subtotal = floatval($p['subtotal']);

        $stockCheckStmt->bind_param("s", $product_id);
        $stockCheckStmt->execute();
        $stockResult = $stockCheckStmt->get_result();
        if ($stockResult->num_rows === 0) {
            throw new Exception("Product not found: $product_id");
        }
        $row = $stockResult->fetch_assoc();
        if ((int)$row['Stock_Quantity'] < $quantity) {
            throw new Exception("Insufficient stock for product: $product_id");
        }

        // ✅ Use formatted invoice number as sales_invoice_id
        $detailStmt->bind_param("ssiddds", $invoice_number, $product_id, $quantity, $unit_price, $discount, $subtotal, $created_at);
        if (!$detailStmt->execute()) {
            throw new Exception("Detail insert failed: " . $detailStmt->error);
        }

        $stockUpdateStmt->bind_param("is", $quantity, $product_id);
        if (!$stockUpdateStmt->execute()) {
            throw new Exception("Stock update failed: " . $stockUpdateStmt->error);
        }
    }

    $detailStmt->close();
    $stockCheckStmt->close();
    $stockUpdateStmt->close();

    $conn->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Sale recorded successfully.",
        "invoice_number" => $invoice_number,
        "sale_date" => $sale_date,
        "products" => $products,
        "total_amount" => $total_amount,
        "total_discount" => $total_discount,
        "paid_amount" => $paid_amount,
        "balance" => $balance,
    ]);

} catch (Exception $e) {
    $conn->rollback();
    error_log("Sale transaction failed: " . $e->getMessage(), 3, "../logs/sales_error.log");

    echo json_encode([
        "status" => "error",
        "message" => "Transaction failed: " . $e->getMessage()
    ]);
}
?>
 