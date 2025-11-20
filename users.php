<?php
require_once 'backend/config/session.php';
requireLogin();

// Only admin can access this page
if (!hasRole('admin')) {
    header('Location: dashboard.php');
    exit();
}

$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HarvestTrack - User Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <h2>HarvestTrack</h2>
            </div>
            
            <ul class="sidebar-menu">
                <li>
                    <a href="dashboard.php">
                        <span class="icon">📊</span>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="harvest-data.php">
                        <span class="icon">🌾</span>
                        Harvest Data
                    </a>
                </li>
                <li>
                    <a href="reports.php">
                        <span class="icon">📈</span>
                        Reports
                    </a>
                </li>
                <li>
                    <a href="feedback.php">
                        <span class="icon">💬</span>
                        Feedback
                    </a>
                </li>
                <li class="active">
                    <a href="users.php">
                        <span class="icon">👥</span>
                        Users
                    </a>
                </li>
                <li>
                    <a href="settings.php">
                        <span class="icon">⚙️</span>
                        Settings
                    </a>
                </li>
            </ul>
            
            <div class="sidebar-footer">
                <div class="user-info">
                    <p><strong><?php echo htmlspecialchars($currentUser['name']); ?></strong></p>
                    <p class="user-role"><?php echo ucfirst($currentUser['role']); ?></p>
                </div>
                <a href="backend/auth.php?action=logout" class="btn btn-secondary btn-sm">Logout</a>
            </div>
        </nav>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <h1>User Management</h1>
                <p>Manage system users and their roles</p>
            </div>
            
            <!-- Add User Form -->
            <div class="card">
                <div class="card-header">
                    <h3>Add New User</h3>
                </div>
                <div class="card-body">
                    <form id="addUserForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Full Name *</label>
                                <input type="text" id="name" name="name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="password">Password *</label>
                                <input type="password" id="password" name="password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="role">Role *</label>
                                <select id="role" name="role" required>
                                    <option value="farmer">Farmer</option>
                                    <option value="officer">Agricultural Officer</option>
                                    <option value="admin">Administrator</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone">
                            </div>
                            
                            <div class="form-group">
                                <label for="location">Location</label>
                                <input type="text" id="location" name="location">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </form>
                    
                    <div id="addUserMessage" class="message" style="display: none;"></div>
                </div>
            </div>
            
            <!-- Users List -->
            <div class="card">
                <div class="card-header">
                    <h3>All Users</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Phone</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody">
                                <tr>
                                    <td colspan="9">Loading users...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Edit User Modal -->
    <div id="editModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit User</h3>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" id="edit_id" name="id">
                    
                    <div class="form-group">
                        <label for="edit_name">Full Name *</label>
                        <input type="text" id="edit_name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_email">Email *</label>
                        <input type="email" id="edit_email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_role">Role *</label>
                        <select id="edit_role" name="role" required>
                            <option value="farmer">Farmer</option>
                            <option value="officer">Agricultural Officer</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_phone">Phone Number</label>
                        <input type="tel" id="edit_phone" name="phone">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_location">Location</label>
                        <input type="text" id="edit_location" name="location">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_status">Status *</label>
                        <select id="edit_status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update User</button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Load users on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadUsers();
        });
        
        // Add User
        document.getElementById('addUserForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            formData.append('action', 'add_user');
            
            const messageDiv = document.getElementById('addUserMessage');
            messageDiv.textContent = 'Adding user...';
            messageDiv.className = 'message';
            messageDiv.style.display = 'block';
            
            fetch('backend/api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageDiv.textContent = data.message;
                    messageDiv.className = 'message success';
                    e.target.reset();
                    
                    setTimeout(() => {
                        messageDiv.style.display = 'none';
                        loadUsers();
                    }, 2000);
                } else {
                    messageDiv.textContent = data.message || 'Failed to add user';
                    messageDiv.className = 'message error';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                messageDiv.textContent = 'An error occurred';
                messageDiv.className = 'message error';
            });
        });
        
        // Load Users
        function loadUsers() {
            fetch('backend/api.php?action=get_users')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('usersTableBody');
                
                if (data.success && data.data.length > 0) {
                    tbody.innerHTML = data.data.map(user => {
                        const date = new Date(user.created_at).toLocaleDateString();
                        const statusClass = user.status === 'active' ? 'success' : 'danger';
                        const roleClass = user.role === 'admin' ? 'danger' : 
                                        user.role === 'officer' ? 'warning' : 'info';
                        
                        return `
                            <tr>
                                <td>${user.id}</td>
                                <td>${user.name}</td>
                                <td>${user.email}</td>
                                <td><span class="badge badge-${roleClass}">${user.role}</span></td>
                                <td>${user.phone || 'N/A'}</td>
                                <td>${user.location || 'N/A'}</td>
                                <td><span class="badge badge-${statusClass}">${user.status}</span></td>
                                <td>${date}</td>
                                <td>
                                    <button onclick='editUser(${JSON.stringify(user)})' class="btn btn-sm btn-primary">Edit</button>
                                    <button onclick="deleteUser(${user.id})" class="btn btn-sm btn-danger">Delete</button>
                                </td>
                            </tr>
                        `;
                    }).join('');
                } else {
                    tbody.innerHTML = '<tr><td colspan="9">No users found</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('usersTableBody').innerHTML = '<tr><td colspan="9">Error loading users</td></tr>';
            });
        }
        
        // Edit User
        function editUser(user) {
            document.getElementById('edit_id').value = user.id;
            document.getElementById('edit_name').value = user.name;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_role').value = user.role;
            document.getElementById('edit_phone').value = user.phone || '';
            document.getElementById('edit_location').value = user.location || '';
            document.getElementById('edit_status').value = user.status;
            
            document.getElementById('editModal').style.display = 'block';
        }
        
        // Close Edit Modal
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        // Update User
        document.getElementById('editUserForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            formData.append('action', 'update_user');
            
            fetch('backend/api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    closeEditModal();
                    loadUsers();
                } else {
                    alert(data.message || 'Failed to update user');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        });
        
        // Delete User
        function deleteUser(userId) {
            if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'delete_user');
            formData.append('id', userId);
            
            fetch('backend/api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    loadUsers();
                } else {
                    alert(data.message || 'Failed to delete user');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target == modal) {
                closeEditModal();
            }
        }
    </script>
    
    <style>
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        
        .modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 0;
            border: 1px solid #888;
            width: 90%;
            max-width: 600px;
            border-radius: 5px;
        }
        
        .modal-header {
            padding: 15px 20px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h3 {
            margin: 0;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover,
        .close:focus {
            color: #000;
        }
        
        .badge {
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 0.85em;
            font-weight: bold;
        }
        
        .badge-success {
            background: #28a745;
            color: white;
        }
        
        .badge-danger {
            background: #dc3545;
            color: white;
        }
        
        .badge-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .badge-info {
            background: #17a2b8;
            color: white;
        }
    </style>
</body>
</html>
