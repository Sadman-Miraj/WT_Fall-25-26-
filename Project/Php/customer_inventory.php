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
function checkout() {
    global $conn, $customer_id, $discount_percentage;
    
    $data = json_decode(file_get_contents('php://input'), true);
    $use_points = $data['use_points'] ?? false;
    $points_to_use = intval($data['points_to_use'] ?? 0);
    
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        echo json_encode(['success' => false, 'message' => 'Cart is empty']);
        return;
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Calculate cart total
        $cart_total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $cart_total += $item['price'] * $item['quantity'];
        }
        
        // Calculate points earned (5 points per 1000 BDT)
        $points_earned = floor($cart_total / 1000) * 5;
        
        // Apply tier discount
        $tier_discount = ($cart_total * $discount_percentage) / 100;
        
        // Apply points discount if requested
        $points_discount = 0;
        if ($use_points && $points_to_use > 0) {
            // Check if customer has enough points
            $points_query = $conn->prepare("SELECT points FROM customers WHERE id = ?");
            $points_query->bind_param("i", $customer_id);
            $points_query->execute();
            $points_result = $points_query->get_result();
            $customer_points = $points_result->fetch_assoc()['points'];
            
            if ($points_to_use <= $customer_points) {
                // 1 point = 10 BDT discount
                $points_discount = $points_to_use * 10;
                
                // Deduct points
                $new_points = $customer_points - $points_to_use + $points_earned;
                $update_points = $conn->prepare("UPDATE customers SET points = ? WHERE id = ?");
                $update_points->bind_param("ii", $new_points, $customer_id);
                $update_points->execute();
            }
        } else {
            // Just add earned points
            $points_query = $conn->prepare("SELECT points FROM customers WHERE id = ?");
            $points_query->bind_param("i", $customer_id);
            $points_query->execute();
            $points_result = $points_query->get_result();
            $customer_points = $points_result->fetch_assoc()['points'];
            
            $new_points = $customer_points + $points_earned;
            $update_points = $conn->prepare("UPDATE customers SET points = ? WHERE id = ?");
            $update_points->bind_param("ii", $new_points, $customer_id);
            $update_points->execute();
        }
        
        // Calculate final amount
        $total_discount = $tier_discount + $points_discount;
        $final_amount = $cart_total - $total_discount;
        
        if ($final_amount < 0) $final_amount = 0;
        
        // Create order
        $items_json = json_encode($_SESSION['cart']);
        $order_query = $conn->prepare("INSERT INTO orders (customer_id, items, total_amount, points_earned, discount_applied, final_amount, status) VALUES (?, ?, ?, ?, ?, ?, 'completed')");
        $order_query->bind_param("isdidd", $customer_id, $items_json, $cart_total, $points_earned, $total_discount, $final_amount);
        $order_query->execute();
        $order_id = $order_query->insert_id;
        
        // Update inventory quantities
        foreach ($_SESSION['cart'] as $item) {
            $update_inv = $conn->prepare("UPDATE inventory SET quantity = quantity - ? WHERE id = ?");
            $update_inv->bind_param("ii", $item['quantity'], $item['id']);
            $update_inv->execute();
        }
        
        // Update customer total spent
        $update_spent = $conn->prepare("UPDATE customers SET total_spent = total_spent + ? WHERE id = ?");
        $update_spent->bind_param("di", $final_amount, $customer_id);
        $update_spent->execute();
        
        // Record points discount if used
        if ($points_discount > 0) {
            $discount_query = $conn->prepare("INSERT INTO discounts (customer_id, points_used, discount_amount, order_id) VALUES (?, ?, ?, ?)");
            $discount_query->bind_param("iidi", $customer_id, $points_to_use, $points_discount, $order_id);
            $discount_query->execute();
        }
        
        // Commit transaction
        $conn->commit();
        
        // Clear cart
        $_SESSION['cart'] = [];
        
        echo json_encode([
            'success' => true,
            'message' => 'Order placed successfully!',
            'order_id' => $order_id,
            'points_earned' => $points_earned,
            'new_points' => $new_points,
            'total_discount' => $total_discount,
            'final_amount' => $final_amount
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error processing order: ' . $e->getMessage()]);
    }
}
function applyPointsDiscount() {
    global $conn, $customer_id;
    
    $data = json_decode(file_get_contents('php://input'), true);
    $points_to_use = intval($data['points'] ?? 0);
    $cart_total = floatval($data['cart_total'] ?? 0);
    
    if ($points_to_use <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid points']);
        return;
    }
    
    // Check customer points
    $points_query = $conn->prepare("SELECT points FROM customers WHERE id = ?");
    $points_query->bind_param("i", $customer_id);
    $points_query->execute();
    $points_result = $points_query->get_result();
    $customer_points = $points_result->fetch_assoc()['points'];
    
    if ($points_to_use > $customer_points) {
        echo json_encode(['success' => false, 'message' => 'Not enough points']);
        return;
    }
    
    // Calculate discount (1 point = 10 BDT)
    $discount = $points_to_use * 10;
    
    // Don't allow discount more than cart total
    if ($discount > $cart_total) {
        $discount = $cart_total;
        $points_to_use = floor($discount / 10);
    }
    
    echo json_encode([
        'success' => true,
        'points_used' => $points_to_use,
        'discount_amount' => $discount,
        'remaining_points' => $customer_points - $points_to_use
    ]);
}

