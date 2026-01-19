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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="../css/login.css">
    <style>
                .forgot-container {
            max-width: 500px;
        }
        
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        
        .step-indicator::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 10%;
            right: 10%;
            height: 2px;
            background: #e1e5e9;
            z-index: 1;
        }
        
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
            flex: 1;
        }
        
        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e1e5e9;
            color: #666;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 5px;
            transition: all 0.3s;
        }
        
        .step.active .step-number {
            background: #667eea;
            color: white;
            transform: scale(1.1);
        }
        
        .step.completed .step-number {
            background: #28a745;
            color: white;
        }
        
        .step-label {
            font-size: 12px;
            color: #666;
            text-align: center;
        }
        
        .step.active .step-label {
            color: #667eea;
            font-weight: 600;
        }
        
        .password-hint {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .back-btn {
            background: none;
            border: none;
            color: #667eea;
            cursor: pointer;
            font-size: 14px;
            margin-top: 10px;
        }
        
        .back-btn:hover {
            text-decoration: underline;
        }
        
        .success-message {
            text-align: center;
            padding: 20px;
        }
        
        .success-icon {
            font-size: 48px;
            color: #28a745;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
        <div class="login-container forgot-container">
        <h2>Reset Password</h2>
        
        <!-- Step Indicator -->
        <div class="step-indicator">
            <div class="step <?php echo $step >= 1 ? 'active' : ''; ?> <?php echo $step > 1 ? 'completed' : ''; ?>">
                <div class="step-number">1</div>
                <div class="step-label">Verify Email</div>
            </div>
            <div class="step <?php echo $step >= 2 ? 'active' : ''; ?> <?php echo $step > 2 ? 'completed' : ''; ?>">
                <div class="step-number">2</div>
                <div class="step-label">New Password</div>
            </div>
            <div class="step <?php echo $step >= 3 ? 'active' : ''; ?>">
                <div class="step-number">3</div>
                <div class="step-label">Complete</div>
            </div>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
                <?php if ($step == 1): ?>
            <!-- Step 1: Email Verification -->
            <form method="post" action="" id="verifyForm">
                <p class="instructions">Enter your email address to verify your account.</p>
                
                <div class="form-group">
                    <label for="email">Email Address:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                
                <input type="hidden" name="verify_email" value="1">
                <button type="submit" class="login-btn">Verify Email</button>
                
                <div class="back-to-login">
                    <a href="login.php" class="link">← Back to Login</a>
                </div>
            </form>
                    <?php elseif ($step == 2): ?>
            <!-- Step 2: Password Reset -->
            <form method="post" action="" id="resetForm">
                <p class="instructions">Create a new password for your account.</p>
                
                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password" required>
                    <div class="password-hint">Must be at least 6 characters long</div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($_SESSION['reset_token'] ?? ''); ?>">
                <input type="hidden" name="reset_password" value="1">
                
                <button type="submit" class="login-btn">Reset Password</button>
                
                <button type="button" class="back-btn" onclick="window.location.href='forgot.php'">
                    ← Use different email
                </button>
            </form>