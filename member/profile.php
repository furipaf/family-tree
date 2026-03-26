<?php
require_once '../includes/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$users = json_decode(file_get_contents(USERS_FILE), true) ?: [];
$currentUser = null;

// Find current user
$userId = $_SESSION['member_id'] ?? null;
foreach ($users as $user) {
    if ($user['id'] === $userId) {
        $currentUser = $user;
        break;
    }
}

if (!$currentUser) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$success = '';
$error = '';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $currentPassword = $_POST['currentPassword'] ?? '';
    $newPassword = $_POST['newPassword'] ?? '';
    
    if (empty($currentPassword) || empty($newPassword)) {
        $error = 'All fields are required';
    } elseif (strlen($newPassword) < 6) {
        $error = 'New password must be at least 6 characters';
    } elseif (!password_verify($currentPassword, $currentUser['password'])) {
        $error = 'Current password is incorrect';
    } else {
        // Update password
        foreach ($users as &$user) {
            if ($user['id'] === $userId) {
                $user['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
                $user['updatedAt'] = date('Y-m-d H:i:s');
                break;
            }
        }
        file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT));
        logActivity('password_changed', "User changed password: {$currentUser['email']}");
        $success = 'Password changed successfully!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Family Tree</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        .profile-container {
            max-width: 500px;
            margin: 0 auto;
        }
        .profile-card {
            padding: 32px;
        }
        .profile-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .profile-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--accent-cyan), var(--accent-blue));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 32px;
        }
        .profile-name {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .profile-email {
            color: var(--text-secondary);
            font-size: 14px;
        }
        .profile-role {
            display: inline-block;
            margin-top: 8px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }
        .role-admin {
            background: rgba(255, 71, 87, 0.15);
            color: #ff4757;
            border: 1px solid rgba(255, 71, 87, 0.3);
        }
        .role-member {
            background: rgba(0, 178, 255, 0.15);
            color: var(--accent-cyan);
            border: 1px solid rgba(0, 178, 255, 0.3);
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--glass-border);
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            color: var(--text-muted);
            font-size: 13px;
        }
        .info-value {
            color: var(--text-primary);
            font-size: 14px;
        }
        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin: 32px 0 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--glass-border);
        }
    </style>
</head>
<body class="login-page">
    <div class="login-container profile-container">
        <div class="profile-card glass-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($currentUser['name'], 0, 1)); ?>
                </div>
                <div class="profile-name"><?php echo htmlspecialchars($currentUser['name']); ?></div>
                <div class="profile-email"><?php echo htmlspecialchars($currentUser['email']); ?></div>
                <span class="profile-role role-<?php echo $currentUser['role']; ?>">
                    <?php echo $currentUser['role']; ?>
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">User ID</span>
                <span class="info-value"><?php echo $currentUser['id']; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Member Since</span>
                <span class="info-value"><?php echo date('F j, Y', strtotime($currentUser['createdAt'])); ?></span>
            </div>
            <?php if (!empty($currentUser['updatedAt']) && $currentUser['updatedAt'] !== $currentUser['createdAt']): ?>
            <div class="info-row">
                <span class="info-label">Last Updated</span>
                <span class="info-value"><?php echo date('F j, Y', strtotime($currentUser['updatedAt'])); ?></span>
            </div>
            <?php endif; ?>

            <h3 class="section-title">Change Password</h3>
            
            <?php if ($success): ?>
                <div class="success-message" style="background: rgba(0, 208, 132, 0.1); border: 1px solid rgba(0, 208, 132, 0.3); color: var(--success); padding: 12px 16px; border-radius: var(--radius-md); margin-bottom: 20px; font-size: 14px;">
                    ✓ <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" class="login-form">
                <input type="hidden" name="action" value="change_password">
                
                <div class="form-group">
                    <label for="currentPassword">Current Password</label>
                    <input type="password" id="currentPassword" name="currentPassword" required 
                           class="glass-input" placeholder="Enter current password">
                </div>
                
                <div class="form-group">
                    <label for="newPassword">New Password</label>
                    <input type="password" id="newPassword" name="newPassword" required 
                           class="glass-input" placeholder="Min 6 characters" minlength="6">
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">
                    Update Password
                </button>
            </form>

            <div class="login-footer" style="margin-top: 32px;">
                <a href="../index.php" class="back-link">← Back to Family Tree</a>
                <br><br>
                <a href="logout.php" style="color: var(--error);">Logout</a>
            </div>
        </div>
    </div>
</body>
</html>
