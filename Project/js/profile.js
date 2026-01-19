// DOM Elements
const messageDiv = document.getElementById('message');
const editModal = document.getElementById('editModal');
const editForm = document.getElementById('editForm');

// Open Edit Modal
function openEditModal() {
    // Load current profile data
    loadProfileData();
    editModal.style.display = 'flex';
}

// Close Edit Modal
function closeEditModal() {
    editModal.style.display = 'none';
}

// Load profile data into edit form
async function loadProfileData() {
    try {
        const response = await fetch('profile.php?action=get_profile');
        const result = await response.json();
        
        if (result.success) {
            const user = result.user;
            document.getElementById('editName').value = user.name;
            document.getElementById('editAge').value = user.age;
            document.getElementById('editAddress').value = user.address;
        } else {
            showMessage('Error loading profile data', 'error');
        }
    } catch (error) {
        console.error(error);
        showMessage('Error loading profile data', 'error');
    }
}

// Update Profile
editForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = {
        name: document.getElementById('editName').value.trim(),
        age: parseInt(document.getElementById('editAge').value),
        address: document.getElementById('editAddress').value.trim()
    };
    
    // Validation
    if (!formData.name) {
        showMessage('Name is required', 'error');
        return;
    }
    
    if (formData.age < 18 || formData.age > 100 || isNaN(formData.age)) {
        showMessage('Age must be between 18 and 100', 'error');
        return;
    }
    
    if (!formData.address) {
        showMessage('Address is required', 'error');
        return;
    }
    
    try {
        const response = await fetch('profile.php?action=update_profile', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Update profile display
            updateProfileDisplay(formData);
            
            // Close modal and show success message
            closeEditModal();
            showMessage('Profile updated successfully!', 'success');
            
            // Update session name in header (if on index page)
            updateHeaderName(formData.name);
        } else {
            showMessage(result.message || 'Error updating profile', 'error');
        }
    } catch (error) {
        console.error(error);
        showMessage('Error updating profile', 'error');
    }
});

// Update profile display with new data
function updateProfileDisplay(data) {
    // Update name
    const profileName = document.getElementById('profileName');
    profileName.textContent = data.name;
    
    // Update avatar initial
    const profileAvatar = document.getElementById('profileAvatar');
    profileAvatar.textContent = data.name.charAt(0).toUpperCase();
    
    // Update age
    const profileAge = document.getElementById('profileAge');
    profileAge.textContent = `${data.age} years`;
    
    // Update address
    const profileAddress = document.getElementById('profileAddress');
    profileAddress.textContent = data.address;
}

// Update header name on index page
function updateHeaderName(newName) {
    const userNameElements = document.querySelectorAll('.user-name');
    userNameElements.forEach(element => {
        element.textContent = newName;
    });
    
    const userInitialElements = document.querySelectorAll('.user-icon');
    userInitialElements.forEach(element => {
        element.textContent = newName.charAt(0).toUpperCase();
    });
}

// Show message
function showMessage(text, type) {
    messageDiv.textContent = text;
    messageDiv.className = `message ${type}`;
    messageDiv.style.display = 'block';
    
    // Auto hide after 3 seconds
    setTimeout(() => {
        messageDiv.style.display = 'none';
    }, 3000);
}

// Close modal when clicking outside
window.addEventListener('click', (e) => {
    if (e.target === editModal) {
        closeEditModal();
    }
});

// Keyboard shortcuts
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && editModal.style.display === 'flex') {
        closeEditModal();
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    // Add any initialization code here
});