<?php
ob_start();
session_start();
include('includes/header.php');
include('includes/navbar.php');
include('../includes/dbconfig.php');

// Auto-generate supplier ID function
function getNewSupplierId($conn)
{
    $query = "SELECT supplier_id FROM suppliers ORDER BY supplier_id DESC LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $lastId = $row['supplier_id'];
        $num = (int) substr($lastId, 3) + 1;
        return 'SUP' . str_pad($num, 3, '0', STR_PAD_LEFT);
    }
    return 'SUP001';
}

$supplier_id = getNewSupplierId($conn);

// Insert Supplier
if (isset($_POST['addcat'])) {
    $supplier_id = getNewSupplierId($conn);
    $supplier_name = mysqli_real_escape_string($conn, $_POST['supplierName']);
    $supplier_address = mysqli_real_escape_string($conn, $_POST['supplierAddress']);
    $supplier_phone = mysqli_real_escape_string($conn, $_POST['supplierPno']);
    $supplier_email = mysqli_real_escape_string($conn, $_POST['supplierEmail']);
    $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

    $query = "INSERT INTO suppliers (supplier_id, supplier_name, address, phone_number, email, created_by, modified_by, created_at) 
              VALUES ('$supplier_id', '$supplier_name', '$supplier_address', '$supplier_phone', '$supplier_email', $user_id, $user_id, NOW())";

    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Supplier added successfully.";
    } else {
        $_SESSION['status'] = "Error adding supplier: " . mysqli_error($conn);
    }
    header("Location: supplier.php");
    exit();
}

// Update Supplier
if (isset($_POST['updateSupplier'])) {
    $supplier_id = mysqli_real_escape_string($conn, $_POST['edit_supplier_id']);
    $supplier_name = mysqli_real_escape_string($conn, $_POST['edit_supplierName']);
    $supplier_address = mysqli_real_escape_string($conn, $_POST['edit_supplierAddress']);
    $supplier_phone = mysqli_real_escape_string($conn, $_POST['edit_supplierPno']);
    $supplier_email = mysqli_real_escape_string($conn, $_POST['edit_supplierEmail']);
    $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

    $update_query = "UPDATE suppliers SET 
                        supplier_name='$supplier_name',
                        address='$supplier_address',
                        phone_number='$supplier_phone',
                        email='$supplier_email',
                        modified_by=$user_id,
                        modified_at=NOW()
                     WHERE supplier_id='$supplier_id'";

    if (mysqli_query($conn, $update_query)) {
        $_SESSION['updated'] = "Supplier updated successfully.";
    } else {
        $_SESSION['status'] = "Error updating supplier: " . mysqli_error($conn);
    }
    header("Location: supplier.php");
    exit();
}

// Delete Supplier
if (isset($_POST['deleteSupplier'])) {
    $supplier_id = mysqli_real_escape_string($conn, $_POST['delete_supplier_id']);

    $delete_query = "DELETE FROM suppliers WHERE supplier_id='$supplier_id'";

    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['deleted'] = "Supplier deleted successfully.";
    } else {
        $_SESSION['status'] = "Error deleting supplier: " . mysqli_error($conn);
    }
    header("Location: supplier.php");
    exit();
}
?>

