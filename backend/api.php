<?php
require_once 'config/database.php';
require_once 'config/session.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Require login for all API calls
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

switch($action) {
    case 'get_dashboard_data':
        getDashboardData($db);
        break;
    case 'get_harvests':
        getHarvests($db);
        break;
    case 'add_harvest':
        addHarvest($db);
        break;
    case 'update_harvest':
        updateHarvest($db);
        break;
    case 'delete_harvest':
        deleteHarvest($db);
        break;
    case 'get_users':
        getUsers($db);
        break;
    case 'add_user':
        addUser($db);
        break;
    case 'update_user':
        updateUser($db);
        break;
    case 'get_reports':
        getReports($db);
        break;
    case 'get_feedback':
        getFeedback($db);
        break;
    case 'add_feedback':
        addFeedback($db);
        break;
    case 'get_notifications':
        getNotifications($db);
        break;
    case 'mark_notification_read':
        markNotificationRead($db);
        break;
    case 'generate_report':
        generateReport($db);
        break;
    case 'update_feedback_status':
        updateFeedbackStatus($db);
        break;
    case 'get_settings':
        getSettings($db);
        break;
    case 'update_settings':
        updateSettings($db);
        break;
    case 'delete_user':
        deleteUser($db);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function getDashboardData($db) {
    try {
        $currentUser = getCurrentUser();
        
        // Total harvests
        $query = "SELECT SUM(quantity) as total FROM harvests";
        if ($currentUser['role'] === 'farmer') {
            $query .= " WHERE user_id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$currentUser['id']]);
        } else {
            $stmt = $db->prepare($query);
            $stmt->execute();
        }
        $totalHarvests = $stmt->fetch()['total'] ?? 0;
        
        // Active farmers count (only for admin/officer)
        if ($currentUser['role'] === 'farmer') {
            // Farmers see their own record count instead
            $query = "SELECT COUNT(*) as count FROM harvests WHERE user_id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$currentUser['id']]);
            $activeFarmers = $stmt->fetch()['count'];
        } else {
            $query = "SELECT COUNT(*) as count FROM users WHERE role = 'farmer' AND status = 'active'";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $activeFarmers = $stmt->fetch()['count'];
        }
        
        // Crops in season
        $query = "SELECT DISTINCT crop_type FROM harvests WHERE MONTH(harvest_date) = MONTH(CURDATE())";
        if ($currentUser['role'] === 'farmer') {
            $query .= " AND user_id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$currentUser['id']]);
        } else {
            $stmt = $db->prepare($query);
            $stmt->execute();
        }
        $cropsInSeason = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Storage capacity (only for admin/officer)
        if ($currentUser['role'] === 'farmer') {
            // Farmers see their own harvest percentage of total instead
            $totalQuery = "SELECT SUM(quantity) as total FROM harvests";
            $totalStmt = $db->prepare($totalQuery);
            $totalStmt->execute();
            $totalHarvestsAll = $totalStmt->fetch()['total'] ?? 1;
            
            $farmerHarvests = $totalHarvests; // Already calculated above for farmer
            $storageLeft = $totalHarvestsAll > 0 ? round(($farmerHarvests / $totalHarvestsAll) * 100) : 0;
        } else {
            $query = "SELECT total_capacity, used_capacity FROM storage_capacity LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $storage = $stmt->fetch();
            $storageLeft = $storage ? round(($storage['used_capacity'] / $storage['total_capacity']) * 100) : 0;
        }
        
        // Harvest trends (last 12 months)
        $query = "SELECT MONTH(harvest_date) as month, SUM(quantity) as total 
                  FROM harvests 
                  WHERE harvest_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)";
        if ($currentUser['role'] === 'farmer') {
            $query .= " AND user_id = ? GROUP BY MONTH(harvest_date)";
            $stmt = $db->prepare($query);
            $stmt->execute([$currentUser['id']]);
        } else {
            $query .= " GROUP BY MONTH(harvest_date)";
            $stmt = $db->prepare($query);
            $stmt->execute();
        }
        $trends = $stmt->fetchAll();
        
        // Crop distribution
        $query = "SELECT crop_type, SUM(quantity) as total FROM harvests";
        if ($currentUser['role'] === 'farmer') {
            $query .= " WHERE user_id = ? GROUP BY crop_type";
            $stmt = $db->prepare($query);
            $stmt->execute([$currentUser['id']]);
        } else {
            $query .= " GROUP BY crop_type";
            $stmt = $db->prepare($query);
            $stmt->execute();
        }
        $cropDistribution = $stmt->fetchAll();
        
        // Recent notifications (filtered by role)
        if ($currentUser['role'] === 'farmer') {
            // Farmers see only their own notifications and general system notifications
            $query = "SELECT * FROM notifications WHERE user_id IS NULL OR user_id = ? ORDER BY created_at DESC LIMIT 5";
            $stmt = $db->prepare($query);
            $stmt->execute([$currentUser['id']]);
        } else {
            // Admins and officers see all notifications
            $query = "SELECT * FROM notifications ORDER BY created_at DESC LIMIT 10";
            $stmt = $db->prepare($query);
            $stmt->execute();
        }
        $notifications = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'data' => [
                'totalHarvests' => round($totalHarvests, 1),
                'activeFarmers' => $activeFarmers,
                'cropsInSeason' => $cropsInSeason,
                'storageLeft' => $storageLeft,
                'trends' => $trends,
                'cropDistribution' => $cropDistribution,
                'notifications' => $notifications
            ]
        ]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function getHarvests($db) {
    try {
        $currentUser = getCurrentUser();
        
        $query = "SELECT h.*, u.name as farmer_name FROM harvests h 
                  LEFT JOIN users u ON h.user_id = u.id";
        
        if ($currentUser['role'] === 'farmer') {
            $query .= " WHERE h.user_id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$currentUser['id']]);
        } else {
            $query .= " ORDER BY h.created_at DESC";
            $stmt = $db->prepare($query);
            $stmt->execute();
        }
        
        $harvests = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'data' => $harvests]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function addHarvest($db) {
    $currentUser = getCurrentUser();
    
    $crop_type = $_POST['crop_type'] ?? '';
    $quantity = $_POST['quantity'] ?? 0;
    $unit = $_POST['unit'] ?? 'tons';
    $harvest_date = $_POST['harvest_date'] ?? '';
    $location = $_POST['location'] ?? '';
    $farm_name = $_POST['farm_name'] ?? '';
    $season = $_POST['season'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    if (empty($crop_type) || empty($quantity) || empty($harvest_date)) {
        echo json_encode(['success' => false, 'message' => 'Crop type, quantity and date are required']);
        return;
    }
    
    try {
        $query = "INSERT INTO harvests (user_id, crop_type, quantity, unit, harvest_date, location, farm_name, season, notes) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$currentUser['id'], $crop_type, $quantity, $unit, $harvest_date, $location, $farm_name, $season, $notes])) {
            // Add notification
            $notificationQuery = "INSERT INTO notifications (type, title, message) VALUES (?, ?, ?)";
            $notificationStmt = $db->prepare($notificationQuery);
            $notificationStmt->execute(['system', 'New harvest record added by ' . $currentUser['name'], 'A new harvest record has been added to the system']);
            
            echo json_encode(['success' => true, 'message' => 'Harvest record added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add harvest record']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function getUsers($db) {
    // Only admin can view all users
    if (!hasRole('admin')) {
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        return;
    }
    
    try {
        $query = "SELECT id, name, email, role, phone, location, status, created_at FROM users ORDER BY created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $users = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'data' => $users]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function getFeedback($db) {
    try {
        $currentUser = getCurrentUser();
        
        if ($currentUser['role'] === 'admin') {
            $query = "SELECT f.*, u.name as user_name FROM feedback f 
                      LEFT JOIN users u ON f.user_id = u.id 
                      ORDER BY f.created_at DESC";
            $stmt = $db->prepare($query);
            $stmt->execute();
        } else {
            $query = "SELECT * FROM feedback WHERE user_id = ? ORDER BY created_at DESC";
            $stmt = $db->prepare($query);
            $stmt->execute([$currentUser['id']]);
        }
        
        $feedback = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'data' => $feedback]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function addFeedback($db) {
    $currentUser = getCurrentUser();
    
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    
    if (empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Message is required']);
        return;
    }
    
    try {
        $query = "INSERT INTO feedback (user_id, subject, message) VALUES (?, ?, ?)";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$currentUser['id'], $subject, $message])) {
            echo json_encode(['success' => true, 'message' => 'Feedback submitted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to submit feedback']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function updateHarvest($db) {
    $currentUser = getCurrentUser();
    $harvestId = $_POST['id'] ?? '';
    
    if (empty($harvestId)) {
        echo json_encode(['success' => false, 'message' => 'Harvest ID is required']);
        return;
    }
    
    // Check if user owns this harvest record or is admin/officer
    if ($currentUser['role'] === 'farmer') {
        $checkQuery = "SELECT user_id FROM harvests WHERE id = ?";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->execute([$harvestId]);
        $harvest = $checkStmt->fetch();
        
        if (!$harvest || $harvest['user_id'] != $currentUser['id']) {
            echo json_encode(['success' => false, 'message' => 'Access denied - you can only edit your own records']);
            return;
        }
    }
    
    $crop_type = $_POST['crop_type'] ?? '';
    $quantity = $_POST['quantity'] ?? 0;
    $unit = $_POST['unit'] ?? 'tons';
    $harvest_date = $_POST['harvest_date'] ?? '';
    $location = $_POST['location'] ?? '';
    $farm_name = $_POST['farm_name'] ?? '';
    $season = $_POST['season'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    if (empty($crop_type) || empty($quantity) || empty($harvest_date)) {
        echo json_encode(['success' => false, 'message' => 'Crop type, quantity and date are required']);
        return;
    }
    
    try {
        $query = "UPDATE harvests SET crop_type = ?, quantity = ?, unit = ?, harvest_date = ?, 
                  location = ?, farm_name = ?, season = ?, notes = ?, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$crop_type, $quantity, $unit, $harvest_date, $location, $farm_name, $season, $notes, $harvestId])) {
            echo json_encode(['success' => true, 'message' => 'Harvest record updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update harvest record']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function deleteHarvest($db) {
    $currentUser = getCurrentUser();
    $harvestId = $_POST['id'] ?? '';
    
    if (empty($harvestId)) {
        echo json_encode(['success' => false, 'message' => 'Harvest ID is required']);
        return;
    }
    
    // Check if user owns this harvest record or is admin
    if ($currentUser['role'] === 'farmer') {
        $checkQuery = "SELECT user_id FROM harvests WHERE id = ?";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->execute([$harvestId]);
        $harvest = $checkStmt->fetch();
        
        if (!$harvest || $harvest['user_id'] != $currentUser['id']) {
            echo json_encode(['success' => false, 'message' => 'Access denied - you can only delete your own records']);
            return;
        }
    } elseif ($currentUser['role'] === 'officer') {
        echo json_encode(['success' => false, 'message' => 'Access denied - officers cannot delete harvest records']);
        return;
    }
    
    try {
        $query = "DELETE FROM harvests WHERE id = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$harvestId])) {
            echo json_encode(['success' => true, 'message' => 'Harvest record deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete harvest record']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function addUser($db) {
    // Only admin can add users
    if (!hasRole('admin')) {
        echo json_encode(['success' => false, 'message' => 'Access denied - admin role required']);
        return;
    }
    
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'farmer';
    $phone = $_POST['phone'] ?? '';
    $location = $_POST['location'] ?? '';
    
    if (empty($name) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Name, email and password are required']);
        return;
    }
    
    // Validate role
    $validRoles = ['admin', 'farmer', 'officer'];
    if (!in_array($role, $validRoles)) {
        echo json_encode(['success' => false, 'message' => 'Invalid role specified']);
        return;
    }
    
    try {
        // Check if email already exists
        $query = "SELECT id FROM users WHERE email = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            return;
        }
        
        // Insert new user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO users (name, email, password, role, phone, location) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$name, $email, $hashedPassword, $role, $phone, $location])) {
            echo json_encode(['success' => true, 'message' => 'User created successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create user']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function updateUser($db) {
    // Only admin can update users
    if (!hasRole('admin')) {
        echo json_encode(['success' => false, 'message' => 'Access denied - admin role required']);
        return;
    }
    
    $userId = $_POST['id'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $location = $_POST['location'] ?? '';
    $status = $_POST['status'] ?? 'active';
    
    if (empty($userId) || empty($name) || empty($email) || empty($role)) {
        echo json_encode(['success' => false, 'message' => 'ID, name, email and role are required']);
        return;
    }
    
    // Validate role
    $validRoles = ['admin', 'farmer', 'officer'];
    if (!in_array($role, $validRoles)) {
        echo json_encode(['success' => false, 'message' => 'Invalid role specified']);
        return;
    }
    
    try {
        $query = "UPDATE users SET name = ?, email = ?, role = ?, phone = ?, location = ?, status = ?, 
                  updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$name, $email, $role, $phone, $location, $status, $userId])) {
            echo json_encode(['success' => true, 'message' => 'User updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update user']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function getReports($db) {
    $currentUser = getCurrentUser();
    
    // Officers and admins can view all reports, farmers only their own
    try {
        if ($currentUser['role'] === 'farmer') {
            $query = "SELECT r.*, h.crop_type, h.quantity, h.harvest_date 
                      FROM reports r 
                      LEFT JOIN harvests h ON r.harvest_id = h.id 
                      WHERE h.user_id = ? 
                      ORDER BY r.generated_at DESC";
            $stmt = $db->prepare($query);
            $stmt->execute([$currentUser['id']]);
        } else {
            $query = "SELECT r.*, h.crop_type, h.quantity, h.harvest_date, u.name as farmer_name 
                      FROM reports r 
                      LEFT JOIN harvests h ON r.harvest_id = h.id 
                      LEFT JOIN users u ON h.user_id = u.id 
                      ORDER BY r.generated_at DESC";
            $stmt = $db->prepare($query);
            $stmt->execute();
        }
        
        $reports = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'data' => $reports]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function getNotifications($db) {
    try {
        $currentUser = getCurrentUser();
        
        $query = "SELECT * FROM notifications WHERE user_id IS NULL OR user_id = ? ORDER BY created_at DESC LIMIT 20";
        $stmt = $db->prepare($query);
        $stmt->execute([$currentUser['id']]);
        
        $notifications = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'data' => $notifications]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function markNotificationRead($db) {
    $currentUser = getCurrentUser();
    $notificationId = $_POST['id'] ?? '';
    
    if (empty($notificationId)) {
        echo json_encode(['success' => false, 'message' => 'Notification ID is required']);
        return;
    }
    
    try {
        $query = "UPDATE notifications SET is_read = 1 WHERE id = ? AND (user_id = ? OR user_id IS NULL)";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$notificationId, $currentUser['id']])) {
            echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update notification']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function generateReport($db) {
    $currentUser = getCurrentUser();
    
    // Officers and admins can generate reports
    if (!hasAnyRole(['admin', 'officer'])) {
        echo json_encode(['success' => false, 'message' => 'Access denied - admin or officer role required']);
        return;
    }
    
    $reportType = $_POST['report_type'] ?? 'harvest_summary';
    $startDate = $_POST['start_date'] ?? date('Y-m-01');
    $endDate = $_POST['end_date'] ?? date('Y-m-d');
    $userId = $_POST['user_id'] ?? null;
    
    try {
        $metrics = [];
        
        switch($reportType) {
            case 'harvest_summary':
                // Total harvests by crop type
                $query = "SELECT crop_type, SUM(quantity) as total_quantity, COUNT(*) as harvest_count 
                          FROM harvests 
                          WHERE harvest_date BETWEEN ? AND ?";
                if ($userId) {
                    $query .= " AND user_id = ?";
                    $stmt = $db->prepare($query . " GROUP BY crop_type");
                    $stmt->execute([$startDate, $endDate, $userId]);
                } else {
                    $stmt = $db->prepare($query . " GROUP BY crop_type");
                    $stmt->execute([$startDate, $endDate]);
                }
                $metrics['crop_summary'] = $stmt->fetchAll();
                
                // Total quantity
                $query = "SELECT SUM(quantity) as total FROM harvests WHERE harvest_date BETWEEN ? AND ?";
                if ($userId) {
                    $query .= " AND user_id = ?";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$startDate, $endDate, $userId]);
                } else {
                    $stmt = $db->prepare($query);
                    $stmt->execute([$startDate, $endDate]);
                }
                $metrics['total_quantity'] = $stmt->fetch()['total'] ?? 0;
                break;
                
            case 'farmer_performance':
                // Performance by farmer
                $query = "SELECT u.name, u.location, COUNT(h.id) as harvest_count, SUM(h.quantity) as total_quantity 
                          FROM users u 
                          LEFT JOIN harvests h ON u.id = h.user_id 
                          WHERE u.role = 'farmer' AND h.harvest_date BETWEEN ? AND ?";
                if ($userId) {
                    $query .= " AND u.id = ?";
                    $stmt = $db->prepare($query . " GROUP BY u.id");
                    $stmt->execute([$startDate, $endDate, $userId]);
                } else {
                    $stmt = $db->prepare($query . " GROUP BY u.id");
                    $stmt->execute([$startDate, $endDate]);
                }
                $metrics['farmer_performance'] = $stmt->fetchAll();
                break;
                
            case 'seasonal_analysis':
                // Analysis by season
                $query = "SELECT season, crop_type, SUM(quantity) as total_quantity 
                          FROM harvests 
                          WHERE harvest_date BETWEEN ? AND ?";
                if ($userId) {
                    $query .= " AND user_id = ?";
                    $stmt = $db->prepare($query . " GROUP BY season, crop_type");
                    $stmt->execute([$startDate, $endDate, $userId]);
                } else {
                    $stmt = $db->prepare($query . " GROUP BY season, crop_type");
                    $stmt->execute([$startDate, $endDate]);
                }
                $metrics['seasonal_data'] = $stmt->fetchAll();
                break;
        }
        
        // Insert report record
        $insertQuery = "INSERT INTO reports (report_type, metrics, generated_by) VALUES (?, ?, ?)";
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->execute([$reportType, json_encode($metrics), $currentUser['id']]);
        $reportId = $db->lastInsertId();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Report generated successfully',
            'report_id' => $reportId,
            'data' => $metrics
        ]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function updateFeedbackStatus($db) {
    // Only admin can update feedback status
    if (!hasRole('admin')) {
        echo json_encode(['success' => false, 'message' => 'Access denied - admin role required']);
        return;
    }
    
    $feedbackId = $_POST['id'] ?? '';
    $status = $_POST['status'] ?? 'pending';
    
    if (empty($feedbackId)) {
        echo json_encode(['success' => false, 'message' => 'Feedback ID is required']);
        return;
    }
    
    $validStatuses = ['pending', 'reviewed', 'resolved'];
    if (!in_array($status, $validStatuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        return;
    }
    
    try {
        $query = "UPDATE feedback SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$status, $feedbackId])) {
            echo json_encode(['success' => true, 'message' => 'Feedback status updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update feedback status']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function getSettings($db) {
    try {
        $currentUser = getCurrentUser();
        
        $query = "SELECT * FROM user_settings WHERE user_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$currentUser['id']]);
        
        $settings = $stmt->fetch();
        
        if (!$settings) {
            // Return default settings
            $settings = [
                'notifications_enabled' => true,
                'email_notifications' => true,
                'theme' => 'light',
                'language' => 'en'
            ];
        }
        
        echo json_encode(['success' => true, 'data' => $settings]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function updateSettings($db) {
    $currentUser = getCurrentUser();
    
    $notificationsEnabled = $_POST['notifications_enabled'] ?? 1;
    $emailNotifications = $_POST['email_notifications'] ?? 1;
    $theme = $_POST['theme'] ?? 'light';
    $language = $_POST['language'] ?? 'en';
    
    try {
        // Check if settings exist
        $checkQuery = "SELECT id FROM user_settings WHERE user_id = ?";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->execute([$currentUser['id']]);
        
        if ($checkStmt->rowCount() > 0) {
            // Update existing settings
            $query = "UPDATE user_settings SET notifications_enabled = ?, email_notifications = ?, 
                      theme = ?, language = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$notificationsEnabled, $emailNotifications, $theme, $language, $currentUser['id']]);
        } else {
            // Insert new settings
            $query = "INSERT INTO user_settings (user_id, notifications_enabled, email_notifications, theme, language) 
                      VALUES (?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->execute([$currentUser['id'], $notificationsEnabled, $emailNotifications, $theme, $language]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Settings updated successfully']);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function deleteUser($db) {
    // Only admin can delete users
    if (!hasRole('admin')) {
        echo json_encode(['success' => false, 'message' => 'Access denied - admin role required']);
        return;
    }
    
    $userId = $_POST['id'] ?? '';
    
    if (empty($userId)) {
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        return;
    }
    
    $currentUser = getCurrentUser();
    if ($userId == $currentUser['id']) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete your own account']);
        return;
    }
    
    try {
        $query = "DELETE FROM users WHERE id = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$userId])) {
            echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}
?>
