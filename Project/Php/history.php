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
// Home services
$sql_home = "SELECT * FROM home WHERE name = ?";
$stmt_home = $conn->prepare($sql_home);
if ($stmt_home) {
    $stmt_home->bind_param("s", $user_name);
    $stmt_home->execute();
    $result_home = $stmt_home->get_result();
    while ($row = $result_home->fetch_assoc()) {
        $row['service_type'] = 'home';
        $services[] = $row;
    }
    $stmt_home->close();
}

// Regular services
$sql_regular = "SELECT * FROM regular WHERE name = ?";
$stmt_regular = $conn->prepare($sql_regular);
if ($stmt_regular) {
    $stmt_regular->bind_param("s", $user_name);
    $stmt_regular->execute();
    $result_regular = $stmt_regular->get_result();
    while ($row = $result_regular->fetch_assoc()) {
        $row['service_type'] = 'regular';
        $services[] = $row;
    }
    $stmt_regular->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My History - Automobiles Solution</title>
    <link rel="stylesheet" href="../css/history.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="history-container">
        <h1>My Service History</h1>
        
        <div class="back-link">
            <a href="profile.php"><i class="fas fa-arrow-left"></i> Back to Profile</a>
        </div>

                <!-- Services Section -->
        <div class="section">
            <h2 class="section-title">Services History</h2>
            
            <?php if (empty($services)): ?>
                <div class="empty-state">
                    <i class="fas fa-history"></i>
                    <p>No services booked yet.</p>
                    <p class="subtext">Book your first service today!</p>
                </div>
            <?php else: ?>
                <div class="services-list">