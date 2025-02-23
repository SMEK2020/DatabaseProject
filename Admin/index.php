








<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
    

</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="bg-light border-end" id="sidebar-wrapper">
            <div class="sidebar-heading text-center py-4 primary-text"> 
                <img src="../image/my pic.png" class="rounded-circle" width="80" alt="Profile Picture">
                <h6>SAZID MAHMUD EMON KHAN</h6>
            </div>
            <div class="list-group list-group-flush">
                <a href="#" class="list-group-item list-group-item-action active">Dashboard</a>
                <a href="#" class="list-group-item list-group-item-action">Update Profile</a>
                <a href="#" class="list-group-item list-group-item-action">Input Final Mark</a>
                <a href="#" class="list-group-item list-group-item-action">Enroll Course</a>
                <a href="#" class="list-group-item list-group-item-action">Student Registration</a>
                <a href="#" class="list-group-item list-group-item-action">Crrtificate Approval</a>
                <a href="#" class="list-group-item list-group-item-action">Result Publish</a>
                <a href="changepass.php" class="list-group-item list-group-item-action">Change Password</a>
                <a href="logouthelper.php" class="list-group-item list-group-item-action">Logout</a>
            </div>
        </div>

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <nav  class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-3">
                <div class="container-fluid d-flex align-items-center" >
                    <img src="../image/bsmru_logo.png" alt="Logo" width="70" class="me-2">
                    <h5 class="m-0">Bangabandhu Sheikh Mujibur Rahman University, Kishoreganj</h5>
                </div>
            </nav>

            <div class="container mt-4">
    <div class="row">
        <!-- Left Section (Result & Certificate + Table) -->
        <div class="col-md-8">
            <div class="row">
                <!-- Result Card -->
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-body">
                            <h5 class="card-title">New Student Registration</h5>
                            <a href="#" class="btn btn-primary">Register</a>
                        </div>
                    </div>
                </div>

                <!-- Certificate Card -->
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-body">
                            <h5 class="card-title">Certificate Request</h5>
                            <button type="button" class="btn btn-primary position-relative">
                            Request
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            4
                                <span class="visually-hidden">unread messages</span>
                            </span>
                            </button>
                            
                        </div>
                    </div>
                </div>
            </div>

            <div style="margin-top: 15px;"></div>

            <div class="row">
                <!-- Result Card -->
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-body">
                            <h5 class="card-title">New Teacher Registration</h5>
                            <a href="#" class="btn btn-primary">Register</a>
                        </div>
                    </div>
                </div>

                <!-- Certificate Card -->
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-body">
                            
                            
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Below Result & Certificate -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card shadow p-3">
                        <h4 class="text-center">InCourse Mark</h4>
                        <div class="table-responsive">
    
</div>

                    </div>
                </div>
            </div>
        </div>


        

        <!-- Right Sidebar (Attendance Card) -->
        <div class="col-md-4">
            <div class="card shadow text-center"> 
                
            </div>
        </div>
    </div>
    
</div>

        </div>
    </div>

    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
