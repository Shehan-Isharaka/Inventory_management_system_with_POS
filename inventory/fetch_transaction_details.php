<?php
include('../includes/dbconfig.php');

if (isset($_POST['invoice_id'])) {
    $invoice_number = $_POST['invoice_id']; // like "POS-000001"

    // Fetch invoice main info
    $sql_invoice = $conn->prepare("SELECT invoice_number, sale_date, created_by, paid_amount, change_amount, total_amount 
                                   FROM sales_invoices 
                                   WHERE invoice_number = ?");
    $sql_invoice->bind_param("s", $invoice_number);
    $sql_invoice->execute();
    $result_invoice = $sql_invoice->get_result();

    if ($result_invoice->num_rows > 0) {
        $invoice = $result_invoice->fetch_assoc();

        $sale_date = date('Y-m-d', strtotime($invoice['sale_date']));
        $sale_time = date('H:i:s', strtotime($invoice['sale_date']));

        // Fetch item details
        $sql_details = $conn->prepare("
            SELECT sid.product_id, 
                   p.Product_Name AS product_name, 
                   b.Brand_Name AS brand_name,
                   sid.quantity, sid.unit_price, sid.discount, sid.subtotal
            FROM sales_invoice_details sid
            LEFT JOIN products p ON sid.product_id = p.Product_ID
            LEFT JOIN brands b ON p.Brand_ID = b.Brand_ID
            WHERE sid.sales_invoice_id = ?
        ");
        $sql_details->bind_param("s", $invoice_number);
        $sql_details->execute();
        $result_details = $sql_details->get_result();

        $products = [];
        $subtotal = 0;
        $total_discount = 0;

        while ($row = $result_details->fetch_assoc()) {
            $products[] = [
                'product_id' => $row['product_id'],
                'product_name' => $row['product_name'] . ' (' . $row['brand_name'] . ')',
                'quantity' => $row['quantity'],
                'unit_price' => $row['unit_price'],
                'discount' => $row['discount'],
                'subtotal' => $row['subtotal']
            ];
            $subtotal += $row['subtotal'];
            $total_discount += $row['discount'];
        }

        $response = [
            'success' => true,
            'data' => [
                'invoice_number' => $invoice['invoice_number'],
                'sale_date' => $sale_date . ' ' . $sale_time,
                'cashier' => $invoice['created_by'],  // renamed for frontend consistency
                'products' => $products,
                'subtotal' => $subtotal,
                'total_discount' => $total_discount,
                'total_amount' => $invoice['total_amount'],
                'paid_amount' => $invoice['paid_amount'],
                'change' => $invoice['change_amount']  // renamed for frontend compatibility
            ]
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Invoice not found']);
        exit;
    }
}
?>
