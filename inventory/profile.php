<?php

include('includes/header.php');
include('includes/navbar.php');

?>

<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">

    <!-- Main Content -->
    <div id="content-header">



        <!-- Begin Page Content -->
        <div class="container-fluid">
            <h1 class="h3 mb-4 text-gray-800">My Profile</h1>

            <div class="row">
                <!-- Profile Picture Section -->
                <div class="col-md-4">
                    <div class="card shadow">
                        <div class="card-body text-center">
                            <img src="assets/img/default-profile.png" class="rounded-circle mb-3" width="150"
                                height="150" id="profilePreview">
                            <h5 class="card-title">Profile Picture</h5>
                            <input type="file" class="form-control-file" name="profile_picture" id="profileUpload"
                                onchange="previewProfile(event)">
                        </div>
                    </div>
                </div>

                <!-- Profile Form -->
                <div class="col-md-8">
                    <form action="update-profile.php" method="POST" enctype="multipart/form-data">
                        <div class="card shadow">
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="fullname">Full Name</label>
                                    <input type="text" class="form-control" id="fullname" name="fullname"
                                        value="John Doe">
                                </div>
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="johndoe@example.com">
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="text" class="form-control" id="phone" name="phone" value="0771234567">
                                </div>
                                <div class="form-group text-right">
                                    <button type="button" class="btn btn-warning mr-2" data-toggle="modal"
                                        data-target="#passwordModal">Change Password</button>
                                    <button type="submit" class="btn btn-success">Update Profile</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Password Modal -->
        <div class="modal fade" id="passwordModal" tabindex="-1" role="dialog" aria-labelledby="passwordModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form action="change-password.php" method="POST">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Change Password</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <div class="form-group">
                                <label for="currentPassword">Current Password</label>
                                <input type="password" class="form-control" name="current_password" id="currentPassword"
                                    required>
                            </div>
                            <div class="form-group">
                                <label for="newPassword">New Password</label>
                                <input type="password" class="form-control" name="new_password" id="newPassword"
                                    required>
                            </div>
                            <div class="form-group">
                                <label for="confirmPassword">Confirm New Password</label>
                                <input type="password" class="form-control" name="confirm_password" id="confirmPassword"
                                    required>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Update Password</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Profile Preview Script -->
        <script>
            function previewProfile(event) {
                const reader = new FileReader();
                reader.onload = function () {
                    const output = document.getElementById('profilePreview');
                    output.src = reader.result;
                };
                reader.readAsDataURL(event.target.files[0]);
            }
        </script>

        <!-- /.container-fluid -->

    </div>
    <!-- End of Main Content -->



    <!-- Insert Modal -->
    <div class="modal fade" id="addCatModel" tabindex="-1" role="dialog" aria-labelledby="catModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="catModalLabel">Add Category</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="departmentid">Category ID</label>
                                <!-- <input type="text" class="form-control" name="departmentid" id="departmentid"  readonly> -->
                                <input type="text" class="form-control" name="categoryid" id="categoryid" value=""
                                    readonly>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="departmentsname">Category Name</label>
                                <input type="text" class="form-control" name="categoryname" id="categoryname" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" name="addcat" class="btn btn-primary">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /.Insert Modal -->

    <?php

    include('includes/scripts.php');
    include('includes/footer.php');

    ?>