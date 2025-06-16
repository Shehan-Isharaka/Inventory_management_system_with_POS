<?php
ob_start();
session_start();
include('includes/header.php');
include('includes/navbar.php');
include('../includes/dbconfig.php');

// Fetch suppliers
$supplier_query = mysqli_query($conn, "SELECT * FROM suppliers ORDER BY supplier_name ASC");

// Generate next invoice number
$lastInvoiceRes = mysqli_query($conn, "SELECT invoice_number FROM purchase_invoices ORDER BY id DESC LIMIT 1");
$lastInvoice = mysqli_fetch_assoc($lastInvoiceRes);

if ($lastInvoice) {
    $lastNum = intval(substr($lastInvoice['invoice_number'], 4));
    $nextNum = $lastNum + 1;
} else {
    $nextNum = 1;
}
$invoice_number = 'INVO' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $supplier_id = $_POST['supplier_id'];
    $invoice_date = $_POST['invoice_date'];
    $invoice_number = $_POST['invoice_number'];
    $shipping_cost = floatval($_POST['shippingCost']);
    $created_by = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
    $created_at = date('Y-m-d H:i:s');

    // Product data
    $product_ids = $_POST['product_id'];
    $quantities = $_POST['qty'];
    $prices = $_POST['price'];
    $discounts = $_POST['discount'];
    $subtotals = $_POST['subtotal'];

    $grand_total = 0;
    $total_discount = 0;
    for ($i = 0; $i < count($subtotals); $i++) {
        $grand_total += floatval($subtotals[$i]);
        $total_discount += floatval($discounts[$i]);
    }
    $grand_total += $shipping_cost;

    // Temporarily disable MySQLi exceptions
    mysqli_report(MYSQLI_REPORT_OFF);

    // Insert invoice
    $insertInvoiceSQL = "INSERT INTO purchase_invoices 
        (invoice_number, supplier_id, invoice_date, shipping_cost, total_discount, total_amount, created_at, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insertInvoiceSQL);

    if (!$stmt) {
        die("Prepare failed: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param(
        $stmt,
        "sssdddsi",
        $invoice_number,
        $supplier_id,
        $invoice_date,
        $shipping_cost,
        $total_discount,
        $grand_total,
        $created_at,
        $created_by
    );

    if (mysqli_stmt_execute($stmt)) {
        $purchase_invoice_id = $invoice_number;
        mysqli_stmt_close($stmt);
    } else {
        // Handle duplicate invoice number
        if (mysqli_errno($conn) == 1062) {
            mysqli_stmt_close($stmt);
            $stmtSelect = mysqli_prepare($conn, "SELECT id FROM purchase_invoices WHERE invoice_number = ?");
            if (!$stmtSelect) {
                die("Prepare failed: " . mysqli_error($conn));
            }
            mysqli_stmt_bind_param($stmtSelect, "s", $invoice_number);
            mysqli_stmt_execute($stmtSelect);
            mysqli_stmt_bind_result($stmtSelect, $purchase_invoice_id);
            mysqli_stmt_fetch($stmtSelect);
            mysqli_stmt_close($stmtSelect);

            if (!$purchase_invoice_id) {
                die("Duplicate invoice, but ID not found.");
            }
        } else {
            die("Insert failed: " . mysqli_error($conn));
        }
    }

    // Re-enable MySQLi exceptions
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    // Insert each product row
    for ($i = 0; $i < count($product_ids); $i++) {
        $product_id = $product_ids[$i];
        $qty = floatval($quantities[$i]);
        $unit_price = floatval($prices[$i]);
        $discount = floatval($discounts[$i]);
        $subtotal = floatval($subtotals[$i]);

        // Insert into purchase_invoice_details
        $insertDetailSQL = "INSERT INTO purchase_invoice_details 
                        (purchase_invoice_id, product_id, quantity, unit_price, discount, subtotal, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmtDetail = mysqli_prepare($conn, $insertDetailSQL);
        if (!$stmtDetail) {
            die("Prepare failed (details): " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmtDetail, "ssdddds", $purchase_invoice_id, $product_id, $qty, $unit_price, $discount, $subtotal, $created_at);
        mysqli_stmt_execute($stmtDetail);
        mysqli_stmt_close($stmtDetail);

        // Update product stock
        $updateStockSQL = "UPDATE products SET Stock_Quantity = Stock_Quantity + ?, Modified_At = ?, Modified_By = ? WHERE Product_ID = ?";
        $stmtStock = mysqli_prepare($conn, $updateStockSQL);
        if (!$stmtStock) {
            die("Prepare failed (stock): " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmtStock, "dsis", $qty, $created_at, $created_by, $product_id);
        mysqli_stmt_execute($stmtStock);
        mysqli_stmt_close($stmtStock);
    }

    $_SESSION['success'] = "Supplier invoice <strong>$invoice_number</strong> added successfully.";
    header("Location: add-supplier-invoice.php");
    exit;
}
?>



<style>
    /* Make Select2 look like a Bootstrap input field */
    .select2-container .select2-selection--single {
        height: calc(1.5em + 0.75rem + 2px);
        /* Match Bootstrap input height */
        padding: 0.375rem 0.75rem;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        background-color: #fff;
        line-height: 1.5;
        font-size: 1rem;
        color: #495057;
    }

    /* Arrow inside the select box */
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: calc(1.5em + 0.75rem + 2px);
        /* Same height as input */
        right: 8px;
        top: 3px;
    }

    /* Placeholder styling */
    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #6c757d;
    }

    /* Focus effect */
    .select2-container .select2-selection--single:focus {
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    /* Select2 dropdown styling */
    .select2-dropdown {
        border-radius: 0.25rem;
    }
</style>





<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">

    <!-- Main Content -->
    <div id="content-header">
        <div class="container-fluid">
            <h1 class="h3 mb-4 text-gray-800">Enter Supplier Invoice</h1>

            <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php elseif (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>


            <form action="" method="POST">
                <div class="row">
                    <!-- Supplier Info -->
                    <div class="col-md-8">
                        <div class="card mb-4">
                            <div class="card-header m-0 font-weight-bold text-primary">Supplier Information</div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Row 1 -->
                                    <div class="form-group col-md-6">
                                        <label for="supplierSelect">Select Supplier</label>
                                        <select class="form-control" name="supplier_id" id="supplierSelect" required
                                            onchange="fillSupplierInfo()">
                                            <option selected disabled value="">-- Select Supplier --</option>
                                            <?php
                                            while ($row = mysqli_fetch_assoc($supplier_query)) {
                                                echo "<option value='{$row['supplier_id']}' data-email='" . htmlspecialchars($row['email']) . "' data-phone='" . htmlspecialchars($row['phone_number']) . "' data-address='" . htmlspecialchars($row['address']) . "'>" . htmlspecialchars($row['supplier_name']) . "</option>";

                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="supplierEmail">Email</label>
                                        <input type="email" class="form-control" id="supplierEmail"
                                            name="supplier_email" readonly>
                                    </div>

                                    <!-- Row 2 -->
                                    <div class="form-group col-md-6">
                                        <label for="supplierPhone">Phone</label>
                                        <input type="text" class="form-control" id="supplierPhone" name="supplier_phone"
                                            readonly>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="supplierAddress">Address</label>
                                        <textarea class="form-control" id="supplierAddress" name="supplier_address"
                                            rows="2" readonly></textarea>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Invoice Date & ID Card -->
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header m-0 font-weight-bold text-primary">Invoice Details</div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="invoiceDate">Invoice Date</label>
                                    <input type="date" class="form-control" name="invoice_date" id="invoiceDate"
                                        required>
                                </div>
                                <div class="form-group">
                                    <label for="invoiceNumber">Invoice ID</label>
                                    <input type="text" class="form-control" name="invoice_number" id="invoiceNumber"
                                        value="<?php echo $invoice_number; ?>" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Table -->
                <div class="card">
                    <div class="card-header m-0 font-weight-bold text-primary d-flex align-items-center">
                        <button type="button" class="btn btn-success btn-sm mr-2" onclick="addRow()">
                            <i class="fas fa-plus"></i>
                        </button>
                        <strong>Add Products</strong>
                    </div>
                    <div class="card-body p-2">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="invoiceTable">
                                <thead>
                                    <tr>
                                        <th>Action</th>
                                        <th>Product</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Discount</th>
                                        <th>Sub Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm"
                                                onclick="removeRow(this)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                        <td>
                                            <select class="form-control productSearch" name="product_id[]"
                                                style="width: 100%;"></select>

                                        </td>
                                        <td><input type="number" class="form-control" name="qty[]" value="0"
                                                oninput="calculateRow(this)"></td>
                                        <td><input type="number" class="form-control" name="price[]" value="0.00"
                                                oninput="calculateRow(this)"></td>
                                        <td><input type="text" class="form-control" name="discount[]"
                                                placeholder="Enter % OR value" oninput="calculateRow(this)"></td>
                                        <td><input type="text" class="form-control" name="subtotal[]" value="0.00"
                                                readonly></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Summary -->
                        <div class="row mt-3">
                            <div class="col-md-6"></div>
                            <div class="col-md-6">
                                <table class="table">
                                    <tr>
                                        <th>Sub Total:</th>
                                        <td id="subTotalDisplay">Rs.0.00</td>
                                    </tr>
                                    <tr>
                                        <th>Discount:</th>
                                        <td id="totalDiscountDisplay">Rs.0.00</td>
                                    </tr>
                                    <tr>
                                        <th>Shipping:</th>
                                        <td><input type="number" class="form-control" id="shippingCost"
                                                name="shippingCost" value="0.00" oninput="calculateTotal()"></td>
                                    </tr>
                                    <tr>
                                        <th>Total:</th>
                                        <td><strong id="grandTotal">Rs.0.00</strong></td>
                                    </tr>
                                </table>
                                <div class="text-right">
                                    <button type="submit" class="btn btn-primary">Save Invoice</button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </form>
        </div>

        <script>

            $(document).ready(function () {
                initializeSelect2();

                console.log("Select2 loaded and configured for initial product search.");
            });

            function initializeSelect2() {
                $('.productSearch').select2({
                    placeholder: 'Search for a product...',
                    ajax: {
                        url: '../inventory/fetch_products.php',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                term: params.term
                            };
                        },
                        processResults: function (data) {
                            return {
                                results: data
                            };
                        },
                        cache: true
                    }
                });
            }

            function addRow() {
                let table = document.querySelector('#invoiceTable tbody');
                let newRow = table.rows[0].cloneNode(true);

                // Clear inputs
                newRow.querySelectorAll('input').forEach(input => input.value = '');
                newRow.querySelector('.select2-container')?.remove(); // Remove any leftover select2 container
                let newSelect = newRow.querySelector('select');
                newSelect.innerHTML = ''; // Clear previous options if any
                newSelect.classList.remove('select2-hidden-accessible'); // Reset state
                $(newSelect).removeAttr('data-select2-id'); // Remove Select2 tracking attributes

                table.appendChild(newRow);

                // Re-initialize Select2 for new select
                initializeSelect2();
            }


            function removeRow(btn) {
                let row = btn.closest('tr');
                let table = row.closest('tbody');
                if (table.rows.length > 1) {
                    row.remove();
                }
                calculateTotal();
            }

            function calculateRow(el) {
                let row = el.closest('tr');
                let qty = parseFloat(row.querySelector('input[name="qty[]"]').value) || 0;
                let price = parseFloat(row.querySelector('input[name="price[]"]').value) || 0;
                let discountRaw = row.querySelector('input[name="discount[]"]').value;
                let subtotal = qty * price;
                let discount = 0;

                if (discountRaw.includes('%')) {
                    let percent = parseFloat(discountRaw.replace('%', '')) || 0;
                    discount = subtotal * (percent / 100);
                } else {
                    discount = parseFloat(discountRaw) || 0;
                }

                subtotal -= discount;
                row.querySelector('input[name="subtotal[]"]').value = subtotal.toFixed(2);
                calculateTotal();
            }

            function calculateTotal() {
                let rows = document.querySelectorAll('#invoiceTable tbody tr');
                let subtotal = 0, totalDiscount = 0;

                rows.forEach(row => {
                    let qty = parseFloat(row.querySelector('input[name="qty[]"]').value) || 0;
                    let price = parseFloat(row.querySelector('input[name="price[]"]').value) || 0;
                    let discountRaw = row.querySelector('input[name="discount[]"]').value || 0;
                    let rowTotal = qty * price;
                    let discount = 0;

                    if (discountRaw.includes('%')) {
                        let percent = parseFloat(discountRaw.replace('%', '')) || 0;
                        discount = rowTotal * (percent / 100);
                    } else {
                        discount = parseFloat(discountRaw) || 0;
                    }

                    subtotal += rowTotal;
                    totalDiscount += discount;
                });

                let shipping = parseFloat(document.getElementById("shippingCost").value) || 0;
                let total = subtotal - totalDiscount + shipping;

                document.getElementById("subTotalDisplay").innerText = `$${subtotal.toFixed(2)}`;
                document.getElementById("totalDiscountDisplay").innerText = `$${totalDiscount.toFixed(2)}`;
                document.getElementById("grandTotal").innerText = `$${total.toFixed(2)}`;
            }

            function fillSupplierInfo() {
                let selected = document.querySelector('#supplierSelect option:checked');
                document.getElementById('supplierEmail').value = selected.dataset.email || '';
                document.getElementById('supplierPhone').value = selected.dataset.phone || '';
                document.getElementById('supplierAddress').value = selected.dataset.address || '';
            }
        </script>



    </div>
    <!-- End of Main Content -->

    <?php

    include('includes/scripts.php');
    include('includes/footer.php');

    ?>