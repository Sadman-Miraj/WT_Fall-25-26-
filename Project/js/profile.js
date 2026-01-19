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