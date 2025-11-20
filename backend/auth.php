<?php
require_once 'config/database.php';
require_once 'config/session.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Handle logout separately (needs redirect, not JSON)
if ($action === 'logout') {
    session_destroy();
    header('Location: ../index.php');
    exit();
}

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

switch($action) {
    case 'login':
        login($db);
        break;
    case 'register':
        register($db);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function login($db) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email and password are required']);
        return;
    }
    
    try {
        $query = "SELECT id, name, email, password, role, status FROM users WHERE email = ? AND status = 'active'";
        $stmt = $db->prepare($query);
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch();
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'user' => [
                        'id' => $user['id'],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'role' => $user['role']
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found or inactive']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function register($db) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'farmer';
    
    // Validate role
    $validRoles = ['farmer', 'officer'];
    if (!in_array($role, $validRoles)) {
        echo json_encode(['success' => false, 'message' => 'Invalid role specified']);
        return;
    }
    $phone = $_POST['phone'] ?? '';
    $location = $_POST['location'] ?? '';
    
    if (empty($name) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Name, email and password are required']);
        return;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        return;
    }
    
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
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
            echo json_encode(['success' => true, 'message' => 'Registration successful']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Registration failed']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}
?>
