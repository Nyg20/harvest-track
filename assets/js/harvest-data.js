// Harvest Data Management JavaScript
let currentEditId = null;

document.addEventListener('DOMContentLoaded', function() {
    loadHarvestData();
    
    const harvestForm = document.getElementById('harvestForm');
    if (harvestForm) {
        harvestForm.addEventListener('submit', handleHarvestSubmit);
    }
});

function loadHarvestData() {
    fetch('backend/api.php?action=get_harvests')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayHarvestData(data.data);
            } else {
                console.error('Failed to load harvest data:', data.message);
            }
        })
        .catch(error => {
            console.error('Error loading harvest data:', error);
        });
}

function displayHarvestData(harvests) {
    const tableBody = document.getElementById('harvestTableBody');
    
    if (!harvests || harvests.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center">No harvest records found</td>
            </tr>
        `;
        return;
    }
    
    // Check if farmer column should be shown (based on table header)
    const headers = Array.from(document.querySelectorAll('th'));
    const showFarmerColumn = headers.some(th => th.textContent.trim() === 'Farmer');
    
    const tableHTML = harvests.map(harvest => {
        const date = new Date(harvest.harvest_date).toLocaleDateString();
        const farmerColumn = (showFarmerColumn && harvest.farmer_name) ? `<td>${harvest.farmer_name}</td>` : '';
        
        return `
            <tr>
                <td>${date}</td>
                <td>${harvest.crop_type}</td>
                <td>${harvest.quantity} ${harvest.unit}</td>
                <td>${harvest.location || '-'}</td>
                <td>${harvest.farm_name || '-'}</td>
                <td>${harvest.season || '-'}</td>
                ${farmerColumn}
                <td>
                    <button class="action-btn btn-edit" onclick="editHarvest(${harvest.id})">Edit</button>
                    <button class="action-btn btn-delete" onclick="deleteHarvest(${harvest.id})">Delete</button>
                </td>
            </tr>
        `;
    }).join('');
    
    tableBody.innerHTML = tableHTML;
}

function showAddForm() {
    currentEditId = null;
    document.getElementById('modalTitle').textContent = 'Add New Harvest Record';
    document.getElementById('harvestForm').reset();
    document.getElementById('harvestId').value = '';
    document.getElementById('harvestModal').style.display = 'block';
}

function editHarvest(id) {
    // Find the harvest data from the current table
    fetch(`backend/api.php?action=get_harvests`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const harvest = data.data.find(h => h.id == id);
                if (harvest) {
                    populateEditForm(harvest);
                }
            }
        })
        .catch(error => {
            console.error('Error loading harvest for edit:', error);
        });
}

function populateEditForm(harvest) {
    currentEditId = harvest.id;
    document.getElementById('modalTitle').textContent = 'Edit Harvest Record';
    document.getElementById('harvestId').value = harvest.id;
    document.getElementById('crop_type').value = harvest.crop_type;
    document.getElementById('quantity').value = harvest.quantity;
    document.getElementById('unit').value = harvest.unit;
    document.getElementById('harvest_date').value = harvest.harvest_date;
    document.getElementById('location').value = harvest.location || '';
    document.getElementById('farm_name').value = harvest.farm_name || '';
    document.getElementById('season').value = harvest.season || '';
    document.getElementById('notes').value = harvest.notes || '';
    
    document.getElementById('harvestModal').style.display = 'block';
}

function deleteHarvest(id) {
    if (confirm('Are you sure you want to delete this harvest record?')) {
        const formData = new FormData();
        formData.append('action', 'delete_harvest');
        formData.append('id', id);
        
        fetch('backend/api.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadHarvestData(); // Reload the table
            } else {
                alert('Failed to delete harvest record: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error deleting harvest:', error);
            alert('An error occurred while deleting the record.');
        });
    }
}

function handleHarvestSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const action = currentEditId ? 'update_harvest' : 'add_harvest';
    formData.append('action', action);
    
    if (currentEditId) {
        formData.append('id', currentEditId);
    }
    
    fetch('backend/api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal();
            loadHarvestData(); // Reload the table
        } else {
            alert('Failed to save harvest record: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error saving harvest:', error);
        alert('An error occurred while saving the record.');
    });
}

function closeModal() {
    document.getElementById('harvestModal').style.display = 'none';
    currentEditId = null;
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('harvestModal');
    if (event.target === modal) {
        closeModal();
    }
}
