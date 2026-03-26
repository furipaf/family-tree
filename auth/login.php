<?php
require_once '../includes/config.php';

$error = '';
$success = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $users = json_decode(file_get_contents(USERS_FILE), true) ?: [];
    
    foreach ($users as $user) {
        if ($user['email'] === $email && password_verify($password, $user['password'])) {
            $_SESSION['member_logged_in'] = true;
            $_SESSION['member_id'] = $user['id'];
            $_SESSION['member_name'] = $user['name'];
            $_SESSION['member_email'] = $user['email'];
            logActivity('member_login', "Member logged in: {$email}");
            header('Location: ../index.php');
            exit;
        }
    }
    
    logActivity('member_login_failed', "Failed login attempt for email: {$email}");
    $error = 'Invalid email or password';
}

// Redirect if already logged in
if (isset($_SESSION['member_logged_in']) && $_SESSION['member_logged_in']) {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Family Tree SaaS</title>
    <link rel="stylesheet" href="../assets/css/auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="auth-page">
    <div class="auth-container">
        <!-- Left Side - Branding -->
        <div class="auth-branding">
            <div class="branding-content">
                <div class="logo">
                    <svg width="64" height="64" viewBox="0 0 48 48" fill="none">
                        <circle cx="24" cy="16" r="8" stroke="#00b2ff" stroke-width="2"/>
                        <circle cx="12" cy="36" r="6" stroke="#00b2ff" stroke-width="2"/>
                        <circle cx="36" cy="36" r="6" stroke="#00b2ff" stroke-width="2"/>
                        <path d="M20 22L14 30" stroke="#00b2ff" stroke-width="2"/>
                        <path d="M28 22L34 30" stroke="#00b2ff" stroke-width="2"/>
                    </svg>
                </div>
                <h1>Family Tree</h1>
                <p class="tagline">Connect with your roots. Preserve your legacy.</p>
                <div class="features">
                    <div class="feature">
                        <span class="feature-icon">🌳</span>
                        <span>Interactive family tree visualization</span>
                    </div>
                    <div class="feature">
                        <span class="feature-icon">📸</span>
                        <span>Secure photo galleries</span>
                    </div>
                    <div class="feature">
                        <span class="feature-icon">🔒</span>
                        <span>Privacy-first design</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="auth-form-section">
            <div class="auth-card">
                <div class="auth-header">
                    <h2>Welcome back</h2>
                    <p>Sign in to access your family tree</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-wrapper">
                            <svg class="input-icon" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                            </svg>
                            <input type="email" id="email" name="email" required 
                                   placeholder="you@example.com" autocomplete="email">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <svg class="input-icon" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            <input type="password" id="password" name="password" required 
                                   placeholder="Enter your password" autocomplete="current-password">
                            <button type="button" class="toggle-password" onclick="togglePassword()">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="checkbox-wrapper">
                            <input type="checkbox" name="remember" id="remember">
                            <span class="checkmark"></span>
                            <span class="checkbox-label">Remember me</span>
                        </label>
                        <a href="forgot-password.php" class="forgot-link">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full">
                        Sign In
                    </button>
                </form>

                <div class="auth-footer">
                    <p>Need access? <a href="../index.php">Contact admin via WhatsApp</a></p>
                    <a href="../index.php" class="back-link">← Back to Family Tree</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>
