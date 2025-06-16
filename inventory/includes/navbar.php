<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
        <!-- <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-laugh-wink"></i>
        </div> -->
        <div class="sidebar-brand-text mx-3">Inventory<sup></sup></div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

<!-- Nav Item - Dashboard -->
<li class="nav-item active">
    <a class="nav-link" href="index.php">
        <i class="fas fa-fw fa-tachometer-alt"></i>
        <span>Dashboard</span>
    </a>
</li>

<!-- Divider -->
<hr class="sidebar-divider">

<!-- Heading -->
<div class="sidebar-heading">
    Interface
</div>

<!-- Nav Item - Pages Collapse Menu -->

<!-- Category Management -->
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseCategory"
        aria-expanded="true" aria-controls="collapseCategory">
        <i class="fas fa-fw fa-list-alt"></i>
        <span>Category</span>
    </a>
    <div id="collapseCategory" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Category Managements:</h6>
            <a class="collapse-item" href="categories.php">Category</a>
            <a class="collapse-item" href="sub-categories.php">Sub Category</a>
        </div>
    </div>
</li>

<!-- Nav Item - Brand -->
<li class="nav-item">
    <a class="nav-link" href="brand.php">
        <i class="fas fa-fw fa-tags"></i>
        <span>Brand</span>
    </a>
</li>

<!-- Product Management -->
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseProduct"
        aria-expanded="true" aria-controls="collapseProduct">
        <i class="fas fa-fw fa-cogs"></i>
        <span>Product</span>
    </a>
    <div id="collapseProduct" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Product Managements:</h6>
            <a class="collapse-item" href="add-product.php">Add Product</a>
            <a class="collapse-item" href="product.php">View Products</a>
        </div>
    </div>
</li>

<!-- Supplier Management -->
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSupplier"
        aria-expanded="true" aria-controls="collapseSupplier">
        <i class="fas fa-fw fa-truck"></i>
        <span>Supplier</span>
    </a>
    <div id="collapseSupplier" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Supplier Managements:</h6>
            <a class="collapse-item" href="supplier.php">Supplier</a>
            <h6 class="collapse-header">Invoice Managements:</h6>
            <a class="collapse-item" href="add-supplier-invoice.php">Add Supplier Invoice</a>
            <a class="collapse-item" href="supplier-invoice.php">Manage Supplier Invoice</a>
        </div>
    </div>
</li>

<!-- Nav Item - Sales -->
<li class="nav-item">
    <a class="nav-link" href="sales-transaction.php">
        <i class="fas fa-fw fa-shopping-cart"></i>
        <span>Sales Transactions</span>
    </a>
</li>

<!-- Reports -->
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseReports"
        aria-expanded="true" aria-controls="collapseReports">
        <i class="fas fa-fw fa-chart-line"></i>
        <span>Reports</span>
    </a>
    <div id="collapseReports" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Reports Management:</h6>
            <a class="collapse-item" href="inventory_report.php">Inventory Reports</a>
            <a class="collapse-item" href="purchase_report.php">purchase Reports</a>
            <a class="collapse-item" href="report.php">Sales Reports</a>
        </div>
    </div>
</li>

<!-- Nav Item - User Management -->
<li class="nav-item">
    <a class="nav-link" href="user.php">
        <i class="fas fa-fw fa-users"></i>
        <span>User Management</span>
    </a>
</li>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>
<!-- End of Sidebar -->



<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">

    <!-- Main Content -->
    <div id="content">
        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

            <!-- Sidebar Toggle (Topbar) -->
            <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                <i class="fa fa-bars"></i>
            </button>

            <!-- Topbar Navbar -->
            <ul class="navbar-nav ml-auto">

                <div class="topbar-divider d-none d-sm-block"></div>

                <!-- Nav Item - User Information -->
                <li class="nav-item dropdown no-arrow">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        <span class="mr-2 d-none d-lg-inline text-gray-600 small">Admin</span>
                        <img class="img-profile rounded-circle" src="img/undraw_profile.svg">
                    </a>
                    <!-- Dropdown - User Information -->
                    <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                        aria-labelledby="userDropdown">
                        
                       
                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                            <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                            Logout
                        </a>
                    </div>
                </li>

            </ul>

        </nav>
        <!-- End of Topbar -->

        <!-- Scroll to Top Button-->
        <a class="scroll-to-top rounded" href="#page-top">
            <i class="fas fa-angle-up"></i>
        </a>


        <!-- Logout Modal-->
        <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                        <a class="btn btn-primary" href="../login.php">Logout</a>
                    </div>
                </div>
            </div>
        </div>