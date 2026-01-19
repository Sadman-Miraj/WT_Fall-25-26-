// Debug: Check if script is loaded
console.log('customer_inventory.js loaded!');

// Global variables
let cart = window.cart || [];
let pointsDiscount = 0;
let pointsUsed = 0;
let isProcessing = false;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded - Inventory page initialized');
    console.log('Customer Data:', window.customerData);
    console.log('Products:', window.products);
    console.log('Initial Cart:', cart);
    
    // Initialize cart display
    updateCartCount();
    loadCartItems();
    updateCartSummary();
    
    // Initialize filters
    const filters = document.querySelectorAll('.filter-btn');
    if (filters && filters.length > 0) {
        filters.forEach(btn => {
            btn.addEventListener('click', function() {
                filters.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                const cat = this.dataset.category;
                document.querySelectorAll('.product-card').forEach(item => {
                    item.style.display = (cat === 'all' || item.dataset.category === cat) ? 'flex' : 'none';
                });
            });
        });
    }
    
    // Points checkbox toggle
    const usePoints = document.getElementById('usePoints');
    const pointsInputGroup = document.getElementById('pointsInputGroup');
    if (usePoints) {
        usePoints.addEventListener('change', function() {
            if (this.checked) {
                if (pointsInputGroup) {
                    pointsInputGroup.style.display = 'flex';
                    const pointsToUseInput = document.getElementById('pointsToUse');
                    if (pointsToUseInput) {
                        pointsToUseInput.max = window.customerData?.points || 0;
                    }
                }
            } else {
                if (pointsInputGroup) {
                    pointsInputGroup.style.display = 'none';
                }
                pointsDiscount = 0;
                pointsUsed = 0;
                updateCartSummary();
            }
        });
    }
    
    // Cart icon click event
    const cartIcon = document.getElementById('cartIcon');
    if (cartIcon) {
        cartIcon.addEventListener('click', openCart);
        console.log('Cart icon event listener added');
    }
    
    // Close cart button
    const closeCart = document.getElementById('closeCart');
    if (closeCart) {
        closeCart.addEventListener('click', closeCartFunc);
    }
    
    // Cart overlay click
    const cartOverlay = document.getElementById('cartOverlay');
    if (cartOverlay) {
        cartOverlay.addEventListener('click', closeCartFunc);
    }
    
    // Add event listeners to all "Add to Cart" buttons
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        const onclick = button.getAttribute('onclick');
        if (onclick && onclick.includes('addToCart')) {
            const match = onclick.match(/addToCart\((\d+)\)/);
            if (match) {
                const itemId = match[1];
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    addToCart(itemId);
                });
            }
        }
    });
    
    // Add event listeners to quantity buttons
    document.querySelectorAll('.qty-btn').forEach(button => {
        const onclick = button.getAttribute('onclick');
        if (onclick && onclick.includes('updateQuantity')) {
            const match = onclick.match(/updateQuantity\((\d+),\s*(-?\d+)\)/);
            if (match) {
                const itemId = match[1];
                const change = parseInt(match[2]);
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    updateQuantity(itemId, change);
                });
            }
        }
    });
    
    // Apply points button
    const applyPointsBtn = document.querySelector('.apply-points-btn');
    if (applyPointsBtn) {
        applyPointsBtn.addEventListener('click', function(e) {
            e.preventDefault();
            applyPoints();
        });
    }
    
    // Checkout button
    const checkoutBtn = document.getElementById('checkoutBtn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            processCheckout();
        });
    }
    
    console.log('All event listeners added');
});

// Cart Toggle functions
function openCart() {
    console.log('Opening cart');
    const cartSidebar = document.getElementById('cartSidebar');
    const cartOverlay = document.getElementById('cartOverlay');
    
    if (cartSidebar) {
        cartSidebar.style.right = '0';
    }
    if (cartOverlay) {
        cartOverlay.style.display = 'block';
    }
    document.body.style.overflow = 'hidden';
}

function closeCartFunc() {
    console.log('Closing cart');
    const cartSidebar = document.getElementById('cartSidebar');
    const cartOverlay = document.getElementById('cartOverlay');
    
    if (cartSidebar) {
        cartSidebar.style.right = '-400px';
    }
    if (cartOverlay) {
        cartOverlay.style.display = 'none';
    }
    document.body.style.overflow = 'auto';
}

