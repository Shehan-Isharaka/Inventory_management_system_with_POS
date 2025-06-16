<?php
ob_start();
session_start();
include('includes/header.php');
include('includes/navbar.php');
include('../includes/dbconfig.php');


// Initialize the variable to avoid undefined variable error
$nextSubCatID = 'SUB001';

// Fetch the last inserted Subcategory ID
$sql = "SELECT Subcategory_ID FROM subcategories ORDER BY Subcategory_ID DESC LIMIT 1";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    // Extract the numeric part from the last ID (e.g., SUB001 -> 1)
    $lastID = intval(substr($row['Subcategory_ID'], 3));
    // Increment the number part and format it with leading zeros
    $nextID = str_pad($lastID + 1, 3, '0', STR_PAD_LEFT);
    // Generate the next Subcategory ID
    $nextSubCatID = 'SUB' . $nextID;
}


?>

<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">
    <!-- Main Content -->
    <div id="content-header">
        <div class="container-fluid">
            <!-- Page Heading -->
            <h1 class="h3 mb-2 text-gray-800">Subcategories</h1>

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

            <!-- Subcategories Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Subcategory Details</h6>
                    <a href="#" data-toggle="modal" data-target="#addSubCatModel" class="btn btn-primary btn-sm">Add
                        Subcategory</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="dataTable" class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Subcategory Name</th>
                                    <th>Category Name</th>
                                    <th>Last Modified By</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>ID</th>
                                    <th>Subcategory Name</th>
                                    <th>Category Name</th>
                                    <th>Last Modified By</th>
                                    <th>Action</th>
                                </tr>
                            </tfoot>
                            <tbody>
                                <?php
                                $sql = "SELECT subcategories.*, categories.Category_Name, users.username 
        FROM subcategories 
        JOIN categories ON subcategories.Category_ID = categories.Category_ID
        LEFT JOIN users ON subcategories.Modified_By = users.id";  // join users for username
                                
                                $result = mysqli_query($conn, $sql);
                                if (mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo '<tr>
            <td>' . htmlspecialchars($row['Subcategory_ID']) . '</td>
            <td>' . htmlspecialchars($row['Subcategory_Name']) . '</td>
            <td>' . htmlspecialchars($row['Category_Name']) . '</td>
            <td>' . htmlspecialchars($row['username'] ?? 'N/A') . '</td>  <!-- Show username or N/A -->
            <td>
                <button type="button" class="btn btn-success editbtn btn-sm"><i class="fas fa-edit"></i></button>
                <button type="button" class="btn btn-danger deletebtn btn-sm"><i class="fas fa-trash-alt"></i></button>
            </td>
        </tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan="5">No records found</td></tr>';
                                }
                                ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Add Subcategory Modal -->
            <div class="modal fade" id="addSubCatModel" tabindex="-1" role="dialog" aria-labelledby="subCatModalLabel"
                aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form action="" method="POST">
                            <div class="modal-header">
                                <h5 class="modal-title">Add Subcategory</h5>
                                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Subcategory ID</label>
                                    <input type="text" class="form-control" name="subcategoryid"
                                        value="<?php echo $nextSubCatID; ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Subcategory Name</label>
                                    <input type="text" class="form-control" name="subcategoryname" required>
                                </div>
                                <div class="form-group">
                                    <label>Category</label>
                                    <select class="form-control" name="categoryid" required>
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
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="addsubcategory" class="btn btn-primary">Add</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Subcategory Modal -->
<div class="modal fade" id="editSubCatModal" tabindex="-1" role="dialog" aria-labelledby="editSubCatLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Subcategory</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Subcategory ID</label>
                        <input type="text" class="form-control" name="subcategory_id" id="edit_subcategory_id" readonly>
                    </div>
                    <div class="form-group">
                        <label>Subcategory Name</label>
                        <input type="text" class="form-control" name="subcategoryname"
                            id="edit_subcategoryname" required>
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select class="form-control" name="categoryid" id="edit_categoryid" required>
                            <?php
                            $cat_sql = "SELECT * FROM categories";
                            $cat_result = mysqli_query($conn, $cat_sql);
                            while ($cat_row = mysqli_fetch_assoc($cat_result)) {
                                echo '<option value="' . $cat_row['Category_ID'] . '">' . $cat_row['Category_Name'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="updatesubcategory" class="btn btn-primary">Update</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>


            <!-- Delete Subcategory Modal -->
            <div class="modal fade" id="deleteSubCatModal" tabindex="-1" role="dialog"
                aria-labelledby="deleteSubCatLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form action="" method="POST">
                            <div class="modal-header">
                                <h5 class="modal-title">Delete Subcategory</h5>
                                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="delete_subcategory_id" id="delete_subcategory_id">
                                <p>Are you sure you want to delete this subcategory?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="deletesubcategory" class="btn btn-danger">Yes,
                                    Delete</button>
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

// Insert Subcategory
if (isset($_POST['addsubcategory'])) {
    $subcategoryid = mysqli_real_escape_string($conn, $_POST['subcategoryid']);
    $subcategoryname = mysqli_real_escape_string($conn, $_POST['subcategoryname']);
    $categoryid = mysqli_real_escape_string($conn, $_POST['categoryid']);
    $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

    $sql = "INSERT INTO subcategories (Subcategory_ID, Subcategory_Name, Category_ID, Created_By, Modified_By) VALUES ('$subcategoryid', '$subcategoryname', '$categoryid', $user_id, $user_id)";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Subcategory has been added successfully.";
    } else {
        $_SESSION['status'] = "Error adding subcategory: " . mysqli_error($conn);
    }
    header("Location: sub-categories.php");
    exit();
}

// Update Subcategory
if (isset($_POST['updatesubcategory'])) {
    $subcategoryid = mysqli_real_escape_string($conn, $_POST['subcategory_id']);
    $subcategoryname = mysqli_real_escape_string($conn, $_POST['subcategoryname']);
    $categoryid = mysqli_real_escape_string($conn, $_POST['categoryid']);
    $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

    $sql = "UPDATE subcategories SET Subcategory_Name = '$subcategoryname', Category_ID = '$categoryid', Modified_By = $user_id WHERE Subcategory_ID = '$subcategoryid'";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['updated'] = "Subcategory has been updated successfully.";
    } else {
        $_SESSION['status'] = "Error updating subcategory: " . mysqli_error($conn);
    }
    header("Location: sub-categories.php");
    exit();
}

