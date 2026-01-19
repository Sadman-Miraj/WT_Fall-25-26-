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
        case 'get_employees':
            getEmployees();
            break;
        case 'add_employee':
            addEmployee();
            break;
        case 'delete_employee':
            deleteEmployee();
            break;
        case 'update_work':
            updateWork();
            break;
        case 'add_report':
            addReport();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit();
}

// Function to get all employees
function getEmployees() {
    global $conn;
    
    $sql = "SELECT * FROM employees ORDER BY created_at DESC";
    $result = $conn->query($sql);
    
    $employees = [];
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
    
    echo json_encode([
        'success' => true, 
        'employees' => $employees
    ]);
}

// Function to add employee
function addEmployee() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $name = trim($data['name'] ?? '');
    $email = trim($data['email'] ?? '');
    $mobile = trim($data['mobile'] ?? '');
    $post = trim($data['post'] ?? '');
    $address = trim($data['address'] ?? '');
    $salary = floatval($data['salary'] ?? 0);
    $hired_date = $data['hired_date'] ?? date('Y-m-d');
    
    if (empty($name) || empty($email) || empty($mobile) || empty($post) || empty($address)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        return;
    }
    
    // Check if email already exists
    $checkSql = "SELECT id FROM employees WHERE email = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        $checkStmt->close();
        return;
    }
    $checkStmt->close();
    
    // Insert new employee
    $sql = "INSERT INTO employees (name, email, mobile, post, address, salary, hired_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssds", $name, $email, $mobile, $post, $address, $salary, $hired_date);
    
    if ($stmt->execute()) {
        $newId = $stmt->insert_id;
        echo json_encode(['success' => true, 'id' => $newId, 'message' => 'Employee added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error adding employee']);
    }
    $stmt->close();
}

// Function to delete employee
function deleteEmployee() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    $id = intval($data['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid employee ID']);
        return;
    }
    
    $sql = "DELETE FROM employees WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Employee deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting employee']);
    }
    $stmt->close();
}

// Function to update work done
function updateWork() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    $id = intval($data['id'] ?? 0);
    $work_done = trim($data['work_done'] ?? '');
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid employee ID']);
        return;
    }
    
    $sql = "UPDATE employees SET work_done = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $work_done, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Work updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating work']);
    }
    $stmt->close();
}

// Function to add report
function addReport() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    $id = intval($data['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid employee ID']);
        return;
    }
    
    // Get current reports
    $getSql = "SELECT reports FROM employees WHERE id = ?";
    $getStmt = $conn->prepare($getSql);
    $getStmt->bind_param("i", $id);
    $getStmt->execute();
    $result = $getStmt->get_result();
    $employee = $result->fetch_assoc();
    $currentReports = intval($employee['reports']);
    $getStmt->close();
    
    // Update reports
    $newReports = $currentReports + 1;
    $sql = "UPDATE employees SET reports = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $newReports, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'reports' => $newReports, 'message' => 'Report added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error adding report']);
    }
    $stmt->close();
}

// Get initial employees for page load
$sql = "SELECT COUNT(*) as total FROM employees";
$countResult = $conn->query($sql);
$totalEmployees = $countResult->fetch_assoc()['total'];

