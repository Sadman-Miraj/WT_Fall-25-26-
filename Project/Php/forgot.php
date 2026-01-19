<?php
session_start();
include "../db/db.php";

$email = "";
$step = 1; // 1 = email verification, 2 = password reset
$message = "";
$messageType = "";
$token = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['verify_email'])) {
        // Step 1: Verify email
        $email = trim($_POST["email"] ?? "");
        
        if (empty($email)) {
            $message = "Email is required.";
            $messageType = "error";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Invalid email format.";
            $messageType = "error";
        } else {
            // Check if email exists
            $sql = "SELECT id FROM signup WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Generate verification token
                $token = bin2hex(random_bytes(16));
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_token'] = $token;
                $_SESSION['reset_expiry'] = time() + 900; // 15 minutes
                
                $step = 2; // Move to password reset step
                $message = "Email verified! You can now reset your password.";
                $messageType = "success";
            } else {
                $message = "Email not found in our system.";
                $messageType = "error";
            }
            
            $stmt->close();
        }
    }
        elseif (isset($_POST['reset_password'])) {
        // Step 2: Reset password
        $email = $_SESSION['reset_email'] ?? "";
        $session_token = $_SESSION['reset_token'] ?? "";
        $expiry = $_SESSION['reset_expiry'] ?? 0;
        $token = $_POST['token'] ?? "";
        $new_password = $_POST["new_password"] ?? "";
        $confirm_password = $_POST["confirm_password"] ?? "";
        
        // Validate token
        if (empty($email) || $token !== $session_token || time() > $expiry) {
            $message = "Session expired or invalid. Please start over.";
            $messageType = "error";
            $step = 1;
            session_destroy();
        } elseif (empty($new_password)) {
            $message = "New password is required.";
            $messageType = "error";
            $step = 2;
        } elseif (strlen($new_password) < 6) {
            $message = "Password must be at least 6 characters.";
            $messageType = "error";
            $step = 2;
        } elseif ($new_password !== $confirm_password) {
            $message = "Passwords do not match.";
            $messageType = "error";
            $step = 2;
        } else {
            // Update password in database
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $updateSql = "UPDATE signup SET password = ? WHERE email = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("ss", $hashed_password, $email);
            
            if ($updateStmt->execute()) {
                // Clear reset session
                unset($_SESSION['reset_email']);
                unset($_SESSION['reset_token']);
                unset($_SESSION['reset_expiry']);
                
                $message = "Password reset successfully! You can now login with your new password.";
                $messageType = "success";
                $step = 3; // Success step
            } else {
                $message = "Error resetting password. Please try again.";
                $messageType = "error";
                $step = 2;
            }
            
            $updateStmt->close();
        }
    }
}
$conn->close();
?>