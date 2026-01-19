const messageDiv = document.getElementById('message');
const addModal = document.getElementById('addModal');
const workModal = document.getElementById('workModal');
const addForm = document.getElementById('addForm');
const workForm = document.getElementById('workForm');

// Open Add Modal
function openAddModal() {
    addForm.reset();
    addModal.style.display = 'flex';
}

// Close Add Modal
function closeAddModal() {
    addModal.style.display = 'none';
}

// Open Work Modal
function openWorkModal(employeeId) {
    document.getElementById('workEmpId').value = employeeId;
    document.getElementById('workDone').value = '';
    workModal.style.display = 'flex';
}

// Close Work Modal
function closeWorkModal() {
    workModal.style.display = 'none';
}

// Close modals when clicking outside
window.addEventListener('click', (e) => {
    if (e.target === addModal) closeAddModal();
    if (e.target === workModal) closeWorkModal();
});

// Add Employee
addForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = {
        name: document.getElementById('empName').value.trim(),
        email: document.getElementById('empEmail').value.trim(),
        mobile: document.getElementById('empMobile').value.trim(),
        post: document.getElementById('empPost').value,
        address: document.getElementById('empAddress').value.trim(),
        salary: parseFloat(document.getElementById('empSalary').value) || 0,
        hired_date: document.getElementById('empHiredDate').value || new Date().toISOString().split('T')[0]
    };
    
    // Validation
    if (!formData.name || !formData.email || !formData.mobile || !formData.post || !formData.address) {
        showMessage('All required fields must be filled', 'error');
        return;
    }
    
    try {
        const response = await fetch('employee.php?action=add_employee', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Add new employee to table
            addEmployeeToTable({
                id: result.id,
                ...formData,
                reports: 0,
                work_done: null
            });
            
            // Update total count
            const totalEmployeesSpan = document.getElementById('totalEmployees');
            totalEmployeesSpan.textContent = parseInt(totalEmployeesSpan.textContent) + 1;
            
            // Close modal and show message
            closeAddModal();
            showMessage('Employee added successfully!', 'success');
        } else {
            showMessage(result.message || 'Error adding employee', 'error');
        }
    } catch (error) {
        console.error(error);
        showMessage('Error adding employee', 'error');
    }
});

// Delete Employee
async function deleteEmployee(employeeId) {
    if (!confirm('Are you sure you want to delete this employee?')) {
        return;
    }
    
    try {
        const response = await fetch('employee.php?action=delete_employee', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: employeeId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Remove employee from table
            const employeeRow = document.querySelector(`tr[data-id="${employeeId}"]`);
            if (employeeRow) {
                employeeRow.style.opacity = '0';
                employeeRow.style.transform = 'translateX(-20px)';
                
                setTimeout(() => {
                    employeeRow.remove();
                    
                    // Update total count
                    const totalEmployeesSpan = document.getElementById('totalEmployees');
                    totalEmployeesSpan.textContent = parseInt(totalEmployeesSpan.textContent) - 1;
                    
                    // Show message if no employees left
                    const remainingRows = document.querySelectorAll('#employeesBody tr');
                    if (remainingRows.length === 1 && remainingRows[0].querySelector('.no-employees')) {
                        document.querySelector('#employeesBody').innerHTML = 
                            '<tr><td colspan="9" class="no-employees">No employees found</td></tr>';
                    }
                }, 300);
            }
            
            showMessage('Employee deleted successfully!', 'success');
        } else {
            showMessage(result.message || 'Error deleting employee', 'error');
        }
    } catch (error) {
        console.error(error);
        showMessage('Error deleting employee', 'error');
    }
}

// Update Work Done
workForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const employeeId = document.getElementById('workEmpId').value;
    const workDone = document.getElementById('workDone').value.trim();
    
    if (!workDone) {
        showMessage('Please enter work description', 'error');
        return;
    }
    
    try {
        const response = await fetch('employee.php?action=update_work', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: employeeId, work_done: workDone })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Update work in table
            const employeeRow = document.querySelector(`tr[data-id="${employeeId}"]`);
            if (employeeRow) {
                const workDoneDiv = employeeRow.querySelector('.work-done');
                workDoneDiv.textContent = workDone;
            }
            
            // Close modal and show message
            closeWorkModal();
            showMessage('Work updated successfully!', 'success');
        } else {
            showMessage(result.message || 'Error updating work', 'error');
        }
    } catch (error) {
        console.error(error);
        showMessage('Error updating work', 'error');
    }
});

// Add Report
async function addReport(employeeId) {
    try {
        const response = await fetch('employee.php?action=add_report', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: employeeId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Update report count in table
            const reportCount = document.getElementById(`reports-${employeeId}`);
            if (reportCount) {
                reportCount.textContent = result.reports;
                reportCount.style.transform = 'scale(1.2)';
                setTimeout(() => {
                    reportCount.style.transform = 'scale(1)';
                }, 300);
            }
            showMessage('Report added successfully!', 'success');
        } else {
            showMessage(result.message || 'Error adding report', 'error');
        }
    } catch (error) {
        console.error(error);
        showMessage('Error adding report', 'error');
    }
}

// Add employee to table
function addEmployeeToTable(employee) {
    // Remove no-employees message if present
    const noEmployees = document.querySelector('.no-employees');
    if (noEmployees) noEmployees.closest('tr').remove();
    
    const tableBody = document.getElementById('employeesBody');
    const newRow = document.createElement('tr');
    newRow.dataset.id = employee.id;
    
    const hiredDate = employee.hired_date || new Date().toISOString().split('T')[0];
    
    newRow.innerHTML = `
        <td>#${employee.id.toString().padStart(4, '0')}</td>
        <td>
            <div class="employee-name">${escapeHtml(employee.name)}</div>
            <div class="employee-post">${escapeHtml(employee.post)}</div>
        </td>
        <td>
            <div class="employee-email">${escapeHtml(employee.email)}</div>
        </td>
        <td class="employee-mobile">${escapeHtml(employee.mobile)}</td>
        <td class="employee-post-td">${escapeHtml(employee.post)}</td>
        <td class="employee-salary">$${parseFloat(employee.salary).toFixed(2)}</td>
        <td class="employee-reports">
            <div class="report-count" id="reports-${employee.id}">0</div>
            <button class="report-btn" onclick="addReport(${employee.id})">+</button>
        </td>
        <td>
            <div class="work-done">No work recorded</div>
            <button class="work-btn" onclick="openWorkModal(${employee.id})">Update</button>
        </td>
        <td>
            <button class="delete-btn" onclick="deleteEmployee(${employee.id})">Delete</button>
        </td>
    `;
    
    // Add animation
    newRow.style.opacity = '0';
    newRow.style.transform = 'translateY(20px)';
    tableBody.prepend(newRow);
    
    setTimeout(() => {
        newRow.style.transition = 'all 0.3s';
        newRow.style.opacity = '1';
        newRow.style.transform = 'translateY(0)';
    }, 10);
}

// Show message
function showMessage(text, type) {
    messageDiv.textContent = text;
    messageDiv.className = `message ${type}`;
    messageDiv.style.display = 'block';
    
    setTimeout(() => {
        messageDiv.style.display = 'none';
    }, 3000);
}

// Escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Keyboard shortcuts
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeAddModal();
        closeWorkModal();
    }
});