$sql = "SELECT * FROM employees ORDER BY created_at DESC";
$result = $conn->query($sql);
$employees = [];
while ($row = $result->fetch_assoc()) {
    $employees[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management</title>
    <link rel="stylesheet" href="../css/employee.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Employee Management</h1>
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-number" id="totalEmployees"><?php echo $totalEmployees; ?></div>
                    <div class="stat-label">Total Employees</div>
                </div>
            </div>
            <a href="admin.php" class="btn back-btn">← Back to Dashboard</a>
        </div>
        
        <!-- Message Area -->
        <div id="message" class="message"></div>
        
        <!-- Add Employee Button -->
        <div class="add-employee-btn">
            <button class="btn add-btn" onclick="openAddModal()">+ Add New Employee</button>
        </div>
        
        <!-- Employees Table -->
        <div class="employees-table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Post</th>
                        <th>Salary</th>
                        <th>Reports</th>
                        <th>Work Done</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="employeesBody">
                    <?php if (empty($employees)): ?>
                        <tr>
                            <td colspan="9" class="no-employees">No employees found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($employees as $employee): ?>
                        <tr data-id="<?php echo $employee['id']; ?>">
                            <td>#<?php echo str_pad($employee['id'], 4, '0', STR_PAD_LEFT); ?></td>
                            <td>
                                <div class="employee-name"><?php echo htmlspecialchars($employee['name']); ?></div>
                                <div class="employee-post"><?php echo htmlspecialchars($employee['post']); ?></div>
                            </td>
                            <td>
                                <div class="employee-email"><?php echo htmlspecialchars($employee['email']); ?></div>
                            </td>
                            <td class="employee-mobile"><?php echo htmlspecialchars($employee['mobile']); ?></td>
                            <td class="employee-post-td"><?php echo htmlspecialchars($employee['post']); ?></td>
                            <td class="employee-salary">$<?php echo number_format($employee['salary'], 2); ?></td>
                            <td class="employee-reports">
                                <div class="report-count" id="reports-<?php echo $employee['id']; ?>">
                                    <?php echo $employee['reports']; ?>
                                </div>
                                <button class="report-btn" onclick="addReport(<?php echo $employee['id']; ?>)">+</button>
                            </td>
                            <td>
                                <div class="work-done">
                                    <?php echo htmlspecialchars($employee['work_done'] ?? 'No work recorded'); ?>
                                </div>
                                <button class="work-btn" onclick="openWorkModal(<?php echo $employee['id']; ?>)">Update</button>
                            </td>
                            <td>
                                <button class="delete-btn" onclick="deleteEmployee(<?php echo $employee['id']; ?>)">Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Add Employee Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <h2>Add New Employee</h2>
            <form id="addForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="empName">Name *</label>
                        <input type="text" id="empName" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="empEmail">Email *</label>
                        <input type="email" id="empEmail" name="email" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="empMobile">Mobile *</label>
                        <input type="tel" id="empMobile" name="mobile" required>
                    </div>
                    <div class="form-group">
                        <label for="empPost">Post *</label>
                        <select id="empPost" name="post" required>
                            <option value="">Select Post</option>
                            <option value="Mechanic">Mechanic</option>
                            <option value="Manager">Manager</option>
                            <option value="Receptionist">Receptionist</option>
                            <option value="Cleaner">Cleaner</option>
                            <option value="Driver">Driver</option>
                            <option value="Technician">Technician</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="empAddress">Address *</label>
                    <textarea id="empAddress" name="address" rows="3" required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="empSalary">Salary ($)</label>
                        <input type="number" id="empSalary" name="salary" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label for="empHiredDate">Hired Date</label>
                        <input type="date" id="empHiredDate" name="hired_date">
                    </div>
                </div>
                
                <div class="modal-buttons">
                    <button type="submit" class="btn save-btn">Add Employee</button>
                    <button type="button" class="btn cancel-btn" onclick="closeAddModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Update Work Modal -->
    <div id="workModal" class="modal">
        <div class="modal-content">
            <h2>Update Work Done</h2>
            <form id="workForm">
                <input type="hidden" id="workEmpId">
                <div class="form-group">
                    <label for="workDone">Work Description *</label>
                    <textarea id="workDone" name="work_done" rows="5" required placeholder="Describe the work completed by employee..."></textarea>
                </div>
                <div class="modal-buttons">
                    <button type="submit" class="btn save-btn">Update Work</button>
                    <button type="button" class="btn cancel-btn" onclick="closeWorkModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="../js/employee.js"></script>
</body>
</html>
<?php $conn->close(); ?>