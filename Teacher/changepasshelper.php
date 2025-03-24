<?php
include('../connect.php');
session_start();

if (!isset($_SESSION['userid'])) {
    header('location: Tlogin.php');
    exit;
}

$fullname = $_SESSION['fullname'];
$profilePic = $_SESSION['profilepicture'];


// BLOB ইমেজকে `base64_encode()` করে সেট করা
if (isset($_SESSION['profilepicture']) && !empty($_SESSION['profilepicture'])) {
    $profilePic = 'data:image/jpeg;base64,' . base64_encode($_SESSION['profilepicture']);
} else {
    $profilePic = 'image/default.png'; // যদি ছবি না থাকে, ডিফল্ট ইমেজ দেখাবে
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $previous_password = $_POST['previous_password'];
    $new_password = $_POST['new_password'];
    $user_id = $_SESSION["userid"]; // সঠিক সেশন ID ব্যবহার করুন

    // Fetch the current hashed password from the database
    $query = "SELECT password FROM users WHERE userid = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id);  // সঠিক user_id ব্যবহার করুন
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($hashed_password);
        $stmt->fetch();

        // Debugging output (Remove after testing)
        echo "Entered Password: " . $previous_password . "<br>";
        echo "Stored Hashed Password: " . $hashed_password . "<br>";

        // Verify password (Handle plaintext passwords)
        if ($previous_password === $hashed_password || password_verify($previous_password, $hashed_password)) {
            $new_hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

            // Update password in the database
            $update_query = "UPDATE users SET password = ? WHERE userid = ?";
            $update_stmt = $conn->prepare($update_query);

            if (!$update_stmt) {
                die("Error preparing update statement: " . $conn->error);
            }

            $update_stmt->bind_param("si", $new_hashed_password, $user_id);

            if ($update_stmt->execute()) {
                echo "<script>alert('✅ Password updated successfully!'); window.location.href='changepass.php';</script>";
            } else {
                echo "<script>alert('❌ Error updating password. Please try again.');</script>";
            }
        } else {
            echo "<script>alert('❌ Incorrect previous password!');</script>";
        }
    } else {
        echo "<script>alert('❌ User not found.');</script>";
    }

    // Close statements
    $stmt->close();
    $conn->close();
}
?>
