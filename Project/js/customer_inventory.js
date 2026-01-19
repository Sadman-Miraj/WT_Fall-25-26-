// Debug: Check if script is loaded
console.log('customer_inventory.js loaded!');

// Global variables
let cart = window.cart || [];
let pointsDiscount = 0;
let pointsUsed = 0;
let isProcessing = false;