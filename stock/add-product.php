<?php
ob_start();
session_start();
include('includes/header.php');
include('includes/navbar.php');
include('../includes/dbconfig.php');


// Generate next Product ID like PROD001, PROD002
$sql = "SELECT product_id FROM products ORDER BY product_id DESC LIMIT 1";
$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $lastId = $row['product_id'];
    $num = (int) substr($lastId, 4); // Skip 'PROD'
    $num++;
    $nextProductID = 'PROD' . str_pad($num, 3, '0', STR_PAD_LEFT);
} else {
    $nextProductID = 'PROD001'; // First product ID
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $category_id = mysqli_real_escape_string($conn, $_POST['catid']);
    $subcategory_id = mysqli_real_escape_string($conn, $_POST['subcatid']);
    $brand_id = mysqli_real_escape_string($conn, $_POST['brandid']);
    $model = mysqli_real_escape_string($conn, $_POST['model']);
    $purchase_price = floatval($_POST['purchase_price']);
    $selling_price = floatval($_POST['selling_price']);
    $stock_quantity = intval($_POST['stock_quantity']);
    $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0; // adjust user tracking as needed

    $insert_sql = "INSERT INTO products 
        (product_id, product_name, Category_ID, Subcategory_ID, Brand_ID, model, purchase_price, selling_price, stock_quantity, created_by, created_at)
        VALUES
        ('$product_id', '$product_name', '$category_id', '$subcategory_id', '$brand_id', '$model', $purchase_price, $selling_price, $stock_quantity, $user_id, NOW())";

    // Improved Error Handling
    if (mysqli_query($conn, $insert_sql)) {
        $_SESSION['success'] = "Product added successfully with ID: $product_id";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['error'] = "Error adding product: " . mysqli_error($conn) . " SQL: " . $insert_sql;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

}

?>

<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">

    <!-- Main Content -->
    <div id="content-header">



        <!-- Begin Page Content -->
        <div class="container-fluid">

            <!-- Page Heading -->
            <h1 class="h3 mb-2 text-gray-800">Products</h1>

            <!-- Success/Error Messages -->
            <?php
            if (isset($_SESSION['success'])) {
                echo '
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong><i class="fas fa-check-circle"></i> Success!</strong> ' . $_SESSION['success'] . '
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>';
                unset($_SESSION['success']);
            }

            if (isset($_SESSION['updated'])) {
                echo '
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong><i class="fas fa-sync-alt"></i> Updated!</strong> ' . $_SESSION['updated'] . '
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>';
                unset($_SESSION['updated']);
            }

            if (isset($_SESSION['deleted'])) {
                echo '
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong><i class="fas fa-trash-alt"></i> Deleted!</strong> ' . $_SESSION['deleted'] . '
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>';
                unset($_SESSION['deleted']);
            }

            if (isset($_SESSION['status'])) {
                echo '
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong><i class="fas fa-exclamation-triangle"></i> Error!</strong> ' . $_SESSION['status'] . '
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>';
                unset($_SESSION['status']);
            }
            ?>




            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Add Product Details</h6>
                </div>
                <div class="card-body">
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                        <!-- Row 1 -->
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="productID">Product ID</label>
                                <input type="text" class="form-control" id="productID" name="product_id"
                                    value="<?php echo $nextProductID; ?>" readonly>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="productName">Product Name</label>
                                <input type="text" class="form-control" id="productName" name="product_name"
                                    placeholder="Enter Product Name" required>
                            </div>
                        </div>

                        <!-- Row 2 -->
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="categoryid">Select Category</label>
                                <select class="form-control" name="catid" id="categoryid" required>
                                    <option selected disabled value="">Select Category</option>
                                    <?php
                                    $cat_sql = "SELECT * FROM categories";
                                    $cat_result = mysqli_query($conn, $cat_sql);
                                    while ($cat_row = mysqli_fetch_assoc($cat_result)) {
                                        echo '<option value="' . $cat_row['Category_ID'] . '">' . $cat_row['Category_Name'] . '</option>';
                                    }
                                    ?>
                                </select>

                            </div>
                            <div class="form-group col-md-6">
                                <label for="subcategoryid">Select Sub Category</label>
                                <select class="form-control" name="subcatid" id="subcategoryid" required>
                                    <option selected disabled value="">Select Subcategory</option>
                                </select>
                            </div>
                        </div>

                        <!-- Row 3 -->
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="brand">Select Brand</label>
                                <select name="brandid" class="form-control" id="brand" required>
                                    <option selected disabled value="">Select Brand</option>
                                    <?php
                                    $brand_sql = "SELECT * FROM brands";
                                    $brand_result = mysqli_query($conn, $brand_sql);
                                    if ($brand_result && mysqli_num_rows($brand_result) > 0) {
                                        while ($brand_row = mysqli_fetch_assoc($brand_result)) {
                                            // Sanitize the brand name to prevent encoding issues
                                            $brand_name = htmlspecialchars($brand_row['brand_name'], ENT_QUOTES, 'UTF-8');
                                            echo '<option value="' . $brand_row['brand_id'] . '">' . $brand_name . '</option>';
                                        }
                                    } else {
                                        echo '<option disabled>No brands found</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="model">Model</label>
                                <input type="text" class="form-control" id="model" name="model"
                                    placeholder="Enter Model" required>
                            </div>
                        </div>

                        <!-- Row 4 -->
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="purchasePrice">Purchase Price</label>
                                <input type="number" class="form-control" id="purchasePrice" name="purchase_price"
                                    placeholder="Enter Purchase Price" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="sellingPrice">Selling Price</label>
                                <input type="number" class="form-control" id="sellingPrice" name="selling_price"
                                    placeholder="Enter Selling Price" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="stockQty">Stock Quantity</label>
                                <input type="number" class="form-control" id="stockQty" name="stock_quantity"
                                    placeholder="Enter Stock Quantity" required>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="form-row">
                            <div class="form-group col-md-12 text-right">
                                <button type="submit" class="btn btn-primary">Add Product</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
        <!-- /.container-fluid -->

    </div>
    <!-- End of Main Content -->

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#categoryid').on('change', function () {
                console.log("Category changed!");
                var category_id = $(this).val();
                if (category_id) {
                    $.ajax({
                        url: "get_subcategories.php",
                        type: "POST",
                        data: { category_id: category_id },
                        dataType: "html",
                        success: function (data) {
                            //console.log("Response from server: " + data);
                            $('#subcategoryid').html(data);
                        },
                        error: function (xhr, status, error) {
                            //console.error("AJAX Error: " + xhr.status + " - " + error);
                            alert('Error loading subcategories.');
                        }
                    });
                } else {
                    $('#subcategoryid').html('<option selected disabled>Select Subcategory</option>');
                }
            });
        });
    </script>


    <?php

    include('includes/scripts.php');
    include('includes/footer.php');

    ?>