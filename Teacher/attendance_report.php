<?php
require('../fpdf.php'); // FPDF লাইব্রেরি ইমপোর্ট করুন
include('../config.php'); // ডাটাবেজ কানেকশন

// PDF ক্লাস এক্সটেন্ড
class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(190, 10, 'Daily Attendance Report', 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

// PDF অবজেক্ট তৈরি
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Attendance Data ফেচ করুন
$date = date('Y-m-d'); // আজকের তারিখ
$course_id = 1; // ধরে নিচ্ছি কোর্স আইডি 1

$stmt = $conn->prepare("SELECT students.roll, students.name, attendance.status FROM attendance 
                        JOIN students ON attendance.student_id = students.roll 
                        WHERE attendance.course_id = ? AND attendance.attendance_date = ?");
$stmt->bind_param("is", $course_id, $date);
$stmt->execute();
$result = $stmt->get_result();

// টেবিল হেডার
$pdf->Cell(60, 10, 'Student ID', 1, 0, 'C');
$pdf->Cell(80, 10, 'Student Name', 1, 0, 'C');
$pdf->Cell(50, 10, 'Status', 1, 1, 'C');

// ডাটা লুপ করে দেখানো
while ($row = $result->fetch_assoc()) {
    $pdf->Cell(60, 10, $row['roll'], 1, 0, 'C');
    $pdf->Cell(80, 10, $row['name'], 1, 0, 'C');
    $pdf->Cell(50, 10, $row['status'], 1, 1, 'C');
}

// PDF আউটপুট
$pdf->Output('D', 'Attendance_Report.pdf'); // ডাউনলোড হবে
?>
