<?php
session_start();
include "../db/db.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'get_customers':
            getCustomers();
            break;
        case 'delete_customer':
            deleteCustomer();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit();
}

// Function to get all customers
function getCustomers() {
    global $conn;
    
    $limit = 10;
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM signup";
    $countResult = $conn->query($countSql);
    $total = $countResult->fetch_assoc()['total'];
    
    // Get customers
    $sql = "SELECT id, name, email, age, address, created_at FROM signup ORDER BY created_at DESC";
    $result = $conn->query($sql);
    
    $customers = [];
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
    
    echo json_encode([
        'success' => true, 
        'customers' => $customers,
        'total' => $total
    ]);
}

// Function to delete customer
function deleteCustomer() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    $id = intval($data['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid customer ID']);
        return;
    }
    
    // Delete customer
    $sql = "DELETE FROM signup WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Customer deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting customer']);
    }
    $stmt->close();
}

// Get initial customers for page load
$sql = "SELECT COUNT(*) as total FROM signup";
$countResult = $conn->query($sql);
$totalCustomers = $countResult->fetch_assoc()['total'];

$sql = "SELECT id, name, email, age, address, created_at FROM signup ORDER BY created_at DESC";
$result = $conn->query($sql);
$customers = [];
while ($row = $result->fetch_assoc()) {
    $customers[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management</title>
    <link rel="stylesheet" href="../css/customer.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Customer Management</h1>
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-number" id="totalCustomers"><?php echo $totalCustomers; ?></div>
                    <div class="stat-label">Total Customers</div>
                </div>
            </div>
            <a href="admin.php" class="btn back-btn">← Back to Dashboard</a>
        </div>
        
        <!-- Message Area -->
        <div id="message" class="message"></div>
        
        <!-- Customers Table -->
        <div class="customers-table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Age</th>
                        <th>Address</th>
                        <th>Joined Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="customersBody">
                    <?php if (empty($customers)): ?>
                        <tr>
                            <td colspan="7" class="no-customers">No customers found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($customers as $customer): ?>
                        <tr data-id="<?php echo $customer['id']; ?>">
                            <td>#<?php echo str_pad($customer['id'], 4, '0', STR_PAD_LEFT); ?></td>
                            <td>
                                <div class="customer-name"><?php echo htmlspecialchars($customer['name']); ?></div>
                            </td>
                            <td>
                                <div class="customer-email"><?php echo htmlspecialchars($customer['email']); ?></div>
                            </td>
                            <td class="customer-age"><?php echo $customer['age']; ?></td>
                            <td>
                                <div class="customer-address" title="<?php echo htmlspecialchars($customer['address']); ?>">
                                    <?php echo htmlspecialchars($customer['address']); ?>
                                </div>
                            </td>
                            <td>
                                <div class="customer-date">
                                    <?php echo date('M d, Y', strtotime($customer['created_at'])); ?>
                                </div>
                            </td>
                            <td>
                                <button class="delete-btn" onclick="deleteCustomer(<?php echo $customer['id']; ?>)">Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script src="../js/customer.js"></script>
</body>
</html>
<?php $conn->close(); ?>