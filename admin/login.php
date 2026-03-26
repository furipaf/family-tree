<?php
require_once '../includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (login($username, $password)) {
        logActivity('admin_login', "Admin logged in: {$username}");
        header('Location: dashboard.php');
        exit;
    } else {
        logActivity('admin_login_failed', "Failed login attempt for username: {$username}");
        $error = 'Invalid username or password';
    }
}

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Family Tree</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card glass-card">
            <div class="login-header">
                <div class="logo">
                    <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
                        <circle cx="24" cy="16" r="8" stroke="#00b2ff" stroke-width="2"/>
                        <circle cx="12" cy="36" r="6" stroke="#00b2ff" stroke-width="2"/>
                        <circle cx="36" cy="36" r="6" stroke="#00b2ff" stroke-width="2"/>
                        <path d="M20 22L14 30" stroke="#00b2ff" stroke-width="2"/>
                        <path d="M28 22L34 30" stroke="#00b2ff" stroke-width="2"/>
                    </svg>
                </div>
                <h1>Family Tree</h1>
                <p>Admin Panel</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required 
                           class="glass-input" placeholder="Enter username">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required 
                           class="glass-input" placeholder="Enter password">
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">
                    Sign In
                </button>
            </form>
            
            <div class="login-footer">
                <a href="../index.php" class="back-link">← Back to Family Tree</a>
            </div>
        </div>
    </div>
</body>
</html>
