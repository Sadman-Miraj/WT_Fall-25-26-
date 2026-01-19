document.addEventListener('DOMContentLoaded', function() {
    const signupForm = document.getElementById('signupForm');
    const nameInput = document.getElementById('name');
    const ageInput = document.getElementById('age');
    const emailInput = document.getElementById('email');
    const addressInput = document.getElementById('address');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    
    // Create password strength indicator
    createPasswordStrengthIndicator();
    
    // Clear error on input
    const allInputs = [nameInput, ageInput, emailInput, addressInput, passwordInput, confirmPasswordInput];
    allInputs.forEach(input => {
        input.addEventListener('input', function() {
            clearFieldError(this);
            if (this === passwordInput) {
                updatePasswordStrength(this.value);
            }
        });
    });
    
    // Real-time password match check
    confirmPasswordInput.addEventListener('input', function() {
        const password = passwordInput.value;
        const confirmPassword = this.value;
        
        if (confirmPassword && password !== confirmPassword) {
            showError(this, "Passwords do not match!");
        } else {
            clearFieldError(this);
        }
    });
    
    signupForm.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Clear previous errors
        clearAllErrors();
        
        // Validate name
        const name = nameInput.value.trim();
        if (!name) {
            showError(nameInput, "Name is required!");
            isValid = false;
        }
        
        // Validate age
        const age = parseInt(ageInput.value);
        if (!ageInput.value) {
            showError(ageInput, "Age is required!");
            isValid = false;
        } else if (isNaN(age) || age < 18 || age > 100) {
            showError(ageInput, "Age must be between 18 and 100!");
            isValid = false;
        }
        
        // Validate email
        const email = emailInput.value.trim();
        if (!email) {
            showError(emailInput, "Email is required!");
            isValid = false;
        } else if (!validateEmail(email)) {
            showError(emailInput, "Please enter a valid email address!");
            isValid = false;
        }
        
        // Validate address
        const address = addressInput.value.trim();
        if (!address) {
            showError(addressInput, "Address is required!");
            isValid = false;
        } else if (address.length < 10) {
            showError(addressInput, "Address should be more detailed!");
            isValid = false;
        }
        
        // Validate password
        const password = passwordInput.value;
        if (!password) {
            showError(passwordInput, "Password is required!");
            isValid = false;
        } else if (password.length < 6) {
            showError(passwordInput, "Password must be at least 6 characters!");
            isValid = false;
        }
        
        // Validate confirm password
        const confirmPassword = confirmPasswordInput.value;
        if (!confirmPassword) {
            showError(confirmPasswordInput, "Please confirm your password!");
            isValid = false;
        } else if (password !== confirmPassword) {
            showError(confirmPasswordInput, "Passwords do not match!");
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    // Email validation function
    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // Create password strength indicator
    function createPasswordStrengthIndicator() {
        const passwordGroup = passwordInput.parentElement;
        const strengthContainer = document.createElement('div');
        strengthContainer.className = 'password-strength';
        
        const strengthMeter = document.createElement('div');
        strengthMeter.className = 'strength-meter';
        
        const strengthText = document.createElement('div');
        strengthText.className = 'strength-text';
        
        strengthContainer.appendChild(strengthMeter);
        passwordGroup.appendChild(strengthContainer);
        passwordGroup.appendChild(strengthText);
    }
    
    // Update password strength indicator
    function updatePasswordStrength(password) {
        const strengthMeter = document.querySelector('.strength-meter');
        const strengthText = document.querySelector('.strength-text');
        
        let strength = 0;
        let text = '';
        let color = '';
        
        if (password.length >= 6) strength += 25;
        if (/[A-Z]/.test(password)) strength += 25;
        if (/[0-9]/.test(password)) strength += 25;
        if (/[^A-Za-z0-9]/.test(password)) strength += 25;
        
        if (strength === 0) {
            text = '';
            color = 'transparent';
        } else if (strength <= 25) {
            text = 'Weak';
            color = '#f5576c';
        } else if (strength <= 50) {
            text = 'Fair';
            color = '#f093fb';
        } else if (strength <= 75) {
            text = 'Good';
            color = '#4facfe';
        } else {
            text = 'Strong';
            color = '#2af598';
        }
        
        strengthMeter.style.width = strength + '%';
        strengthMeter.style.background = color;
        strengthText.textContent = text;
        strengthText.style.color = color;
    }
    
    // Show error for specific field
    function showError(inputElement, message) {
        clearFieldError(inputElement);
        
        inputElement.classList.add('error');
        
        const errorElement = document.createElement('div');
        errorElement.className = 'field-error';
        errorElement.textContent = message;
        
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
    
    // Clear all errors
    function clearAllErrors() {
        document.querySelectorAll('.field-error').forEach(function(error) {
            error.remove();
        });
        document.querySelectorAll('.error').forEach(function(input) {
            input.classList.remove('error');
        });
    }
});