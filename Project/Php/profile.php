<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: auth/login.php");
    exit();
}

include "../db/db.php";

// Handle AJAX requests
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

// Handle GET AJAX requests
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

// Function to get profile data
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

// Function to update profile
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
    <div class="profile-container">
        <h1>My Profile</h1>
        
        <!-- Message Area -->
        <div id="message" class="message"></div>
        
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar" id="profileAvatar">
                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                </div>
                <h2 id="profileName"><?php echo htmlspecialchars($user['name']); ?></h2>
                <p class="user-email" id="profileEmail"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            
            <div class="profile-details">
                <div class="detail-item">
                    <span class="detail-label">Age:</span>
                    <span class="detail-value" id="profileAge"><?php echo htmlspecialchars($user['age']); ?> years</span>
                </div>
                
                <div class="detail-item">
                    <span class="detail-label">Address:</span>
                    <span class="detail-value" id="profileAddress"><?php echo htmlspecialchars($user['address']); ?></span>
                </div>
                
                <div class="detail-item">
                    <span class="detail-label">Member Since:</span>
                    <span class="detail-value" id="profileMemberSince">
                        <?php 
                            $date = new DateTime($user['created_at']);
                            echo $date->format('F j, Y');
                        ?>
                    </span>
                </div>
            </div>
            
            <div class="profile-actions">
                <button class="btn btn-primary" onclick="openEditModal()">Edit Profile</button>
                <a href="history.php" class="btn btn-history">My History</a>
                <a href="forgot.php" class="btn btn-secondary">Change Password</a>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
        
        <div class="back-link">
            <a href="index.php">← Back to Home</a>
        </div>
    </div>
    
    <!-- Edit Profile Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h2>Edit Profile</h2>
            <form id="editForm">
                <div class="form-group">
                    <label for="editName">Full Name *</label>
                    <input type="text" id="editName" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="editAge">Age *</label>
                    <input type="number" id="editAge" name="age" min="18" max="100" required>
                </div>
                
                <div class="form-group">
                    <label for="editAddress">Address *</label>
                    <textarea id="editAddress" name="address" rows="3" required></textarea>
                </div>
                
                <div class="modal-buttons">
                    <button type="submit" class="btn save-btn">Save Changes</button>
                    <button type="button" class="btn cancel-btn" onclick="closeEditModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="../js/profile.js"></script>
</body>
</html>
<?php $conn->close(); ?>