<?php
include('../connect.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $session = $_POST['session'];

    if (!empty($session)) {
        // সেশন এর ভিত্তিতে স্টুডেন্ট ফেচ করা
        $sql_students = "SELECT s.studentid, s.fullname 
                         FROM student s
                         JOIN session_table st ON s.session = st.session
                         WHERE st.session = ?";

        if ($stmt_students = $conn->prepare($sql_students)) {
            $stmt_students->bind_param("s", $session);
            $stmt_students->execute();
            $result_students = $stmt_students->get_result();

            if ($result_students->num_rows > 0) {
                $students = "<table class='table table-bordered'>
                                <thead>
                                    <tr>
                                        <th>Select</th>
                                        <th>Student Name</th>
                                    </tr>
                                </thead>
                                <tbody>";

                while ($row = $result_students->fetch_assoc()) {
                    $students .= "<tr>
                                    <td><input type='checkbox' name='studentid[]' value='" . $row['studentid'] . "'></td>
                                    <td>" . $row['fullname'] . "</td>
                                  </tr>";
                }

                $students .= "</tbody></table>";
                echo json_encode(['students' => $students]);
            } else {
                echo json_encode(["error" => "No students found for this session."]);
            }
            $stmt_students->close();
        } else {
            echo json_encode(["error" => "Failed to prepare student query."]);
        }
    } else {
        echo json_encode(["error" => "Please select a session."]);
    }
}
?>
