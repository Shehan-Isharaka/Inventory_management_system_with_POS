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
            <h1 class="h3 mb-4 text-gray-800">Inventory Report</h1>

            <!-- Alert Messages -->
            <?php
            $alerts = [
                'success' => ['type' => 'success', 'icon' => 'check-circle', 'title' => 'Success!'],
                'updated' => ['type' => 'success', 'icon' => 'sync-alt', 'title' => 'Updated!'],
                'deleted' => ['type' => 'danger', 'icon' => 'trash-alt', 'title' => 'Deleted!'],
                'status' => ['type' => 'danger', 'icon' => 'exclamation-triangle', 'title' => 'Error!']
            ];
            foreach ($alerts as $key => $alert) {
                if (isset($_SESSION[$key])) {
                    echo '
                    <div class="alert alert-' . $alert['type'] . ' alert-dismissible fade show" role="alert">
                        <strong><i class="fas fa-' . $alert['icon'] . '"></i> ' . $alert['title'] . '</strong> ' . $_SESSION[$key] . '
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
                    unset($_SESSION[$key]);
                }
            }
            ?>

            <!-- Product Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Product Details</h6>
                    <div>
                        <a href="add-product.php" class="btn btn-primary btn-sm">Add Product</a>
                        <button id="exportExcel" class="btn btn-success btn-sm ml-2">Export to Excel</button>
                        <button id="downloadPDF" class="btn btn-danger btn-sm ml-2">Download PDF</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="dataTable" class="table table-bordered" width="100%" cellspacing="0">

                            <thead class="thead-light">
                                <tr>
                                    <th>Product ID</th>
                                    <th>Product Name</th>
                                    <th>Brand</th>
                                    <th>Model</th>
                                    <th>Purchase Price</th>
                                    <th>Selling Price</th>
                                    <th>Stock Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT p.product_id, p.product_name, b.brand_name, p.model, p.purchase_price, p.selling_price, p.stock_quantity
                                          FROM products p
                                          LEFT JOIN brands b ON p.Brand_ID = b.brand_id";
                                $query_run = mysqli_query($conn, $query);

                                if (mysqli_num_rows($query_run) > 0) {
                                    while ($row = mysqli_fetch_assoc($query_run)) {
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['product_id']) ?></td>
                                            <td><?= htmlspecialchars($row['product_name']) ?></td>
                                            <td><?= htmlspecialchars($row['brand_name']) ?></td>
                                            <td><?= htmlspecialchars($row['model']) ?></td>
                                            <td><?php echo 'Rs. ' . number_format((float) $row['purchase_price'], 2); ?></td>
                                            <td><?php echo 'Rs. ' . number_format((float) $row['selling_price'], 2); ?></td>
                                            <td><?= htmlspecialchars($row['stock_quantity']) ?></td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='7' class='text-center'>No products found.</td></tr>";
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
    // Fix: assign jsPDF from UMD module
    window.jsPDF = window.jspdf.jsPDF;
</script>



<script>
    $(document).ready(function () {
        const table = $('#dataTable').DataTable({
            dom: 'lfrtip',
            buttons: [
                {
                    extend: 'excelHtml5',
                    title: 'Inventory_List',
                    exportOptions: {
                        columns: ':visible'
                    }
                }
            ],
            paging: true, // Optional
            searching: true // Optional
        });

        // Trigger Excel export on button click
        $('#exportExcel').on('click', function () {
            table.button('.buttons-excel').trigger();
        });
    });


    document.getElementById("downloadPDF").addEventListener("click", function () {
        const table = document.querySelector("#dataTable");

        html2canvas(table, {
            scale: 2,
        }).then(canvas => {
            const imgData = canvas.toDataURL("image/png");
            const pdf = new jsPDF("landscape", "pt", "a4");

            const imgProps = pdf.getImageProperties(imgData);
            const pdfWidth = pdf.internal.pageSize.getWidth();
            const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

            pdf.addImage(imgData, "PNG", 20, 20, pdfWidth - 40, pdfHeight);
            pdf.save("products-table.pdf");
        });
    });

</script>