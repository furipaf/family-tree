<?php
require_once '../includes/config.php';

$error = '';
$success = '';

// Check for invite code
$inviteCode = $_GET['invite'] ?? '';
$inviteData = null;

if ($inviteCode) {
    $invites = json_decode(file_get_contents(INVITES_FILE), true) ?: [];
    foreach ($invites as $invite) {
        if ($invite['code'] === $inviteCode && !$invite['used']) {
            $inviteData = $invite;
            break;
        }
    }
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $inviteCode = $_POST['inviteCode'] ?? '';
    
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'All fields are required';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        $users = json_decode(file_get_contents(USERS_FILE), true) ?: [];
        
        // Check if email exists
        $exists = false;
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                $exists = true;
                break;
            }
        }
        
        if ($exists) {
            $error = 'Email already registered';
        } else {
            // Verify invite code
            $invites = json_decode(file_get_contents(INVITES_FILE), true) ?: [];
            $validInvite = false;
            
            foreach ($invites as &$invite) {
                if ($invite['code'] === $inviteCode && !$invite['used']) {
                    $validInvite = true;
                    $invite['used'] = true;
                    $invite['usedAt'] = date('Y-m-d H:i:s');
                    $invite['usedBy'] = $email;
                    break;
                }
            }
            
            if ($validInvite || empty($inviteCode)) {
                // Create user
                $users[] = [
                    'id' => uniqid('user_'),
                    'name' => $name,
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'role' => 'member',
                    'createdAt' => date('Y-m-d H:i:s')
                ];
                
                file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT));
                file_put_contents(INVITES_FILE, json_encode($invites, JSON_PRETTY_PRINT));
                
                logActivity('member_registered', "New member registered: {$email} (Name: {$name})");
                
                $success = 'Account created! You can now log in.';
            } else {
                $error = 'Invalid or expired invite code';
            }
        }
    }
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
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
    <title>Member Login - Family Tree</title>
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
                <p>Member Access</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message" style="background: rgba(0, 208, 132, 0.1); border: 1px solid rgba(0, 208, 132, 0.3); color: var(--success); padding: 12px 16px; border-radius: var(--radius-md); margin-bottom: 20px; font-size: 14px;"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <!-- Login Form -->
            <form method="POST" class="login-form" id="loginForm" <?php echo $inviteData ? 'style="display: none;"' : ''; ?>>
                <input type="hidden" name="action" value="login">
                
                <div class="form-group">
                    <label for="loginEmail">Email</label>
                    <input type="email" id="loginEmail" name="email" required 
                           class="glass-input" placeholder="your@email.com">
                </div>
                
                <div class="form-group">
                    <label for="loginPassword">Password</label>
                    <input type="password" id="loginPassword" name="password" required 
                           class="glass-input" placeholder="Enter password">
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">
                    Sign In
                </button>
            </form>
            
            <!-- Registration Form (hidden by default, shown with invite) -->
            <form method="POST" class="login-form" id="registerForm" <?php echo $inviteData ? 'style="display: block;"' : 'style="display: none;"'; ?>>
                <input type="hidden" name="action" value="register">
                <input type="hidden" name="inviteCode" value="<?php echo $inviteCode; ?>">
                
                <div class="form-group">
                    <label for="regName">Full Name</label>
                    <input type="text" id="regName" name="name" required 
                           class="glass-input" placeholder="Your name"
                           value="<?php echo $inviteData['name'] ?? ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="regEmail">Email</label>
                    <input type="email" id="regEmail" name="email" required 
                           class="glass-input" placeholder="your@email.com"
                           value="<?php echo $inviteData['email'] ?? ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="regPassword">Password</label>
                    <input type="password" id="regPassword" name="password" required 
                           class="glass-input" placeholder="Min 6 characters" minlength="6">
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">
                    Create Account
                </button>
            </form>
            
            <div class="login-footer">
                <?php if ($inviteData): ?>
                    <div style="background: rgba(0, 208, 132, 0.1); border: 1px solid rgba(0, 208, 132, 0.3); border-radius: 8px; padding: 12px; margin-bottom: 16px;">
                        <p style="color: var(--success); margin: 0; font-size: 14px;">
                            ✓ Valid invite for: <strong><?php echo htmlspecialchars($inviteData['email']); ?></strong>
                        </p>
                    </div>
                <?php endif; ?>
                
                <p id="toggleText" <?php echo $inviteData ? 'style="display: none;"' : ''; ?>>
                    Don't have an account? 
                    <span style="color: var(--text-muted);">(Invite required)</span>
                </p>
                <p id="toggleTextLogin" <?php echo $inviteData ? 'style="display: block;"' : 'style="display: none;"'; ?>>
                    Already have an account? <a href="#" onclick="showLogin()">Sign In</a>
                </p>
                <br>
                <a href="../index.php" class="back-link">← Back to Family Tree</a>
                <br><br>
                <a href="../admin/login.php" style="font-size: 12px; color: var(--text-muted);">Admin Login</a>
            </div>
        </div>
    </div>
    
    <script>
        function showRegister() {
            document.getElementById('loginForm').style.display = 'none';
            document.getElementById('registerForm').style.display = 'block';
            var toggleText = document.getElementById('toggleText');
            var toggleTextLogin = document.getElementById('toggleTextLogin');
            if (toggleText) toggleText.style.display = 'none';
            if (toggleTextLogin) toggleTextLogin.style.display = 'block';
        }
        
        function showLogin() {
            document.getElementById('loginForm').style.display = 'block';
            document.getElementById('registerForm').style.display = 'none';
            var toggleText = document.getElementById('toggleText');
            var toggleTextLogin = document.getElementById('toggleTextLogin');
            if (toggleText) toggleText.style.display = 'block';
            if (toggleTextLogin) toggleTextLogin.style.display = 'none';
        }
        
        <?php if ($inviteData): ?>
        // Auto-show registration if invite is valid
        document.addEventListener('DOMContentLoaded', function() {
            showRegister();
        });
        <?php endif; ?>
    </script>
</body>
</html>
