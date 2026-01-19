<?php
session_start();
include "../db/db.php"; 

$name = $age = $email = $address = $password = $confirm_password = "";
$message = "";
$messageType = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"] ?? "");
    $age = trim($_POST["age"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";
    
    if (empty($name)) {
        $message = "Name is required.";
        $messageType = "error";
    } elseif (empty($age)) {
        $message = "Age is required.";
        $messageType = "error";
    } elseif (!is_numeric($age) || $age < 18 || $age > 100) {
        $message = "Age must be between 18 and 100.";
        $messageType = "error";
    } elseif (empty($email)) {
        $message = "Email is required.";
        $messageType = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
        $messageType = "error";
    } elseif (empty($address)) {
        $message = "Address is required.";
        $messageType = "error";
    } elseif (empty($password)) {
        $message = "Password is required.";
        $messageType = "error";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters.";
        $messageType = "error";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $messageType = "error";
    } else {
        // Check if email already exists
        $check_sql = "SELECT id FROM signup WHERE email = '$email'";
        $check_result = $conn->query($check_sql);
        
        if ($check_result->num_rows > 0) {
            $message = "Email already registered. Please use another email or login.";
            $messageType = "error";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO signup (name, age, email, address, password) 
                    VALUES ('$name', '$age', '$email', '$address', '$hashed_password')";
            
            if($conn->query($sql)) {
                $message = "Registration successful! Redirecting to login...";
                $messageType = "success";
                $name = $age = $email = $address = $password = $confirm_password = "";
                
                // Redirect to login page after 2 seconds
                header("refresh:2;url=login.php");
            } else {
                $message = "Database error: " . $conn->error;
                $messageType = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="../css/signup.css">
</head>
<body>
    <div class="signup-form">
        <h2>Create Account</h2>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <form method="post" action="" id="signupForm">
            <div class="form-group">
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="age">Age:</label>
                <input type="number" id="age" name="age" value="<?php echo htmlspecialchars($age); ?>" min="18" max="100" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="address">Address:</label>
                <textarea id="address" name="address" required><?php echo htmlspecialchars($address); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="submit-btn">Create Account</button>
        </form>
        
        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
    
    <script src="../js/sign.js"></script>
</body>
</html>
<?php
$conn->close();
?>