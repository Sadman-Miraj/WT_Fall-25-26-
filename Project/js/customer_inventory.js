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