// Add to Cart
function addToCart(itemId) {
    console.log('addToCart called with itemId:', itemId);
    
    if (isProcessing) {
        console.log('Already processing, please wait');
        return;
    }
    
    const qtyInput = document.getElementById('qty-' + itemId);
    if (!qtyInput) {
        console.error('Quantity input not found for item:', itemId);
        showMessage('Error adding to cart', 'error');
        return;
    }
    
    const quantity = parseInt(qtyInput.value) || 1;
    console.log('Quantity:', quantity);
    
    if (quantity <= 0) {
        showMessage('Invalid quantity', 'error');
        return;
    }
    
    isProcessing = true;
    showMessage('Adding to cart...', 'info');
    
    fetch('customer_inventory.php?action=add_to_cart', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({item_id: itemId, quantity: quantity})
    })
    .then(response => {
        console.log('Response received');
        return response.json();
    })
    .then(data => {
        console.log('Add to cart response:', data);
        if (data.success) {
            cart = data.cart || cart;
            updateCartCount();
            loadCartItems();
            updateCartSummary();
            showMessage('Item added to cart!', 'success');
            qtyInput.value = 1; // Reset quantity input
        } else {
            showMessage(data.message || 'Error adding to cart', 'error');
        }
    })
    .catch(error => {
        console.error('Fetch Error:', error);
        showMessage('Network error. Please check your connection.', 'error');
    })
    .finally(() => {
        isProcessing = false;
    });
}

// Update quantity in product card
function updateQuantity(itemId, change) {
    const qtyInput = document.getElementById('qty-' + itemId);
    if (!qtyInput) {
        console.error('Quantity input not found for item:', itemId);
        return;
    }
    
    let currentQty = parseInt(qtyInput.value) || 1;
    currentQty += change;
    
    // Find product max quantity
    const product = window.products?.find(p => p.id == itemId);
    if (product) {
        if (currentQty < 1) currentQty = 1;
        if (currentQty > product.quantity) currentQty = product.quantity;
        qtyInput.value = currentQty;
    }
}

