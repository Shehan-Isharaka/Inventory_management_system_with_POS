<?php
ob_start();
session_start();
include('includes/header.php');
include('includes/navbar.php');
include('../includes/dbconfig.php');

// Check if product ID is passed
if (!isset($_GET['product_id'])) {
    $_SESSION['status'] = 'No product selected to edit.';
    $_SESSION['status_code'] = 'error';
    header('Location: product.php');
    exit();
}

$product_id = mysqli_real_escape_string($conn, $_GET['product_id']);

// Update product details if the form is submitted
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
    $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

    // Update query
    $update_sql = "UPDATE products 
                   SET Product_Name='$product_name', 
                       Category_ID='$category_id', 
                       Subcategory_ID='$subcategory_id', 
                       Brand_ID='$brand_id', 
                       Model='$model', 
                       Purchase_Price='$purchase_price', 
                       Selling_Price='$selling_price', 
                       Stock_Quantity='$stock_quantity', 
                       Modified_By='$user_id', 
                       Modified_At=NOW() 
                   WHERE Product_ID='$product_id'";

    // if (mysqli_query($conn, $update_sql)) {
    //     $_SESSION['status'] = 'Product updated successfully.';
    //     $_SESSION['status_code'] = 'success';
    // } else {
    //     $_SESSION['status'] = 'Error updating product: ' . mysqli_error($conn);
    //     $_SESSION['status_code'] = 'error';
    // }

    if (mysqli_query($conn, $update_sql)) {
        $_SESSION['updated'] = "Product has been updated successfully.";
    } else {
        $_SESSION['status'] = "Error updating product: " . mysqli_error($conn);
    }
    header("Location: product.php");


    // header('Location: product.php');
    exit();
}

// Fetch product details for the edit form
$query = "SELECT * FROM products WHERE Product_ID = '$product_id'";
$result = mysqli_query($conn, $query);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    $_SESSION['status'] = 'Product not found.';
    $_SESSION['status_code'] = 'error';
    header('Location: product.php');
    exit();
}

?>

<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Edit Product</h1>

    <?php if (isset($_SESSION['status'])): ?>
        <div class="alert alert-danger"> <?= $_SESSION['status'];
        unset($_SESSION['status']); ?> </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Edit Product Details</h6>
        </div>
        <div class="card-body">
            <form action="" method="POST">
                <!-- Row 1 -->
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Product ID</label>
                        <input type="text" class="form-control" name="product_id" value="<?= $product['Product_ID']; ?>"
                            readonly>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Product Name</label>
                        <input type="text" class="form-control" name="product_name"
                            value="<?= $product['Product_Name']; ?>" required>
                    </div>
                </div>

                <!-- Row 2 -->
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Select Category</label>
                        <select class="form-control" name="catid" id="categoryid" required>
                            <option selected disabled value="">Select Category</option>
                            <?php
                            $cat_sql = "SELECT * FROM categories";
                            $cat_result = mysqli_query($conn, $cat_sql);
                            while ($cat_row = mysqli_fetch_assoc($cat_result)) {
                                $selected = ($cat_row['Category_ID'] == $product['Category_ID']) ? 'selected' : '';
                                echo '<option value="' . $cat_row['Category_ID'] . '" ' . $selected . '>' . $cat_row['Category_Name'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Select Subcategory</label>
                        <select class="form-control" name="subcatid" id="subcategoryid" required>
                            <option selected disabled value="">Select Subcategory</option>
                            <?php
                            $subcat_sql = "SELECT * FROM subcategories WHERE Category_ID = '{$product['Category_ID']}'";
                            $subcat_result = mysqli_query($conn, $subcat_sql);
                            while ($subcat_row = mysqli_fetch_assoc($subcat_result)) {
                                $selected = ($subcat_row['Subcategory_ID'] == $product['Subcategory_ID']) ? 'selected' : '';
                                echo '<option value="' . $subcat_row['Subcategory_ID'] . '" ' . $selected . '>' . $subcat_row['Subcategory_Name'] . '</option>';
                            }
                            ?>
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
                        <label>Model</label>
                        <input type="text" class="form-control" name="model" value="<?= $product['Model']; ?>" required>
                    </div>
                </div>

                <!-- Row 4 -->
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Purchase Price</label>
                        <input type="number" class="form-control" name="purchase_price"
                            value="<?= $product['Purchase_Price']; ?>" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Selling Price</label>
                        <input type="number" class="form-control" name="selling_price"
                            value="<?= $product['Selling_Price']; ?>" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Stock Quantity</label>
                        <input type="number" class="form-control" name="stock_quantity"
                            value="<?= $product['Stock_Quantity']; ?>" required>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="form-row">
                    <div class="form-group col-md-12 text-right">
                        <button type="submit" class="btn btn-primary">Update Product</button>
                        <a href="product.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Fetch subcategories when category changes
    $('#categoryid').on('change', function () {
        var cat_id = $(this).val();
        $.ajax({
            url: 'fetch-subcategories.php',
            type: 'POST',
            data: { category_id: cat_id },
            success: function (response) {
                $('#subcategoryid').html(response);
            }
        });
    });
</script>

<?php
include('includes/scripts.php');
include('includes/footer.php');
?>