<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">

    <!-- Main Content -->
    <div id="content-header">

        <!-- Begin Page Content -->
        <div class="container-fluid">

            <!-- Page Heading -->
            <h1 class="h3 mb-2 text-gray-800">Suppliers</h1>

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

            <!-- DataTables Example -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Supplier Details</h6>
                    <a href="#" data-toggle="modal" data-target="#addSupplierModal"
                        class="btn btn-primary btn-sm float-right">Add Supplier</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Supplier Name</th>
                                    <th>Address</th>
                                    <th>Phone Number</th>
                                    <th>Email</th>
                                    <th>Last Modified By</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT s.supplier_id, s.supplier_name, s.address, s.phone_number, s.email, u.username AS modified_by
                                    FROM suppliers s
                                    LEFT JOIN users u ON s.modified_by = u.id
                                    ORDER BY s.supplier_id";

                                $result = mysqli_query($conn, $sql);

                                if (mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo '<tr>
                                        <td>' . htmlspecialchars($row['supplier_id']) . '</td>
                                        <td>' . htmlspecialchars($row['supplier_name']) . '</td>
                                        <td>' . htmlspecialchars($row['address']) . '</td>
                                        <td>' . htmlspecialchars($row['phone_number']) . '</td>
                                        <td>' . htmlspecialchars($row['email']) . '</td>
                                        <td>' . htmlspecialchars($row['modified_by'] ?? 'N/A') . '</td>
                                        <td>
                                            <button type="button" class="btn btn-success editbtn btn-sm"><i class="fas fa-edit"></i></button>
                                            <button type="button" class="btn btn-danger deletebtn btn-sm"><i class="fas fa-trash-alt"></i></button>
                                        </td>
                                    </tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan="7">No records found</td></tr>';
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

    <!-- Add Supplier Modal -->
    <div class="modal fade" id="addSupplierModal" tabindex="-1" role="dialog" aria-labelledby="supplierModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="" method="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="supplierModalLabel">Add Supplier</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="supplierId">Supplier ID</label>
                                <input type="text" class="form-control" name="supplierId" id="supplierId"
                                    value="<?php echo $supplier_id; ?>" readonly>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="supplierName">Supplier Name</label>
                                <input type="text" class="form-control" name="supplierName" id="supplierName" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="supplierAddress">Address</label>
                                <input type="text" class="form-control" name="supplierAddress" id="supplierAddress"
                                    required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="supplierPno">Phone Number</label>
                                <input type="text" class="form-control" name="supplierPno" id="supplierPno" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="supplierEmail">Email</label>
                                <input type="email" class="form-control" name="supplierEmail" id="supplierEmail"
                                    required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="addcat" class="btn btn-primary">Add</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Supplier Modal -->
    <div class="modal fade" id="editSupplierModal" tabindex="-1" role="dialog" aria-labelledby="editSupplierModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="" method="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editSupplierModalLabel">Edit Supplier</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Same layout as add modal -->
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="edit_supplierId">Supplier ID</label>
                                <input type="text" id="edit_supplier_id" name="edit_supplier_id" class="form-control"
                                    readonly>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="edit_supplierName">Supplier Name</label>
                                <input type="text" class="form-control" name="edit_supplierName" id="edit_supplierName"
                                    required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="edit_supplierAddress">Address</label>
                                <input type="text" class="form-control" name="edit_supplierAddress"
                                    id="edit_supplierAddress" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="edit_supplierPno">Phone Number</label>
                                <input type="text" class="form-control" name="edit_supplierPno" id="edit_supplierPno"
                                    required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="edit_supplierEmail">Email</label>
                                <input type="email" class="form-control" name="edit_supplierEmail"
                                    id="edit_supplierEmail" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="updateSupplier" class="btn btn-success">Update</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Supplier Modal -->
    <div class="modal fade" id="deleteSupplierModal" tabindex="-1" role="dialog"
        aria-labelledby="deleteSupplierModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="" method="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteSupplierModalLabel">Delete Supplier</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="delete_supplier_id" id="delete_supplier_id">
                        <p>Are you sure you want to delete this supplier?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="deleteSupplier" class="btn btn-danger">Delete</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>




<?php
include('includes/scripts.php');
include('includes/footer.php');
?>

<script>
    $(document).ready(function () {
        // Edit button click
        $('.editbtn').on('click', function () {
            // Get current table row data
            var tr = $(this).closest('tr');
            var data = tr.children('td').map(function () {
                return $(this).text();
            }).get();

            // Populate modal fields
            $('#edit_supplier_id').val(data[0]);
            $('#edit_supplierName').val(data[1]);
            $('#edit_supplierAddress').val(data[2]);
            $('#edit_supplierPno').val(data[3]);
            $('#edit_supplierEmail').val(data[4]);

            // Show modal
            $('#editSupplierModal').modal('show');
        });

        // Delete button click
        $('.deletebtn').on('click', function () {
            var tr = $(this).closest('tr');
            var supplierId = tr.children('td').eq(0).text();

            $('#delete_supplier_id').val(supplierId);
            $('#deleteSupplierModal').modal('show');
        });
    });
</script>