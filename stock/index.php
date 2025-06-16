<?php
session_start();
include('includes/header.php');
include('includes/navbar.php');
include('../includes/dbconfig.php');


// Get category count
$categoryCountQuery = mysqli_query($conn, "SELECT COUNT(*) AS count FROM categories");
$categoryCount = mysqli_fetch_assoc($categoryCountQuery)['count'];

// Get subcategory count
$subCategoryCountQuery = mysqli_query($conn, "SELECT COUNT(*) AS count FROM subcategories");
$subCategoryCount = mysqli_fetch_assoc($subCategoryCountQuery)['count'];

$brandCountQuery = mysqli_query($conn, "SELECT COUNT(*) AS count FROM brands");
$brandCount = mysqli_fetch_assoc($brandCountQuery)['count'];

// Get product count
$productCountQuery = mysqli_query($conn, "SELECT COUNT(*) AS count FROM products");
$productCount = mysqli_fetch_assoc($productCountQuery)['count'];

// Get supplier count
$supplierCountQuery = mysqli_query($conn, "SELECT COUNT(*) AS count FROM suppliers");
$supplierCount = mysqli_fetch_assoc($supplierCountQuery)['count'];

// Get total stock quantity (sum of stock quantities)
$totalStockQuery = mysqli_query($conn, "SELECT SUM(Stock_Quantity) AS total FROM products");
$totalStock = mysqli_fetch_assoc($totalStockQuery)['total'];


// Get total expenses from purchase_invoices
$expensesQuery = mysqli_query($conn, "SELECT SUM(total_amount) AS total_expenses FROM purchase_invoices");
$expensesTotal = mysqli_fetch_assoc($expensesQuery)['total_expenses'];
$expensesTotal = $expensesTotal ?? 0; // fallback if null


// Get total users
$userQuery = mysqli_query($conn, "SELECT COUNT(*) AS total_users FROM users");
$userCount = mysqli_fetch_assoc($userQuery)['total_users'];


$invoiceLabels = [];
$invoiceAmounts = [];

$sql = "SELECT invoice_number, total_amount 
        FROM purchase_invoices 
        ORDER BY invoice_date ASC";

$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    $invoiceLabels[] = $row['invoice_number'];  // e.g. "INVO0001"
    $invoiceAmounts[] = (float)$row['total_amount'];
}


$categoryLabels = [];
$categoryCounts = [];

$sql = "SELECT c.Category_Name, COUNT(p.Product_ID) AS product_count
        FROM categories c
        LEFT JOIN products p ON c.Category_ID = p.Category_ID
        GROUP BY c.Category_ID
        ORDER BY c.Category_Name ASC";

$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    $categoryLabels[] = $row['Category_Name'];
    $categoryCounts[] = (int) $row['product_count'];
}

?>


<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">

    <!-- Main Content -->
    <div id="content">



        <!-- Begin Page Content -->
        <div class="container-fluid">

            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>

            </div>

            <!-- Content Row -->

            <div class="row">

            
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Categories</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo $categoryCount; ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-tags fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

               
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Products</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo $productCount; ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-boxes fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

              
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Suppliers
                                    </div>
                                    <div class="row no-gutters align-items-center">
                                        <div class="col-auto">
                                            <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                                <?php echo $supplierCount; ?>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-truck fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

             
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Total Stock</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo $totalStock; ?>
                                    </div>

                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-warehouse fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Brands</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $brandCount; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-trademark fa-2x text-gray-300"></i> <!-- Trademark icon for brands -->
                    </div>
                </div>
            </div>
        </div>
    </div>

             

         
                 <!-- Subcategories Card -->
                 <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Subcategories</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $subCategoryCount; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-th-large fa-2x text-gray-300"></i> <!-- Grid icon for subcategories -->
                    </div>
                </div>
            </div>
        </div>
    </div>

              
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Expenses
                                    </div>
                                    <div class="row no-gutters align-items-center">
                                        <div class="col-auto">
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                Rs. <?php echo number_format($expensesTotal, 2); ?>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

             
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Users</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo $userCount; ?>
                                    </div>

                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-users fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

         <!-- Content Row -->
<div class="row">

<!-- Bar Chart Column -->
<div class="col-lg-8">
    <div class="card shadow mb-4 h-100">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Purchases per Invoice</h6>
        </div>
        <div class="card-body">
            <div class="chart-bar" style="height: 300px;">
                <canvas id="mynewBarChart"></canvas>
            </div>
            <hr>Showing purchase total by invoice number.
        </div>
    </div>
</div>

<!-- Pie Chart Column -->
<div class="col-lg-4">
    <div class="card shadow mb-4 h-100">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Products by Category</h6>
        </div>
        <div class="card-body">
            <div class="chart-pie pt-4 pb-2 d-flex justify-content-center">
                <canvas id="mynewPieChart" style="max-width: 250px;"></canvas>
            </div>
            <div class="mt-4 text-center small" id="categoryLegend"></div>
        </div>
    </div>
</div>

</div>
<!-- End of Content Row -->

</div>
<!-- /.container-fluid -->

</div>
<!-- End of Main Content -->

<?php
include('includes/scripts.php');
include('includes/footer.php');
?>

<!-- Load Chart.js from CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
// === BAR CHART ===
const barCtx = document.getElementById("mynewBarChart").getContext("2d");

const invoiceLabels = <?php echo json_encode($invoiceLabels); ?>;
const invoiceAmounts = <?php echo json_encode($invoiceAmounts); ?>;

new Chart(barCtx, {
    type: 'bar',
    data: {
        labels: invoiceLabels,
        datasets: [{
            label: "Total Amount (Rs.)",
            data: invoiceAmounts,
            backgroundColor: "rgba(78, 115, 223, 0.6)",
            borderColor: "rgba(78, 115, 223, 1)",
            borderWidth: 1
        }]
    },
    options: {
        // indexAxis: 'y', ← ❌ REMOVE this line to make it vertical
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: {
                title: { display: true, text: 'Invoice Number' },
                ticks: { autoSkip: false, maxRotation: 90, minRotation: 45 }
            },
            y: {
                beginAtZero: true,
                title: { display: true, text: 'Total Amount (Rs.)' }
            }
        },
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function (context) {
                        return `Rs. ${context.parsed.y.toFixed(2)}`;
                    }
                }
            }
        }
    }
});

// === PIE CHART ===
const pieLabels = <?php echo json_encode($categoryLabels); ?>;
const pieData = <?php echo json_encode($categoryCounts); ?>;
const pieColors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'];

const pieCtx = document.getElementById("mynewPieChart").getContext("2d");

new Chart(pieCtx, {
    type: 'pie',
    data: {
        labels: pieLabels,
        datasets: [{
            data: pieData,
            backgroundColor: pieColors,
            borderColor: "#fff",
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function (context) {
                        return `${context.label}: ${context.raw} product(s)`;
                    }
                }
            }
        }
    }
});

// === Custom Pie Legend ===
const legendContainer = document.getElementById('categoryLegend');
pieLabels.forEach((label, index) => {
    const color = pieColors[index % pieColors.length];
    const count = pieData[index];
    legendContainer.innerHTML += `
        <span class="mr-3">
            <i class="fas fa-circle" style="color:${color}"></i> ${label} (${count})
        </span>
    `;
});
});
</script>