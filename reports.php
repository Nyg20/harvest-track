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
    <title>HarvestTrack - Reports</title>
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
                <li class="active">
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
                <h1>Reports</h1>
                <p>Generate and view harvest reports</p>
            </div>
            
            <?php if (hasAnyRole(['admin', 'officer'])): ?>
            <!-- Report Generation Form -->
            <div class="card">
                <div class="card-header">
                    <h3>Generate New Report</h3>
                </div>
                <div class="card-body">
                    <form id="generateReportForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="report_type">Report Type *</label>
                                <select id="report_type" name="report_type" required>
                                    <option value="harvest_summary">Harvest Summary</option>
                                    <option value="farmer_performance">Farmer Performance</option>
                                    <option value="seasonal_analysis">Seasonal Analysis</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="start_date">Start Date *</label>
                                <input type="date" id="start_date" name="start_date" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="end_date">End Date *</label>
                                <input type="date" id="end_date" name="end_date" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Generate Report</button>
                    </form>
                    
                    <div id="reportMessage" class="message" style="display: none;"></div>
                </div>
            </div>
            
            <!-- Generated Report Display -->
            <div id="reportDisplay" class="card" style="display: none;">
                <div class="card-header">
                    <h3>Report Results</h3>
                    <button onclick="printReport()" class="btn btn-secondary btn-sm">Print Report</button>
                </div>
                <div class="card-body" id="reportContent">
                    <!-- Report content will be dynamically inserted here -->
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Reports History -->
            <div class="card">
                <div class="card-header">
                    <h3>Reports History</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Report Type</th>
                                    <th>Generated At</th>
                                    <?php if (hasAnyRole(['admin', 'officer'])): ?>
                                    <th>Generated By</th>
                                    <?php endif; ?>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="reportsTableBody">
                                <tr>
                                    <td colspan="<?php echo hasAnyRole(['admin', 'officer']) ? '5' : '4'; ?>">Loading reports...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Set default dates
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            
            document.getElementById('start_date').valueAsDate = firstDay;
            document.getElementById('end_date').valueAsDate = today;
            
            loadReports();
        });
        
        // Generate Report
        document.getElementById('generateReportForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            formData.append('action', 'generate_report');
            
            const messageDiv = document.getElementById('reportMessage');
            messageDiv.textContent = 'Generating report...';
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
                    
                    // Display report
                    displayReport(data.data, formData.get('report_type'));
                    
                    // Reload reports list
                    setTimeout(() => {
                        loadReports();
                        messageDiv.style.display = 'none';
                    }, 2000);
                } else {
                    messageDiv.textContent = data.message || 'Failed to generate report';
                    messageDiv.className = 'message error';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                messageDiv.textContent = 'An error occurred';
                messageDiv.className = 'message error';
            });
        });
        
        // Display Report
        function displayReport(data, reportType) {
            const reportDisplay = document.getElementById('reportDisplay');
            const reportContent = document.getElementById('reportContent');
            
            let html = '<div class="report-content">';
            
            if (reportType === 'harvest_summary') {
                html += '<h4>Harvest Summary Report</h4>';
                html += '<p><strong>Total Quantity:</strong> ' + (data.total_quantity || 0) + ' tons</p>';
                html += '<h5>Crop Summary:</h5>';
                html += '<table class="data-table"><thead><tr><th>Crop Type</th><th>Total Quantity</th><th>Harvest Count</th></tr></thead><tbody>';
                
                if (data.crop_summary && data.crop_summary.length > 0) {
                    data.crop_summary.forEach(crop => {
                        html += `<tr><td>${crop.crop_type}</td><td>${crop.total_quantity} tons</td><td>${crop.harvest_count}</td></tr>`;
                    });
                } else {
                    html += '<tr><td colspan="3">No data available</td></tr>';
                }
                html += '</tbody></table>';
            } else if (reportType === 'farmer_performance') {
                html += '<h4>Farmer Performance Report</h4>';
                html += '<table class="data-table"><thead><tr><th>Farmer Name</th><th>Location</th><th>Harvest Count</th><th>Total Quantity</th></tr></thead><tbody>';
                
                if (data.farmer_performance && data.farmer_performance.length > 0) {
                    data.farmer_performance.forEach(farmer => {
                        html += `<tr><td>${farmer.name}</td><td>${farmer.location || 'N/A'}</td><td>${farmer.harvest_count}</td><td>${farmer.total_quantity} tons</td></tr>`;
                    });
                } else {
                    html += '<tr><td colspan="4">No data available</td></tr>';
                }
                html += '</tbody></table>';
            } else if (reportType === 'seasonal_analysis') {
                html += '<h4>Seasonal Analysis Report</h4>';
                html += '<table class="data-table"><thead><tr><th>Season</th><th>Crop Type</th><th>Total Quantity</th></tr></thead><tbody>';
                
                if (data.seasonal_data && data.seasonal_data.length > 0) {
                    data.seasonal_data.forEach(item => {
                        html += `<tr><td>${item.season || 'N/A'}</td><td>${item.crop_type}</td><td>${item.total_quantity} tons</td></tr>`;
                    });
                } else {
                    html += '<tr><td colspan="3">No data available</td></tr>';
                }
                html += '</tbody></table>';
            }
            
            html += '</div>';
            reportContent.innerHTML = html;
            reportDisplay.style.display = 'block';
        }
        
        // Load Reports
        function loadReports() {
            fetch('backend/api.php?action=get_reports')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('reportsTableBody');
                
                if (data.success && data.data.length > 0) {
                    tbody.innerHTML = data.data.map(report => {
                        const date = new Date(report.generated_at).toLocaleString();
                        const reportType = report.report_type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                        
                        let row = `
                            <tr>
                                <td>${report.id}</td>
                                <td>${reportType}</td>
                                <td>${date}</td>`;
                        
                        <?php if (hasAnyRole(['admin', 'officer'])): ?>
                        row += `<td>${report.farmer_name || 'System'}</td>`;
                        <?php endif; ?>
                        
                        row += `<td>
                                    <button onclick="viewReport(${report.id}, '${report.report_type}', '${report.metrics}')" class="btn btn-sm btn-primary">View</button>
                                </td>
                            </tr>`;
                        return row;
                    }).join('');
                } else {
                    tbody.innerHTML = '<tr><td colspan="<?php echo hasAnyRole(['admin', 'officer']) ? '5' : '4'; ?>">No reports found</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('reportsTableBody').innerHTML = '<tr><td colspan="<?php echo hasAnyRole(['admin', 'officer']) ? '5' : '4'; ?>">Error loading reports</td></tr>';
            });
        }
        
        // View Report
        function viewReport(id, reportType, metricsJson) {
            try {
                const metrics = JSON.parse(metricsJson);
                displayReport(metrics, reportType);
            } catch (e) {
                alert('Error loading report data');
            }
        }
        
        // Print Report
        function printReport() {
            const reportContent = document.getElementById('reportContent').innerHTML;
            const printWindow = window.open('', '', 'height=600,width=800');
            printWindow.document.write('<html><head><title>Print Report</title>');
            printWindow.document.write('<link rel="stylesheet" href="assets/css/style.css">');
            printWindow.document.write('</head><body>');
            printWindow.document.write(reportContent);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.print();
        }
    </script>
</body>
</html>
