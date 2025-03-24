<?php
include('../connect.php');
session_start(); // Database connection

$userid = $_SESSION['userid'] ?? 1; // ইউজার আইডি সঠিকভাবে সেট করুন

if (isset($_POST['upload'])) {
    $targetDir = "uploads/"; // আপলোড ফোল্ডার
    $fileName = basename($_FILES["profilepicture"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    // অনুমোদিত ফাইল ফরম্যাট
    $allowedTypes = array("jpg", "jpeg", "png", "gif");
    if (in_array($fileType, $allowedTypes)) {
        // ফাইল আপলোড করা হচ্ছে
        if (move_uploaded_file($_FILES["profilepicture"]["tmp_name"], $targetFilePath)) {
            // ডাটাবেজে প্রোফাইল পিকচার আপডেট
            $sql = "UPDATE student SET profilepicture=? WHERE userid=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $targetFilePath, $userid);

            if ($stmt->execute()) {
                // ✅ সেশন আপডেট করুন
                $_SESSION['profilepicture'] = $targetFilePath;
                $_SESSION['profile_updated'] = true; // আপডেট ফ্ল্যাগ সেট

                header("Location: index.php"); // রিডাইরেক্ট করে পেজ রিফ্রেশ করুন
                exit();
            } else {
                echo "Database update failed.";
            }
        } else {
            echo "File upload failed.";
        }
    } else {
        echo "Only JPG, JPEG, PNG, & GIF files are allowed.";
    }
}
?>
