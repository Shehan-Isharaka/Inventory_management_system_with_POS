<?php
include('../includes/dbconfig.php');

if (isset($_POST['id'])) {
    $invoice_id = $_POST['id']; // numeric ID

    // Fetch invoice + supplier info + invoice_number
    $query = "SELECT pi.*, s.supplier_name, s.email, s.phone_number, s.address 
              FROM purchase_invoices pi
              JOIN suppliers s ON pi.supplier_id = s.supplier_id
              WHERE pi.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $invoice_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($invoice = $result->fetch_assoc()) {
        // 🟢 Correct usage: now fetch product details using invoice_number
        $invoice_number = $invoice['invoice_number'];

        $details_query = "
            SELECT pid.*, p.Product_Name
            FROM purchase_invoice_details pid
            JOIN products p ON pid.product_id = p.Product_ID
            WHERE pid.purchase_invoice_id = ?
        ";
        $stmt2 = $conn->prepare($details_query);
        $stmt2->bind_param("s", $invoice_number); // use "s" for string
        $stmt2->execute();
        $details_result = $stmt2->get_result();

        $products_html = "";
        $count = 1;
        $subtotal = 0;

        while ($item = $details_result->fetch_assoc()) {
            $line_total = ($item['unit_price'] * $item['quantity']) - $item['discount'];
            $subtotal += $line_total;

            $products_html .= "
                <tr>
                    <td>{$count}</td>
                    <td>" . htmlspecialchars($item['Product_Name']) . "</td>
                    <td>{$item['quantity']}</td>
                    <td>Rs." . number_format($item['unit_price'], 2) . "</td>
                    <td>Rs." . number_format($item['discount'], 2) . "</td>
                    <td>Rs." . number_format($line_total, 2) . "</td>
                </tr>
            ";
            $count++;
        }

        $total_discount = floatval($invoice['total_discount']);
        $shipping = floatval($invoice['shipping_cost']);
        $total = $subtotal + $shipping - $total_discount;

        // Format display
        $subtotalFormatted = number_format($subtotal, 2);
        $shippingFormatted = number_format($shipping, 2);
        $discountFormatted = number_format($total_discount, 2);
        $totalFormatted = number_format($total, 2);

        echo "
        <div class='row mb-4'>
            <div class='col-md-6'>
                <img src='../inventory/img/logo_inventory.png' alt='Company Logo' height='50' class='mb-2'>
                <div class='border-left pl-3'>
                    <h6 class='font-weight-bold'>Supplier Details</h6>
                    <p class='mb-1'><strong>Name:</strong> {$invoice['supplier_name']}</p>
                    <p class='mb-1'><strong>Email:</strong> {$invoice['email']}</p>
                    <p class='mb-1'><strong>Phone:</strong> {$invoice['phone_number']}</p>
                    <p class='mb-0'><strong>Address:</strong> {$invoice['address']}</p>
                </div>
            </div>
            <div class='col-md-6 text-right'>
                <div class='bg-light p-3 rounded'>
                    <h6 class='font-weight-bold'>Invoice Info</h6>
                    <p class='mb-1'><strong>Invoice Date:</strong> {$invoice['invoice_date']}</p>
                    <p class='mb-1'><strong>Invoice No:</strong> {$invoice['invoice_number']}</p>
                    <p class='mb-0'><strong>Shipping:</strong> Rs.{$shippingFormatted}</p>
                </div>
            </div>
        </div>

        <div class='table-responsive'>
            <table class='table table-bordered'>
                <thead class='thead-dark'>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Discount</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    {$products_html}
                </tbody>
            </table>
        </div>

        <div class='row justify-content-end'>
            <div class='col-md-5'>
                <table class='table table-borderless'>
                    <tr>
                        <th class='text-right'>Subtotal:</th>
                        <td class='text-right'>Rs.{$subtotalFormatted}</td>
                    </tr>
                    <tr>
                        <th class='text-right'>Discount:</th>
                        <td class='text-right'>Rs.{$discountFormatted}</td>
                    </tr>
                    <tr>
                        <th class='text-right'>Shipping:</th>
                        <td class='text-right'>Rs.{$shippingFormatted}</td>
                    </tr>
                    <tr class='border-top'>
                        <th class='text-right'>Total Amount:</th>
                        <td class='text-right font-weight-bold'>Rs.{$totalFormatted}</td>
                    </tr>
                </table>
            </div>
        </div>
        ";
    } else {
        echo "<div class='alert alert-danger'>Invoice not found.</div>";
    }
} else {
    echo "<div class='alert alert-warning'>Invalid request.</div>";
}
?>
