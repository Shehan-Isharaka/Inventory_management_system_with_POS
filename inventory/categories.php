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
        <div class="container-fluid">
            <!-- Page Heading -->
            <h1 class="h3 mb-2 text-gray-800">Categories</h1>

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



            <!-- Categories Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Category Details</h6>
                    <a href="#" data-toggle="modal" data-target="#addCatModel" class="btn btn-primary btn-sm">Add
                        Category</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="dataTable" class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Category Name</th>
                                    <th>Last Modified By</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>ID</th>
                                    <th>Category Name</th>
                                    <th>Last Modified By</th>
                                    <th>Action</th>
                                </tr>
                            </tfoot>
                            <tbody>
                                <?php
                                $sql = "SELECT c.Category_ID, c.Category_Name, u.username AS Modified_By_Username
            FROM categories c
            LEFT JOIN users u ON c.Modified_By = u.id";
                                $result = mysqli_query($conn, $sql);
                                if (mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo '<tr>
                <td>' . htmlspecialchars($row['Category_ID']) . '</td>
                <td>' . htmlspecialchars($row['Category_Name']) . '</td>
                <td>' . htmlspecialchars($row['Modified_By_Username'] ?? 'N/A') . '</td>
                <td>
                    <button type="button" class="btn btn-success editbtn btn-sm"><i class="fas fa-edit"></i></button>
                    <button type="button" class="btn btn-danger deletebtn btn-sm"><i class="fas fa-trash-alt"></i></button>
                </td>
            </tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan="4">No records found</td></tr>';
                                }
                                ?>
                            </tbody>

                        </table>
                    </div>
                </div>
            </div>

            <!-- Auto-generate next Category ID -->
            <?php
            $sql = "SELECT Category_ID FROM categories ORDER BY Category_ID DESC LIMIT 1";
            $result = mysqli_query($conn, $sql);
            if ($result && mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $lastID = $row['Category_ID'];
                $num = intval(substr($lastID, 3)) + 1;
                $nextID = 'CAT' . str_pad($num, 3, '0', STR_PAD_LEFT);
            } else {
                $nextID = 'CAT001';
            }
            ?>

            <!-- Add Modal -->
            <div class="modal fade" id="addCatModel" tabindex="-1" role="dialog" aria-labelledby="catModalLabel"
                aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form action="" method="POST">
                            <div class="modal-header">
                                <h5 class="modal-title">Add Category</h5>
                                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Category ID</label>
                                    <input type="text" class="form-control" name="categoryid"
                                        value="<?php echo $nextID; ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Category Name</label>
                                    <input type="text" class="form-control" name="categoryname" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="addcat" class="btn btn-primary">Add</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Modal -->
            <div class="modal fade" id="editCatModal" tabindex="-1" role="dialog" aria-labelledby="editCatLabel"
                aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form action="" method="POST">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Category</h5>
                                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Category ID</label>
                                    <input type="text" class="form-control" name="category_id" id="edit_category_id"
                                        readonly>
                                </div>
                                <div class="form-group">
                                    <label>Category Name</label>
                                    <input type="text" class="form-control" name="categoryname" id="edit_categoryname"
                                        required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="updatecat" class="btn btn-primary">Update</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>


            <!-- Delete Modal -->
            <div class="modal fade" id="deleteCatModal" tabindex="-1" role="dialog" aria-labelledby="deleteCatLabel"
                aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form action="" method="POST">
                            <div class="modal-header">
                                <h5 class="modal-title">Delete Category</h5>
                                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="delete_category_id" id="delete_category_id">
                                <p>Are you sure you want to delete this category?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="deletecat" class="btn btn-danger">Yes, Delete</button>
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

// Insert
if (isset($_POST['addcat'])) {
    $categoryid = mysqli_real_escape_string($conn, $_POST['categoryid']);
    $categoryname = mysqli_real_escape_string($conn, $_POST['categoryname']);
    $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

    $sql = "INSERT INTO categories (Category_ID, Category_Name, Created_By, Modified_By) VALUES ('$categoryid', '$categoryname', $user_id, $user_id)";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Category has been added successfully.";
    } else {
        $_SESSION['status'] = "Error adding category: " . mysqli_error($conn);
    }
    header("Location: categories.php");
    exit();
}

// Update
if (isset($_POST['updatecat'])) {
    $categoryid = mysqli_real_escape_string($conn, $_POST['category_id']);
    $categoryname = mysqli_real_escape_string($conn, $_POST['categoryname']);
    $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

    $sql = "UPDATE categories SET Category_Name = '$categoryname', Modified_By = $user_id WHERE Category_ID = '$categoryid'";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['updated'] = "Category has been updated successfully.";
    } else {
        $_SESSION['status'] = "Error updating category: " . mysqli_error($conn);
    }
    header("Location: categories.php");
    exit();
}

// Delete
if (isset($_POST['deletecat'])) {
    $categoryid = mysqli_real_escape_string($conn, $_POST['delete_category_id']);
    $sql = "DELETE FROM categories WHERE Category_ID = '$categoryid'";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['deleted'] = "Category has been deleted successfully.";
    } else {
        $_SESSION['status'] = "Error deleting category: " . mysqli_error($conn);
    }
    header("Location: categories.php");
    exit();
}


include('includes/scripts.php');
include('includes/footer.php');
?>

<!-- Modal Data Fill Script -->
<script>
    $(document).ready(function () {
        $('.editbtn').on('click', function () {
            $('#editCatModal').modal('show');
            var data = $(this).closest("tr").children("td").map(function () {
                return $(this).text();
            }).get();
            $('#edit_category_id').val(data[0]);
            $('#edit_categoryname').val(data[1]);
        });

        $('.deletebtn').on('click', function () {
            $('#deleteCatModal').modal('show');
            var data = $(this).closest("tr").children("td").map(function () {
                return $(this).text();
            }).get();
            $('#delete_category_id').val(data[0]);
        });

        // Optional: Activate DataTables
        $('#dataTable').DataTable();
    });
</script>

<?php ob_end_flush(); ?>