// Delete Subcategory
if (isset($_POST['deletesubcategory'])) {
    $subcategoryid = mysqli_real_escape_string($conn, $_POST['delete_subcategory_id']);
    $sql = "DELETE FROM subcategories WHERE Subcategory_ID = '$subcategoryid'";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['deleted'] = "Subcategory has been deleted successfully.";
    } else {
        $_SESSION['status'] = "Error deleting subcategory: " . mysqli_error($conn);
    }
    header("Location: sub-categories.php");
    exit();
}


include('includes/scripts.php');
include('includes/footer.php');
?>

<!-- Modal Data Fill Script -->
<script>
    $('.editbtn').on('click', function() {
    var tr = $(this).closest('tr');
    var data = tr.children('td').map(function() {
        return $(this).text();
    }).get();

    $('#edit_subcategory_id').val(data[0]);
    $('#edit_subcategoryname').val(data[1]);

    var categoryName = data[2];  // category name from table
    $('#edit_categoryid option').filter(function() {
        return $(this).text() === categoryName;
    }).prop('selected', true);

    $('#editSubCatModal').modal('show');
});

// Delete Button Event
    $(document).on('click', '.deletebtn', function() {
        var tr = $(this).closest('tr');
        var data = tr.children('td').map(function() {
            return $(this).text();
        }).get();

        $('#delete_subcategory_id').val(data[0]); // Set the subcategory ID
        $('#deleteSubCatModal').modal('show');    // Show the delete modal
    });

</script>

<?php ob_end_flush(); ?>