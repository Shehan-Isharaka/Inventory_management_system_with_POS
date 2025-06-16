<?php
ob_start();
session_start();
include('includes/header.php');
include('includes/navbar.php');
include('../includes/dbconfig.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add User
    if (isset($_POST['add_user'])) {
        $user_type = $_POST['user_type'];
        $status = $_POST['status'];
        $username = $_POST['username'];
        $phone = $_POST['phone'];
        $full_name = $_POST['full_name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if ($password !== $confirm_password) {
            $_SESSION['status'] = 'Passwords do not match.';
        } else {
            // Check if email already exists
            $check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check_email->bind_param("s", $email);
            $check_email->execute();
            $check_email->store_result();

            // Check if username already exists
            $check_username = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $check_username->bind_param("s", $username);
            $check_username->execute();
            $check_username->store_result();

            if ($check_email->num_rows > 0) {
                $_SESSION['status'] = 'Email already exists. Please use a different email.';
            } elseif ($check_username->num_rows > 0) {
                $_SESSION['status'] = 'Username already exists. Please choose a different username.';
            } else {
                // Hash password and insert
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("
                    INSERT INTO users (user_type, status, full_name, username, email, phone, password)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->bind_param("sisssss", $user_type, $status, $full_name, $username, $email, $phone, $hash);
                if ($stmt->execute()) {
                    $_SESSION['success'] = 'User added successfully.';
                } else {
                    $_SESSION['status'] = "Error: {$stmt->error}";
                }
            }

            $check_email->close();
            $check_username->close();
        }

        header("Location: user.php");
        exit;
    }

    // Update User
    elseif (isset($_POST['update_user'])) {
        $id = $_POST['edit_user_id'];
        $stmt = $conn->prepare("
            UPDATE users SET user_type=?, status=?, full_name=?, username=?, email=?, phone=?
            WHERE id=?
        ");
        $stmt->bind_param(
            "sissssi",
            $_POST['user_type'],
            $_POST['status'],
            $_POST['full_name'],
            $_POST['username'],
            $_POST['email'],
            $_POST['phone'],
            $id
        );
        if ($stmt->execute()) {
            $_SESSION['updated'] = 'User updated successfully.';
        } else {
            $_SESSION['status'] = "Error: {$stmt->error}";
        }
        header("Location: user.php");
        exit;
    }

    // Delete User
    elseif (isset($_POST['delete_user'])) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $_POST['delete_user_id']);
        if ($stmt->execute()) {
            $_SESSION['deleted'] = 'User deleted successfully.';
        } else {
            $_SESSION['status'] = "Error: {$stmt->error}";
        }
        header("Location: user.php");
        exit;
    }

    // Change Password
    elseif (isset($_POST['change_password'])) {
        $user_id = $_POST['change_password_user_id'];
        $new_password = $_POST['new_password'];
        $confirm_new_password = $_POST['confirm_new_password'];

        if ($new_password !== $confirm_new_password) {
            $_SESSION['status'] = 'Passwords do not match.';
        } else {
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
            $stmt->bind_param("si", $hash, $user_id);
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Password updated successfully.';
            } else {
                $_SESSION['status'] = "Error: {$stmt->error}";
            }
        }

        header("Location: user.php");
        exit;
    }
}

?>

