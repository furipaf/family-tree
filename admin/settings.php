<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Require admin login
requireAdmin();

$error = '';
$success = '';

// Settings file path
$settingsFile = DATA_DIR . 'settings.json';

// Load current settings
$settings = [];
if (file_exists($settingsFile)) {
    $settings = json_decode(file_get_contents($settingsFile), true) ?: [];
}

// Default settings
$defaults = [
    'whatsapp_number' => '923227778881',
    'whatsapp_message' => 'Hi, I need help with the Family Tree.',
    'site_title' => 'Family Tree',
    'email_enabled' => false,
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_username' => '',
    'smtp_password' => '',
    'smtp_encryption' => 'tls',
    'from_email' => '',
    'from_name' => 'Family Tree'
];

// Merge with defaults
$settings = array_merge($defaults, $settings);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save_general') {
        $settings['whatsapp_number'] = sanitizeInput($_POST['whatsapp_number'] ?? '');
        $settings['whatsapp_message'] = sanitizeInput($_POST['whatsapp_message'] ?? '');
        $settings['site_title'] = sanitizeInput($_POST['site_title'] ?? 'Family Tree');
        
        if (file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT))) {
            $success = 'General settings saved successfully!';
            logActivity('settings_updated', 'General settings updated by admin');
        } else {
            $error = 'Failed to save settings. Please check file permissions.';
        }
    }
    
    if ($action === 'save_email') {
        $settings['email_enabled'] = isset($_POST['email_enabled']);
        $settings['smtp_host'] = sanitizeInput($_POST['smtp_host'] ?? 'smtp.gmail.com');
        $settings['smtp_port'] = intval($_POST['smtp_port'] ?? 587);
        $settings['smtp_username'] = sanitizeInput($_POST['smtp_username'] ?? '');
        $settings['smtp_password'] = $_POST['smtp_password'] ?? '';
        $settings['smtp_encryption'] = sanitizeInput($_POST['smtp_encryption'] ?? 'tls');
        $settings['from_email'] = sanitizeInput($_POST['from_email'] ?? '');
        $settings['from_name'] = sanitizeInput($_POST['from_name'] ?? 'Family Tree');
        
        if (file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT))) {
            $success = 'Email settings saved successfully!';
            logActivity('settings_updated', 'Email settings updated by admin');
        } else {
            $error = 'Failed to save settings. Please check file permissions.';
        }
    }
    
    if ($action === 'test_email') {
        require_once '../includes/email.php';
        $testEmail = sanitizeInput($_POST['test_email'] ?? '');
        
        if (!empty($testEmail)) {
            $result = sendEmail($testEmail, 'Test Email - Family Tree', 
                '<h2>Test Email</h2><p>This is a test email from your Family Tree application.</p><p>If you received this, your email settings are working correctly!</p>',
                'This is a test email from your Family Tree application. If you received this, your email settings are working correctly!'
            );
            
            if ($result['success']) {
                $success = 'Test email sent successfully to ' . $testEmail;
            } else {
                $error = 'Failed to send test email: ' . $result['message'];
            }
        } else {
            $error = 'Please enter a test email address.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Family Tree</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        .settings-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .settings-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .settings-header h1 {
            color: var(--accent-cyan);
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .settings-section {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .settings-section h2 {
            color: var(--accent-cyan);
            margin-bottom: 24px;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        
        .help-text {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 6px;
        }
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 26px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: var(--glass-border);
            transition: .3s;
            border-radius: 26px;
        }
        
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .3s;
            border-radius: 50%;
        }
        
        input:checked + .toggle-slider {
            background-color: var(--accent-cyan);
        }
        
        input:checked + .toggle-slider:before {
            transform: translateX(24px);
        }
        
        .toggle-container {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .toggle-label {
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        .smtp-provider-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .smtp-tab {
            padding: 10px 20px;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            color: var(--text-secondary);
        }
        
        .smtp-tab:hover {
            border-color: var(--accent-cyan);
        }
        
        .smtp-tab.active {
            background: var(--accent-cyan);
            color: white;
            border-color: var(--accent-cyan);
        }
        
        .test-email-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--glass-border);
        }
        
        /* Back link */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            color: var(--accent-cyan);
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body class="tree-page">
    <header class="tree-header">
        <h1>
            <svg width="32" height="32" viewBox="0 0 48 48" fill="none">
                <circle cx="24" cy="16" r="8" stroke="#00b2ff" stroke-width="2"/>
                <circle cx="12" cy="36" r="6" stroke="#00b2ff" stroke-width="2"/>
                <circle cx="36" cy="36" r="6" stroke="#00b2ff" stroke-width="2"/>
                <path d="M20 22L14 30" stroke="#00b2ff" stroke-width="2"/>
                <path d="M28 22L34 30" stroke="#00b2ff" stroke-width="2"/>
            </svg>
            Settings
        </h1>
        <div class="tree-controls">
            <a href="dashboard.php" class="btn btn-secondary">Dashboard</a>
            <a href="users.php" class="btn btn-secondary">👥 Members</a>
            <a href="logs.php" class="btn btn-secondary">📋 Logs</a>
            <a href="../tree.php" class="btn btn-secondary">View Tree</a>
            <a href="logout.php" class="btn btn-outline">Logout</a>
        </div>
    </header>

    <main class="tree-container">
        <div class="settings-container">
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <!-- General Settings -->
            <div class="settings-section">
                <h2>🌐 General Settings</h2>
                
                <form method="POST">
                    <input type="hidden" name="action" value="save_general">
                    
                    <div class="form-group">
                        <label for="site_title">Site Title</label>
                        <input type="text" id="site_title" name="site_title" class="glass-input" 
                               value="<?php echo htmlspecialchars($settings['site_title']); ?>">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="whatsapp_number">WhatsApp Number</label>
                            <input type="text" id="whatsapp_number" name="whatsapp_number" class="glass-input" 
                                   value="<?php echo htmlspecialchars($settings['whatsapp_number']); ?>"
                                   placeholder="923001234567">
                            <p class="help-text">Include country code without + or spaces (e.g., 923001234567)</p>
                        </div>
                        
                        <div class="form-group">
                            <label for="whatsapp_message">Default WhatsApp Message</label>
                            <input type="text" id="whatsapp_message" name="whatsapp_message" class="glass-input" 
                                   value="<?php echo htmlspecialchars($settings['whatsapp_message']); ?>">
                            <p class="help-text">Pre-filled message when users click WhatsApp button</p>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Save General Settings</button>
                </form>
            </div>

            <!-- Email Settings -->
            <div class="settings-section">
                <h2>📧 Email Settings (SMTP)</h2>
                
                <form method="POST">
                    <input type="hidden" name="action" value="save_email">
                    
                    <div class="form-group">
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" name="email_enabled" 
                                       <?php echo $settings['email_enabled'] ? 'checked' : ''; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Enable Email Sending</span>
                        </div>
                        <p class="help-text">When enabled, users can receive password reset emails. When disabled, password reset is only available via WhatsApp.</p>
                    </div>
                    
                    <div class="smtp-provider-tabs">
                        <div class="smtp-tab active" onclick="setSMTPDefaults('gmail')">Gmail</div>
                        <div class="smtp-tab" onclick="setSMTPDefaults('zoho')">Zoho Mail</div>
                        <div class="smtp-tab" onclick="setSMTPDefaults('custom')">Custom</div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="smtp_host">SMTP Host</label>
                            <input type="text" id="smtp_host" name="smtp_host" class="glass-input" 
                                   value="<?php echo htmlspecialchars($settings['smtp_host']); ?>"
                                   placeholder="smtp.gmail.com">
                        </div>
                        
                        <div class="form-group">
                            <label for="smtp_port">SMTP Port</label>
                            <input type="number" id="smtp_port" name="smtp_port" class="glass-input" 
                                   value="<?php echo $settings['smtp_port']; ?>"
                                   placeholder="587">
                            <p class="help-text">Usually 587 for TLS, 465 for SSL</p>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="smtp_username">SMTP Username</label>
                            <input type="text" id="smtp_username" name="smtp_username" class="glass-input" 
                                   value="<?php echo htmlspecialchars($settings['smtp_username']); ?>"
                                   placeholder="your-email@gmail.com">
                        </div>
                        
                        <div class="form-group">
                            <label for="smtp_password">SMTP Password / App Password</label>
                            <input type="password" id="smtp_password" name="smtp_password" class="glass-input" 
                                   value="<?php echo htmlspecialchars($settings['smtp_password']); ?>"
                                   placeholder="••••••••">
                            <p class="help-text">For Gmail, use App Password (not your regular password)</p>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="smtp_encryption">Encryption</label>
                            <select id="smtp_encryption" name="smtp_encryption" class="glass-input">
                                <option value="tls" <?php echo $settings['smtp_encryption'] === 'tls' ? 'selected' : ''; ?>>TLS (Recommended)</option>
                                <option value="ssl" <?php echo $settings['smtp_encryption'] === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="from_email">From Email</label>
                            <input type="email" id="from_email" name="from_email" class="glass-input" 
                                   value="<?php echo htmlspecialchars($settings['from_email']); ?>"
                                   placeholder="noreply@yourdomain.com">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="from_name">From Name</label>
                        <input type="text" id="from_name" name="from_name" class="glass-input" 
                               value="<?php echo htmlspecialchars($settings['from_name']); ?>"
                               placeholder="Family Tree">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Save Email Settings</button>
                </form>
                
                <!-- Test Email -->
                <div class="test-email-section">
                    <h3 style="margin-bottom: 16px; color: var(--text-primary);">Test Email Configuration</h3>
                    <form method="POST" class="form-row">
                        <input type="hidden" name="action" value="test_email">
                        <div class="form-group" style="flex: 1;">
                            <input type="email" name="test_email" class="glass-input" 
                                   placeholder="Enter test email address">
                        </div>
                        <button type="submit" class="btn btn-secondary">Send Test Email</button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- WhatsApp Floating Button -->
    <a href="https://wa.me/<?php echo getSetting('whatsapp_number', '923227778881'); ?>" target="_blank" class="whatsapp-float" title="Contact Admin">
        <svg viewBox="0 0 32 32" width="32" height="32">
            <path fill="currentColor" d="M16 2C8.268 2 2 8.268 2 16c0 2.585.66 5.03 1.82 7.168L2 30l6.832-1.82A13.957 13.957 0 0016 30c7.732 0 14-6.268 14-14S23.732 2 16 2zm0 25.2a11.2 11.2 0 01-5.72-1.568l-.408-.244-4.072 1.084 1.084-4.072-.244-.408A11.2 11.2 0 1116 27.2zm6.16-8.48c-.336-.168-1.992-.984-2.304-1.096-.312-.112-.536-.168-.76.168-.224.336-.872 1.096-1.064 1.32-.2.224-.392.248-.728.08-.336-.168-1.416-.52-2.696-1.656-1-.888-1.672-1.984-1.872-2.32-.2-.336-.02-.52.152-.688.152-.152.336-.392.504-.584.168-.2.224-.336.336-.56.112-.224.056-.416-.028-.584-.08-.168-.76-1.832-1.04-2.504-.272-.656-.552-.568-.76-.576-.2-.008-.424-.008-.648-.008-.224 0-.584.08-.888.416-.304.336-1.16 1.136-1.16 2.768 0 1.64 1.192 3.224 1.36 3.448.168.224 2.352 3.592 5.704 5.04.8.344 1.424.552 1.912.704.8.256 1.528.22 2.104.136.64-.096 1.992-.816 2.272-1.6.28-.784.28-1.456.2-1.6-.08-.144-.304-.224-.64-.392z"/>
        </svg>
    </a>

    <script>
        function setSMTPDefaults(provider) {
            const host = document.getElementById('smtp_host');
            const port = document.getElementById('smtp_port');
            const encryption = document.getElementById('smtp_encryption');
            
            // Update active tab
            document.querySelectorAll('.smtp-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            event.target.classList.add('active');
            
            switch(provider) {
                case 'gmail':
                    host.value = 'smtp.gmail.com';
                    port.value = '587';
                    encryption.value = 'tls';
                    break;
                case 'zoho':
                    host.value = 'smtp.zoho.com';
                    port.value = '587';
                    encryption.value = 'tls';
                    break;
                case 'custom':
                    // Keep current values
                    break;
            }
        }
    </script>
</body>
</html>
