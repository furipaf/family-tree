<?php
require_once 'includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: tree.php');
    exit;
}

$error = '';

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
            header('Location: tree.php');
            exit;
        }
    }
    
    logActivity('member_login_failed', "Failed login attempt for email: {$email}");
    $error = 'Invalid email or password';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family Tree - Connect With Your Roots</title>
    <link rel="stylesheet" href="assets/css/landing.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="landing-page">
    <!-- Navigation -->
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
        <div class="nav-links">
            <a href="#features">Features</a>
            <a href="#about">About</a>
            <a href="help.php">Help</a>
            <a href="admin/login.php" class="nav-admin">Admin</a>
        </div>
    </nav>

    <!-- Hero Section with Login -->
    <section class="hero" style="background: linear-gradient(rgba(7, 10, 23, 0.75), rgba(7, 10, 23, 0.85)), url('assets/images/bg.gif') center/cover no-repeat;">
        <div class="hero-content">
            <div class="hero-text">
                <h1>Preserve Your Family Legacy</h1>
                <p class="hero-subtitle">Connect generations, share memories, and keep your family history alive in a beautiful, interactive family tree.</p>
                
                <div class="hero-features">
                    <div class="hero-feature">
                        <span class="feature-check">✓</span>
                        <span>Interactive tree visualization</span>
                    </div>
                    <div class="hero-feature">
                        <span class="feature-check">✓</span>
                        <span>Secure photo galleries</span>
                    </div>
                    <div class="hero-feature">
                        <span class="feature-check">✓</span>
                        <span>Privacy-first design</span>
                    </div>
                    <div class="hero-feature">
                        <span class="feature-check">✓</span>
                        <span>Text view for easy printing</span>
                    </div>
                    <div class="hero-feature">
                        <span class="feature-check">✓</span>
                        <span>Mobile-friendly</span>
                    </div>
                </div>
            </div>

            <!-- Login Card -->
            <div class="login-card">
                <div class="login-header">
                    <h2>Welcome Back</h2>
                    <p>Sign in to view your family tree</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" class="login-form">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required 
                               placeholder="you@example.com" autocomplete="email">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required 
                               placeholder="Enter your password" autocomplete="current-password">
                    </div>

                    <div class="form-options">
                        <label class="checkbox-wrapper">
                            <input type="checkbox" name="remember">
                            <span class="checkmark"></span>
                            <span>Remember me</span>
                        </label>
                        <a href="forgot-password.php" class="forgot-link">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full">
                        Sign In
                    </button>
                </form>

                <div class="login-footer">
                    <p>Need access? <a href="https://wa.me/<?php echo getSetting('whatsapp_number', '923227778881'); ?>?text=<?php echo urlencode(getSetting('whatsapp_message', 'Hi, I need help with the Family Tree.')); ?>" target="_blank">Contact admin via WhatsApp</a></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features-section">
        <div class="container">
            <h2>Everything You Need</h2>
            <p class="section-subtitle">Powerful features to help you document and share your family history</p>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🌳</div>
                    <h3>Interactive Tree</h3>
                    <p>Beautiful top-down visualization with expandable cards and relationship lines</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📸</div>
                    <h3>Photo Galleries</h3>
                    <p>Each family member can have their own photo gallery with full-screen viewer</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔒</div>
                    <h3>Privacy Protected</h3>
                    <p>Photos are blurred for public visitors. Only members see clear images</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📱</div>
                    <h3>Mobile Ready</h3>
                    <p>Access your family tree from any device - desktop, tablet, or phone</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💑</div>
                    <h3>Spouse Support</h3>
                    <p>Handle complex relationships including multiple spouses and divorces</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📋</div>
                    <h3>Activity Logs</h3>
                    <p>Complete audit trail of all changes and access for security</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📝</div>
                    <h3>Text & Print View</h3>
                    <p>Switch to simple text view for easy reading and print a clean family tree</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">⚡</div>
                    <h3>Smart Navigation</h3>
                    <p>Double-click to collapse branches, zoom controls, and search functionality</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about-section">
        <div class="container">
            <div class="about-content">
                <h2>About Family Tree</h2>
                <p>Our family tree application helps families preserve their history and stay connected across generations. With an intuitive interface and powerful features, documenting your family has never been easier.</p>
                <p>Built with privacy in mind, your family data is secure and only accessible to authorized members.</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="landing-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <svg width="32" height="32" viewBox="0 0 48 48" fill="none">
                        <circle cx="24" cy="16" r="8" stroke="#00b2ff" stroke-width="2"/>
                        <circle cx="12" cy="36" r="6" stroke="#00b2ff" stroke-width="2"/>
                        <circle cx="36" cy="36" r="6" stroke="#00b2ff" stroke-width="2"/>
                        <path d="M20 22L14 30" stroke="#00b2ff" stroke-width="2"/>
                        <path d="M28 22L34 30" stroke="#00b2ff" stroke-width="2"/>
                    </svg>
                    <span>Family Tree</span>
                </div>
                <div class="footer-links">
                    <a href="help.php">Help & Tutorial</a>
                    <a href="https://wa.me/923227778881" target="_blank">Contact Admin</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Family Tree. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- WhatsApp Floating Button -->
    <a href="https://wa.me/923227778881" target="_blank" class="whatsapp-float" title="Contact Admin for Access">
        <svg viewBox="0 0 32 32" width="28" height="28">
            <path fill="currentColor" d="M16 2C8.268 2 2 8.268 2 16c0 2.585.66 5.03 1.82 7.168L2 30l6.832-1.82A13.957 13.957 0 0016 30c7.732 0 14-6.268 14-14S23.732 2 16 2zm0 25.2a11.2 11.2 0 01-5.72-1.568l-.408-.244-4.072 1.084 1.084-4.072-.244-.408A11.2 11.2 0 1116 27.2zm6.16-8.48c-.336-.168-1.992-.984-2.304-1.096-.312-.112-.536-.168-.76.168-.224.336-.872 1.096-1.064 1.32-.2.224-.392.248-.728.08-.336-.168-1.416-.52-2.696-1.656-1-.888-1.672-1.984-1.872-2.32-.2-.336-.02-.52.152-.688.152-.152.336-.392.504-.584.168-.2.224-.336.336-.56.112-.224.056-.416-.028-.584-.08-.168-.76-1.832-1.04-2.504-.272-.656-.552-.568-.76-.576-.2-.008-.424-.008-.648-.008-.224 0-.584.08-.888.416-.304.336-1.16 1.136-1.16 2.768 0 1.64 1.192 3.224 1.36 3.448.168.224 2.352 3.592 5.704 5.04.8.344 1.424.552 1.912.704.8.256 1.528.22 2.104.136.64-.096 1.992-.816 2.272-1.6.28-.784.28-1.456.2-1.6-.08-.144-.304-.224-.64-.392z"/>
        </svg>
    </a>
</body>
</html>
