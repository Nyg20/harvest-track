<?php
/**
 * Add Sample Data - Populate database with test data
 */
require_once 'backend/config/database.php';

try {
    $db = getDBConnection();
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Add Sample Data</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
            .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
            h1 { color: #2ecc71; }
            .success { color: #27ae60; padding: 10px; background: #d4edda; border-radius: 5px; margin: 10px 0; }
            .error { color: #e74c3c; padding: 10px; background: #f8d7da; border-radius: 5px; margin: 10px 0; }
            .btn { padding: 10px 20px; background: #2ecc71; color: white; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>🌾 Add Sample Data</h1>";
    
    // Check if data already exists
    $stmt = $db->query("SELECT COUNT(*) as count FROM harvests");
    $existingCount = $stmt->fetch()['count'];
    
    if ($existingCount > 0) {
        echo "<div class='success'>Database already has $existingCount harvest records.</div>";
        echo "<p>Do you want to add more sample data?</p>";
    }
    
    // Add storage capacity if not exists
    $stmt = $db->query("SELECT COUNT(*) as count FROM storage_capacity");
    if ($stmt->fetch()['count'] == 0) {
        $db->exec("INSERT INTO storage_capacity (total_capacity, used_capacity) VALUES (10000, 3500)");
        echo "<div class='success'>✓ Added storage capacity data</div>";
    }
    
    // Get a user ID (preferably a farmer)
    $stmt = $db->query("SELECT id FROM users WHERE role = 'farmer' LIMIT 1");
    $user = $stmt->fetch();
    
    if (!$user) {
        // Try any user
        $stmt = $db->query("SELECT id FROM users LIMIT 1");
        $user = $stmt->fetch();
    }
    
    if (!$user) {
        echo "<div class='error'>✗ No users found in database. Please create a user first.</div>";
        echo "<a href='register.php' class='btn'>Register User</a>";
        exit;
    }
    
    $userId = $user['id'];
    
    // Sample harvest data
    $sampleData = [
        ['Wheat', 125.5, '2024-01-15', 'North Farm', 'Winter'],
        ['Maize', 89.3, '2024-02-20', 'East Field', 'Spring'],
        ['Rice', 156.8, '2024-03-10', 'South Paddy', 'Spring'],
        ['Beans', 45.2, '2024-04-05', 'West Plot', 'Spring'],
        ['Wheat', 134.7, '2024-05-12', 'North Farm', 'Spring'],
        ['Maize', 98.5, '2024-06-18', 'East Field', 'Summer'],
        ['Potatoes', 67.3, '2024-07-22', 'Central Farm', 'Summer'],
        ['Tomatoes', 52.1, '2024-08-15', 'Greenhouse A', 'Summer'],
        ['Wheat', 142.9, '2024-09-08', 'North Farm', 'Fall'],
        ['Maize', 105.6, '2024-10-14', 'East Field', 'Fall'],
        ['Rice', 168.4, '2024-11-01', 'South Paddy', 'Fall'],
        ['Beans', 51.8, '2024-11-16', 'West Plot', 'Fall'],
    ];
    
    $insertedCount = 0;
    
    foreach ($sampleData as $data) {
        list($crop, $quantity, $date, $location, $season) = $data;
        
        $query = "INSERT INTO harvests (user_id, crop_type, quantity, unit, harvest_date, location, farm_name, season, notes) 
                  VALUES (?, ?, ?, 'tons', ?, ?, ?, ?, 'Sample data for testing')";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$userId, $crop, $quantity, $date, $location, $location, $season])) {
            $insertedCount++;
        }
    }
    
    echo "<div class='success'>✓ Successfully added $insertedCount harvest records</div>";
    
    // Add some notifications
    $notifications = [
        ['system', 'Welcome to HarvestTrack', 'System initialized with sample data'],
        ['info', 'New harvest season', 'Spring planting season has begun'],
        ['alert', 'Storage reminder', 'Storage capacity at 35%'],
    ];
    
    foreach ($notifications as $notif) {
        list($type, $title, $message) = $notif;
        $query = "INSERT INTO notifications (type, title, message) VALUES (?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$type, $title, $message]);
    }
    
    echo "<div class='success'>✓ Added notification data</div>";
    
    echo "<h2>✅ Sample Data Added Successfully!</h2>";
    echo "<p>The database now has harvest records, storage data, and notifications.</p>";
    echo "<a href='dashboard.php' class='btn'>View Dashboard</a>";
    echo "<a href='test_dashboard_api.php' class='btn'>Test API</a>";
    
    echo "</div>
    </body>
    </html>";
    
} catch (Exception $e) {
    echo "<div class='error'>✗ Error: " . $e->getMessage() . "</div>";
}
?>