// Fetch inventory items
$sql = "SELECT * FROM inventory WHERE quantity > 0 ORDER BY category, name";
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
    <title>Auto Parts Store - Automobiles Solution</title>
    <link rel="stylesheet" href="../css/customer_inventory.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
                <!-- Header with Cart and User Info -->
        <div class="header">
            <h1>Auto Parts Store</h1>
            <div class="user-info">
                <?php if (isset($customer['loyalty_tier'])): ?>
                <div class="loyalty-card">
                    <div class="tier-badge tier-<?php echo $customer['loyalty_tier']; ?>">
                        <i class="fas fa-crown"></i>
                        <?php echo ucfirst($customer['loyalty_tier']); ?>
                    </div>
                    <div class="points-info">
                        <span class="points-label">Points:</span>
                        <span class="points-value" id="customerPoints"><?php echo $customer['points'] ?? 0; ?></span>
                        <i class="fas fa-coins"></i>
                    </div>
                    <?php if($discount_percentage > 0): ?>
                    <div class="discount-badge">
                        <i class="fas fa-percentage"></i>
                        <?php echo $discount_percentage; ?>% Discount
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <div class="cart-section">
                    <div class="cart-icon" id="cartIcon">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count" id="cartCount">0</span>
                    </div>
                    <div class="user-welcome">
                        Welcome, <?php echo htmlspecialchars($customer_name); ?>
                    </div>
                    <a href="index.php" class="logout-btn">
                        <i class="fas fa-home"></i> Home
                    </a>
                </div>
            </div>
        </div>
                <!-- Points Info Banner -->
        <div class="points-banner">
            <div class="points-system">
                <h3><i class="fas fa-gift"></i> Loyalty Points System</h3>
                <div class="points-rules">
                    <div class="rule">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Every 1000 BDT spent = <strong>5 points</strong></span>
                    </div>
                    <div class="rule">
                        <i class="fas fa-medal"></i>
                        <span>Tiers: 
                            <span class="tier-bronze">Bronze (30+ points) - 5% off</span> | 
                            <span class="tier-silver">Silver (60+ points) - 10% off</span> | 
                            <span class="tier-platinum">Platinum (100+ points) - 15% off</span> | 
                            <span class="tier-gold">Gold (150+ points) - 20% off</span>
                        </span>
                    </div>
                    <div class="rule">
                        <i class="fas fa-exchange-alt"></i>
                        <span>Redeem points: <strong>1 point = 10 BDT discount</strong></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Message Area -->
        <div id="message" class="message"></div>
                <!-- Category Filters -->
        <div class="category-filters">
            <button class="filter-btn active" data-category="all">All Products</button>
            <button class="filter-btn" data-category="light">Lights</button>
            <button class="filter-btn" data-category="wheel">Wheels</button>
            <button class="filter-btn" data-category="shock">Shock Absorbers</button>
            <button class="filter-btn" data-category="battery">Batteries</button>
        </div>
                <!-- Product Grid -->
        <div class="product-grid" id="productGrid">
            <?php if (empty($items)): ?>
                <div class="no-products">
                    <i class="fas fa-box-open"></i>
                    <p>No products available at the moment.</p>
                </div>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                <div class="product-card" data-category="<?php echo $item['category']; ?>">
                    <div class="product-image">
                        <!-- Placeholder for product image -->
                        <div class="image-placeholder">
                            <i class="fas fa-car"></i>
                        </div>
                        <?php if($item['quantity'] <= 5): ?>
                        <span class="low-stock-badge">Low Stock</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-info">
                        <h3 class="product-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                        <span class="product-category"><?php echo ucfirst($item['category']); ?></span>
                        
                        <div class="product-price">
                            <span class="price">৳<?php echo number_format($item['price'], 2); ?></span>
                            <?php if($item['quantity'] <= 5): ?>
                            <span class="stock-warning">Only <?php echo $item['quantity']; ?> left!</span>
                            <?php endif; ?>
                        </div>
                        
                        <p class="product-description"><?php echo htmlspecialchars($item['description'] ?? 'No description available'); ?></p>
                        
                        <div class="quantity-control">
                            <div class="qty-selector">
                                <button class="qty-btn minus" onclick="updateQuantity(<?php echo $item['id']; ?>, -1)">-</button>
                                <input type="number" 
                                       class="qty-input" 
                                       id="qty-<?php echo $item['id']; ?>" 
                                       value="1" 
                                       min="1" 
                                       max="<?php echo $item['quantity']; ?>">
                                <button class="qty-btn plus" onclick="updateQuantity(<?php echo $item['id']; ?>, 1)">+</button>
                            </div>
                            <button class="add-to-cart-btn" onclick="addToCart(<?php echo $item['id']; ?>)">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
        <!-- Shopping Cart Sidebar -->
    <div class="cart-sidebar" id="cartSidebar">
        <div class="cart-header">
            <h2><i class="fas fa-shopping-cart"></i> Your Cart</h2>
            <button class="close-cart" id="closeCart">&times;</button>
        </div>
        
        <div class="cart-items" id="cartItems">
            <!-- Cart items will be loaded here -->
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <p>Your cart is empty</p>
            </div>
        </div>
        
        <div class="cart-summary">
            <div class="summary-row">
                <span>Subtotal:</span>
                <span id="cartSubtotal">৳0.00</span>
            </div>
            <div class="summary-row">
                <span>Tier Discount (<?php echo $discount_percentage; ?>%):</span>
                <span id="tierDiscount">-৳0.00</span>
            </div>
            <div class="summary-row">
                <span>Points Discount:</span>
                <span id="pointsDiscount">-৳0.00</span>
            </div>
            <div class="summary-row total">
                <span>Total:</span>
                <span id="cartTotal">৳0.00</span>
            </div>
            
            <!-- Points Redemption -->
            <div class="points-redemption">
                <label for="usePoints">
                    <input type="checkbox" id="usePoints"> Use Points for Discount
                </label>
                <div class="points-input-group" id="pointsInputGroup" style="display: none;">
                    <input type="number" 
                           id="pointsToUse" 
                           min="1" 
                           max="<?php echo $customer['points'] ?? 0; ?>" 
                           placeholder="Points to use">
                    <button class="apply-points-btn" onclick="applyPoints()">Apply</button>
                    <small>1 point = 10 BDT discount</small>
                </div>
            </div>
            
            <button class="checkout-btn" id="checkoutBtn" onclick="processCheckout()">
                <i class="fas fa-lock"></i> Proceed to Checkout
            </button>
        </div>
    </div>
        <!-- Cart Overlay -->
    <div class="cart-overlay" id="cartOverlay"></div>
    
    <!-- Pass data to JavaScript -->
    <script>
        window.customerData = {
            id: <?php echo $customer_id; ?>,
            points: <?php echo $customer['points'] ?? 0; ?>,
            tier: '<?php echo $customer['loyalty_tier'] ?? 'none'; ?>',
            discountPercentage: <?php echo $discount_percentage; ?>
        };
        
        window.products = <?php echo json_encode($items); ?>;
        
        // Initialize cart from session
        window.cart = <?php echo json_encode($_SESSION['cart'] ?? []); ?>;
        
        // Debug info
        console.log('PHP Data Loaded:');
        console.log('Customer:', window.customerData);
        console.log('Products:', window.products);
        console.log('Cart:', window.cart);
    </script>
        <!-- Debug script to test if buttons work -->
    <script>
        // Simple test script
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded - Testing buttons');
            
            // Test if cart icon exists and is clickable
            const cartIcon = document.getElementById('cartIcon');
            if (cartIcon) {
                console.log('Cart icon found:', cartIcon);
                cartIcon.addEventListener('click', function() {
                    console.log('Cart icon clicked!');
                    const cartSidebar = document.getElementById('cartSidebar');
                    if (cartSidebar) {
                        cartSidebar.style.right = '0';
                        console.log('Cart opened');
                    }
                });
            } else {
                console.error('Cart icon NOT found!');
            }
            
            // Test if "Add to Cart" buttons exist
            const addButtons = document.querySelectorAll('.add-to-cart-btn');
            console.log('Found', addButtons.length, 'Add to Cart buttons');
            
            // Simple test function
            window.testAddToCart = function(itemId) {
                console.log('Test addToCart called for item:', itemId);
                const qtyInput = document.getElementById('qty-' + itemId);
                if (qtyInput) {
                    console.log('Quantity input found:', qtyInput.value);
                }
                return 'Test function working';
            };
        });
    </script>
    
    <!-- Link to external JavaScript file -->
    <script src="../js/customer_inventory.js"></script>
