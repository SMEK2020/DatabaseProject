<?php
include('../connect.php');
session_start();

if (!isset($_SESSION['userid'])) {
    header('location: ../Tlogin.php');
    exit;
}

$userid = $_SESSION['userid']; // সঠিক সেশন ভেরিয়েবল

$fullname = mysqli_real_escape_string($conn, $_POST["name"]);
$fatherName = mysqli_real_escape_string($conn, $_POST["fatherName"]);
$email = mysqli_real_escape_string($conn, $_POST["email"]);
$phone = mysqli_real_escape_string($conn, $_POST["phone"]);
$motherName = mysqli_real_escape_string($conn, $_POST["motherName"]);
$villageRoad = mysqli_real_escape_string($conn, $_POST["villageRoad"]);
$thanaUpazilla = mysqli_real_escape_string($conn, $_POST["thanaUpazilla"]);
$district = mysqli_real_escape_string($conn, $_POST["district"]);

// Check if profile picture is uploaded
if (!empty($_FILES["profilePicture"]["name"])) {
    $file_tmp = $_FILES["profilePicture"]["tmp_name"];
    $file_size = $_FILES["profilePicture"]["size"];
    $target_dir = "../uploads/";  // Ensure this directory exists
    $target_file = $target_dir . basename($_FILES["profilePicture"]["name"]);

    // Ensure file size does not exceed 2MB
    if ($file_size > 2 * 1024 * 1024) {
        echo "<script>alert('File size exceeds 2MB!'); window.location.href='updateprofile.php';</script>";
        exit();
    }

    if (move_uploaded_file($file_tmp, $target_file)) {
        // Update with profile picture in the database
        $sql1 = "UPDATE teacher SET fullname='$fullname', fathersname='$fatherName', mothersname='$motherName', 
                village_road='$villageRoad', thana_upazilla='$thanaUpazilla', district='$district', 
                profilepicture='$target_file' WHERE userid='$userid'";

        // Update session profile picture
        $_SESSION['profilepicture'] = $target_file;  // Just path stored
    } else {
        echo "<script>alert('Error uploading profile picture!'); window.location.href='updateprofile.php';</script>";
        exit();
    }
} else {
    // Update without profile picture
    $sql1 = "UPDATE teacher SET fullname='$fullname', fathersname='$fatherName', mothersname='$motherName', 
            village_road='$villageRoad', thana_upazilla='$thanaUpazilla', district='$district' 
            WHERE userid='$userid'";

    // Fetch profile picture path to keep session updated
    $result = $conn->query("SELECT profilepicture FROM student WHERE userid='$userid'");
    if ($result && $row = $result->fetch_assoc()) {
        $_SESSION['profilepicture'] = $row['profilepicture'];
    }
}

// Update users table for email and phone
$sql2 = "UPDATE users SET email='$email', phone='$phone' WHERE userid='$userid'";

// Execute both queries
if ($conn->query($sql1) === TRUE && $conn->query($sql2) === TRUE) {
    // Update session variables after successful update
    $_SESSION['fullname'] = $fullname;
    $_SESSION['email'] = $email;
    $_SESSION['phone'] = $phone;
    $_SESSION['fathersname'] = $fatherName;
    $_SESSION['mothersname'] = $motherName;
    $_SESSION['village_road'] = $villageRoad;
    $_SESSION['thana_upazilla'] = $thanaUpazilla;
    $_SESSION['district'] = $district;

    echo "<script>alert('Profile updated successfully!'); window.location.href='index.php';</script>";
} else {
    echo "Error updating profile: " . $conn->error;
}

$conn->close();
?>