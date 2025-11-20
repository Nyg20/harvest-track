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
    <title>HarvestTrack - Feedback</title>
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
                <li class="active">
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
                <h1>Feedback</h1>
                <p>Submit feedback or view submitted feedback</p>
            </div>
            
            <!-- Submit Feedback Form -->
            <div class="card">
                <div class="card-header">
                    <h3>Submit Feedback</h3>
                </div>
                <div class="card-body">
                    <form id="feedbackForm">
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" placeholder="Brief description of your feedback">
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message *</label>
                            <textarea id="message" name="message" rows="5" required placeholder="Provide detailed feedback, suggestions, or report issues..."></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Submit Feedback</button>
                    </form>
                    
                    <div id="feedbackMessage" class="message" style="display: none;"></div>
                </div>
            </div>
            
            <!-- Feedback List -->
            <div class="card">
                <div class="card-header">
                    <h3><?php echo $currentUser['role'] === 'admin' ? 'All Feedback' : 'My Feedback'; ?></h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <?php if ($currentUser['role'] === 'admin'): ?>
                                    <th>User</th>
                                    <?php endif; ?>
                                    <th>Subject</th>
                                    <th>Message</th>
                                    <th>Status</th>
                                    <th>Submitted</th>
                                    <?php if ($currentUser['role'] === 'admin'): ?>
                                    <th>Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody id="feedbackTableBody">
                                <tr>
                                    <td colspan="<?php echo $currentUser['role'] === 'admin' ? '7' : '5'; ?>">Loading feedback...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Update Status Modal (Admin Only) -->
    <?php if ($currentUser['role'] === 'admin'): ?>
    <div id="statusModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Update Feedback Status</h3>
                <span class="close" onclick="closeStatusModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="updateStatusForm">
                    <input type="hidden" id="feedback_id" name="id">
                    
                    <div class="form-group">
                        <label for="status">Status *</label>
                        <select id="status" name="status" required>
                            <option value="pending">Pending</option>
                            <option value="reviewed">Reviewed</option>
                            <option value="resolved">Resolved</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Status</button>
                    <button type="button" class="btn btn-secondary" onclick="closeStatusModal()">Cancel</button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <script>
        // Load feedback on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadFeedback();
        });
        
        // Submit Feedback
        document.getElementById('feedbackForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            formData.append('action', 'add_feedback');
            
            const messageDiv = document.getElementById('feedbackMessage');
            messageDiv.textContent = 'Submitting feedback...';
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
                        loadFeedback();
                    }, 2000);
                } else {
                    messageDiv.textContent = data.message || 'Failed to submit feedback';
                    messageDiv.className = 'message error';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                messageDiv.textContent = 'An error occurred';
                messageDiv.className = 'message error';
            });
        });
        
        // Load Feedback
        function loadFeedback() {
            fetch('backend/api.php?action=get_feedback')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('feedbackTableBody');
                const isAdmin = <?php echo $currentUser['role'] === 'admin' ? 'true' : 'false'; ?>;
                
                if (data.success && data.data.length > 0) {
                    tbody.innerHTML = data.data.map(feedback => {
                        const date = new Date(feedback.created_at).toLocaleString();
                        const statusClass = feedback.status === 'resolved' ? 'success' : 
                                          feedback.status === 'reviewed' ? 'warning' : 'info';
                        
                        let row = `
                            <tr>
                                <td>${feedback.id}</td>`;
                        
                        if (isAdmin) {
                            row += `<td>${feedback.user_name || 'Unknown'}</td>`;
                        }
                        
                        row += `
                                <td>${feedback.subject || 'N/A'}</td>
                                <td>${feedback.message.substring(0, 100)}${feedback.message.length > 100 ? '...' : ''}</td>
                                <td><span class="badge badge-${statusClass}">${feedback.status}</span></td>
                                <td>${date}</td>`;
                        
                        if (isAdmin) {
                            row += `
                                <td>
                                    <button onclick="openStatusModal(${feedback.id}, '${feedback.status}')" class="btn btn-sm btn-primary">Update Status</button>
                                </td>`;
                        }
                        
                        row += `</tr>`;
                        return row;
                    }).join('');
                } else {
                    tbody.innerHTML = `<tr><td colspan="${isAdmin ? '7' : '5'}">No feedback found</td></tr>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const isAdmin = <?php echo $currentUser['role'] === 'admin' ? 'true' : 'false'; ?>;
                document.getElementById('feedbackTableBody').innerHTML = `<tr><td colspan="${isAdmin ? '7' : '5'}">Error loading feedback</td></tr>`;
            });
        }
        
        <?php if ($currentUser['role'] === 'admin'): ?>
        // Open Status Modal
        function openStatusModal(feedbackId, currentStatus) {
            document.getElementById('feedback_id').value = feedbackId;
            document.getElementById('status').value = currentStatus;
            document.getElementById('statusModal').style.display = 'block';
        }
        
        // Close Status Modal
        function closeStatusModal() {
            document.getElementById('statusModal').style.display = 'none';
        }
        
        // Update Status
        document.getElementById('updateStatusForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            formData.append('action', 'update_feedback_status');
            
            fetch('backend/api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    closeStatusModal();
                    loadFeedback();
                } else {
                    alert(data.message || 'Failed to update status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        });
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('statusModal');
            if (event.target == modal) {
                closeStatusModal();
            }
        }
        <?php endif; ?>
    </script>
</body>
</html>
