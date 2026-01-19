document.addEventListener('DOMContentLoaded', function() {
    const forgotForm = document.getElementById('forgotForm');
    const emailInput = document.getElementById('email');
    
    emailInput.addEventListener('input', function() {
        clearFieldError(this);
    });
    
    forgotForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        let isValid = true;
        
        // Clear previous error
        clearFieldError(emailInput);
        
        // Validate email
        const email = emailInput.value.trim();
        if (!email) {
            showError(emailInput, "Email is required!");
            isValid = false;
        } else if (!validateEmail(email)) {
            showError(emailInput, "Please enter a valid email address!");
            isValid = false;
        }
        
        // If validation passes, submit the form
        if (isValid) {
            this.submit();
        }
    });
    
    // Email validation function
    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // Show error for specific field
    function showError(inputElement, message) {
        clearFieldError(inputElement);
        
        inputElement.classList.add('error');
        
        const errorElement = document.createElement('div');
        errorElement.className = 'field-error';
        errorElement.textContent = message;
        errorElement.style.color = '#721c24';
        errorElement.style.fontSize = '14px';
        errorElement.style.marginTop = '5px';
        
        inputElement.parentNode.appendChild(errorElement);
        inputElement.focus();
    }
    
    // Clear error for specific field
    function clearFieldError(inputElement) {
        inputElement.classList.remove('error');
        const errorElement = inputElement.parentNode.querySelector('.field-error');
        if (errorElement) {
            errorElement.remove();
        }
    }
});