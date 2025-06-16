<?php

ob_start();
session_start();
include('includes/header.php');
include('includes/navbar.php');
include('../includes/dbconfig.php');
?>

<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">

    <!-- Main Content -->
    <div id="content-header">

        <!-- Begin Page Content -->
        <div class="container-fluid">

            <!-- Page Heading -->
            <h1 class="h3 mb-2 text-gray-800">Supplier's Invoice</h1>


            <!-- Success/Error Messages -->
            <?php
            // Displaying success and error messages
            if (isset($_SESSION['success'])) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong><i class="fas fa-check-circle"></i> Success!</strong> ' . $_SESSION['success'] . '
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
                unset($_SESSION['success']);
            }


            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong><i class="fas fa-exclamation-triangle"></i> Error!</strong> ' . $_SESSION['status'] . '
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
                unset($_SESSION['error']);
            }
            ?>

            <!-- DataTales Example -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Supplier's Invoice Details</h6>
                    <a href="add-supplier-invoice.php" class="btn btn-primary btn-sm float-right">Add Supplier
                        Invoice</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Invoice No</th>
                                        <th>Supplier ID</th>
                                        <th>Invoice Date</th>
                                        <th>Shipping Cost</th>
                                        <th>Total Amount</th>
                                        <th>Created By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tfoot class="thead-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Invoice No</th>
                                        <th>Supplier ID</th>
                                        <th>Invoice Date</th>
                                        <th>Shipping Cost</th>
                                        <th>Total Amount</th>
                                        <th>Created By</th>
                                        <th>Actions</th>
                                    </tr>
                                </tfoot>
                                <tbody>
                                    <?php
                                    //include('config/dbcon.php'); // your DB connection
                                    
                                    $query = " SELECT pi.*, u.username 
                                    FROM purchase_invoices pi 
                                    LEFT JOIN users u ON pi.created_by = u.id 
                                    ORDER BY pi.id ASC";
                                    $query_run = mysqli_query($conn, $query);

                                    if (mysqli_num_rows($query_run) > 0) {
                                        while ($row = mysqli_fetch_assoc($query_run)) {
                                            ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['id']) ?></td>
                                                <td><?= htmlspecialchars($row['invoice_number']) ?></td>
                                                <td><?= htmlspecialchars($row['supplier_id']) ?></td>
                                                <td><?= htmlspecialchars($row['invoice_date']) ?></td>
                                                <td><?php echo 'Rs. ' . number_format((float) $row['shipping_cost'], 2); ?></td>
                                                <td><?php echo 'Rs. ' . number_format((float) $row['total_amount'], 2); ?></td>
                                                <td><?php echo htmlspecialchars($row['username'] ?? 'N/A'); ?></td>

                                                <td>
                                                    <!-- In your invoice listing table -->
                                                    <a href="#" class="btn btn-info btn-sm viewInvoiceBtn"
                                                        data-id="<?= $row['id'] ?>" data-toggle="modal"
                                                        data-target="#viewInvoiceModal" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>


                                                    <a href="#" class="btn btn-danger btn-sm deleteInvoiceBtn"
                                                        data-id="<?= $row['id'] ?>" data-toggle="modal"
                                                        data-target="#deleteInvoiceModal">
                                                        <i class="fas fa-trash"></i>
                                                    </a>

                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    } else {
                                        echo '<tr><td colspan="8" class="text-center">No supplier invoices found.</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>

                    </div>

                </div>
            </div>

        </div>
        <!-- /.container-fluid -->

    </div>
    <!-- End of Main Content -->

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function () {
            $(document).on('click', '.viewInvoiceBtn', function (e) {
                e.preventDefault();

                var id = $(this).data('id');
                console.log("View button clicked, Invoice ID:", id);

                $('#invoiceContent').html('<div class="text-center"><span class="spinner-border text-primary"></span> Loading invoice...</div>');

                $.ajax({
                    url: '../inventory/fetch_invoice_details.php',
                    type: 'POST',
                    data: { id: id },
                    success: function (response) {
                        console.log("AJAX response:", response);
                        $('#invoiceContent').html(response);
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error:", error);
                        $('#invoiceContent').html('<div class="alert alert-danger">Failed to load invoice details.</div>');
                    }
                });
            });
        });


        $(document).on('click', '.deleteInvoiceBtn', function () {
            var invoiceId = $(this).data('id');
            $('#deleteInvoiceId').val(invoiceId);
        });




    </script>



    </script>

    <!-- View Invoice Modal -->
    <div class="modal fade" id="viewInvoiceModal" tabindex="-1" role="dialog" aria-labelledby="invoiceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content border-0 rounded-lg shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold" id="invoiceModalLabel">Invoice Details</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4" id="invoiceContent">
                    Invoice content will be loaded here via AJAX
                    <div class="text-center">
                        <span class="spinner-border text-primary"></span> Loading invoice...
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="printInvoice()">Print</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteInvoiceModal" tabindex="-1" role="dialog"
        aria-labelledby="deleteInvoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="delete-invoice.php" method="POST">
                <input type="hidden" name="delete_id" id="deleteInvoiceId">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteInvoiceModalLabel">Confirm Deletion</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete this invoice? This will also update product stock and delete
                        related records.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                        <button type="submit" class="btn btn-danger">Yes, Delete</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>

        function printInvoice() {
            const modalContent = document.querySelector('#viewInvoiceModal .modal-body');

            if (!modalContent) {
                alert('Invoice content not found!');
                return;
            }

            const printWindow = window.open('', '_blank', 'height=800,width=1000');
            printWindow.document.write(`
        <html>
            <head>
                <title>Purchase Invoice</title>
                <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
                <style>
                    body {
                        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                        padding: 20px;
                        background: #fff;
                        color: #000;
                    }
                    .invoice-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-bottom: 20px;
                        border-bottom: 2px solid #000;
                        padding-bottom: 10px;
                    }
                    .invoice-header img {
                        height: 60px;
                    }
                    .invoice-section-title {
                        font-weight: bold;
                        font-size: 18px;
                        margin-top: 20px;
                        border-bottom: 1px solid #ccc;
                        padding-bottom: 5px;
                    }
                    .table th, .table td {
                        vertical-align: middle !important;
                    }
                    .summary-table th, .summary-table td {
                        text-align: right;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    ${modalContent.innerHTML}
                </div>
            </body>
        </html>
    `);

            printWindow.document.close();
            printWindow.focus();

            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 500);
        }



    </script>



    <?php

    include('includes/scripts.php');
    include('includes/footer.php');

    ?>