// Load cart items
function loadCartItems() {
    const cartItems = document.getElementById('cartItems');
    if (!cartItems) {
        console.error('cartItems element not found');
        return;
    }
    
    if (!cart || cart.length === 0) {
        cartItems.innerHTML = `
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <p>Your cart is empty</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    cart.forEach(item => {
        const total = (item.price * item.quantity).toFixed(2);
        html += `
            <div class="cart-item" data-id="${item.id}">
                <div class="cart-item-image">
                    <i class="fas fa-car"></i>
                </div>
                <div class="cart-item-info">
                    <div class="cart-item-name">${item.name}</div>
                    <div class="cart-item-price">৳${parseFloat(item.price).toFixed(2)} × ${item.quantity}</div>
                    <div class="cart-item-total">৳${total}</div>
                </div>
                <div class="cart-item-actions">
                    <input type="number" 
                           class="cart-item-qty" 
                           value="${item.quantity}" 
                           min="1"
                           onchange="updateCartItem(${item.id}, this.value)">
                    <button class="remove-item" onclick="removeFromCart(${item.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    });
    cartItems.innerHTML = html;
}

// Update cart item quantity
function updateCartItem(itemId, quantity) {
    quantity = parseInt(quantity);
    console.log('updateCartItem called:', itemId, quantity);
    
    if (isNaN(quantity) || quantity < 0) {
        showMessage('Invalid quantity', 'error');
        loadCartItems(); // Reload to reset incorrect quantity
        return;
    }
    
    fetch('customer_inventory.php?action=update_cart', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({item_id: itemId, quantity: quantity})
    })
    .then(response => response.json())
    .then(data => {
        console.log('Update cart response:', data);
        if (data.success) {
            cart = data.cart || cart;
            loadCartItems();
            updateCartSummary();
            showMessage('Cart updated', 'success');
        } else {
            showMessage(data.message || 'Error updating cart', 'error');
            loadCartItems(); // Reload to reset incorrect quantity
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error updating cart', 'error');
    });
}

// Remove from cart
function removeFromCart(itemId) {
    console.log('removeFromCart called:', itemId);
    
    if (!confirm('Remove this item from cart?')) return;
    
    fetch('customer_inventory.php?action=remove_from_cart', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({item_id: itemId})
    })
    .then(response => response.json())
    .then(data => {
        console.log('Remove from cart response:', data);
        if (data.success) {
            cart = data.cart || cart;
            updateCartCount();
            loadCartItems();
            updateCartSummary();
            showMessage('Item removed from cart', 'success');
        } else {
            showMessage(data.message || 'Error removing item', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error removing item', 'error');
    });
}

// Update cart count
function updateCartCount() {
    const cartCount = document.getElementById('cartCount');
    if (!cartCount) {
        console.error('cartCount element not found');
        return;
    }
    
    const count = cart.reduce((total, item) => total + (item.quantity || 0), 0);
    cartCount.textContent = count;
    console.log('Cart count updated:', count);
}

// Update cart summary
function updateCartSummary() {
    const subtotal = cart.reduce((total, item) => total + (item.price * (item.quantity || 0)), 0);
    const tierDiscount = (subtotal * (window.customerData?.discountPercentage || 0)) / 100;
    const total = Math.max(0, subtotal - tierDiscount - pointsDiscount);
    
    // Update display elements
    const subtotalEl = document.getElementById('cartSubtotal');
    const tierDiscountEl = document.getElementById('tierDiscount');
    const pointsDiscountEl = document.getElementById('pointsDiscount');
    const totalEl = document.getElementById('cartTotal');
    
    if (subtotalEl) subtotalEl.textContent = `৳${subtotal.toFixed(2)}`;
    if (tierDiscountEl) tierDiscountEl.textContent = `-৳${tierDiscount.toFixed(2)}`;
    if (pointsDiscountEl) pointsDiscountEl.textContent = `-৳${pointsDiscount.toFixed(2)}`;
    if (totalEl) totalEl.textContent = `৳${total.toFixed(2)}`;
    
    console.log('Cart summary updated:', { subtotal, tierDiscount, pointsDiscount, total });
}

// Apply points discount
function applyPoints() {
    const pointsToUseInput = document.getElementById('pointsToUse');
    if (!pointsToUseInput) {
        showMessage('Points input not found', 'error');
        return;
    }
    
    const pointsToUse = parseInt(pointsToUseInput.value);
    
    if (!pointsToUse || pointsToUse < 1) {
        showMessage('Enter valid points', 'error');
        return;
    }
    
    const subtotal = cart.reduce((total, item) => total + (item.price * (item.quantity || 0)), 0);
    const tierDiscount = (subtotal * (window.customerData?.discountPercentage || 0)) / 100;
    const afterTierDiscount = subtotal - tierDiscount;
    
    fetch('customer_inventory.php?action=apply_points_discount', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            points: pointsToUse,
            cart_total: afterTierDiscount
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Apply points response:', data);
        if (data.success) {
            pointsDiscount = data.discount_amount;
            pointsUsed = data.points_used;
            updateCartSummary();
            showMessage(`Applied ${pointsUsed} points for ৳${pointsDiscount.toFixed(2)} discount`, 'success');
            
            // Update customer points display
            const customerPointsEl = document.getElementById('customerPoints');
            if (customerPointsEl && data.remaining_points !== undefined) {
                customerPointsEl.textContent = data.remaining_points;
                window.customerData.points = data.remaining_points;
            }
        } else {
            showMessage(data.message || 'Error applying points', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error applying points', 'error');
    });
}

// Process checkout
function processCheckout() {
    if (isProcessing) return;
    
    if (!cart || cart.length === 0) {
        showMessage('Your cart is empty', 'error');
        return;
    }
    
    if (!confirm('Proceed with checkout?')) return;
    
    isProcessing = true;
    
    const usePointsCheckbox = document.getElementById('usePoints');
    const checkoutData = {
        use_points: usePointsCheckbox?.checked || false,
        points_to_use: pointsUsed
    };
    
    fetch('customer_inventory.php?action=checkout', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(checkoutData)
    })
    .then(response => response.json())
    .then(data => {
        console.log('Checkout response:', data);
        if (data.success) {
            // Update customer points display
            const customerPointsEl = document.getElementById('customerPoints');
            if (customerPointsEl && data.new_points !== undefined) {
                customerPointsEl.textContent = data.new_points;
                window.customerData.points = data.new_points;
            }
            
            // Clear cart
            cart = [];
            updateCartCount();
            loadCartItems();
            updateCartSummary();
            
            // Reset points discount
            pointsDiscount = 0;
            pointsUsed = 0;
            if (usePointsCheckbox) {
                usePointsCheckbox.checked = false;
                const pointsInputGroup = document.getElementById('pointsInputGroup');
                if (pointsInputGroup) {
                    pointsInputGroup.style.display = 'none';
                }
            }
            
            showMessage(
                `Order placed successfully! You earned ${data.points_earned} points. Total discount: ৳${data.total_discount?.toFixed(2) || 0}`,
                'success'
            );
            
            closeCartFunc();
        } else {
            showMessage(data.message || 'Error processing order', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error processing order', 'error');
    })
    .finally(() => {
        isProcessing = false;
    });
}

// Show message
function showMessage(text, type) {
    const message = document.getElementById('message');
    if (!message) {
        console.log('Message would show:', text, type);
        return;
    }
    
    message.textContent = text;
    message.className = `message ${type}`;
    message.style.display = 'block';
    
    setTimeout(() => {
        message.style.display = 'none';
    }, 5000);
}

// Make functions globally available
window.addToCart = addToCart;
window.updateQuantity = updateQuantity;
window.updateCartItem = updateCartItem;
window.removeFromCart = removeFromCart;
window.applyPoints = applyPoints;
window.processCheckout = processCheckout;
window.openCart = openCart;
window.closeCart = closeCartFunc;

console.log('All functions registered globally');