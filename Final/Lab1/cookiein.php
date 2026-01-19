<?php
$message = "";
$currentCookieValue = "";

// Initialize current cookie value
if (isset($_COOKIE['username'])) {
    $currentCookieValue = $_COOKIE['username'];
}

// Handle form submission BEFORE HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['button'])) {
    
    // Handle SET COOKIE
    if ($_POST['button'] == 'set' && isset($_POST['username'])) {
        $val = trim($_POST['username']);
        
        if (!empty($val)) {
            // Set cookie for 1 day
            setcookie('username', htmlspecialchars($val), time() + 86400, "/");
            // Update current cookie value for immediate display
            $currentCookieValue = $val;
            $message = "Cookie 'username' has been set!";
        } else {
            $message = "Please enter a username";
        }
    }
    
    // Handle DELETE COOKIE
    if ($_POST['button'] == 'delete') {
        if (isset($_COOKIE['username'])) {
            setcookie('username', "", time() - 3600, "/");
            $currentCookieValue = "";
            $message = "Cookie 'username' has been deleted";
        } else {
            $message = "No cookie to delete";
        }
    }
    
    // Handle GET COOKIE
    if ($_POST['button'] == 'get') {
        if (isset($_COOKIE['username'])) {
            $message = "Cookie value is: " . htmlspecialchars($_COOKIE['username']);
        } else {
            $message = "Cookie is not set";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cookie Manager</title>
    <style>
        body { font-family: Arial; margin: 40px; }
        .message { 
            background: #f0f0f0; 
            padding: 10px; 
            margin: 10px 0; 
            border-left: 4px solid #4CAF50;
        }
        input, button { 
            padding: 8px; 
            margin: 5px 0; 
        }
        button { 
            background: #4CAF50; 
            color: white; 
            border: none; 
            cursor: pointer; 
            padding: 10px 15px;
            margin: 5px;
        }
        button:hover { background: #45a049; }
        .delete-btn { background: #f44336; }
        .delete-btn:hover { background: #d32f2f; }
        .cookie-status {
            background: #e8f4f8;
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h2>Cookie Management System</h2>
    
    <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <form method="post" action="">
        <input type="text" name="username" placeholder="Enter your name" 
               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
        <br><br>
        <button type="submit" name="button" value="set">Set Cookie</button>
        <button type="submit" name="button" value="get">Get Cookie</button>
        <button type="submit" name="button" value="delete" class="delete-btn">Delete Cookie</button>
    </form>
    
    <div class="cookie-status">
        <h3>Current Cookie Status:</h3>
        <?php if ($currentCookieValue): ?>
            <p><strong>Username Cookie is SET</strong></p>
            <p>Value: <?php echo htmlspecialchars($currentCookieValue); ?></p>
        <?php else: ?>
            <p><strong>No 'username' cookie is currently set</strong></p>
        <?php endif; ?>
    </div>
</body>
</html>