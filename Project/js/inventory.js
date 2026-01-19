// DOM Elements
const addModal = document.getElementById('addModal');
const editModal = document.getElementById('editModal');
const addForm = document.getElementById('addForm');
const editForm = document.getElementById('editForm');
const message = document.getElementById('message');
const filters = document.querySelectorAll('.filter-btn');
const grid = document.getElementById('inventoryGrid');

// Event Listeners
document.getElementById('openAddModal').addEventListener('click', () => addModal.style.display = 'flex');
document.getElementById('closeAddModal').addEventListener('click', () => addModal.style.display = 'none');
document.getElementById('closeEditModal').addEventListener('click', () => editModal.style.display = 'none');

window.addEventListener('click', (e) => {
    if (e.target === addModal) addModal.style.display = 'none';
    if (e.target === editModal) editModal.style.display = 'none';
});

// Filtering
filters.forEach(btn => {
    btn.addEventListener('click', function() {
        filters.forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        const cat = this.dataset.category;
        document.querySelectorAll('.item-card').forEach(item => {
            item.style.display = (cat === 'all' || item.dataset.category === cat) ? 'block' : 'none';
        });
    });
});

// Add Item
addForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = {
        name: document.getElementById('itemName').value.trim(),
        category: document.getElementById('itemCategory').value,
        price: parseFloat(document.getElementById('itemPrice').value),
        quantity: parseInt(document.getElementById('itemQuantity').value),
        description: document.getElementById('itemDescription').value.trim()
    };
    
    if (!formData.name || !formData.category || formData.price <= 0 || formData.quantity < 0) {
        showMsg('Fill required fields', 'error');
        return;
    }
    
    try {
        const res = await fetch('inventory.php?action=add_item', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(formData)
        });
        
        const result = await res.json();
        
        if (result.success) {
            addItemToGrid({id: result.id, ...formData});
            addForm.reset();
            addModal.style.display = 'none';
            showMsg('Item added', 'success');
        } else {
            showMsg(result.message, 'error');
        }
    } catch (err) {
        showMsg('Error adding item', 'error');
    }
});

// Update Quantity
function updateQuantity(id) {
    const input = document.getElementById(`qty-input-${id}`);
    const qty = parseInt(input.value);
    
    if (isNaN(qty) || qty < 0) {
        showMsg('Invalid quantity', 'error');
        return;
    }
    
    fetch('inventory.php?action=update_item', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id, field: 'quantity', value: qty})
    })
    .then(r => r.json())
    .then(result => {
        if (result.success) {
            document.getElementById(`quantity-${id}`).textContent = qty;
            const item = document.querySelector(`[data-id="${id}"]`);
            const status = item.querySelector('.status');
            if (qty > 0) {
                status.textContent = 'In Stock';
                status.className = 'status in-stock';
            } else {
                status.textContent = 'Out';
                status.className = 'status out-stock';
            }
            showMsg('Quantity updated', 'success');
        } else {
            showMsg(result.message, 'error');
        }
    });
}

// Edit Item
function editItem(id) {
    const item = window.currentItems.find(i => i.id == id);
    if (!item) return;
    
    document.getElementById('editItemId').value = id;
    document.getElementById('editItemName').value = item.name;
    document.getElementById('editItemCategory').value = item.category;
    document.getElementById('editItemPrice').value = item.price;
    document.getElementById('editItemDescription').value = item.description || '';
    editModal.style.display = 'flex';
}

// Save Edit
editForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('editItemId').value;
    
    const updates = [
        {field: 'name', value: document.getElementById('editItemName').value.trim()},
        {field: 'category', value: document.getElementById('editItemCategory').value},
        {field: 'price', value: parseFloat(document.getElementById('editItemPrice').value)},
        {field: 'description', value: document.getElementById('editItemDescription').value.trim()}
    ];
    
    let success = 0;
    for (const update of updates) {
        try {
            const res = await fetch('inventory.php?action=update_item', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id, field: update.field, value: update.value})
            });
            const result = await res.json();
            if (result.success) success++;
        } catch (err) {}
    }
    
    if (success === updates.length) {
        const item = document.querySelector(`[data-id="${id}"]`);
        const name = item.querySelector('.item-name');
        const cat = item.querySelector('.item-category');
        const price = item.querySelector('.detail-value');
        
        name.textContent = updates[0].value;
        cat.textContent = updates[1].value.charAt(0).toUpperCase() + updates[1].value.slice(1);
        price.textContent = '$' + parseFloat(updates[2].value).toFixed(2);
        item.dataset.category = updates[1].value;
        
        editModal.style.display = 'none';
        showMsg('Item updated', 'success');
    } else {
        showMsg('Error updating', 'error');
    }
});

// Delete Item
function deleteItem(id) {
    if (!confirm('Delete this item?')) return;
    
    fetch('inventory.php?action=delete_item', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id})
    })
    .then(r => r.json())
    .then(result => {
        if (result.success) {
            const item = document.querySelector(`[data-id="${id}"]`);
            item.style.opacity = '0';
            item.style.transform = 'scale(0.8)';
            
            setTimeout(() => {
                item.remove();
                if (!document.querySelectorAll('.item-card').length) {
                    grid.innerHTML = '<p>No items. Click "Add Item" to start!</p>';
                }
            }, 300);
            
            showMsg('Item deleted', 'success');
        } else {
            showMsg(result.message, 'error');
        }
    });
}

// Helper Functions
function addItemToGrid(item) {
    const noItems = document.querySelector('.no-items');
    if (noItems) noItems.remove();
    
    const card = document.createElement('div');
    card.className = 'item-card';
    card.dataset.category = item.category;
    card.dataset.id = item.id;
    
    card.innerHTML = `
        <div class="item-header">
            <div>
                <div class="item-name">${item.name}</div>
                <span>${item.category.charAt(0).toUpperCase() + item.category.slice(1)}</span>
            </div>
            <span class="status ${item.quantity > 0 ? 'in-stock' : 'out-stock'}">
                ${item.quantity > 0 ? 'In Stock' : 'Out'}
            </span>
        </div>
        <div class="detail-row">
            <span>Price:</span>
            <span>$${parseFloat(item.price).toFixed(2)}</span>
        </div>
        <div class="detail-row">
            <span>Quantity:</span>
            <span id="quantity-${item.id}">${item.quantity}</span>
        </div>
        <div class="quantity-control">
            <input type="number" class="quantity-input" id="qty-input-${item.id}" value="${item.quantity}" min="0">
            <button class="update-qty-btn" onclick="updateQuantity(${item.id})">Update</button>
        </div>
        <div class="item-actions">
            <button class="edit-btn" onclick="editItem(${item.id})">Edit</button>
            <button class="delete-btn" onclick="deleteItem(${item.id})">Delete</button>
        </div>
    `;
    
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    grid.prepend(card);
    
    setTimeout(() => {
        card.style.transition = 'all 0.3s';
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
    }, 10);
    
    window.currentItems.push(item);
}

function showMsg(text, type) {
    message.textContent = text;
    message.className = `message ${type}`;
    message.style.display = 'block';
    
    setTimeout(() => {
        message.style.display = 'none';
    }, 3000);
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.item-card').forEach((card, i) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, i * 100);
    });
});