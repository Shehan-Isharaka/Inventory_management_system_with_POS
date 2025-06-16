<?php
session_start();
include('includes/header.php');
include('includes/navbar.php');
include('../includes/dbconfig.php');



// Get category count
$categoryCountQuery = mysqli_query($conn, "SELECT COUNT(*) AS count FROM categories");
$categoryCount = mysqli_fetch_assoc($categoryCountQuery)['count'];

// Get product count
$productCountQuery = mysqli_query($conn, "SELECT COUNT(*) AS count FROM products");
$productCount = mysqli_fetch_assoc($productCountQuery)['count'];

// Get supplier count
$supplierCountQuery = mysqli_query($conn, "SELECT COUNT(*) AS count FROM suppliers");
$supplierCount = mysqli_fetch_assoc($supplierCountQuery)['count'];

// Get total stock quantity (sum of stock quantities)
$totalStockQuery = mysqli_query($conn, "SELECT SUM(Stock_Quantity) AS total FROM products");
$totalStock = mysqli_fetch_assoc($totalStockQuery)['total'];

// Get total sales from sales_invoices
$salesQuery = mysqli_query($conn, "SELECT SUM(total_amount) AS total_sales FROM sales_invoices");
$salesTotal = mysqli_fetch_assoc($salesQuery)['total_sales'];
$salesTotal = $salesTotal ?? 0; // fallback if null

// Get total expenses from purchase_invoices
$expensesQuery = mysqli_query($conn, "SELECT SUM(total_amount) AS total_expenses FROM purchase_invoices");
$expensesTotal = mysqli_fetch_assoc($expensesQuery)['total_expenses'];
$expensesTotal = $expensesTotal ?? 0; // fallback if null

// Calculate profit
$profitTotal = $salesTotal - $expensesTotal;

// Get total users
$userQuery = mysqli_query($conn, "SELECT COUNT(*) AS total_users FROM users");
$userCount = mysqli_fetch_assoc($userQuery)['total_users'];


// Start and end of the current week (Monday to Sunday)
$startOfWeek = date('Y-m-d', strtotime('monday this week'));
$endOfWeek = date('Y-m-d', strtotime('sunday this week'));

$labels = [];
$totals = [];

// Create an array for each day of the current week with 0 as default sales
$weekDays = [];
$dayPointer = strtotime($startOfWeek);
while ($dayPointer <= strtotime($endOfWeek)) {
    $day = date('Y-m-d', $dayPointer);
    $weekDays[$day] = 0;
    $dayPointer = strtotime('+1 day', $dayPointer);
}

// Fetch sales for the current week from sales_invoices
$sql = "SELECT DATE(sale_date) as sale_day, SUM(total_amount) as total_sales
        FROM sales_invoices
        WHERE sale_date BETWEEN '$startOfWeek' AND '$endOfWeek'
        GROUP BY sale_day
        ORDER BY sale_day ASC";

$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    $saleDay = $row['sale_day'];
    if (isset($weekDays[$saleDay])) {
        $weekDays[$saleDay] = $row['total_sales'];
    }
}

// Prepare labels and totals for the chart
$labels = array_keys($weekDays);
$totals = array_values($weekDays);


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
                <!-- <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                                class="fas fa-download fa-sm text-white-50"></i> Generate Report</a> -->
            </div>

            <!-- Content Row -->

            <div class="row">

                <!-- Earnings (Monthly) Card Example -->
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

                <!-- Earnings (Monthly) Card Example -->
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

                <!-- Earnings (Monthly) Card Example -->
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

                <!-- Pending Requests Card Example -->
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

                <!-- Earnings (Monthly) Card Example -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Sales</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        Rs. <?php echo number_format($salesTotal, 2); ?>
                                    </div>

                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-cash-register fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Earnings (Monthly) Card Example -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Profit</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        Rs. <?php echo number_format($profitTotal, 2); ?>
                                    </div>

                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Earnings (Monthly) Card Example -->
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

                <!-- Pending Requests Card Example -->
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

                <!-- Area Chart -->
                <div class="col-xl-8 col-lg-7">
                    <div class="card shadow mb-4">
                        <!-- Card Header - Dropdown -->
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Earnings Overview (Weekly)</h6>
                        </div>
                        <!-- Card Body -->
                        <div class="card-body">
                            <div class="chart-area">
                                <canvas id="mynewAreaChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pie Chart -->
                <div class="col-xl-4 col-lg-5">
                    <div class="card shadow mb-4">
                        <!-- Card Header -->
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Products by Category</h6>
                        </div>
                        <!-- Card Body -->
                        <div class="card-body">
                            <div class="chart-pie pt-4 pb-2 d-flex justify-content-center">
                                <canvas id="mynewPieChart" style="max-width: 250px;"></canvas>
                            </div>
                            <div class="mt-4 text-center small" id="categoryLegend"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- /.container-fluid -->

    </div>
    <!-- End of Main Content -->




    <?php

    include('includes/scripts.php');
    include('includes/footer.php');

    ?>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // === AREA CHART ===
            const areaCtx = document.getElementById("mynewAreaChart").getContext('2d');
            const areaChart = new Chart(areaCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($labels); ?>,
                    datasets: [{
                        label: "Total Sales (Rs.)",
                        data: <?php echo json_encode($totals); ?>,
                        backgroundColor: "rgba(78, 115, 223, 0.2)",
                        borderColor: "rgba(78, 115, 223, 1)",
                        pointRadius: 3,
                        pointBackgroundColor: "rgba(78, 115, 223, 1)",
                        pointBorderColor: "rgba(78, 115, 223, 1)",
                        pointHoverRadius: 4,
                        pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                        pointHitRadius: 10,
                        pointBorderWidth: 2,
                        tension: 0.3,
                        fill: true,
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            type: 'category',
                            grid: { display: false }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: "rgba(234, 236, 244, 1)" }
                        }
                    },
                    plugins: {
                        legend: { display: true },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return `Rs. ${context.parsed.y.toLocaleString()}`;
                                }
                            }
                        }
                    }
                }
            });

            // === PIE CHART ===
            const pieLabels = <?php echo json_encode($categoryLabels); ?>;
            const pieData = <?php echo json_encode($categoryCounts); ?>;
            const pieColors = [
                '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e',
                '#e74a3b', '#858796', '#20c997', '#6f42c1',
                '#fd7e14', '#6c757d'
            ];

            const pieCtx = document.getElementById("mynewPieChart").getContext("2d");

            const pieChart = new Chart(pieCtx, {
                type: 'pie',
                data: {
                    labels: pieLabels,
                    datasets: [{
                        data: pieData,
                        backgroundColor: pieColors,
                        hoverBackgroundColor: pieColors,
                        borderColor: "#fff",
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    return `${label}: ${value} product(s)`;
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


    <!-- Required Chart.js scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/luxon@3/build/global/luxon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-luxon@1"></script>