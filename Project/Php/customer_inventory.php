<?php
session_start();
include "../db/db.php";

// Add error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Accept both customer login OR regular login
if (!isset($_SESSION['customer_logged_in']) && !isset($_SESSION['logged_in'])) {
    // Neither customer nor regular user is logged in
    header("Location: ../auth/login.php");
    exit();
}

// Get user details
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // Regular user is logged in
    $customer_id = $_SESSION['user_id'] ?? 0;
    $customer_name = $_SESSION['user_name'] ?? 'Customer';
    $user_email = $_SESSION['user_name']; // Using name as email since we don't have email in session
    
    // Ensure customer record exists
    $check_customer = $conn->prepare("SELECT * FROM customers WHERE name = ? OR email = ?");
    $check_customer->bind_param("ss", $customer_name, $customer_name);
    $check_customer->execute();
    $customer_result = $check_customer->get_result();
    
    if ($customer_result->num_rows === 0) {
        // Create customer record
        $insert_customer = $conn->prepare("INSERT INTO customers (name, email) VALUES (?, ?)");
        $insert_customer->bind_param("ss", $customer_name, $customer_name);
        $insert_customer->execute();
        $customer_id = $insert_customer->insert_id;
    } else {
        $customer = $customer_result->fetch_assoc();
        $customer_id = $customer['id'];
    }
    
    // Set customer session for consistency
    $_SESSION['customer_logged_in'] = true;
    $_SESSION['customer_id'] = $customer_id;
    $_SESSION['customer_name'] = $customer_name;
} else {
    // Customer is logged in via separate customer login
    $customer_id = $_SESSION['customer_id'];
    $customer_name = $_SESSION['customer_name'];
    $user_email = $_SESSION['customer_email'] ?? $customer_name;
}
// Get customer details and points
$customer_query = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$customer_query->bind_param("i", $customer_id);
$customer_query->execute();
$customer_result = $customer_query->get_result();
$customer = $customer_result->fetch_assoc();

// Calculate loyalty tier based on points
function calculateLoyaltyTier($points) {
    if ($points >= 150) return 'gold';
    if ($points >= 100) return 'platinum';
    if ($points >= 60) return 'silver';
    if ($points >= 30) return 'bronze';
    return 'none';
}

// Update customer tier if needed
if ($customer) {
    $current_tier = calculateLoyaltyTier($customer['points']);
    if ($customer['loyalty_tier'] !== $current_tier) {
        $update_tier = $conn->prepare("UPDATE customers SET loyalty_tier = ? WHERE id = ?");
        $update_tier->bind_param("si", $current_tier, $customer_id);
        $update_tier->execute();
        $customer['loyalty_tier'] = $current_tier;
    }
}

// Get tier discount percentage
function getTierDiscount($tier) {
    switch($tier) {
        case 'bronze': return 5;  // 5% discount
        case 'silver': return 10; // 10% discount
        case 'platinum': return 15; // 15% discount
        case 'gold': return 20; // 20% discount
        default: return 0;
    }
}

$discount_percentage = getTierDiscount($customer['loyalty_tier'] ?? 'none');
// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'get_cart':
            echo json_encode(['success' => true, 'cart' => $_SESSION['cart'] ?? []]);
            break;
        case 'add_to_cart':
            addToCart();
            break;
        case 'update_cart':
            updateCart();
            break;
        case 'remove_from_cart':
            removeFromCart();
            break;
        case 'checkout':
            checkout();
            break;
        case 'apply_points_discount':
            applyPointsDiscount();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit();
}
// Shopping cart functions
function addToCart() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    $item_id = intval($data['item_id'] ?? 0);
    $quantity = intval($data['quantity'] ?? 1);
    
    error_log("addToCart called: item_id=$item_id, quantity=$quantity");
    
    if ($item_id <= 0 || $quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid item or quantity']);
        return;
    }
    
    // Check item availability
    $check_query = $conn->prepare("SELECT * FROM inventory WHERE id = ? AND quantity >= ?");
    $check_query->bind_param("ii", $item_id, $quantity);
    $check_query->execute();
    $item_result = $check_query->get_result();
    
    if ($item_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Item not available in requested quantity']);
        return;
    }
    
    $item = $item_result->fetch_assoc();
    
    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Check if item already in cart
    $found = false;
    foreach ($_SESSION['cart'] as &$cart_item) {
        if ($cart_item['id'] == $item_id) {
            $new_quantity = $cart_item['quantity'] + $quantity;
            
            // Check if total exceeds available quantity
            $check_quantity = $conn->prepare("SELECT quantity FROM inventory WHERE id = ?");
            $check_quantity->bind_param("i", $item_id);
            $check_quantity->execute();
            $check_result = $check_quantity->get_result();
            $stock = $check_result->fetch_assoc()['quantity'];
            
            if ($new_quantity <= $stock) {
                $cart_item['quantity'] = $new_quantity;
                $found = true;
            } else {
                echo json_encode(['success' => false, 'message' => 'Cannot add more than available stock']);
                return;
            }
            break;
        }
    }
    
    if (!$found) {
        $_SESSION['cart'][] = [
            'id' => $item_id,
            'name' => $item['name'],
            'price' => $item['price'],
            'quantity' => $quantity,
            'category' => $item['category']
        ];
    }
    
    echo json_encode(['success' => true, 'cart' => $_SESSION['cart'], 'cart_count' => count($_SESSION['cart'])]);
}
function updateCart() {
    $data = json_decode(file_get_contents('php://input'), true);
    $item_id = intval($data['item_id'] ?? 0);
    $quantity = intval($data['quantity'] ?? 1);
    
    error_log("updateCart called: item_id=$item_id, quantity=$quantity");
    
    if ($item_id <= 0 || $quantity < 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        return;
    }
    
    if (!isset($_SESSION['cart'])) {
        echo json_encode(['success' => false, 'message' => 'Cart is empty']);
        return;
    }
    
    if ($quantity === 0) {
        // Remove item
        foreach ($_SESSION['cart'] as $key => $item) {
            if ($item['id'] == $item_id) {
                unset($_SESSION['cart'][$key]);
                $_SESSION['cart'] = array_values($_SESSION['cart']);
                break;
            }
        }
    } else {
        // Update quantity
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $item_id) {
                $item['quantity'] = $quantity;
                break;
            }
        }
    }
    
    echo json_encode(['success' => true, 'cart' => $_SESSION['cart']]);
}

function removeFromCart() {
    $data = json_decode(file_get_contents('php://input'), true);
    $item_id = intval($data['item_id'] ?? 0);
    
    error_log("removeFromCart called: item_id=$item_id");
    
    if ($item_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
        return;
    }
    
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $key => $item) {
            if ($item['id'] == $item_id) {
                unset($_SESSION['cart'][$key]);
                $_SESSION['cart'] = array_values($_SESSION['cart']);
                break;
            }
        }
    }
    
    echo json_encode(['success' => true, 'cart' => $_SESSION['cart']]);
}