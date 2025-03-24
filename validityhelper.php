<?php
// Enable full error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
include('connect.php');
session_start();

// Debugging: Check if form data is being submitted
//echo "<pre>";
var_dump($_POST);  // Check if the form is sending data
//echo "</pre>";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if 'e' is set in POST
    if (isset($_POST['e']) && !empty($_POST['e'])) {
        // Get the entered ID from the form
        $entered_id = $_POST['e'];

        // Debugging: Check if ID is being received
        //echo "Entered ID: " . $entered_id . "<br>";

        // Query to match the entered ID with the unique_id in the certificates table
        $sql = "SELECT s.studentid, s.fullname, c.unique_id FROM certificates c
                JOIN student s ON c.studentid = s.studentid
                WHERE c.unique_id = ?";

        // Debugging: Show the query to ensure it's correct
        //echo "SQL Query: " . $sql . "<br>";

        // Prepare and bind the statement
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $entered_id); // Bind the entered ID to the query

            // Execute the query
            if ($stmt->execute()) {
                // Debugging: If execution is successful
                //echo "Query executed successfully.<br>";
                
                // Get the result
                $result = $stmt->get_result();

                // Check if we found any matching record
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    // Match found, show valid message
                    $student_id = $row['studentid'];
                    $student_name = $row['fullname'];
                    echo "<script>alert('Valid! Student ID: $student_id, Name: $student_name'); window.location.href = 'validity.php';</script>";
                } else {
                    // No match found, show invalid message
                    echo "<script>alert('Invalid! No record found for the entered ID.'); window.location.href = 'validity.php';</script>";
                }
            } else {
                // If query execution failed
                //echo "Error executing query: " . $stmt->error . "<br>";
                echo "<script>alert('Error: Unable to process the request.'); window.location.href = 'validity.php';</script>";
            }

            // Close the statement
            $stmt->close();
        } else {
            // Error preparing the statement
           // echo "Error preparing query: " . $conn->error . "<br>";
            echo "<script>alert('Error: Unable to process the request.'); window.location.href = 'validity.php';</script>";
        }
    } else {
        // If no ID is provided in the form
        echo "<script>alert('Invalid request. Please enter a valid ID.'); window.location.href = 'validity.php';</script>";
    }
} else {
    // If the form wasn't submitted properly
    echo "<script>alert('Invalid request.'); window.location.href = 'validity.php';</script>";
}
?>
