<?php
ob_start();
session_start();
include('includes/header.php');
include('includes/navbar.php');
include('../includes/dbconfig.php');
?>

<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">
    <div id="content-header">
        <div class="container-fluid">
            <h1 class="h3 mb-4 text-gray-800">Purchase Report</h1>

            <!-- Purchase Invoices Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Purchase Invoices</h6>
                    <div>
                        <a href="add-supplier-invoice.php" class="btn btn-primary btn-sm">Add Supplier Invoice</a>
                        <button id="exportExcel" class="btn btn-success btn-sm ml-2">Export to Excel</button>
                        <button id="downloadPDF" class="btn btn-danger btn-sm ml-2">Download PDF</button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="purchaseTable" class="table table-bordered" width="100%" cellspacing="0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Invoice No</th>
                                    <th>Supplier ID</th>
                                    <th>Invoice Date</th>
                                    <th>Shipping Cost</th>
                                    <th>Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT invoice_number, supplier_id, invoice_date, shipping_cost, total_amount FROM purchase_invoices";
                                $query_run = mysqli_query($conn, $query);

                                if (mysqli_num_rows($query_run) > 0) {
                                    while ($row = mysqli_fetch_assoc($query_run)) {
                                        echo "<tr>
                                            <td>" . htmlspecialchars($row['invoice_number']) . "</td>
                                            <td>" . htmlspecialchars($row['supplier_id']) . "</td>
                                            <td>" . htmlspecialchars($row['invoice_date']) . "</td>
                                            <td>Rs. " . number_format((float) $row['shipping_cost'], 2) . "</td>
                                            <td>Rs. " . number_format((float) $row['total_amount'], 2) . "</td>
                                        </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='text-center'>No purchase records found.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php
include('includes/scripts.php');
include('includes/footer.php');
?>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />

<!-- DataTables Buttons -->
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css" />

<!-- html2canvas & jsPDF for PDF export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
    window.jsPDF = window.jspdf.jsPDF;
</script>

<script>
    $(document).ready(function () {
        const table = $('#purchaseTable').DataTable({
            dom: 'lfrtip',
            buttons: [
                {
                    extend: 'excelHtml5',
                    title: 'Purchase_Report',
                    exportOptions: {
                        columns: ':visible'
                    }
                }
            ],
            paging: true,
            searching: true,
            ordering: true
        });

        // Trigger Excel export
        $('#exportExcel').on('click', function () {
            table.button('.buttons-excel').trigger();
        });

        // Download PDF
        $('#downloadPDF').on('click', function () {
            const table = document.querySelector("#purchaseTable");
            html2canvas(table, { scale: 2 }).then(canvas => {
                const imgData = canvas.toDataURL("image/png");
                const pdf = new jsPDF("landscape", "pt", "a4");

                const imgProps = pdf.getImageProperties(imgData);
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

                pdf.addImage(imgData, "PNG", 20, 20, pdfWidth - 40, pdfHeight);
                pdf.save("purchase-report.pdf");
            });
        });
    });
</script>