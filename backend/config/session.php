<?php
// Session management
session_start();

// Security settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS
ini_set('session.use_strict_mode', 1);

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check user role
function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit();
    }
}

// Require specific role
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: dashboard.php');
        exit();
    }
}

// Check if user has any of the specified roles
function hasAnyRole($roles) {
    if (!isLoggedIn()) {
        return false;
    }
    return in_array($_SESSION['user_role'], $roles);
}

// Check if user can access resource based on role hierarchy
function canAccess($resource) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $role = $_SESSION['user_role'];
    
    switch($resource) {
        case 'user_management':
            return $role === 'admin';
        case 'all_harvests':
            return in_array($role, ['admin', 'officer']);
        case 'delete_harvests':
            return in_array($role, ['admin', 'farmer']); // farmers can delete own, admins can delete any
        case 'reports':
            return in_array($role, ['admin', 'officer', 'farmer']);
        case 'feedback':
            return in_array($role, ['admin', 'officer', 'farmer']);
        default:
            return false;
    }
}

// Get current user info
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role' => $_SESSION['user_role']
    ];
}

// Logout user
function logout() {
    session_destroy();
    header('Location: /login.php');
    exit();
}
?>
