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