<div class="container-fluid">
  <h1 class="mb-4">User Management</h1>

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
            
  <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addUserModal">
    <i class="fas fa-plus"></i> Add User
  </button>

  <div class="table-responsive">
    <table class="table table-bordered" id="dataTable">
      <thead class="thead-light">
        <tr>
          <th>ID</th><th>Type</th><th>Name</th><th>Username</th>
          <th>Email</th><th>Phone</th><th>Status</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
          $res = $conn->query("SELECT * FROM users ORDER BY id ASC");
          while ($row = $res->fetch_assoc()):
        ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= ucfirst($row['user_type']) ?></td>
            <td><?= htmlspecialchars($row['full_name']) ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['phone']) ?></td>
            <td>
              <?php if ($row['status']): ?>
                <span class="badge badge-success">Active</span>
              <?php else: ?>
                <span class="badge badge-secondary">Inactive</span>
              <?php endif; ?>
            </td>
            <td>
              <button class="btn btn-sm btn-info editBtn"
                data-id="<?= $row['id'] ?>"
                data-type="<?= $row['user_type'] ?>"
                data-name="<?= htmlspecialchars($row['full_name']) ?>"
                data-username="<?= htmlspecialchars($row['username']) ?>"
                data-email="<?= htmlspecialchars($row['email']) ?>"
                data-phone="<?= htmlspecialchars($row['phone']) ?>"
                data-status="<?= $row['status'] ?>">
                <i class="fas fa-edit"></i>
              </button>
              <button class="btn btn-sm btn-warning changePwdBtn"
    data-id="<?= $row['id'] ?>"
    data-name="<?= htmlspecialchars($row['full_name']) ?>">
    <i class="fas fa-key"></i>
  </button>
              <button class="btn btn-sm btn-danger deleteBtn"
                data-id="<?= $row['id'] ?>">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ➕ Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="">
      <div class="modal-content">
        <div class="modal-header">
          <h5>Add User</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>User Type</label>
              <select class="form-control" name="user_type" required>
                <option>admin</option><option>stock_keeper</option><option>cashier</option>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label>Status</label>
              <select class="form-control" name="status">
                <option value="1">Active</option><option value="0">Inactive</option>
              </select>
            </div>
          </div>
          <div class="form-group"><label>Name</label><input class="form-control" name="full_name" required></div>
          <div class="form-group"><label>Username</label><input class="form-control" name="username" required></div>
          <div class="form-group"><label>Email</label><input type="email" class="form-control" name="email" required></div>
          <div class="form-group"><label>Phone</label><input class="form-control" name="phone"></div>
          <div class="form-row">
            <div class="form-group col-md-6"><label>Password</label><input type="password" class="form-control" name="password" required></div>
            <div class="form-group col-md-6"><label>Confirm</label><input type="password" class="form-control" name="confirm_password" required></div>
          </div>
        </div>
        <div class="modal-footer">
          <button name="add_user" class="btn btn-success">Create</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- ✏️ Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="">
      <input type="hidden" name="edit_user_id" id="edit_user_id">
      <div class="modal-content">
        <div class="modal-header">
          <h5>Edit User</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>User Type</label>
              <select id="edit_user_type" class="form-control" name="user_type">
                <option>admin</option><option>stock_keeper</option><option>cashier</option>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label>Status</label>
              <select id="edit_status" class="form-control" name="status">
                <option value="1">Active</option><option value="0">Inactive</option>
              </select>
            </div>
          </div>
          <div class="form-group"><label>Name</label><input id="edit_full_name" class="form-control" name="full_name" required></div>
          <div class="form-group"><label>Username</label><input id="edit_username" class="form-control" name="username" required></div>
          <div class="form-group"><label>Email</label><input id="edit_email" type="email" class="form-control" name="email" required></div>
          <div class="form-group"><label>Phone</label><input id="edit_phone" class="form-control" name="phone"></div>
        </div>
        <div class="modal-footer">
          <button name="update_user" class="btn btn-success">Update</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- 🔑 Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="">
      <input type="hidden" name="change_password_user_id" id="change_password_user_id">
      <div class="modal-content">
        <div class="modal-header">
          <h5>Change Password</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_password" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="confirm_new_password" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button name="change_password" class="btn btn-primary">Update Password</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>


<!-- 🗑️ Delete User Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="">
      <input type="hidden" name="delete_user_id" id="delete_user_id">
      <div class="modal-content">
        <div class="modal-header">
          <h5>Delete User</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          Are you sure you want to delete this user?
        </div>
        <div class="modal-footer">
          <button name="delete_user" class="btn btn-danger">Yes, Delete</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script>
  $(function() {
    // Edit button click
    $('.editBtn').click(function() {
      const btn = $(this);
      $('#edit_user_id').val(btn.data('id'));
      $('#edit_user_type').val(btn.data('type'));
      $('#edit_status').val(btn.data('status'));
      $('#edit_full_name').val(btn.data('name'));
      $('#edit_username').val(btn.data('username'));
      $('#edit_email').val(btn.data('email'));
      $('#edit_phone').val(btn.data('phone'));
      $('#editUserModal').modal('show');
    });

    // Delete button click
    $('.deleteBtn').click(function() {
      $('#delete_user_id').val($(this).data('id'));
      $('#deleteUserModal').modal('show');
    });

    // Initialize DataTable if desired
    $('#dataTable').DataTable({
      responsive: true,
      paging: true,
      searching: true,
      ordering: true,
      info: true
    });
  });

  // Change Password button click
$('.changePwdBtn').click(function () {
  const userId = $(this).data('id');
  $('#change_password_user_id').val(userId);
  $('#changePasswordModal').modal('show');
});


</script>

<?php
include('includes/scripts.php');
include('includes/footer.php');
?>
