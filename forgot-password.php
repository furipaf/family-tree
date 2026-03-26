<?php
require_once 'includes/config.php';
require_once 'includes/email.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: tree.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Please enter your email address';
    } else {
        // Check if user exists
        $users = json_decode(file_get_contents(USERS_FILE), true) ?: [];
        $userFound = false;
        $userId = null;
        
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                $userFound = true;
                $userId = $user['id'];
                break;
            }
        }
        
        if ($userFound) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Store reset token
            $tokensFile = DATA_DIR . 'reset_tokens.json';
            $tokens = [];
            if (file_exists($tokensFile)) {
                $tokens = json_decode(file_get_contents($tokensFile), true) ?: [];
            }
            
            // Remove any existing tokens for this user
            $tokens = array_filter($tokens, function($t) use ($userId) {
                return $t['user_id'] !== $userId;
            });
            
            // Add new token
            $tokens[] = [
                'user_id' => $userId,
                'email' => $email,
                'token' => $token,
                'expires' => $expires,
                'created' => date('Y-m-d H:i:s')
            ];
            
            file_put_contents($tokensFile, json_encode(array_values($tokens), JSON_PRETTY_PRINT));
            
            // Build reset link
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $resetLink = $protocol . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/reset-password.php?token=' . $token;
            
            // Send email using the email function
            $emailResult = sendPasswordResetEmail($email, $resetLink);
            
            if (!$emailResult['success'] && EMAIL_ENABLED) {
                // If email failed but is enabled, show error
                $error = 'Failed to send email. Please try again later.';
            } else {
                $success = 'Password reset link has been sent to your email address.';
                
                // Store the reset link in a temp file for demo/debug purposes
                $tempFile = DATA_DIR . 'reset_links.txt';
                $linkEntry = date('Y-m-d H:i:s') . " - {$email}: {$resetLink}\n";
                file_put_contents($tempFile, $linkEntry, FILE_APPEND);
            }
        } else {
            // Don't reveal if email exists or not for security
            $success = 'If an account exists with this email, you will receive a password reset link.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Family Tree</title>
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
        
        /* Toggle Switch Styles */
        .reset-method-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 24px;
            padding: 16px;
            background: rgba(0, 178, 255, 0.05);
            border-radius: 12px;
        }
        
        .toggle-label {
            font-size: 14px;
            color: var(--text-secondary);
            transition: color 0.3s ease;
        }
        
        .toggle-label.active {
            color: var(--primary);
            font-weight: 500;
        }
        
        .toggle-switch {
            position: relative;
            width: 56px;
            height: 28px;
            background: var(--border);
            border-radius: 14px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        
        .toggle-switch.active {
            background: var(--primary);
        }
        
        .toggle-switch::after {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 24px;
            height: 24px;
            background: white;
            border-radius: 50%;
            transition: transform 0.3s cubic-bezier(0.4, 0.0, 0.2, 1);
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .toggle-switch.active::after {
            transform: translateX(28px);
        }
        
        /* Method Content */
        .method-content {
            display: none;
            animation: fadeIn 0.4s ease;
        }
        
        .method-content.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* WhatsApp QR Section */
        .whatsapp-section {
            text-align: center;
        }
        
        .whatsapp-section h3 {
            color: var(--text-primary);
            margin-bottom: 16px;
            font-size: 18px;
        }
        
        .qr-code-container {
            background: white;
            padding: 20px;
            border-radius: 16px;
            display: inline-block;
            margin-bottom: 20px;
        }
        
        .qr-code-container img {
            width: 200px;
            height: 200px;
            display: block;
        }
        
        .whatsapp-instructions {
            color: var(--text-secondary);
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .whatsapp-instructions strong {
            color: var(--primary);
        }
        
        .whatsapp-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #25D366;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .whatsapp-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
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
            <h2>Reset your password</h2>
            <p>Choose how you want to reset your password</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <!-- Toggle Switch -->
            <div class="reset-method-toggle">
                <span class="toggle-label active" id="emailLabel">📧 Email</span>
                <div class="toggle-switch" id="methodToggle" onclick="toggleResetMethod()"></div>
                <span class="toggle-label" id="whatsappLabel">💬 WhatsApp</span>
            </div>

            <!-- Email Method -->
            <div id="emailMethod" class="method-content active">
                <form method="POST">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required 
                               placeholder="you@example.com" autocomplete="email">
                    </div>

                    <button type="submit" class="btn btn-primary btn-full">
                        Send Reset Link
                    </button>
                </form>
            </div>

            <!-- WhatsApp Method -->
            <div id="whatsappMethod" class="method-content">
                <div class="whatsapp-section">
                    <h3>Contact Admin via WhatsApp</h3>
                    
                    <div class="qr-code-container">
                        <img src="assets/images/qr-code.png" alt="WhatsApp QR Code">
                    </div>
                    
                    <p class="whatsapp-instructions">
                        Scan the QR code or click the button below to message the admin.<br>
                        <strong>Include your registered email address</strong> in your message for verification.
                    </p>
                    
                    <a href="https://wa.me/<?php echo getSetting('whatsapp_number', '923227778881'); ?>?text=Hi,%20I%20need%20to%20reset%20my%20password.%20My%20email%20is:%20" 
                       class="whatsapp-btn" target="_blank">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        Open WhatsApp
                    </a>
                </div>
            </div>

            <div class="back-to-login">
                <a href="index.php">← Back to login</a>
            </div>
        </div>
    </div>

    <script>
        function toggleResetMethod() {
            const toggle = document.getElementById('methodToggle');
            const emailLabel = document.getElementById('emailLabel');
            const whatsappLabel = document.getElementById('whatsappLabel');
            const emailMethod = document.getElementById('emailMethod');
            const whatsappMethod = document.getElementById('whatsappMethod');
            
            toggle.classList.toggle('active');
            
            if (toggle.classList.contains('active')) {
                // WhatsApp mode
                emailLabel.classList.remove('active');
                whatsappLabel.classList.add('active');
                emailMethod.classList.remove('active');
                whatsappMethod.classList.add('active');
            } else {
                // Email mode
                emailLabel.classList.add('active');
                whatsappLabel.classList.remove('active');
                emailMethod.classList.add('active');
                whatsappMethod.classList.remove('active');
            }
        }
    </script>
</body>
</html>