</body>
</html>
<?php $conn->close(); ?><?php
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

function checkout() {
    global $conn, $customer_id, $discount_percentage;
    
    $data = json_decode(file_get_contents('php://input'), true);
    $use_points = $data['use_points'] ?? false;
    $points_to_use = intval($data['points_to_use'] ?? 0);
    
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        echo json_encode(['success' => false, 'message' => 'Cart is empty']);
        return;
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Calculate cart total
        $cart_total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $cart_total += $item['price'] * $item['quantity'];
        }
        
        // Calculate points earned (5 points per 1000 BDT)
        $points_earned = floor($cart_total / 1000) * 5;
        
        // Apply tier discount
        $tier_discount = ($cart_total * $discount_percentage) / 100;
        
        // Apply points discount if requested
        $points_discount = 0;
        if ($use_points && $points_to_use > 0) {
            // Check if customer has enough points
            $points_query = $conn->prepare("SELECT points FROM customers WHERE id = ?");
            $points_query->bind_param("i", $customer_id);
            $points_query->execute();
            $points_result = $points_query->get_result();
            $customer_points = $points_result->fetch_assoc()['points'];
            
            if ($points_to_use <= $customer_points) {
                // 1 point = 10 BDT discount
                $points_discount = $points_to_use * 10;
                
                // Deduct points
                $new_points = $customer_points - $points_to_use + $points_earned;
                $update_points = $conn->prepare("UPDATE customers SET points = ? WHERE id = ?");
                $update_points->bind_param("ii", $new_points, $customer_id);
                $update_points->execute();
            }
        } else {
            // Just add earned points
            $points_query = $conn->prepare("SELECT points FROM customers WHERE id = ?");
            $points_query->bind_param("i", $customer_id);
            $points_query->execute();
            $points_result = $points_query->get_result();
            $customer_points = $points_result->fetch_assoc()['points'];
            
            $new_points = $customer_points + $points_earned;
            $update_points = $conn->prepare("UPDATE customers SET points = ? WHERE id = ?");
            $update_points->bind_param("ii", $new_points, $customer_id);
            $update_points->execute();
        }
        
        // Calculate final amount
        $total_discount = $tier_discount + $points_discount;
        $final_amount = $cart_total - $total_discount;
        
        if ($final_amount < 0) $final_amount = 0;
        
        // Create order
        $items_json = json_encode($_SESSION['cart']);
        $order_query = $conn->prepare("INSERT INTO orders (customer_id, items, total_amount, points_earned, discount_applied, final_amount, status) VALUES (?, ?, ?, ?, ?, ?, 'completed')");
        $order_query->bind_param("isdidd", $customer_id, $items_json, $cart_total, $points_earned, $total_discount, $final_amount);
        $order_query->execute();
        $order_id = $order_query->insert_id;
        
        // Update inventory quantities
        foreach ($_SESSION['cart'] as $item) {
            $update_inv = $conn->prepare("UPDATE inventory SET quantity = quantity - ? WHERE id = ?");
            $update_inv->bind_param("ii", $item['quantity'], $item['id']);
            $update_inv->execute();
        }
        
        // Update customer total spent
        $update_spent = $conn->prepare("UPDATE customers SET total_spent = total_spent + ? WHERE id = ?");
        $update_spent->bind_param("di", $final_amount, $customer_id);
        $update_spent->execute();
        
        // Record points discount if used
        if ($points_discount > 0) {
            $discount_query = $conn->prepare("INSERT INTO discounts (customer_id, points_used, discount_amount, order_id) VALUES (?, ?, ?, ?)");
            $discount_query->bind_param("iidi", $customer_id, $points_to_use, $points_discount, $order_id);
            $discount_query->execute();
        }
        
        // Commit transaction
        $conn->commit();
        
        // Clear cart
        $_SESSION['cart'] = [];
        
        echo json_encode([
            'success' => true,
            'message' => 'Order placed successfully!',
            'order_id' => $order_id,
            'points_earned' => $points_earned,
            'new_points' => $new_points,
            'total_discount' => $total_discount,
            'final_amount' => $final_amount
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error processing order: ' . $e->getMessage()]);
    }
}

