<?php
session_start();

// ================================
// PHP SESSION & AUTHENTICATION
// ================================

// Redirect to login if not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: auth/login.php");
    exit();
}

include "../db/db.php";

// ================================
// AJAX REQUEST HANDLER SETUP
// ================================

// Handle AJAX POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'update_profile':
            updateProfile();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit();
}

// Handle AJAX GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'get_profile':
            getProfile();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit();
}

// ================================
// GET PROFILE FUNCTION
// ================================

// Function to get profile data via AJAX
function getProfile() {
    global $conn;
    
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        return;
    }
    
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT * FROM signup WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
    $stmt->close();
}

// ================================
// UPDATE PROFILE FUNCTION
// ================================

// Function to update profile data via AJAX
function updateProfile() {
    global $conn;
    
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        return;
    }
    
    // Get raw POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Check if data was received
    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'No data received']);
        return;
    }
    
    $user_id = $_SESSION['user_id'];
    $name = trim($data['name'] ?? '');
    $age = intval($data['age'] ?? 0);
    $address = trim($data['address'] ?? '');
    
    // Validation
    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Name is required']);
        return;
    }
    
    if ($age < 18 || $age > 100) {
        echo json_encode(['success' => false, 'message' => 'Age must be between 18 and 100']);
        return;
    }
    
    if (empty($address)) {
        echo json_encode(['success' => false, 'message' => 'Address is required']);
        return;
    }
    
    // Update profile
    $sql = "UPDATE signup SET name = ?, age = ?, address = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisi", $name, $age, $address, $user_id);
    
    if ($stmt->execute()) {
        // Update session
        $_SESSION['user_name'] = $name;
        
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } else {
        error_log("Database error: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Error updating profile: ' . $stmt->error]);
    }
    $stmt->close();
}
// ================================
// INITIAL PAGE LOAD DATA
// ================================

// Get user details for initial page load
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM signup WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Automobiles Solution</title>
    <link rel="stylesheet" href="../css/profile.css">
</head>
<body>