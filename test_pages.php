<?php
/**
 * Test Script - Verify all pages and functionality
 * Run this from browser to check if all pages are accessible
 */

// Check if pages exist
$pages = [
    'index.php' => 'Login Page',
    'register.php' => 'Registration Page',
    'dashboard.php' => 'Dashboard',
    'harvest-data.php' => 'Harvest Data',
    'reports.php' => 'Reports Page (NEW)',
    'feedback.php' => 'Feedback Page (NEW)',
    'settings.php' => 'Settings Page (NEW)',
    'users.php' => 'User Management (NEW)',
];

$backendFiles = [
    'backend/auth.php' => 'Authentication Handler',
    'backend/api.php' => 'API Handler',
    'backend/config/database.php' => 'Database Config',
    'backend/config/session.php' => 'Session Management',
];

$databaseFiles = [
    'database/schema.sql' => 'Database Schema',
    'database/migration_add_settings.sql' => 'Settings Migration (NEW)',
];

echo "<!DOCTYPE html>
<html>
<head>
    <title>HarvestTrack - System Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; }
        h2 { color: #34495e; margin-top: 30px; }
        .status { display: inline-block; padding: 5px 10px; border-radius: 3px; font-weight: bold; margin-left: 10px; }
        .success { background: #27ae60; color: white; }
        .error { background: #e74c3c; color: white; }
        .warning { background: #f39c12; color: white; }
        .file-list { list-style: none; padding: 0; }
        .file-list li { padding: 10px; margin: 5px 0; background: #ecf0f1; border-left: 4px solid #3498db; }
        .summary { background: #3498db; color: white; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .summary h3 { margin-top: 0; }
        .info-box { background: #e8f4f8; border-left: 4px solid #3498db; padding: 15px; margin: 15px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        table th, table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        table th { background: #34495e; color: white; }
        table tr:hover { background: #f5f5f5; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🚀 HarvestTrack System Test</h1>
        <p>Testing all files and functionality...</p>";

// Test Frontend Pages
echo "<h2>📄 Frontend Pages</h2>";
echo "<table>";
echo "<tr><th>Page</th><th>Description</th><th>Status</th></tr>";

$frontendOk = 0;
$frontendTotal = count($pages);

foreach ($pages as $file => $description) {
    $exists = file_exists($file);
    $status = $exists ? "<span class='status success'>✓ EXISTS</span>" : "<span class='status error'>✗ MISSING</span>";
    if ($exists) $frontendOk++;
    
    echo "<tr>";
    echo "<td><strong>$file</strong></td>";
    echo "<td>$description</td>";
    echo "<td>$status</td>";
    echo "</tr>";
}

echo "</table>";

// Test Backend Files
echo "<h2>⚙️ Backend Files</h2>";
echo "<table>";
echo "<tr><th>File</th><th>Description</th><th>Status</th></tr>";

$backendOk = 0;
$backendTotal = count($backendFiles);

foreach ($backendFiles as $file => $description) {
    $exists = file_exists($file);
    $status = $exists ? "<span class='status success'>✓ EXISTS</span>" : "<span class='status error'>✗ MISSING</span>";
    if ($exists) $backendOk++;
    
    echo "<tr>";
    echo "<td><strong>$file</strong></td>";
    echo "<td>$description</td>";
    echo "<td>$status</td>";
    echo "</tr>";
}

echo "</table>";

// Test Database Files
echo "<h2>🗄️ Database Files</h2>";
echo "<table>";
echo "<tr><th>File</th><th>Description</th><th>Status</th></tr>";

$dbOk = 0;
$dbTotal = count($databaseFiles);

foreach ($databaseFiles as $file => $description) {
    $exists = file_exists($file);
    $status = $exists ? "<span class='status success'>✓ EXISTS</span>" : "<span class='status error'>✗ MISSING</span>";
    if ($exists) $dbOk++;
    
    echo "<tr>";
    echo "<td><strong>$file</strong></td>";
    echo "<td>$description</td>";
    echo "<td>$status</td>";
    echo "</tr>";
}

echo "</table>";

// Summary
$totalOk = $frontendOk + $backendOk + $dbOk;
$totalFiles = $frontendTotal + $backendTotal + $dbTotal;
$percentage = round(($totalOk / $totalFiles) * 100);

echo "<div class='summary'>";
echo "<h3>📊 Summary</h3>";
echo "<p><strong>Total Files:</strong> $totalFiles</p>";
echo "<p><strong>Files Found:</strong> $totalOk</p>";
echo "<p><strong>Files Missing:</strong> " . ($totalFiles - $totalOk) . "</p>";
echo "<p><strong>Completion:</strong> $percentage%</p>";

if ($percentage == 100) {
    echo "<p style='font-size: 1.2em; margin-top: 15px;'>✅ <strong>All files are present!</strong></p>";
} else {
    echo "<p style='font-size: 1.2em; margin-top: 15px;'>⚠️ <strong>Some files are missing. Please check above.</strong></p>";
}
echo "</div>";

// Check session functions
echo "<h2>🔐 Session Functions Test</h2>";
if (file_exists('backend/config/session.php')) {
    require_once 'backend/config/session.php';
    
    $functions = [
        'isLoggedIn' => 'Check if user is logged in',
        'hasRole' => 'Check specific role',
        'hasAnyRole' => 'Check multiple roles',
        'canAccess' => 'Check resource access',
        'requireLogin' => 'Enforce authentication',
        'requireRole' => 'Enforce specific role',
        'getCurrentUser' => 'Get current user info',
    ];
    
    echo "<table>";
    echo "<tr><th>Function</th><th>Description</th><th>Status</th></tr>";
    
    foreach ($functions as $func => $desc) {
        $exists = function_exists($func);
        $status = $exists ? "<span class='status success'>✓ DEFINED</span>" : "<span class='status error'>✗ MISSING</span>";
        
        echo "<tr>";
        echo "<td><code>$func()</code></td>";
        echo "<td>$desc</td>";
        echo "<td>$status</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p class='status error'>session.php not found!</p>";
}

// Next Steps
echo "<div class='info-box'>";
echo "<h3>📋 Next Steps</h3>";
echo "<ol>";
echo "<li><strong>Database Setup:</strong> Run <code>mysql -u root -p harvesttrack < database/migration_add_settings.sql</code></li>";
echo "<li><strong>Clear Cache:</strong> Clear your browser cache (Ctrl+Shift+Delete)</li>";
echo "<li><strong>Test Login:</strong> Go to <a href='index.php'>index.php</a> and login with admin credentials</li>";
echo "<li><strong>Test Pages:</strong> Navigate through all pages using the sidebar menu</li>";
echo "<li><strong>Test Logout:</strong> Click the logout button to verify it works</li>";
echo "</ol>";
echo "</div>";

// Test Credentials
echo "<div class='info-box'>";
echo "<h3>🔑 Test Credentials</h3>";
echo "<table>";
echo "<tr><th>Role</th><th>Email</th><th>Password</th></tr>";
echo "<tr><td><strong>Admin</strong></td><td>admin@harvesttrack.com</td><td>admin123</td></tr>";
echo "<tr><td><strong>Officer</strong></td><td>jane@agri.gov</td><td>admin123</td></tr>";
echo "<tr><td><strong>Farmer</strong></td><td>john@farm.com</td><td>admin123</td></tr>";
echo "</table>";
echo "</div>";

// Documentation
echo "<h2>📚 Documentation</h2>";
echo "<ul class='file-list'>";
echo "<li><strong>ROLES_AND_FEATURES.md</strong> - Complete feature documentation</li>";
echo "<li><strong>IMPLEMENTATION_SUMMARY.md</strong> - Implementation details</li>";
echo "<li><strong>SETUP_GUIDE.md</strong> - Setup and troubleshooting guide</li>";
echo "</ul>";

echo "<hr style='margin: 30px 0;'>";
echo "<p style='text-align: center; color: #7f8c8d;'>HarvestTrack System Test - " . date('Y-m-d H:i:s') . "</p>";

echo "</div>
</body>
</html>";
?>
