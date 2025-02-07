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
    <title>Certificate Request</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="update.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    

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
                <a href="updateprofile.php" class="list-group-item list-group-item-action  ">Update Profile</a>
                <a href="downloadresult.php" class="list-group-item list-group-item-action">Download Result</a>
                <a href="certificaterequest.php" class="list-group-item list-group-item-action active">Certificate Application</a>
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
            <h2 class="text-center">Cretificate Request</h2>
            <h5>Certificate Type:</h5>
            <div class="form-check">
  <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault1">
  <label class="form-check-label" for="flexRadioDefault1">
    Attestation Certificate
  </label>
</div>
<div class="form-check">
  <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault2" checked>
  <label class="form-check-label" for="flexRadioDefault2">
    Testimonial Certificate
  </label>
</div><br>

            <div class="mb-3">
 
  <textarea class="form-control" id="exampleFormControlTextarea1" rows="3" placeholder="Write the reason for requesting certificate in short..."></textarea>
</div>
<div class="text-center">
        <button type="submit" class="btn btn-primary w-50">Send Request</button>
    </div>
            

        </div>


        <table class="table table-hover">
            <thead>
    <tr>
      <th scope="col">Serial No.</th>
      <th scope="col">Request Number</th>
      <th scope="col">Status</th>
      <th style="text-align: center;">
    
      Download
    
</th>

    </tr>
  </thead>
  <tbody>
    <tr>
      <th scope="row">1</th>
      <td>723455</td>
      <td>Pending</td>
      <td style="text-align: center;">
    <a href="#" style="display: inline-block;">
        <i class="fa-solid fa-download"></i>
    </a>
</td>

    </tr>
   
  </tbody>
            </table>
                </div>
                
            </div>
        </div>
    </div>
</div>

    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
