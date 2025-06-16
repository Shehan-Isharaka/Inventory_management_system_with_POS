<?php
ob_start();
session_start();
include('includes/header.php');
include('includes/navbar.php');
include('../includes/dbconfig.php');



// Delete Logic
if (isset($_POST['deletebrand'])) {
    $delete_brand_id = $_POST['delete_brand_id'];

    $delete_query = "DELETE FROM brands WHERE brand_id = '$delete_brand_id'";
    $delete_run = mysqli_query($conn, $delete_query);

    if ($delete_run) {
        $_SESSION['deleted'] = "Brand deleted successfully!";
        header("Location: brand.php");
    } else {
        $_SESSION['status'] = "Failed to delete brand.";
        header("Location: brand.php");
    }
}

// Auto-generate next Brand ID
$sql = "SELECT brand_id FROM brands ORDER BY brand_id DESC LIMIT 1";
$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $lastID = $row['brand_id'];
    $num = intval(substr($lastID, 1)) + 1;
    $nextID = 'B' . str_pad($num, 3, '0', STR_PAD_LEFT);
} else {
    $nextID = 'B001';
}
?>

<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">
    <!-- Main Content -->
    <div id="content-header">
        <div class="container-fluid">
            <h1 class="h3 mb-2 text-gray-800">Brands</h1>

            <!-- Success/Error Messages -->
            <?php
            // Displaying success and error messages
            if (isset($_SESSION['success'])) {
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong><i class="fas fa-check-circle"></i> Success!</strong> ' . $_SESSION['success'] . '
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
                unset($_SESSION['success']);
            }

            if (isset($_SESSION['updated'])) {
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong><i class="fas fa-sync-alt"></i> Updated!</strong> ' . $_SESSION['updated'] . '
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
                unset($_SESSION['updated']);
            }

            if (isset($_SESSION['deleted'])) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong><i class="fas fa-trash-alt"></i> Deleted!</strong> ' . $_SESSION['deleted'] . '
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
                unset($_SESSION['deleted']);
            }

            if (isset($_SESSION['status'])) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong><i class="fas fa-exclamation-triangle"></i> Error!</strong> ' . $_SESSION['status'] . '
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
                unset($_SESSION['status']);
            }
            ?>

            <!-- Brand Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Brand Details</h6>
                    <a href="#" data-toggle="modal" data-target="#addBrandModal" class="btn btn-primary btn-sm">Add Brand</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="dataTable" class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Brand ID</th>
                                    <th>Brand Name</th>
                                    <th>Last Modified By</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Fetching brand data
                                $query = "SELECT b.brand_id, b.brand_name, u1.username AS created_by, u2.username AS modified_by 
                                          FROM brands b
                                          LEFT JOIN users u1 ON b.created_by = u1.id 
                                          LEFT JOIN users u2 ON b.modified_by = u2.id
                                          ORDER BY b.brand_id ASC";
                                $query_run = mysqli_query($conn, $query);
                                while ($row = mysqli_fetch_assoc($query_run)) {
                                    echo "<tr>
                                            <td>{$row['brand_id']}</td>
                                            <td>{$row['brand_name']}</td>
                                            <td>" . ($row['modified_by'] ?? 'N/A') . "</td>
                                            <td>
                                                <button type='button' class='btn btn-success btn-sm editbtn' data-id='{$row['brand_id']}' data-name='{$row['brand_name']}'>
                                                    <i class='fas fa-edit'></i>
                                                </button>
                                                <button type='button' class='btn btn-danger btn-sm deletebtn' data-id='{$row['brand_id']}'>
                                                    <i class='fas fa-trash-alt'></i>
                                                </button>
                                            </td>
                                        </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Add Modal -->
            <div class="modal fade" id="addBrandModal" tabindex="-1" role="dialog" aria-labelledby="addBrandLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form action="" method="POST">
                            <div class="modal-header">
                                <h5 class="modal-title">Add Brand</h5>
                                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Brand ID</label>
                                    <input type="text" class="form-control" name="brandid" value="<?php echo $nextID; ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Brand Name</label>
                                    <input type="text" class="form-control" name="brandname" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="addbrand" class="btn btn-primary">Add</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Modal -->
            <div class="modal fade" id="editBrandModal" tabindex="-1" role="dialog" aria-labelledby="editBrandLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form action="" method="POST">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Brand</h5>
                                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="brand_id" id="edit_brand_id">
                                <div class="form-group">
                                    <label>Brand Name</label>
                                    <input type="text" class="form-control" name="brandname" id="edit_brandname" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="updatebrand" class="btn btn-primary">Update</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Delete Modal -->
            <div class="modal fade" id="deleteBrandModal" tabindex="-1" role="dialog" aria-labelledby="deleteBrandLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form action="" method="POST">
                            <div class="modal-header">
                                <h5 class="modal-title">Delete Brand</h5>
                                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="delete_brand_id" id="delete_brand_id">
                                <p>Are you sure you want to delete this brand?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="deletebrand" class="btn btn-danger">Yes, Delete</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Insert Logic
if (isset($_POST['addbrand'])) {
    $brandid = $_POST['brandid'];
    $brandname = $_POST['brandname'];
    $created_by = $_SESSION['user_id'];  // Assuming session contains user_id
    
    $insert_query = "INSERT INTO brands (brand_id, brand_name, created_by) VALUES ('$brandid', '$brandname', '$created_by')";
    $insert_run = mysqli_query($conn, $insert_query);

    if ($insert_run) {
        $_SESSION['success'] = "Brand added successfully!";
        header("Location: brand.php");
    } else {
        $_SESSION['status'] = "Failed to add brand.";
        header("Location: brand.php");
    }
}

// Update Logic
if (isset($_POST['updatebrand'])) {
    $brand_id = $_POST['brand_id'];
    $brandname = $_POST['brandname'];
    $modified_by = $_SESSION['user_id'];

    $update_query = "UPDATE brands SET brand_name = '$brandname', modified_by = '$modified_by' WHERE brand_id = '$brand_id'";
    $update_run = mysqli_query($conn, $update_query);

    if ($update_run) {
        $_SESSION['updated'] = "Brand updated successfully!";
        header("Location: brand.php");
    } else {
        $_SESSION['status'] = "Failed to update brand.";
        header("Location: brand.php");
    }
}


include('includes/scripts.php');
include('includes/footer.php');

?>

<script>
// Handle Edit Modal
$(document).on('click', '.editbtn', function() {
    var brand_id = $(this).data('id');
    var brand_name = $(this).data('name');
    
    $('#edit_brand_id').val(brand_id);
    $('#edit_brandname').val(brand_name);
    $('#editBrandModal').modal('show');
});

// Handle Delete Modal
$(document).on('click', '.deletebtn', function() {
    var brand_id = $(this).data('id');
    $('#delete_brand_id').val(brand_id);
    $('#deleteBrandModal').modal('show');
});
</script>

<?php ob_end_flush(); ?>