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