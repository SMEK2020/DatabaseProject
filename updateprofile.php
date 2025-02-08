<?php
    include('connect.php');
    session_start();
    if( !isset($_SESSION['id'])  ){
        header('location: Slogin.php');
        exit;
    }
?>






<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="update.css">
    

</head>

    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="bg-light border-end" id="sidebar-wrapper">
            <div class="sidebar-heading text-center py-4 primary-text"> 
                <img src="image/my pic.png" class="rounded-circle" width="80" alt="Profile Picture">
                <h6>SAZID MAHMUD EMON KHAN</h6>
            </div>
            <div class="list-group list-group-flush">
                <a href="dashboard.php" class="list-group-item list-group-item-action">Dashboard</a>
                <a href="updateprofile.php" class="list-group-item list-group-item-action  active">Update Profile</a>
                <a href="downloadresult.php" class="list-group-item list-group-item-action">Download Result</a>
                <a href="enroll.php" class="list-group-item list-group-item-action">Enroll Course</a>
                <a href="certificaterequest.php" class="list-group-item list-group-item-action">Certificate Application</a>
                <a href="changepass.php" class="list-group-item list-group-item-action">Change Password</a>
                <a href="logouthelper.php" class="list-group-item list-group-item-action">Logout</a>
            </div>
        </div>

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <nav  class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-3">
                <div class="container-fluid d-flex align-items-center" >
                    <img src="image/bsmru_logo.png" alt="Logo" width="70" class="me-2">
                    <h5 class="m-0">Bangabandhu Sheikh Mujibur Rahman University, Kishoreganj</h5>
                </div>
            </nav>

            <div class="container mt-4">

                <div class="container">
                    
            <div class="form-container">
            <h2 class="text-center">Update Your Profile</h2>
            <form>
    <div class="mb-3">
        <label for="name" class="form-label">Full Name</label>
        <input type="text" class="form-control" id="name" placeholder="Enter your name" required>
    </div>
    <div class="mb-3">
        <label for="fatherName" class="form-label">Father's Name</label>
        <input type="text" class="form-control" id="fatherName" placeholder="Enter father's name" required>
    </div>
    <div class="mb-3">
        <label for="motherName" class="form-label">Mother's Name</label>
        <input type="text" class="form-control" id="motherName" placeholder="Enter mother's name" required>
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <input type="email" class="form-control" id="email" placeholder="Enter your email" required>
    </div>
    <div class="mb-3">
        <label for="villageRoad" class="form-label">Village/Road</label>
        <input type="text" class="form-control" id="villageRoad" placeholder="Enter village/road" required>
    </div>
    <div class="mb-3">
        <label for="thanaUpazilla" class="form-label">Thana/Upazilla</label>
        <input type="text" class="form-control" id="thanaUpazilla" placeholder="Enter thana/upazilla" required>
    </div>
    <div class="mb-3">
        <label for="district" class="form-label">District</label>
        <input type="text" class="form-control" id="district" placeholder="Enter district" required>
    </div>
    <div class="mb-3">
        <label for="profilePicture" class="form-label">Profile Picture</label>
        <input type="file" class="form-control" id="profilePicture">
    </div>
    <div class="text-center">
        <button type="submit" class="btn btn-primary w-50">Update Profile</button>
    </div>
</form>

        </div>
                </div>
                
            </div>
        </div>
    </div>
</div>

    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
