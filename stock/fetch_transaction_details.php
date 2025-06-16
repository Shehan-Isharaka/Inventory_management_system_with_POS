<?php
include('../includes/dbconfig.php');

if (isset($_POST['invoice_id'])) {
    $invoice_id = intval($_POST['invoice_id']);

    // Fetch main invoice info
    $sql_invoice = $conn->prepare("SELECT id, invoice_number, sale_date, created_by, paid_amount, change_amount, total_amount FROM sales_invoices WHERE id = ?");
    $sql_invoice->bind_param("i", $invoice_id);
    $sql_invoice->execute();
    $result_invoice = $sql_invoice->get_result();

    if ($result_invoice->num_rows > 0) {
        $invoice = $result_invoice->fetch_assoc();

        $sale_date = date('Y-m-d', strtotime($invoice['sale_date']));
        $sale_time = date('H:i:s', strtotime($invoice['sale_date']));

        // Fetch invoice details
        $sql_details = $conn->prepare("
            SELECT sid.product_id, p.Product_Name AS product_name, b.Brand_Name AS brand_name,
                   sid.quantity, sid.unit_price, sid.discount, sid.subtotal
            FROM sales_invoice_details sid
            LEFT JOIN products p ON sid.product_id = p.Product_ID
            LEFT JOIN brands b ON p.Brand_ID = b.Brand_ID
            WHERE sid.sales_invoice_id = ?
        ");
        $sql_details->bind_param("i", $invoice_id);
        $sql_details->execute();
        $result_details = $sql_details->get_result();

        $products = [];
        $subtotal = 0;
        $total_discount = 0;

        while ($row = $result_details->fetch_assoc()) {
            $products[] = $row;
            $subtotal += $row['subtotal'];
            $total_discount += $row['discount'];
        }

        $response = [
            'success' => true,
            'data' => [
                'invoice_number' => $invoice['invoice_number'],
                'sale_date' => $sale_date,
                'sale_time' => $sale_time,
                'cashier' => $invoice['created_by'],
                'products' => $products,
                'subtotal' => $subtotal,
                'total_discount' => $total_discount,
                'total_amount' => $invoice['total_amount'],
                'paid_amount' => $invoice['paid_amount'],
                'change' => $invoice['change_amount']
            ]
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Invoice not found']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No invoice ID provided']);
    exit;
}
?>
