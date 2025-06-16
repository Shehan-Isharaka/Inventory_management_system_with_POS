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
            <h1 class="h3 mb-2 text-gray-800">Products</h1>
            <!-- <p class="mb-4">DataTables is a third party plugin that is used to generate the demo table below.
                        For more information about DataTables, please visit the <a target="_blank"
                            href="https://datatables.net">official DataTables documentation</a>.</p> -->

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

            <!-- DataTales Example -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Product Details</h6>
                    <a href="add-product.php" class="btn btn-primary btn-sm float-right">Add Product</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Product ID</th>
                                    <th>Product Name</th>
                                    <th>Brand</th>
                                    <th>Model</th>
                                    <th>Purchase Price</th>
                                    <th>Selling Price</th>
                                    <th>Stock Quantity</th>
                                    <th>Last Modified By</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tfoot class="thead-light">
                                <tr>
                                    <th>Product ID</th>
                                    <th>Product Name</th>
                                    <th>Brand</th>
                                    <th>Model</th>
                                    <th>Purchase Price</th>
                                    <th>Selling Price</th>
                                    <th>Stock Quantity</th>
                                    <th>Last Modified By</th>
                                    <th>Action</th>
                                </tr>
                            </tfoot>
                            <tbody>
                                <?php
                                $query = "SELECT p.product_id, p.product_name, b.brand_name, p.model, p.purchase_price, p.selling_price, 
                                        p.stock_quantity, u.username AS modified_by
                                FROM products p
                                LEFT JOIN brands b ON p.Brand_ID = b.brand_id
                                LEFT JOIN users u ON p.modified_by = u.id";
                                $query_run = mysqli_query($conn, $query);

                                if (mysqli_num_rows($query_run) > 0) {
                                    while ($row = mysqli_fetch_assoc($query_run)) {
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['product_id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['brand_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['model']); ?></td>
                                            <td><?php echo 'Rs. ' . number_format((float) $row['purchase_price'], 2); ?></td>
                                            <td><?php echo 'Rs. ' . number_format((float) $row['selling_price'], 2); ?></td>
                                            <td><?php echo htmlspecialchars($row['stock_quantity']); ?></td>
                                            <td><?php echo htmlspecialchars($row['modified_by'] ?? 'N/A'); ?></td>
                                            <td>
                                                <a href="edit-product.php?product_id=<?php echo urlencode($row['product_id']); ?>"
                                                    class="btn btn-success btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                <button type="button" class="btn btn-danger deletebtn btn-sm"
                                                    data-id="<?php echo htmlspecialchars($row['product_id']); ?>">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>

                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='9' class='text-center'>No products found.</td></tr>";
                                }
                                ?>

                            </tbody>
                        </table>
                    </div>
                </div>

            </div>


        </div>
        <!-- /.container-fluid -->

    </div>
    <!-- End of Main Content -->


    <!-- Delete Modal -->
    <div class="modal fade" id="deleteProductModal" tabindex="-1" role="dialog" aria-labelledby="deleteLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Product</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <input type="text" name="delete_product_id" id="delete_product_id">
                        <p>Are you sure you want to delete this product?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="delete" class="btn btn-danger">Yes, Delete</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Delete Product Script -->
    <?php
    if (isset($_POST['delete'])) {
        $product_id = mysqli_real_escape_string($conn, $_POST['delete_product_id']);
        $sql = "DELETE FROM products WHERE Product_ID='$product_id'";
        if (mysqli_query($conn, $sql)) {
            $_SESSION['deleted'] = "Product deleted successfully.";
        } else {
            $_SESSION['status'] = "Error deleting product: " . mysqli_error($conn);
        }
        header("Location: product.php");
        exit();
    }
    ?>

    <?php

    include('includes/scripts.php');
    include('includes/footer.php');

    ?>

    <!-- Script to Trigger Delete Modal -->
    <script>
        $(document).ready(function () {
            $('.deletebtn').on('click', function () {
                var id = $(this).data('id');
                $('#delete_product_id').val(id);
                $('#deleteProductModal').modal('show');
            });
        });
    </script>