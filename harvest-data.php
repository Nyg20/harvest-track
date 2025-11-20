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
    <title>HarvestTrack - Harvest Data</title>
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
                <li class="active">
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
                <?php if ($currentUser['role'] === 'admin'): ?>
                <li>
                    <a href="users.php">
                        <span class="icon">👥</span>
                        Users
                    </a>
                </li>
                <?php endif; ?>
                <li>
                    <a href="feedback.php">
                        <span class="icon">💬</span>
                        Feedback
                    </a>
                </li>
                <li>
                    <a href="settings.php">
                        <span class="icon">⚙️</span>
                        Settings
                    </a>
                </li>
            </ul>
        </nav>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Bar -->
            <header class="top-bar">
                <h1>Harvest Data</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($currentUser['name']); ?></span>
                    <a href="backend/auth.php?action=logout" class="logout-btn">Logout</a>
                </div>
            </header>
            
            <!-- Harvest Data Content -->
            <div class="page-content">
                <div class="section-header">
                    <h2>Harvest Records</h2>
                    <button class="btn btn-primary" onclick="showAddForm()">Add New Record</button>
                </div>
                
                <!-- Add/Edit Form Modal -->
                <div id="harvestModal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 id="modalTitle">Add New Harvest Record</h3>
                            <span class="close" onclick="closeModal()">&times;</span>
                        </div>
                        
                        <form id="harvestForm">
                            <input type="hidden" id="harvestId" name="id">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="crop_type">Crop Type *</label>
                                    <select id="crop_type" name="crop_type" required>
                                        <option value="">Select Crop</option>
                                        <option value="Maize">Maize</option>
                                        <option value="Beans">Beans</option>
                                        <option value="Wheat">Wheat</option>
                                        <option value="Rice">Rice</option>
                                        <option value="Tomatoes">Tomatoes</option>
                                        <option value="Potatoes">Potatoes</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="quantity">Quantity *</label>
                                    <input type="number" id="quantity" name="quantity" step="0.1" min="0" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="unit">Unit</label>
                                    <select id="unit" name="unit">
                                        <option value="tons">Tons</option>
                                        <option value="kg">Kilograms</option>
                                        <option value="bags">Bags</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="harvest_date">Harvest Date *</label>
                                    <input type="date" id="harvest_date" name="harvest_date" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="location">Location</label>
                                    <input type="text" id="location" name="location" placeholder="e.g., Region A">
                                </div>
                                
                                <div class="form-group">
                                    <label for="farm_name">Farm Name</label>
                                    <input type="text" id="farm_name" name="farm_name" placeholder="e.g., Green Valley Farm">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="season">Season</label>
                                    <select id="season" name="season">
                                        <option value="">Select Season</option>
                                        <option value="Spring">Spring</option>
                                        <option value="Summer">Summer</option>
                                        <option value="Fall">Fall</option>
                                        <option value="Winter">Winter</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="notes">Notes</label>
                                <textarea id="notes" name="notes" rows="3" placeholder="Additional notes about the harvest..."></textarea>
                            </div>
                            
                            <div class="form-actions">
                                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Record</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Harvest Records Table -->
                <div class="table-container">
                    <table class="data-table" id="harvestTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Crop Type</th>
                                <th>Quantity</th>
                                <th>Location</th>
                                <th>Farm</th>
                                <th>Season</th>
                                <?php if ($currentUser['role'] !== 'farmer'): ?>
                                <th>Farmer</th>
                                <?php endif; ?>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="harvestTableBody">
                            <!-- Data will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="assets/js/harvest-data.js"></script>
</body>
</html>
