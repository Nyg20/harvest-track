<?php
/**
 * Test Dashboard API - Debug data loading issues
 */
session_start();

// Simulate logged in user for testing
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_name'] = 'Admin User';
    $_SESSION['user_email'] = 'admin@harvesttrack.com';
    $_SESSION['user_role'] = 'admin';
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Dashboard API Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
        h1 { color: #2ecc71; }
        .test-section { margin: 20px 0; padding: 20px; background: #f8f9fa; border-radius: 8px; }
        .success { color: #27ae60; font-weight: bold; }
        .error { color: #e74c3c; font-weight: bold; }
        pre { background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .btn { padding: 10px 20px; background: #2ecc71; color: white; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }
        .btn:hover { background: #27ae60; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔍 Dashboard API Test</h1>
        <p>Testing dashboard data loading and API responses...</p>";

// Test 1: Check database connection
echo "<div class='test-section'>";
echo "<h2>Test 1: Database Connection</h2>";
try {
    require_once 'backend/config/database.php';
    $db = getDBConnection();
    echo "<p class='success'>✓ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}
echo "</div>";

// Test 2: Check if harvests table has data
echo "<div class='test-section'>";
echo "<h2>Test 2: Harvest Data</h2>";
try {
    $stmt = $db->query("SELECT COUNT(*) as count FROM harvests");
    $count = $stmt->fetch()['count'];
    
    if ($count > 0) {
        echo "<p class='success'>✓ Found $count harvest records</p>";
        
        // Show sample data
        $stmt = $db->query("SELECT * FROM harvests LIMIT 5");
        $harvests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<h3>Sample Records:</h3>";
        echo "<pre>" . json_encode($harvests, JSON_PRETTY_PRINT) . "</pre>";
    } else {
        echo "<p class='error'>✗ No harvest records found in database</p>";
        echo "<p>You need to add some harvest data first!</p>";
        echo "<a href='harvest-data.php' class='btn'>Add Harvest Data</a>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test 3: Check users table
echo "<div class='test-section'>";
echo "<h2>Test 3: Users Data</h2>";
try {
    $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'farmer' AND status = 'active'");
    $count = $stmt->fetch()['count'];
    echo "<p class='success'>✓ Found $count active farmers</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test 4: Check storage_capacity table
echo "<div class='test-section'>";
echo "<h2>Test 4: Storage Capacity</h2>";
try {
    $stmt = $db->query("SELECT * FROM storage_capacity LIMIT 1");
    $storage = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($storage) {
        echo "<p class='success'>✓ Storage data found</p>";
        echo "<pre>" . json_encode($storage, JSON_PRETTY_PRINT) . "</pre>";
    } else {
        echo "<p class='error'>✗ No storage capacity data found</p>";
        echo "<p>Inserting default storage data...</p>";
        $db->exec("INSERT INTO storage_capacity (total_capacity, used_capacity) VALUES (10000, 3500)");
        echo "<p class='success'>✓ Default storage data inserted</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test 5: Test the actual API endpoint
echo "<div class='test-section'>";
echo "<h2>Test 5: API Endpoint Response</h2>";
echo "<p>Fetching data from: <code>backend/api.php?action=get_dashboard_data</code></p>";

// Make internal API call
$url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/backend/api.php?action=get_dashboard_data';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>HTTP Status:</strong> $httpCode</p>";
echo "<h3>API Response:</h3>";
echo "<pre>" . $response . "</pre>";

$data = json_decode($response, true);
if ($data && isset($data['success']) && $data['success']) {
    echo "<p class='success'>✓ API returned success</p>";
    echo "<h3>Parsed Data:</h3>";
    echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
} else {
    echo "<p class='error'>✗ API returned error or invalid response</p>";
}
echo "</div>";

// Test 6: JavaScript fetch test
echo "<div class='test-section'>";
echo "<h2>Test 6: JavaScript Fetch Test</h2>";
echo "<button class='btn' onclick='testFetch()'>Test Fetch API</button>";
echo "<div id='fetchResult' style='margin-top: 10px;'></div>";
echo "</div>";

echo "
<script>
function testFetch() {
    const resultDiv = document.getElementById('fetchResult');
    resultDiv.innerHTML = '<p>Loading...</p>';
    
    fetch('backend/api.php?action=get_dashboard_data')
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Data received:', data);
            if (data.success) {
                resultDiv.innerHTML = '<p class=\"success\">✓ Fetch successful!</p><pre>' + JSON.stringify(data, null, 2) + '</pre>';
            } else {
                resultDiv.innerHTML = '<p class=\"error\">✗ API returned error: ' + data.message + '</p>';
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            resultDiv.innerHTML = '<p class=\"error\">✗ Fetch failed: ' + error.message + '</p>';
        });
}
</script>
";

// Summary
echo "<div class='test-section' style='background: #2ecc71; color: white;'>";
echo "<h2>🎯 Quick Actions</h2>";
echo "<a href='dashboard.php' class='btn' style='background: white; color: #2ecc71;'>Go to Dashboard</a>";
echo "<a href='harvest-data.php' class='btn' style='background: white; color: #2ecc71;'>Add Harvest Data</a>";
echo "<a href='index.php' class='btn' style='background: white; color: #2ecc71;'>Logout & Login Again</a>";
echo "</div>";

echo "</div>
</body>
</html>";
?>
