<?php
session_start();

// Check if admin is logging out
if (isset($_SESSION['admin_logged_in'])) {
    // Destroy admin session
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_email']);
    unset($_SESSION['admin_logged_in']);
    
    // Redirect to admin login
    header("Location: login.php");
    exit();
}

// Regular user logout
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
?>