function applyPointsDiscount() {
    global $conn, $customer_id;
    
    $data = json_decode(file_get_contents('php://input'), true);
    $points_to_use = intval($data['points'] ?? 0);
    $cart_total = floatval($data['cart_total'] ?? 0);
    
    if ($points_to_use <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid points']);
        return;
    }
    
    // Check customer points
    $points_query = $conn->prepare("SELECT points FROM customers WHERE id = ?");
    $points_query->bind_param("i", $customer_id);
    $points_query->execute();
    $points_result = $points_query->get_result();
    $customer_points = $points_result->fetch_assoc()['points'];
    
    if ($points_to_use > $customer_points) {
        echo json_encode(['success' => false, 'message' => 'Not enough points']);
        return;
    }
    
    // Calculate discount (1 point = 10 BDT)
    $discount = $points_to_use * 10;
    
    // Don't allow discount more than cart total
    if ($discount > $cart_total) {
        $discount = $cart_total;
        $points_to_use = floor($discount / 10);
    }
    
    echo json_encode([
        'success' => true,
        'points_used' => $points_to_use,
        'discount_amount' => $discount,
        'remaining_points' => $customer_points - $points_to_use
    ]);
}

// Fetch inventory items
$sql = "SELECT * FROM inventory WHERE quantity > 0 ORDER BY category, name";
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
    <title>Auto Parts Store - Automobiles Solution</title>
    <link rel="stylesheet" href="../css/customer_inventory.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Header with Cart and User Info -->
        <div class="header">
            <h1>Auto Parts Store</h1>
            <div class="user-info">
                <?php if (isset($customer['loyalty_tier'])): ?>
                <div class="loyalty-card">
                    <div class="tier-badge tier-<?php echo $customer['loyalty_tier']; ?>">
                        <i class="fas fa-crown"></i>
                        <?php echo ucfirst($customer['loyalty_tier']); ?>
                    </div>
                    <div class="points-info">
                        <span class="points-label">Points:</span>
                        <span class="points-value" id="customerPoints"><?php echo $customer['points'] ?? 0; ?></span>
                        <i class="fas fa-coins"></i>
                    </div>
                    <?php if($discount_percentage > 0): ?>
                    <div class="discount-badge">
                        <i class="fas fa-percentage"></i>
                        <?php echo $discount_percentage; ?>% Discount
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <div class="cart-section">
                    <div class="cart-icon" id="cartIcon">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count" id="cartCount">0</span>
                    </div>
                    <div class="user-welcome">
                        Welcome, <?php echo htmlspecialchars($customer_name); ?>
                    </div>
                    <a href="index.php" class="logout-btn">
                        <i class="fas fa-home"></i> Home
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Points Info Banner -->
        <div class="points-banner">
            <div class="points-system">
                <h3><i class="fas fa-gift"></i> Loyalty Points System</h3>
                <div class="points-rules">
                    <div class="rule">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Every 1000 BDT spent = <strong>5 points</strong></span>
                    </div>
                    <div class="rule">
                        <i class="fas fa-medal"></i>
                        <span>Tiers: 
                            <span class="tier-bronze">Bronze (30+ points) - 5% off</span> | 
                            <span class="tier-silver">Silver (60+ points) - 10% off</span> | 
                            <span class="tier-platinum">Platinum (100+ points) - 15% off</span> | 
                            <span class="tier-gold">Gold (150+ points) - 20% off</span>
                        </span>
                    </div>
                    <div class="rule">
                        <i class="fas fa-exchange-alt"></i>
                        <span>Redeem points: <strong>1 point = 10 BDT discount</strong></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Message Area -->
        <div id="message" class="message"></div>
        
        <!-- Category Filters -->
        <div class="category-filters">
            <button class="filter-btn active" data-category="all">All Products</button>
            <button class="filter-btn" data-category="light">Lights</button>
            <button class="filter-btn" data-category="wheel">Wheels</button>
            <button class="filter-btn" data-category="shock">Shock Absorbers</button>
            <button class="filter-btn" data-category="battery">Batteries</button>
        </div>
        
        <!-- Product Grid -->
        <div class="product-grid" id="productGrid">
            <?php if (empty($items)): ?>
                <div class="no-products">
                    <i class="fas fa-box-open"></i>
                    <p>No products available at the moment.</p>
                </div>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                <div class="product-card" data-category="<?php echo $item['category']; ?>">
                    <div class="product-image">
                        <!-- Placeholder for product image -->
                        <div class="image-placeholder">
                            <i class="fas fa-car"></i>
                        </div>
                        <?php if($item['quantity'] <= 5): ?>
                        <span class="low-stock-badge">Low Stock</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-info">
                        <h3 class="product-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                        <span class="product-category"><?php echo ucfirst($item['category']); ?></span>
                        
                        <div class="product-price">
                            <span class="price">৳<?php echo number_format($item['price'], 2); ?></span>
                            <?php if($item['quantity'] <= 5): ?>
                            <span class="stock-warning">Only <?php echo $item['quantity']; ?> left!</span>
                            <?php endif; ?>
                        </div>
                        
                        <p class="product-description"><?php echo htmlspecialchars($item['description'] ?? 'No description available'); ?></p>
                        
                        <div class="quantity-control">
                            <div class="qty-selector">
                                <button class="qty-btn minus" onclick="updateQuantity(<?php echo $item['id']; ?>, -1)">-</button>
                                <input type="number" 
                                       class="qty-input" 
                                       id="qty-<?php echo $item['id']; ?>" 
                                       value="1" 
                                       min="1" 
                                       max="<?php echo $item['quantity']; ?>">
                                <button class="qty-btn plus" onclick="updateQuantity(<?php echo $item['id']; ?>, 1)">+</button>
                            </div>
                            <button class="add-to-cart-btn" onclick="addToCart(<?php echo $item['id']; ?>)">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Shopping Cart Sidebar -->
    <div class="cart-sidebar" id="cartSidebar">
        <div class="cart-header">
            <h2><i class="fas fa-shopping-cart"></i> Your Cart</h2>
            <button class="close-cart" id="closeCart">&times;</button>
        </div>
        
        <div class="cart-items" id="cartItems">
            <!-- Cart items will be loaded here -->
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <p>Your cart is empty</p>
            </div>
        </div>
        
        <div class="cart-summary">
            <div class="summary-row">
                <span>Subtotal:</span>
                <span id="cartSubtotal">৳0.00</span>
            </div>
            <div class="summary-row">
                <span>Tier Discount (<?php echo $discount_percentage; ?>%):</span>
                <span id="tierDiscount">-৳0.00</span>
            </div>
            <div class="summary-row">
                <span>Points Discount:</span>
                <span id="pointsDiscount">-৳0.00</span>
            </div>
            <div class="summary-row total">
                <span>Total:</span>
                <span id="cartTotal">৳0.00</span>
            </div>
            
            <!-- Points Redemption -->
            <div class="points-redemption">
                <label for="usePoints">
                    <input type="checkbox" id="usePoints"> Use Points for Discount
                </label>
                <div class="points-input-group" id="pointsInputGroup" style="display: none;">
                    <input type="number" 
                           id="pointsToUse" 
                           min="1" 
                           max="<?php echo $customer['points'] ?? 0; ?>" 
                           placeholder="Points to use">
                    <button class="apply-points-btn" onclick="applyPoints()">Apply</button>
                    <small>1 point = 10 BDT discount</small>
                </div>
            </div>
            
            <button class="checkout-btn" id="checkoutBtn" onclick="processCheckout()">
                <i class="fas fa-lock"></i> Proceed to Checkout
            </button>
        </div>
    </div>
    
    <!-- Cart Overlay -->
    <div class="cart-overlay" id="cartOverlay"></div>
    
    <!-- Pass data to JavaScript -->
    <script>
        window.customerData = {
            id: <?php echo $customer_id; ?>,
            points: <?php echo $customer['points'] ?? 0; ?>,
            tier: '<?php echo $customer['loyalty_tier'] ?? 'none'; ?>',
            discountPercentage: <?php echo $discount_percentage; ?>
        };
        
        window.products = <?php echo json_encode($items); ?>;
        
        // Initialize cart from session
        window.cart = <?php echo json_encode($_SESSION['cart'] ?? []); ?>;
        
        // Debug info
        console.log('PHP Data Loaded:');
        console.log('Customer:', window.customerData);
        console.log('Products:', window.products);
        console.log('Cart:', window.cart);
    </script>
    
    <!-- Debug script to test if buttons work -->
    <script>
        // Simple test script
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded - Testing buttons');
            
            // Test if cart icon exists and is clickable
            const cartIcon = document.getElementById('cartIcon');
            if (cartIcon) {
                console.log('Cart icon found:', cartIcon);
                cartIcon.addEventListener('click', function() {
                    console.log('Cart icon clicked!');
                    const cartSidebar = document.getElementById('cartSidebar');
                    if (cartSidebar) {
                        cartSidebar.style.right = '0';
                        console.log('Cart opened');
                    }
                });
            } else {
                console.error('Cart icon NOT found!');
            }
            
            // Test if "Add to Cart" buttons exist
            const addButtons = document.querySelectorAll('.add-to-cart-btn');
            console.log('Found', addButtons.length, 'Add to Cart buttons');
            
            // Simple test function
            window.testAddToCart = function(itemId) {
                console.log('Test addToCart called for item:', itemId);
                const qtyInput = document.getElementById('qty-' + itemId);
                if (qtyInput) {
                    console.log('Quantity input found:', qtyInput.value);
                }
                return 'Test function working';
            };
        });
    </script>
    
    <!-- Link to external JavaScript file -->
    <script src="../js/customer_inventory.js"></script>
</body>
</html>
<?php $conn->close(); ?>