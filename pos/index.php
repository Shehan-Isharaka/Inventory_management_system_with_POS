<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('includes/header.php');
include('includes/navbar.php');
include('../includes/dbconfig.php');



function generateInvoiceNumber($conn)
{
    $query = "SELECT id FROM sales_invoices ORDER BY id DESC LIMIT 1";
    $result = mysqli_query($conn, $query);
    $nextId = ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result)['id'] + 1 : 1;
    return 'PO-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
}

$generatedInvoiceNumber = generateInvoiceNumber($conn);
?>

<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <div class="container-fluid">

            <h1 class="h3 mb-4 text-gray-800">POS</h1>


            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card shadow-sm border-0 rounded-lg">
                        <div class="card-body d-flex justify-content-between">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="date" class="form-control" id="invoiceDate" value="<?= date('Y-m-d'); ?>">
                            </div>
                            <div class="form-group">
                                <label>Invoice #</label>
                                <input type="text" class="form-control" id="invoiceNumber"
                                    value="<?= $generatedInvoiceNumber; ?>" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <?php if (isset($_SESSION['success_msg'])) : ?>
                <div class="alert alert-success"><?= $_SESSION['success_msg'];
                                                unset($_SESSION['success_msg']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error_msg'])) : ?>
                <div class="alert alert-danger"><?= $_SESSION['error_msg'];
                                                unset($_SESSION['error_msg']); ?></div>
            <?php endif; ?>

             <div id="cashError" class="alert alert-warning alert-dismissible fade d-none" role="alert">
  <span id="cashErrorMsg"></span>
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>


            <form id="salesForm" onsubmit="event.preventDefault(); processSale();">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card shadow mb-4">
                            <div class="card-header bg-primary text-white">
                                <h6 class="m-0 font-weight-bold">Add Products</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle" id="salesTable">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Qty</th>
                                                <th>Price</th>
                                                <th>Discount</th>
                                                <th>Subtotal</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="salesBody"></tbody>
                                    </table>
                                </div>
                                <button type="button" class="btn btn-outline-primary" onclick="addRow()">+ Add
                                    Product</button>
                                <div class="text-right mt-3">
                                    <h5>Total: <span id="grandTotal" class="text-success">$0.00</span></h5>
                                </div>
                            </div>
                        </div>
                    </div>

                  

                    <div class="col-lg-4">
                        <div class="card shadow mb-4">
                            <div class="card-body">
                                <h5>Cart Summary</h5>
                                <table class="table">
                                    <tr>
                                        <th>Subtotal:</th>
                                        <td id="subTotal">$0.00</td>
                                    </tr>
                                    <tr>
                                        <th>Discount:</th>
                                        <td id="discount">$0.00</td>
                                    </tr>
                                    <tr>
                                        <th>Total:</th>
                                        <td id="total"><strong>$0.00</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Cash Paid:</th>
                                        <td><input type="number" id="cashPaid" class="form-control" min="0" step="0.01"
                                                oninput="calculateBalance()" required></td>
                                    </tr>
                                    <tr>
                                        <th>Balance:</th>
                                        <td id="balance">$0.00</td>
                                    </tr>
                                </table>
                                <button type="submit" id="processPrintBtn"
                                    class="btn btn-success w-100">Process</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>



        <!-- Your Bill Modal -->
        <div class="modal fade" id="billModal" tabindex="-1" aria-labelledby="billModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="billModalLabel">Invoice</h5>

                    </div>
                    <div class="modal-body">
                        <!-- Invoice content inserted dynamically here -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="printInvoiceBtn" class="btn btn-primary">Print</button>

                    </div>
                </div>
            </div>
        </div>



    </div>

    <?php include('includes/scripts.php');
    include('includes/footer.php'); ?>

    <script>
       function initSelect2(selector) {
    $(selector).select2({
        placeholder: "Search product...",
        ajax: {
            url: 'fetch_products.php',
            type: 'POST',
            dataType: 'json',
            delay: 250,
            data: params => ({ searchTerm: params.term }),
            processResults: data => {
                // Collect all selected product IDs to exclude from dropdown
                const selectedIds = $(".select-product").map(function () {
                    return $(this).val();
                }).get().filter(id => id); // remove nulls

                // Filter out already selected items
                const filtered = data.filter(item => !selectedIds.includes(item.Product_ID.toString()));

                return {
                    results: $.map(filtered, item => ({
                        id: item.Product_ID,
                        text: item.Product_Name,
                        price: parseFloat(item.Selling_Price)
                    }))
                };
            },
            cache: true
        }
    }).on('select2:select', function (e) {
        const selectedId = e.params.data.id;
        const currentRow = $(this).closest("tr");
        let isDuplicate = false;

        // Double check for duplicates (just in case)
        $(".select-product").not(this).each(function () {
            if ($(this).val() === selectedId) {
                isDuplicate = true;
                return false;
            }
        });

        if (isDuplicate) {
            showAlert("This product is already added. Please choose a different product.");
            $(this).val(null).trigger('change');
            currentRow.find(".price").val("0.00");
            currentRow.find(".quantity").val("1");
            updateSubtotal(currentRow.find(".quantity")[0]);
            return;
        }

        const data = e.params.data;
        currentRow.find(".price").val(data.price.toFixed(2));
        currentRow.find(".quantity").val(1);
        updateSubtotal(currentRow.find(".quantity")[0]);
    });
}

        function addRow() {
            const tbody = document.getElementById("salesBody");
            const row = document.createElement("tr");
            row.innerHTML = `
        <td><select class="form-control select-product" name="product_id[]" required style="width:100%;"></select></td>
        <td><input type="number" class="form-control quantity" name="quantity[]" value="0" min="1" onchange="updateSubtotal(this)" required></td>
        <td><input type="number" class="form-control price" name="unit_price[]" value="0.00" step="0.01" onchange="updateSubtotal(this)" required></td>
        <td><input type="number" class="form-control discount" name="discount[]" value="0.00" step="0.01" min="0" onchange="updateSubtotal(this)"></td>
        <td class="subtotal">$0.00</td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">×</button></td>
    `;
            tbody.appendChild(row);
            initSelect2($(row).find(".select-product"));
        }

        function removeRow(btn) {
            btn.closest("tr").remove();
            calculateTotal();
        }

        function updateSubtotal(input) {
            const row = input.closest("tr");
            const qty = parseFloat(row.querySelector(".quantity").value) || 0;
            const price = parseFloat(row.querySelector(".price").value) || 0;
            let discount = parseFloat(row.querySelector(".discount").value) || 0;

            if (discount > qty * price) discount = qty * price;
            row.querySelector(".discount").value = discount.toFixed(2);

            const subtotal = (qty * price) - discount;
            row.querySelector(".subtotal").textContent = `$${subtotal.toFixed(2)}`;
            calculateTotal();
        }

        function calculateTotal() {
            let total = 0, totalDiscount = 0, subtotal = 0;
            document.querySelectorAll("#salesBody tr").forEach(row => {
                const qty = parseFloat(row.querySelector(".quantity").value) || 0;
                const price = parseFloat(row.querySelector(".price").value) || 0;
                const discount = parseFloat(row.querySelector(".discount").value) || 0;

                subtotal += qty * price;
                totalDiscount += discount;
                total += (qty * price) - discount;
            });

            document.getElementById("grandTotal").textContent = `$${total.toFixed(2)}`;
            document.getElementById("subTotal").textContent = `$${subtotal.toFixed(2)}`;
            document.getElementById("discount").textContent = `$${totalDiscount.toFixed(2)}`;
            document.getElementById("total").textContent = `$${total.toFixed(2)}`;
            calculateBalance();
        }

        function calculateBalance() {
        const total = parseFloat(document.getElementById("total").textContent.replace('$', '')) || 0;
        const cashPaid = parseFloat(document.getElementById("cashPaid").value);
        const balance = (cashPaid || 0) - total;
        document.getElementById("balance").textContent = `$${(balance >= 0 ? balance : 0).toFixed(2)}`;

        const cashError = document.getElementById("cashError");
        if (!isNaN(cashPaid) && cashPaid > 0 && cashPaid < total) {
            // Show warning message
            showAlert("Cash paid must be equal to or greater than the total amount.");
        } else {
            // Hide warning message if cash is sufficient or empty
            cashError.classList.remove('show');
            cashError.classList.add('d-none');
        }
    }

        function processSale() {
            const btn = document.getElementById("processPrintBtn");
            btn.disabled = true;  // disable button to prevent double clicks

            const invoiceNumber = document.getElementById("invoiceNumber").value;
            const saleDate = document.getElementById("invoiceDate").value;
            const cashPaid = parseFloat(document.getElementById("cashPaid").value) || 0;
            const total = parseFloat(document.getElementById("total").textContent.replace('$', '')) || 0;
            const balance = cashPaid - total;

            const products = [];
            document.querySelectorAll("#salesBody tr").forEach(row => {
                const productID = $(row).find(".select-product").val();
                const quantity = parseFloat(row.querySelector(".quantity").value);
                const price = parseFloat(row.querySelector(".price").value);
                const discount = parseFloat(row.querySelector(".discount").value);
                const subtotal = (quantity * price) - discount;

                if (productID) {
                    products.push({
                        product_id: productID,
                        quantity, price, discount, subtotal
                    });
                }
            });

            if (products.length === 0) {
                showAlert("Please add at least one product.");
    btn.disabled = false; // re-enable button
    return;
            }



            $.ajax({
                url: 'process_sale.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    invoice_number: invoiceNumber,
                    sale_date: saleDate,
                    paid_amount: cashPaid,
                    total_amount: total,
                    balance,
                    products
                }),
                success: function (response) {
                    let data = response;
                    if (typeof data === "string") {
                        try { data = JSON.parse(data); } catch (e) {
                            showAlert("Invalid response.");
                btn.disabled = false;
                return;
                        }
                    }

                    if (data.status === "success") {
                        const style = `
      <style>
        .invoice-header { text-align: center; margin-bottom: 20px; }
        .invoice-header h2 { margin: 0; font-weight: bold; }
        .invoice-meta { font-size: 14px; margin-bottom: 20px; }
        .table-invoice { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table-invoice th, .table-invoice td {
          border: 1px solid #ccc; padding: 8px; text-align: center;
        }
        .totals { font-size: 15px; margin-top: 20px; }
        .totals p {
          margin: 0; display: flex; justify-content: space-between;
        }
        @media print {
          body * { visibility: hidden; }
          #billModal, #billModal * { visibility: visible; }
          #billModal { position: absolute; left: 0; top: 0; width: 100%; }
          .modal-footer { display: none; }
        }
      </style>
    `;

                        let modalBody = style + `
      <div class="invoice-header">
        <h2>DNet Computers</h2>
        <p class="invoice-meta">
          Invoice #: <strong>${data.invoice_number}</strong><br>
          Date: <strong>${data.sale_date}</strong>
        </p>
      </div>

      <table class="table-invoice">
        <thead>
          <tr>
            <th>Product</th>
            <th>Qty</th>
            <th>Unit Price</th>
            <th>Discount</th>
            <th>Subtotal</th>
          </tr>
        </thead>
        <tbody>`;

                        data.products.forEach(p => {
                            const productName = p.product_name || p.product_id;
                            modalBody += `
          <tr>
            <td>${productName}</td>
            <td>${p.quantity}</td>
            <td>${parseFloat(p.price).toFixed(2)}</td>
            <td>${parseFloat(p.discount).toFixed(2)}</td>
            <td>${parseFloat(p.subtotal).toFixed(2)}</td>
          </tr>`;
                        });

                        modalBody += `
                            </tbody>
                        </table>

                        <div class="totals">
                            <p><strong>Total Discount:</strong> <span>$${parseFloat(data.total_discount).toFixed(2)}</span></p>
                            <p><strong>Total Amount:</strong> <span>$${parseFloat(data.total_amount).toFixed(2)}</span></p>
                            <p><strong>Paid:</strong> <span>$${parseFloat(data.paid_amount).toFixed(2)}</span></p>
                            <p><strong>Balance:</strong> <span>$${parseFloat(data.balance).toFixed(2)}</span></p>
                        </div>

                        <div class="text-center mt-4">
                            <p>Thank you for shopping with us!</p>
                        </div>
                        `;

                        $('#billModal .modal-body').html(modalBody);
                        $('#billModal').modal('show');
                        // ✅ Show success alert
                        const successAlert = document.createElement('div');
                        successAlert.className = 'alert alert-success alert-dismissible fade show mt-3';
                        successAlert.role = 'alert';
                        successAlert.innerHTML = `
                            <strong>Success!</strong> Sale transaction completed successfully.
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        `;

                        document.querySelector(".container-fluid").prepend(successAlert);

                        // ✅ Reset form: remove all product rows
                    const salesBody = document.getElementById("salesBody");
                    salesBody.innerHTML = "";  // Clear all rows
                    addRow(); // Add one blank row

                    // ✅ Reset totals
                    document.getElementById("cashPaid").value = "";
                    document.getElementById("subTotal").textContent = "$0.00";
                    document.getElementById("discount").textContent = "$0.00";
                    document.getElementById("total").textContent = "$0.00";
                    document.getElementById("grandTotal").textContent = "$0.00";
                    document.getElementById("balance").textContent = "$0.00";

                    // ✅ Generate a new invoice number from backend
                    $.get("generate_invoice_number.php", function(newInvoice) {
                        $("#invoiceNumber").val(newInvoice.trim());
                    });


                    }
                    else {
                        showAlert("Error: " + data.message);
            btn.disabled = false;
                    }
                },

                error: function (xhr) {
                    showAlert("Request failed: " + xhr.responseText);
        btn.disabled = false;
                }
            });
        }

        function showAlert(message) {
    const alertDiv = document.getElementById('cashError');
    const alertMsg = document.getElementById('cashErrorMsg');

    alertMsg.textContent = message;
    alertDiv.classList.remove('d-none');
    alertDiv.classList.add('show');

    // Close button handler
    const closeBtn = alertDiv.querySelector('button.btn-close');
    closeBtn.onclick = () => {
        alertDiv.classList.remove('show');
        alertDiv.classList.add('d-none');
    };
}

function refreshAllSelect2Options() {
    $(".select-product").each(function () {
        $(this).select2('close'); // Close open dropdown to avoid flicker
        $(this).select2('open');  // Reopen to trigger fresh AJAX call
        $(this).select2('close'); // Then close again
    });
}



        document.getElementById('printInvoiceBtn').addEventListener('click', function () {
            window.print();
        });

        // Initialize one empty row on load
        document.addEventListener("DOMContentLoaded", () => {
            addRow();
        });
    </script>