<?php
session_start();

// CHECK THIS: Verify admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // If not admin, redirect to login
    header("Location: ../auth/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/admin.css">
    
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Admin Dashboard</h1>
            <div>
                <span>Welcome, Admin</span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
        
        <p style="margin-bottom: 20px; color: #666;">
            Manage your Automobiles Solution system
        </p>
        
        <div class="dashboard-cards">
            <div class="card">
                <h3>📊 Employee</h3>
                <p>Manage employees</p>
                <a href="employee.php" class="card-btn">Manage</a>
            </div>
            
            <div class="card">
                <h3>📦 Inventory</h3>
                <p>Manage inventory items</p>
                <a href="inventory.php" class="card-btn">Manage</a>
            </div>
            
            <div class="card">
                <h3>👥 Customer</h3>
                <p>Manage customers</p>
                <a href="customer.php" class="card-btn">Manage</a>
            </div>
        </div>
        
        <a href="index.php" class="back-link">← Back to Main Site</a>
    </div>
</body>
</html>