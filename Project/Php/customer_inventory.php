<?php
session_start();
include "../db/db.php";

// Add error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Accept both customer login OR regular login
if (!isset($_SESSION['customer_logged_in']) && !isset($_SESSION['logged_in'])) {
    // Neither customer nor regular user is logged in
    header("Location: ../auth/login.php");
    exit();
}

// Get user details
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // Regular user is logged in
    $customer_id = $_SESSION['user_id'] ?? 0;
    $customer_name = $_SESSION['user_name'] ?? 'Customer';
    $user_email = $_SESSION['user_name']; // Using name as email since we don't have email in session
    
    // Ensure customer record exists
    $check_customer = $conn->prepare("SELECT * FROM customers WHERE name = ? OR email = ?");
    $check_customer->bind_param("ss", $customer_name, $customer_name);
    $check_customer->execute();
    $customer_result = $check_customer->get_result();
    
    if ($customer_result->num_rows === 0) {
        // Create customer record
        $insert_customer = $conn->prepare("INSERT INTO customers (name, email) VALUES (?, ?)");
        $insert_customer->bind_param("ss", $customer_name, $customer_name);
        $insert_customer->execute();
        $customer_id = $insert_customer->insert_id;
    } else {
        $customer = $customer_result->fetch_assoc();
        $customer_id = $customer['id'];
    }
    
    // Set customer session for consistency
    $_SESSION['customer_logged_in'] = true;
    $_SESSION['customer_id'] = $customer_id;
    $_SESSION['customer_name'] = $customer_name;
} else {
    // Customer is logged in via separate customer login
    $customer_id = $_SESSION['customer_id'];
    $customer_name = $_SESSION['customer_name'];
    $user_email = $_SESSION['customer_email'] ?? $customer_name;
}