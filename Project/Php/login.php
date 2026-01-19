<?php
session_start();
include "../db/db.php";

$email = $password = "";
$message = "";

// Check if user came from inventory page
$from_inventory = isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'customer_inventory') !== false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    
    // Check if admin credentials
    if ($email === "admin@gmail.com" && $password === "Admin123*") {
        // Set admin session
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_email'] = $email;
        
        // Redirect to admin page
        header("Location: admin.php");
        exit();
    }
    
    // Check if regular user exists
    $sql = "SELECT id, name, email, password FROM signup WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['logged_in'] = true;
            
            // Also set customer session for inventory access
            $_SESSION['customer_id'] = $user['id'];
            $_SESSION['customer_name'] = $user['name'];
            $_SESSION['customer_logged_in'] = true;
            
            // Check if user exists in customers table, if not create one
            $check_customer = $conn->prepare("SELECT * FROM customers WHERE email = ? OR name = ?");
            $check_customer->bind_param("ss", $user['name'], $user['name']);
            $check_customer->execute();
            $customer_result = $check_customer->get_result();
            
            if ($customer_result->num_rows === 0) {
                // Create customer record
                $insert_customer = $conn->prepare("INSERT INTO customers (name, email) VALUES (?, ?)");
                $insert_customer->bind_param("ss", $user['name'], $user['name']);
                $insert_customer->execute();
                $customer_id = $insert_customer->insert_id;
                $_SESSION['customer_id'] = $customer_id;
            }
            
            // Redirect based on where they came from
            if ($from_inventory) {
                header("Location: ../customer_inventory.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $message = "Invalid email or password.";
        }
    } else {
        $message = "Invalid email or password.";
    }
    
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Automobiles Solution</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        
        <?php if (!empty($message)): ?>
            <div class="message error">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <form method="post" action="">
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit" class="login-btn">Login</button>
            
            <div class="links-container">
                <a href="forgot.php" class="link">Forgot Password?</a>
                <a href="signup.php" class="link">Sign Up</a>
            </div>
            
            <?php if ($from_inventory): ?>
                <div class="info-message">
                    <p>Login to access our shop and make purchases.</p>
                </div>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>