<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: auth/login.php");
    exit();
}

include "../db/db.php";

$user_name = $_SESSION['user_name'];
// Get user's service history
$services = [];

// Emergency services
$sql_emergency = "SELECT * FROM emergency WHERE name = ?";
$stmt_emergency = $conn->prepare($sql_emergency);
if ($stmt_emergency) {
    $stmt_emergency->bind_param("s", $user_name);
    $stmt_emergency->execute();
    $result_emergency = $stmt_emergency->get_result();
    while ($row = $result_emergency->fetch_assoc()) {
        $row['service_type'] = 'emergency';
        $row['service_date'] = date('Y-m-d'); // Use current date as fallback
        $services[] = $row;
    }
    $stmt_emergency->close();
}