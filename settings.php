<?php
require_once 'backend/config/session.php';
requireLogin();

$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HarvestTrack - Settings</title>
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
                <?php if ($currentUser['role'] === 'admin'): ?>
                <li>
                    <a href="users.php">
                        <span class="icon">👥</span>
                        Users
                    </a>
                </li>
                <?php endif; ?>
                <li class="active">
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
                <h1>Settings</h1>
                <p>Manage your account preferences</p>
            </div>
            
            <!-- Profile Settings -->
            <div class="card">
                <div class="card-header">
                    <h3>Profile Information</h3>
                </div>
                <div class="card-body">
                    <div class="settings-info">
                        <div class="info-row">
                            <label>Name:</label>
                            <span><?php echo htmlspecialchars($currentUser['name']); ?></span>
                        </div>
                        <div class="info-row">
                            <label>Email:</label>
                            <span><?php echo htmlspecialchars($currentUser['email']); ?></span>
                        </div>
                        <div class="info-row">
                            <label>Role:</label>
                            <span class="badge badge-info"><?php echo ucfirst($currentUser['role']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Notification Settings -->
            <div class="card">
                <div class="card-header">
                    <h3>Notification Preferences</h3>
                </div>
                <div class="card-body">
                    <form id="settingsForm">
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="notifications_enabled" name="notifications_enabled" value="1">
                                <span>Enable in-app notifications</span>
                            </label>
                            <p class="form-help">Receive notifications about harvest updates and system alerts</p>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="email_notifications" name="email_notifications" value="1">
                                <span>Enable email notifications</span>
                            </label>
                            <p class="form-help">Receive important updates via email</p>
                        </div>
                        
                        <div class="form-group">
                            <label for="theme">Theme</label>
                            <select id="theme" name="theme">
                                <option value="light">Light</option>
                                <option value="dark">Dark</option>
                                <option value="auto">Auto (System)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="language">Language</label>
                            <select id="language" name="language">
                                <option value="en">English</option>
                                <option value="sw">Swahili</option>
                                <option value="fr">French</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </form>
                    
                    <div id="settingsMessage" class="message" style="display: none;"></div>
                </div>
            </div>
            
            <!-- Role-Specific Information -->
            <div class="card">
                <div class="card-header">
                    <h3>Role Information</h3>
                </div>
                <div class="card-body">
                    <?php if ($currentUser['role'] === 'admin'): ?>
                    <div class="role-info">
                        <h4>Administrator Privileges</h4>
                        <ul>
                            <li>Full access to all system features</li>
                            <li>Manage users (create, update, delete)</li>
                            <li>View and manage all harvest records</li>
                            <li>Generate comprehensive reports</li>
                            <li>Review and respond to feedback</li>
                            <li>Configure system settings</li>
                        </ul>
                    </div>
                    <?php elseif ($currentUser['role'] === 'officer'): ?>
                    <div class="role-info">
                        <h4>Agricultural Officer Privileges</h4>
                        <ul>
                            <li>View all harvest records</li>
                            <li>Generate reports and analytics</li>
                            <li>Monitor farmer performance</li>
                            <li>Submit feedback and suggestions</li>
                            <li>Access seasonal analysis data</li>
                        </ul>
                    </div>
                    <?php else: ?>
                    <div class="role-info">
                        <h4>Farmer Privileges</h4>
                        <ul>
                            <li>Manage your own harvest records</li>
                            <li>View your harvest history</li>
                            <li>Track your performance</li>
                            <li>Submit feedback</li>
                            <li>Receive notifications</li>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Security Settings -->
            <div class="card">
                <div class="card-header">
                    <h3>Security</h3>
                </div>
                <div class="card-body">
                    <div class="security-actions">
                        <button onclick="changePassword()" class="btn btn-secondary">Change Password</button>
                        <button onclick="confirmLogout()" class="btn btn-danger">Logout from All Devices</button>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Load settings on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadSettings();
        });
        
        // Load Settings
        function loadSettings() {
            fetch('backend/api.php?action=get_settings')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const settings = data.data;
                    
                    // Set checkbox values
                    document.getElementById('notifications_enabled').checked = settings.notifications_enabled == 1 || settings.notifications_enabled === true;
                    document.getElementById('email_notifications').checked = settings.email_notifications == 1 || settings.email_notifications === true;
                    
                    // Set select values
                    document.getElementById('theme').value = settings.theme || 'light';
                    document.getElementById('language').value = settings.language || 'en';
                }
            })
            .catch(error => {
                console.error('Error loading settings:', error);
            });
        }
        
        // Save Settings
        document.getElementById('settingsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('action', 'update_settings');
            formData.append('notifications_enabled', document.getElementById('notifications_enabled').checked ? 1 : 0);
            formData.append('email_notifications', document.getElementById('email_notifications').checked ? 1 : 0);
            formData.append('theme', document.getElementById('theme').value);
            formData.append('language', document.getElementById('language').value);
            
            const messageDiv = document.getElementById('settingsMessage');
            messageDiv.textContent = 'Saving settings...';
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
                    
                    setTimeout(() => {
                        messageDiv.style.display = 'none';
                    }, 3000);
                } else {
                    messageDiv.textContent = data.message || 'Failed to save settings';
                    messageDiv.className = 'message error';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                messageDiv.textContent = 'An error occurred';
                messageDiv.className = 'message error';
            });
        });
        
        // Change Password (placeholder)
        function changePassword() {
            alert('Password change functionality will be implemented. For now, please contact your administrator.');
        }
        
        // Confirm Logout
        function confirmLogout() {
            if (confirm('Are you sure you want to logout from all devices?')) {
                window.location.href = 'backend/auth.php?action=logout';
            }
        }
    </script>
    
    <style>
        .settings-info {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .info-row {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .info-row label {
            font-weight: bold;
            width: 150px;
            margin: 0;
        }
        
        .info-row span {
            flex: 1;
        }
        
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }
        
        .checkbox-label input[type="checkbox"] {
            width: auto;
            margin: 0;
        }
        
        .form-help {
            margin: 5px 0 0 30px;
            font-size: 0.9em;
            color: #666;
        }
        
        .role-info {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .role-info h4 {
            margin-top: 0;
            color: #2c3e50;
        }
        
        .role-info ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        .role-info li {
            margin: 8px 0;
            color: #555;
        }
        
        .security-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .badge {
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 0.9em;
            font-weight: bold;
        }
        
        .badge-info {
            background: #3498db;
            color: white;
        }
    </style>
</body>
</html>
