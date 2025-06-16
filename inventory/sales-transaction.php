<?php
session_start();
include('includes/header.php');
include('includes/navbar.php');
include('../includes/dbconfig.php');

$sql = "
SELECT 
    si.invoice_number, 
    si.sale_date, 
    si.total_amount, 
    si.created_by,
    u.full_name AS cashier_name,
    COUNT(sid.id) AS total_items
FROM sales_invoices si
LEFT JOIN sales_invoice_details sid ON si.invoice_number = sid.sales_invoice_id
LEFT JOIN users u ON si.created_by = u.id
GROUP BY si.invoice_number, si.sale_date, si.total_amount, si.created_by, cashier_name
ORDER BY si.sale_date ASC
";

$result = $conn->query($sql);
?>

<div id="content-wrapper" class="d-flex flex-column">
    <div id="content-header">
        <div class="container-fluid">
            <h1 class="h3 mb-2 text-gray-800">Sales Transactions</h1>

            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Sales Overview</h6>
                    <div>
                        <button id="exportExcel" class="btn btn-success btn-sm ml-2">Export to Excel</button>
                        <button id="downloadPDF" class="btn btn-danger btn-sm ml-2">Download PDF</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTableSales" width="100%" cellspacing="0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Invoice ID</th>
                                    <th>Date</th>
                                    <th>Total Items</th>
                                    <th>Total Amount</th>
                                    <th>Done By</th>
                                </tr>
                            </thead>
                            <tfoot class="thead-light">
                                <tr>
                                    <th>Invoice ID</th>
                                    <th>Date</th>
                                    <th>Total Items</th>
                                    <th>Total Amount</th>
                                    <th>Done By</th>
                                </tr>
                            </tfoot>
                            <tbody>
                                <?php
                                if ($result && $result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['invoice_number']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['sale_date']) . "</td>";
                                        echo "<td>" . intval($row['total_items']) . "</td>";
                                        echo "<td>Rs. " . number_format($row['total_amount'], 2) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['cashier_name'] ?? 'Unknown') . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='text-center'>No sales transactions found.</td></tr>";
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

<!-- DataTables Core -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />

<!-- DataTables Buttons -->
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css" />

<!-- jsPDF and html2canvas for PDF export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
    window.jsPDF = window.jspdf.jsPDF;
</script>

<script>
$(document).ready(function () {
    const table = $('#dataTableSales').DataTable({
        dom: 'lfrtip',
        paging: true,
        searching: true,
        buttons: [
            {
                extend: 'excelHtml5',
                title: 'Sales_Report',
                exportOptions: {
                    columns: ':visible'
                }
            }
        ]
    });

    // Excel export trigger
    $('#exportExcel').on('click', function () {
        table.button('.buttons-excel').trigger();
    });

    // PDF export trigger
    $('#downloadPDF').on('click', function () {
        html2canvas(document.querySelector("#dataTable"), {
            scale: 2
        }).then(canvas => {
            const imgData = canvas.toDataURL("image/png");
            const pdf = new jsPDF("landscape", "pt", "a4");
            const imgProps = pdf.getImageProperties(imgData);
            const pdfWidth = pdf.internal.pageSize.getWidth();
            const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
            pdf.addImage(imgData, "PNG", 20, 20, pdfWidth - 40, pdfHeight);
            pdf.save("sales-report.pdf");
        });
    });
});
</script>
