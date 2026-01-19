const messageDiv = document.getElementById('message');
const editModal = document.getElementById('editModal');
const editForm = document.getElementById('editForm');

function openEditModal() {
    loadProfileData();
    editModal.style.display = 'flex';
}

function closeEditModal() {
    editModal.style.display = 'none';
}
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
editForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = {
        name: document.getElementById('editName').value.trim(),
        age: parseInt(document.getElementById('editAge').value),
        address: document.getElementById('editAddress').value.trim()
    };
    
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
            updateProfileDisplay(formData);
            closeEditModal();
            showMessage('Profile updated successfully!', 'success');
            updateHeaderName(formData.name);
        } else {
            showMessage(result.message || 'Error updating profile', 'error');
        }
    } catch (error) {
        console.error(error);
        showMessage('Error updating profile', 'error');
    }
});
function updateProfileDisplay(data) {
    const profileName = document.getElementById('profileName');
    profileName.textContent = data.name;
    
    const profileAvatar = document.getElementById('profileAvatar');
    profileAvatar.textContent = data.name.charAt(0).toUpperCase();
    
    const profileAge = document.getElementById('profileAge');
    profileAge.textContent = `${data.age} years`;
    
    const profileAddress = document.getElementById('profileAddress');
    profileAddress.textContent = data.address;
}

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

function showMessage(text, type) {
    messageDiv.textContent = text;
    messageDiv.className = `message ${type}`;
    messageDiv.style.display = 'block';
    
    setTimeout(() => {
        messageDiv.style.display = 'none';
    }, 3000);
}

window.addEventListener('click', (e) => {
    if (e.target === editModal) {
        closeEditModal();
    }
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && editModal.style.display === 'flex') {
        closeEditModal();
    }
});

document.addEventListener('DOMContentLoaded', () => {
});