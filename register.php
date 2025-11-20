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
    <title>HarvestTrack - Register</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-form">
            <div class="logo">
                <h1>HarvestTrack</h1>
                <p>Create Your Account</p>
            </div>
            
            <form id="registerForm">
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role">
                        <option value="farmer">Farmer</option>
                        <option value="officer">Agricultural Officer</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone">
                </div>
                
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" placeholder="e.g., Region A">
                </div>
                
                <button type="submit" class="btn btn-primary">Register</button>
                
                <div class="form-links">
                    <a href="index.php">Already have an account? Login</a>
                </div>
            </form>
            
            <div id="message" class="message"></div>
        </div>
    </div>
    
    <script>
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            formData.append('action', 'register');
            
            document.getElementById('message').innerHTML = 'Creating account...';
            document.getElementById('message').className = 'message';
            
            fetch('backend/auth.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const messageDiv = document.getElementById('message');
                if (data.success) {
                    messageDiv.innerHTML = 'Registration successful! Redirecting to login...';
                    messageDiv.className = 'message success';
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 2000);
                } else {
                    messageDiv.innerHTML = data.message || 'Registration failed';
                    messageDiv.className = 'message error';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const messageDiv = document.getElementById('message');
                messageDiv.innerHTML = 'An error occurred. Please try again.';
                messageDiv.className = 'message error';
            });
        });
    </script>
</body>
</html>
