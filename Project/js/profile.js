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