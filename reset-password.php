<?php
require_once 'includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: tree.php');
    exit;
}

$error = '';
$success = '';
$validToken = false;
$userId = null;

$token = $_GET['token'] ?? '';

if (empty($token)) {
    $error = 'Invalid or missing reset token';
} else {
    // Verify token
    $tokensFile = DATA_DIR . 'reset_tokens.json';
    $tokens = [];
    if (file_exists($tokensFile)) {
        $tokens = json_decode(file_get_contents($tokensFile), true) ?: [];
    }
    
    $tokenData = null;
    foreach ($tokens as $t) {
        if ($t['token'] === $token) {
            $tokenData = $t;
            break;
        }
    }
    
    if (!$tokenData) {
        $error = 'Invalid reset token';
    } elseif (strtotime($tokenData['expires']) < time()) {
        $error = 'This reset link has expired. Please request a new one.';
    } else {
        $validToken = true;
        $userId = $tokenData['user_id'];
    }
}

// Handle password reset
if ($validToken && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($password) || strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        // Update user password
        $users = json_decode(file_get_contents(USERS_FILE), true) ?: [];
        $userEmail = '';
        
        foreach ($users as &$user) {
            if ($user['id'] === $userId) {
                $user['password'] = password_hash($password, PASSWORD_DEFAULT);
                $user['updatedAt'] = date('Y-m-d H:i:s');
                $userEmail = $user['email'];
                break;
            }
        }
        
        file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT));
        
        // Remove used token
        $tokens = array_filter($tokens, function($t) use ($token) {
            return $t['token'] !== $token;
        });
        file_put_contents($tokensFile, json_encode(array_values($tokens), JSON_PRETTY_PRINT));
        
        logActivity('password_reset_completed', "Password reset completed for: {$userEmail}");
        
        $success = 'Your password has been reset successfully. You can now log in with your new password.';
        $validToken = false; // Hide the form
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password - Family Tree</title>
    <link rel="stylesheet" href="assets/css/landing.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 120px 20px 40px;
            background: 
                radial-gradient(ellipse at top, rgba(0, 178, 255, 0.15) 0%, transparent 50%),
                var(--bg-dark);
        }
        .auth-box {
            width: 100%;
            max-width: 420px;
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 40px;
            box-shadow: var(--shadow);
        }
        .auth-box h2 {
            text-align: center;
            margin-bottom: 8px;
        }
        .auth-box > p {
            text-align: center;
            color: var(--text-secondary);
            margin-bottom: 32px;
        }
        .back-to-login {
            text-align: center;
            margin-top: 24px;
        }
        .back-to-login a {
            color: var(--primary);
            text-decoration: none;
        }
        .password-requirements {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <nav class="landing-nav">
        <div class="nav-brand">
            <svg width="40" height="40" viewBox="0 0 48 48" fill="none">
                <circle cx="24" cy="16" r="8" stroke="#00b2ff" stroke-width="2"/>
                <circle cx="12" cy="36" r="6" stroke="#00b2ff" stroke-width="2"/>
                <circle cx="36" cy="36" r="6" stroke="#00b2ff" stroke-width="2"/>
                <path d="M20 22L14 30" stroke="#00b2ff" stroke-width="2"/>
                <path d="M28 22L34 30" stroke="#00b2ff" stroke-width="2"/>
            </svg>
            <span>Family Tree</span>
        </div>
    </nav>

    <div class="auth-wrapper">
        <div class="auth-box">
            <?php if ($error && !$validToken): ?>
                <h2>❌ Error</h2>
                <p><?php echo $error; ?></p>
                <div class="back-to-login">
                    <a href="forgot-password.php">Request new reset link</a>
                </div>
            <?php elseif ($success): ?>
                <h2>✅ Success!</h2>
                <p><?php echo $success; ?></p>
                <div class="back-to-login">
                    <a href="index.php">Go to login</a>
                </div>
            <?php elseif ($validToken): ?>
                <h2>Create new password</h2>
                <p>Enter your new password below</p>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input type="password" id="password" name="password" required 
                               placeholder="Min 6 characters" minlength="6">
                        <div class="password-requirements">Must be at least 6 characters</div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required 
                               placeholder="Re-enter your password">
                    </div>

                    <button type="submit" class="btn btn-primary btn-full">
                        Reset Password
                    </button>
                </form>

                <div class="back-to-login">
                    <a href="index.php">← Back to login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
