// Delete customer
async function deleteCustomer(customerId) {
    if (!confirm('Are you sure you want to delete this customer? This action cannot be undone.')) {
        return;
    }
    
    try {
        const response = await fetch('customer.php?action=delete_customer', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: customerId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Remove customer from table
            const customerRow = document.querySelector(`tr[data-id="${customerId}"]`);
            if (customerRow) {
                customerRow.style.opacity = '0';
                customerRow.style.transform = 'translateX(-20px)';
                
                setTimeout(() => {
                    customerRow.remove();
                    
                    // Update total count
                    const totalCustomersSpan = document.getElementById('totalCustomers');
                    totalCustomersSpan.textContent = parseInt(totalCustomersSpan.textContent) - 1;
                    
                    // Show message if no customers left
                    const remainingRows = document.querySelectorAll('#customersBody tr');
                    if (remainingRows.length === 1 && remainingRows[0].querySelector('.no-customers')) {
                        document.querySelector('#customersBody').innerHTML = 
                            '<tr><td colspan="7" class="no-customers">No customers found</td></tr>';
                    }
                }, 300);
            }
            
            showMessage('Customer deleted successfully!', 'success');
        } else {
            showMessage(result.message || 'Error deleting customer', 'error');
        }
    } catch (error) {
        console.error(error);
        showMessage('Error deleting customer', 'error');
    }
}

// Show message
function showMessage(text, type) {
    // Create message div if it doesn't exist
    let messageDiv = document.getElementById('message');
    if (!messageDiv) {
        messageDiv = document.createElement('div');
        messageDiv.id = 'message';
        messageDiv.className = 'message';
        document.querySelector('.container').insertBefore(messageDiv, document.querySelector('.customers-table-container'));
    }
    
    messageDiv.textContent = text;
    messageDiv.className = `message ${type === 'success' ? 'success' : 'error'}`;
    messageDiv.style.display = 'block';
    
    
    setTimeout(() => {
        messageDiv.style.display = 'none';
    }, 3000);
}


function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;

}