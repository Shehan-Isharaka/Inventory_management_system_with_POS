<?php
ob_start();
session_start();
include('includes/header.php');
include('includes/navbar.php');
include('../includes/dbconfig.php');

$results = [];
$title = "Filtered Report";

// Read filter inputs
$filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : 'daily';
$daily_date = isset($_GET['daily_date']) ? trim($_GET['daily_date']) : '';
$weekly_start_date = isset($_GET['weekly_start_date']) ? trim($_GET['weekly_start_date']) : '';
$weekly_end_date = isset($_GET['weekly_end_date']) ? trim($_GET['weekly_end_date']) : '';
$month = isset($_GET['month']) ? trim($_GET['month']) : '';

$whereClause = "";
$isValid = true;

if ($filter_type === 'daily') {
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $daily_date)) {
        $whereClause = "WHERE DATE(si.sale_date) = '" . mysqli_real_escape_string($conn, $daily_date) . "'";
        $title = "Sales Report - $daily_date";
    } else {
        $isValid = false;
    }

} elseif ($filter_type === 'weekly') {
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $weekly_start_date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $weekly_end_date)) {
        $whereClause = "WHERE DATE(si.sale_date) BETWEEN '" . mysqli_real_escape_string($conn, $weekly_start_date) . "' 
                        AND '" . mysqli_real_escape_string($conn, $weekly_end_date) . "'";
        $title = "Sales Report - $weekly_start_date to $weekly_end_date";
    } else {
        $isValid = false;
    }

} elseif ($filter_type === 'monthly') {
    if (preg_match('/^\d{4}-\d{2}$/', $month)) {
        $whereClause = "WHERE DATE_FORMAT(si.sale_date, '%Y-%m') = '" . mysqli_real_escape_string($conn, $month) . "'";
        $title = "Sales Report - $month";
    } else {
        $isValid = false;
    }

} else {
    $isValid = false;
}

// Fetch results only if filter is valid
if ($isValid) {
    $query = "
        SELECT si.invoice_number, si.sale_date, si.total_amount,
               COUNT(sid.sales_invoice_id) AS total_items
        FROM sales_invoices si
        LEFT JOIN sales_invoice_details sid ON si.invoice_number = sid.sales_invoice_id
        $whereClause
        GROUP BY si.invoice_number, si.sale_date, si.total_amount
        ORDER BY si.sale_date ASC";
    $result = mysqli_query($conn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $results[] = $row;
        }
    }
}

?>

<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <div class="container-fluid">
            <h1 class="h3 mb-4 text-gray-800">Sales Report</h1>

            <form method="GET" class="mb-4">
                <div class="form-row align-items-end">
                    <div class="form-group col-md-3">
                        <label for="filter_type">Filter Type</label>
                        <select id="filter_type" name="filter_type" class="form-control">
                            <option value="daily" <?= $filter_type == 'daily' ? 'selected' : '' ?>>Daily</option>
                            <option value="weekly" <?= $filter_type == 'weekly' ? 'selected' : '' ?>>Weekly</option>
                            <option value="monthly" <?= $filter_type == 'monthly' ? 'selected' : '' ?>>Monthly</option>
                        </select>
                    </div>

                    <div class="form-group col-md-3 date-group" id="daily-group">
                        <label for="daily_date">Date</label>
                        <input type="date" id="daily_date" name="daily_date" class="form-control"
                            value="<?= $filter_type == 'daily' ? htmlspecialchars($daily_date) : '' ?>">
                    </div>

                    <div class="form-group col-md-3 date-group" id="weekly-group" style="display: none;">
                        <label for="weekly_start_date">Start Date</label>
                        <input type="date" id="weekly_start_date" name="weekly_start_date" class="form-control"
                            value="<?= $filter_type == 'weekly' ? htmlspecialchars($weekly_start_date) : '' ?>">
                        <label class="mt-2" for="weekly_end_date">End Date</label>
                        <input type="date" id="weekly_end_date" name="weekly_end_date" class="form-control"
                            value="<?= $filter_type == 'weekly' ? htmlspecialchars($weekly_end_date) : '' ?>">
                    </div>

                    <div class="form-group col-md-3 date-group" id="monthly-group" style="display: none;">
                        <label for="month">Month</label>
                        <input type="month" id="month" name="month" class="form-control"
                            value="<?= $filter_type == 'monthly' ? htmlspecialchars($month) : '' ?>">
                    </div>

                    <div class="form-group col-md-2">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </div>
            </form>

            <div class="card shadow mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary"><?= $title ?></h6>
                    <div>
                        <button id="exportExcel" class="btn btn-success btn-sm">Export to Excel</button>
                        <button id="downloadPDF" class="btn btn-danger btn-sm">Download PDF</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="reportTable" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Invoice Number</th>
                                    <th>Sale Date</th>
                                    <th>Total Items</th>
                                    <th>Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($results)): ?>
                                    <?php foreach ($results as $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['invoice_number']) ?></td>
                                            <td><?= htmlspecialchars($row['sale_date']) ?></td>
                                            <td><?= htmlspecialchars($row['total_items']) ?></td>
                                            <td>Rs. <?= number_format((float) $row['total_amount'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td class="text-center">No results found for the selected filter.</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                <?php endif; ?>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
    window.jsPDF = window.jspdf.jsPDF;

    $(document).ready(function () {
        function toggleGroups() {
            const filter = $('#filter_type').val();

            $('.date-group').hide();
            // Disable all inputs first to avoid submitting hidden inputs
            $('#daily_date').prop('disabled', true);
            $('#weekly_start_date').prop('disabled', true);
            $('#weekly_end_date').prop('disabled', true);
            $('#month').prop('disabled', true);

            if (filter === 'daily') {
                $('#daily-group').show();
                $('#daily_date').prop('disabled', false);
            }
            else if (filter === 'weekly') {
                $('#weekly-group').show();
                $('#weekly_start_date').prop('disabled', false);
                $('#weekly_end_date').prop('disabled', false);
            }
            else if (filter === 'monthly') {
                $('#monthly-group').show();
                $('#month').prop('disabled', false);
            }
        }

        const table = $('#reportTable').DataTable({
            dom: 'lfrtip',
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

        $('#exportExcel').on('click', function () {
            table.button('.buttons-excel').trigger();
        });

        $('#downloadPDF').on('click', function () {
            html2canvas(document.querySelector("#reportTable"), { scale: 2 }).then(canvas => {
                const imgData = canvas.toDataURL("image/png");
                const pdf = new jsPDF("landscape", "pt", "a4");
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = (canvas.height * pdfWidth) / canvas.width;
                pdf.addImage(imgData, "PNG", 20, 20, pdfWidth - 40, pdfHeight);
                pdf.save("sales_report.pdf");
            });
        });

        $('#filter_type').on('change', toggleGroups);
        toggleGroups();
    });
</script>