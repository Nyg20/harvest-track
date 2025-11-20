<?php
require_once 'backend/config/session.php';

// Redirect to dashboard if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HarvestTrack - Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-form">
            <div class="logo">
                <h1>HarvestTrack</h1>
                <p>Web-Based System for Tracking Harvests</p>
            </div>
            
            <form id="loginForm">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
                
                <div class="form-links">
                    <a href="register.php">Don't have an account? Register</a>
                </div>
            </form>
            
            <div id="message" class="message"></div>
            
            <div class="demo-credentials">
                <h4>Demo Credentials:</h4>
                <p><strong>Admin:</strong> admin@harvesttrack.com / admin123</p>
                <p><strong>Farmer:</strong> john@farm.com / admin123</p>
                <p><strong>Officer:</strong> jane@agri.gov / admin123</p>
            </div>
        </div>
    </div>
    
    <script src="assets/js/auth.js"></script>
</body>
</html>
