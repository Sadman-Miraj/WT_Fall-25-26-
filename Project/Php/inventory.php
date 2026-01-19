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
        case 'get_items':
            getItems();
            break;
        case 'add_item':
            addItem();
            break;
        case 'update_item':
            updateItem();
            break;
        case 'delete_item':
            deleteItem();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit();
}

// Function to get all items
function getItems() {
    global $conn;
    $sql = "SELECT * FROM inventory ORDER BY category, name";
    $result = $conn->query($sql);
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    echo json_encode(['success' => true, 'items' => $items]);
}

// Function to add item
function addItem() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $name = trim($data['name'] ?? '');
    $category = $data['category'] ?? '';
    $price = floatval($data['price'] ?? 0);
    $quantity = intval($data['quantity'] ?? 0);
    $description = trim($data['description'] ?? '');
    
    if (empty($name) || empty($category) || $price <= 0 || $quantity < 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid input data']);
        return;
    }
    
    $sql = "INSERT INTO inventory (name, category, price, quantity, description) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdis", $name, $category, $price, $quantity, $description);
    
    if ($stmt->execute()) {
        $newId = $stmt->insert_id;
        echo json_encode(['success' => true, 'id' => $newId, 'message' => 'Item added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error adding item']);
    }
    $stmt->close();
}

// Function to update item
function updateItem() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $id = intval($data['id'] ?? 0);
    $field = $data['field'] ?? '';
    $value = $data['value'] ?? '';
    
    if ($id <= 0 || empty($field)) {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        return;
    }
    
    // Validate field
    $allowedFields = ['name', 'category', 'price', 'quantity', 'description'];
    if (!in_array($field, $allowedFields)) {
        echo json_encode(['success' => false, 'message' => 'Invalid field']);
        return;
    }
    
    // Prepare statement based on field type
    if ($field === 'quantity') {
        $value = intval($value);
        $sql = "UPDATE inventory SET quantity = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $value, $id);
    } elseif ($field === 'price') {
        $value = floatval($value);
        $sql = "UPDATE inventory SET price = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $value, $id);
    } else {
        $sql = "UPDATE inventory SET $field = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $value, $id);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Item updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating item']);
    }
    $stmt->close();
}

// Function to delete item
function deleteItem() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    $id = intval($data['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        return;
    }
    
    $sql = "DELETE FROM inventory WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Item deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting item']);
    }
    $stmt->close();
}

// Fetch initial items for page load
$sql = "SELECT * FROM inventory ORDER BY category, name";
$result = $conn->query($sql);
$items = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <link rel="stylesheet" href="../css/inventory.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Inventory Management</h1>
            <div>
                <a href="admin.php" class="btn back-btn">← Back to Dashboard</a>
                <button class="btn add-btn" id="openAddModal">+ Add New Item</button>
            </div>
        </div>
        
        <!-- Message Area -->
        <div id="message" class="message"></div>
        
        <!-- Category Filters -->
        <div class="category-filters">
            <button class="filter-btn active" data-category="all">All Items</button>
            <button class="filter-btn" data-category="light">Lights</button>
            <button class="filter-btn" data-category="wheel">Wheels</button>
            <button class="filter-btn" data-category="shock">Shock Absorbers</button>
            <button class="filter-btn" data-category="battery">Batteries</button>
        </div>
        
        <!-- Inventory Grid -->
        <div class="inventory-grid" id="inventoryGrid">
            <?php if (empty($items)): ?>
                <div class="no-items">
                    <p>No items in inventory. Click "Add New Item" to get started!</p>
                </div>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                <div class="item-card" data-category="<?php echo $item['category']; ?>" data-id="<?php echo $item['id']; ?>">
                    <div class="item-header">
                        <div>
                            <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <span class="item-category"><?php echo ucfirst($item['category']); ?></span>
                        </div>
                        <span class="status <?php echo $item['quantity'] > 0 ? 'in-stock' : 'out-stock'; ?>">
                            <?php echo $item['quantity'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                        </span>
                    </div>
                    
                    <div class="item-details">
                        <div class="detail-row">
                            <span class="detail-label">Price:</span>
                            <span class="detail-value">$<?php echo number_format($item['price'], 2); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Quantity:</span>
                            <span class="detail-value" id="quantity-<?php echo $item['id']; ?>">
                                <?php echo $item['quantity']; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="quantity-control">
                        <input type="number" 
                               class="quantity-input" 
                               id="qty-input-<?php echo $item['id']; ?>" 
                               value="<?php echo $item['quantity']; ?>" 
                               min="0">
                        <button class="update-qty-btn" onclick="updateQuantity(<?php echo $item['id']; ?>)">
                            Update
                        </button>
                    </div>
                    
                    <div class="item-actions">
                        <button class="btn edit-btn" onclick="editItem(<?php echo $item['id']; ?>)">Edit</button>
                        <button class="btn delete-btn" onclick="deleteItem(<?php echo $item['id']; ?>)">Delete</button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Add Item Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <h2>Add New Item</h2>
            <form id="addForm">
                <div class="form-group">
                    <label for="itemName">Item Name *</label>
                    <input type="text" id="itemName" name="name" required placeholder="e.g., LED Headlight">
                </div>
                
                <div class="form-group">
                    <label for="itemCategory">Category *</label>
                    <select id="itemCategory" name="category" required>
                        <option value="">Select Category</option>
                        <option value="light">Light</option>
                        <option value="wheel">Wheel</option>
                        <option value="shock">Shock Absorber</option>
                        <option value="battery">Battery</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="itemPrice">Price ($) *</label>
                    <input type="number" id="itemPrice" name="price" step="0.01" min="0" required placeholder="0.00">
                </div>
                
                <div class="form-group">
                    <label for="itemQuantity">Quantity *</label>
                    <input type="number" id="itemQuantity" name="quantity" min="0" required value="0">
                </div>
                
                <div class="form-group">
                    <label for="itemDescription">Description</label>
                    <textarea id="itemDescription" name="description" rows="3" placeholder="Item description..."></textarea>
                </div>
                
                <div class="modal-buttons">
                    <button type="submit" class="btn save-btn">Save Item</button>
                    <button type="button" class="btn cancel-btn" id="closeAddModal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit Item Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h2>Edit Item</h2>
            <form id="editForm">
                <input type="hidden" id="editItemId">
                <div class="form-group">
                    <label for="editItemName">Item Name *</label>
                    <input type="text" id="editItemName" required>
                </div>
                
                <div class="form-group">
                    <label for="editItemCategory">Category *</label>
                    <select id="editItemCategory" required>
                        <option value="light">Light</option>
                        <option value="wheel">Wheel</option>
                        <option value="shock">Shock Absorber</option>
                        <option value="battery">Battery</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="editItemPrice">Price ($) *</label>
                    <input type="number" id="editItemPrice" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="editItemDescription">Description</label>
                    <textarea id="editItemDescription" rows="3"></textarea>
                </div>
                
                <div class="modal-buttons">
                    <button type="submit" class="btn save-btn">Update Item</button>
                    <button type="button" class="btn cancel-btn" id="closeEditModal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Pass PHP data to JavaScript -->
    <script>
        window.currentItems = <?php echo json_encode($items); ?>;
    </script>
    
    <script src="../js/inventory.js"></script>
</body>
</html>
<?php $conn->close(); ?>