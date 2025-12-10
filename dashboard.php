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
    <title>HarvestTrack - Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <h2>HarvestTrack</h2>
            </div>
            
            <ul class="sidebar-menu">
                <li class="active">
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
                <h1>HarvestTrack</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($currentUser['name']); ?></span>
                    <a href="backend/auth.php?action=logout" class="logout-btn">Logout</a>
                </div>
            </header>
            
            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <!-- Summary Cards -->
                <div class="summary-cards">
                    <div class="card">
                        <div class="card-icon">🌾</div>
                        <div class="card-content">
                            <h3>Total Harvests</h3>
                            <p class="card-value" id="totalHarvests">Loading...</p>
                            <small>tons</small>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-icon">🌱</div>
                        <div class="card-content">
                            <h3>Crops in Season</h3>
                            <p class="card-value" id="cropsInSeason">Loading...</p>
                            <small id="cropsList">varieties</small>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-icon"><?php echo $currentUser['role'] === 'farmer' ? '📋' : '👨‍🌾'; ?></div>
                        <div class="card-content">
                            <h3><?php echo $currentUser['role'] === 'farmer' ? 'My Records' : 'Active Farmers'; ?></h3>
                            <p class="card-value" id="activeFarmers">Loading...</p>
                            <small><?php echo $currentUser['role'] === 'farmer' ? 'harvest records' : 'registered'; ?></small>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-icon"><?php echo $currentUser['role'] === 'farmer' ? '📊' : '📦'; ?></div>
                        <div class="card-content">
                            <h3><?php echo $currentUser['role'] === 'farmer' ? 'My Contribution' : 'Storage Capacity Used'; ?></h3>
                            <p class="card-value" id="storageLeft">Loading...</p>
                            <small>%</small>
                        </div>
                    </div>
                </div>
                
                <!-- Charts Section -->
                <div class="charts-section">
                    <div class="chart-container">
                        <h3>Harvest Trends Over Time</h3>
                        <canvas id="trendsChart"></canvas>
                    </div>
                    
                    <div class="chart-container">
                        <h3>Crops Harvested by Category</h3>
                        <canvas id="cropChart"></canvas>
                    </div>
                </div>
                
                <!-- Recent Updates -->
                <div class="recent-updates">
                    <div class="section-header">
                        <h3>Recent Updates</h3>
                        <button class="btn btn-primary" onclick="window.location.href='harvest-data.php'">Add New Record</button>
                    </div>
                    
                    <div class="updates-list" id="recentUpdates">
                        <!-- Updates will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="assets/js/dashboard.js"></script>
</body>
</html>
