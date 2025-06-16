<?php
session_start();
include('../includes/dbconfig.php');

if (isset($_POST['delete_invoice_number'])) {
    $invoice_number = mysqli_real_escape_string($conn, $_POST['delete_invoice_number']);

    // 1. Verify invoice exists
    $getInvoice = "SELECT invoice_number FROM purchase_invoices WHERE invoice_number = '$invoice_number' LIMIT 1";
    $result = mysqli_query($conn, $getInvoice);

    if (mysqli_num_rows($result) > 0) {
        // invoice_number and purchase_invoice_id are the same (e.g., INVO00001)
        $invoice_id = $invoice_number;

        // 2. Get all purchase details to restore stock
        $getDetails = "SELECT product_id, quantity FROM purchase_invoice_details WHERE purchase_invoice_id = '$invoice_id'";
        $detailsResult = mysqli_query($conn, $getDetails);

        if ($detailsResult && mysqli_num_rows($detailsResult) > 0) {
            while ($detail = mysqli_fetch_assoc($detailsResult)) {
                $product_id = $detail['product_id'];
                $quantity = $detail['quantity'];

                // Restore stock
                $updateStock = "UPDATE products SET Stock_Quantity = Stock_Quantity - $quantity WHERE Product_ID = '$product_id'";
                mysqli_query($conn, $updateStock);
            }
        }

        // 3. Delete purchase details
        $deleteDetails = "DELETE FROM purchase_invoice_details WHERE purchase_invoice_id = '$invoice_id'";
        $detailsDeleted = mysqli_query($conn, $deleteDetails);

        // 4. Delete purchase invoice
        $deleteInvoice = "DELETE FROM purchase_invoices WHERE invoice_number = '$invoice_number'";
        $invoiceDeleted = mysqli_query($conn, $deleteInvoice);

        if ($invoiceDeleted && $detailsDeleted) {
            $_SESSION['success'] = "Invoice $invoice_number and related details deleted. Stock updated.";
        } else {
            $_SESSION['error'] = "Error deleting invoice $invoice_number or its details.";
        }
    } else {
        $_SESSION['error'] = "Invoice number $invoice_number not found.";
    }
} else {
    $_SESSION['error'] = "Invalid request.";
}

header("Location: supplier-invoice.php");
exit;
?>
