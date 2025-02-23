<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection parameters
    $servername = "localhost"; // Change this if necessary
    $username = "root"; // Change to your database username
    $password = ""; // Change to your database password
    $dbname = "sms"; // Change to your database name

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Retrieve and sanitize user input
    $id = trim($_POST['id']);
    $date = trim($_POST['d']);

    $id = $conn->real_escape_string($id);
    $date = $conn->real_escape_string($date);

    // Query the database
    $sql = "SELECT * FROM validity WHERE certificateid = '$id' AND date = '$date'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<script>alert('Valid Data!'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Invalid Data!'); window.location.href='index.php';</script>";
    }

    // Close the connection
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
