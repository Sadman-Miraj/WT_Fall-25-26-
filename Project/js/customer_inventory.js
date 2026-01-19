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