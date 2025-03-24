<?php
include('../connect.php');
session_start();

if (!isset($_SESSION['userid'])) {
    header('location: Slogin.php');
    exit;
}
$fullname = $_SESSION['fullname'];
$profilePic = $_SESSION['profilepicture'];


$userid = $_SESSION['userid'];
$sql = "SELECT studentid FROM student WHERE userid=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $userid);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$studentid = $row['studentid'];
$stmt->close();

// Fetch all certificate requests for this student
$fetch_requests = "SELECT * FROM certificaterequest WHERE studentid = '$studentid' ORDER BY requestdate DESC";
$requests = $conn->query($fetch_requests);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Previous Certificate Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../dashboard.css">
</head>
<body>

<div class="container mt-4">
    <h3 class="text-center">Your Previous Certificate Requests</h3>

    <!-- Display requests in a table -->
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Serial No.</th>
                <th>Certificate Type</th>
                <th>Status</th>
                <th>Request Date</th>
                <th>Download</th> <!-- New column for download link -->
            </tr>
        </thead>
        <tbody>
        <?php 
$serial = 1; 
if ($requests && $requests->num_rows > 0) {
    while ($row = $requests->fetch_assoc()) {
        // Fetch studentid, type, and requestdate from the current row in certificaterequest table
        $studentid = $row['studentid'];  // Student ID from certificaterequest table
        $certificate_type = $row['type']; // Certificate type from the request
        $requestdate = $row['requestdate']; // Request date from the request
        
        // Fetch the pdf_path from the certificates table based on studentid, type, and requestdate
        $fetch_pdf_path = "SELECT pdf_path FROM certificates WHERE studentid = '$studentid' AND type = '$certificate_type' AND requestdate = '$requestdate'";
        $pdf_result = $conn->query($fetch_pdf_path);
        
        // If query executed successfully and we got a result
        if ($pdf_result && $pdf_result->num_rows > 0) {
            $pdf_row = $pdf_result->fetch_assoc();
            $pdf_path = $pdf_row['pdf_path'];
        } else {
            $pdf_path = null; // No pdf_path if query fails
        }


     
        ?>
        <tr>
            <th scope="row"><?= $serial++; ?></th>
            <td><?= htmlspecialchars($row['type']); ?></td>
            <td><?= htmlspecialchars($row['status']); ?></td>
            <td><?= htmlspecialchars($row['requestdate']); ?></td>
            <td>
    <?php 
    // Set the full path
    $full_path = "C:/xampp/htdocs/Database/Admin/" . $pdf_path;
    
    // Check if the file exists
    if ($pdf_path && file_exists($full_path)) { ?>
        <a href="http://localhost/Database/Admin/<?= $pdf_path; ?>" download class="btn btn-success btn-sm">Download</a>
    <?php } else { ?>
        <span class="text-danger">Not Available</span>
    <?php } ?>
</td>

        </tr>
        <?php
    }
} else {
    echo "<tr><td colspan='5' class='text-center'>No previous requests found.</td></tr>";
}
?>



        </tbody>
    </table>

    <div class="text-center">
        <a href="certificaterequest.php" class="btn btn-primary">Back to Request Page</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
