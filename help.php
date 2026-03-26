<?php
require_once 'includes/config.php';
$isLoggedIn = isLoggedIn();
$isAdmin = isAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help & Tutorial - Family Tree</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        .help-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .help-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .help-section {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
        }
        .help-section h2 {
            color: var(--accent-cyan);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .help-section h3 {
            color: var(--text-primary);
            margin: 20px 0 10px;
            font-size: 18px;
        }
        .video-container {
            position: relative;
            padding-bottom: 56.25%; /* 16:9 aspect ratio */
            height: 0;
            overflow: hidden;
            border-radius: 12px;
            margin: 20px 0;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
        }
        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }
        .video-placeholder {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(0, 178, 255, 0.1), rgba(0, 136, 204, 0.1));
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .video-placeholder:hover {
            background: linear-gradient(135deg, rgba(0, 178, 255, 0.2), rgba(0, 136, 204, 0.2));
        }
        .video-placeholder .play-icon {
            width: 80px;
            height: 80px;
            background: var(--accent-cyan);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 16px;
            box-shadow: 0 4px 20px rgba(0, 178, 255, 0.4);
            transition: transform 0.3s ease;
        }
        .video-placeholder:hover .play-icon {
            transform: scale(1.1);
        }
        .video-placeholder p {
            color: var(--text-secondary);
            font-size: 16px;
        }
        .feature-list {
            list-style: none;
            padding: 0;
        }
        .feature-list li {
            padding: 10px 0;
            border-bottom: 1px solid var(--glass-border);
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        .feature-list li:last-child {
            border-bottom: none;
        }
        .feature-icon {
            font-size: 20px;
            min-width: 30px;
        }
        .role-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }
        .role-public { background: #6c757d; color: white; }
        .role-member { background: #00b2ff; color: white; }
        .role-admin { background: #ff4757; color: white; }
        .legend-demo {
            display: flex;
            gap: 20px;
            margin: 15px 0;
            flex-wrap: wrap;
        }
        .legend-demo-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
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
    <div class="help-container">
        <a href="index.php" class="back-link">← Back to Family Tree</a>
        
        <div class="help-header">
            <h1>📚 Family Tree Help & Tutorial</h1>
            <p>Complete guide to using our family tree website</p>
        </div>

        <!-- Video Tutorial Section -->
        <div class="help-section">
            <h2>🎥 Video Tutorial</h2>
            <p>Watch this comprehensive tutorial to learn how to use all features of the Family Tree application:</p>
            <div class="video-container">
                <!-- YouTube Video Placeholder - Replace YOUR_VIDEO_ID with actual YouTube video ID -->
                <div class="video-placeholder" onclick="window.open('https://www.youtube.com/watch?v=YOUR_VIDEO_ID', '_blank')">
                    <div class="play-icon">▶</div>
                    <p>Click to watch the complete tutorial on YouTube</p>
                </div>
                <!-- 
                TO EMBED YOUTUBE VIDEO:
                1. Replace YOUR_VIDEO_ID in the iframe src below with your actual YouTube video ID
                2. Remove the video-placeholder div above
                3. Uncomment the iframe below
                
                <iframe 
                    src="https://www.youtube.com/embed/YOUR_VIDEO_ID" 
                    title="Family Tree Tutorial"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                    allowfullscreen>
                </iframe>
                -->
            </div>
        </div>

        <!-- User Roles Section -->
        <div class="help-section">
            <h2>👥 User Roles & Access Levels</h2>
            <p>Our family tree has three access levels:</p>
            
            <h3>1. Public Visitor <span class="role-badge role-public">PUBLIC</span></h3>
            <ul class="feature-list">
                <li>
                    <span class="feature-icon">👁️</span>
                    <div>
                        <strong>View Family Tree</strong><br>
                        See the complete family structure and relationships
                    </div>
                </li>
                <li>
                    <span class="feature-icon">🔒</span>
                    <div>
                        <strong>Blurred Photos</strong><br>
                        All photos are blurred for privacy protection
                    </div>
                </li>
                <li>
                    <span class="feature-icon">📷</span>
                    <div>
                        <strong>Gallery Access</strong><br>
                        Gallery thumbnails visible but full images locked
                    </div>
                </li>
            </ul>

            <h3>2. Family Member <span class="role-badge role-member">MEMBER</span></h3>
            <ul class="feature-list">
                <li>
                    <span class="feature-icon">🔓</span>
                    <div>
                        <strong>Clear Photos</strong><br>
                        View all family photos without blur
                    </div>
                </li>
                <li>
                    <span class="feature-icon">🖼️</span>
                    <div>
                        <strong>Full Gallery Access</strong><br>
                        View and browse all photo galleries
                    </div>
                </li>
                <li>
                    <span class="feature-icon">🔍</span>
                    <div>
                        <strong>Search Members</strong><br>
                        Find family members by name or details
                    </div>
                </li>
                <li>
                    <span class="feature-icon">📝</span>
                    <div>
                        <strong>Text View Mode</strong><br>
                        Switch to simple text view for easy reading and printing
                    </div>
                </li>
                <li>
                    <span class="feature-icon">🖨️</span>
                    <div>
                        <strong>Print Family Tree</strong><br>
                        Print a clean text version of the family tree
                    </div>
                </li>
                <li>
                    <span class="feature-icon">👤</span>
                    <div>
                        <strong>Profile Management</strong><br>
                        Change your password and view your profile
                    </div>
                </li>
            </ul>

            <h3>3. Administrator <span class="role-badge role-admin">ADMIN</span></h3>
            <ul class="feature-list">
                <li>
                    <span class="feature-icon">➕</span>
                    <div>
                        <strong>Add Members</strong><br>
                        Create new family member profiles with photos and details
                    </div>
                </li>
                <li>
                    <span class="feature-icon">✏️</span>
                    <div>
                        <strong>Edit Members</strong><br>
                        Update any family member's information
                    </div>
                </li>
                <li>
                    <span class="feature-icon">🗑️</span>
                    <div>
                        <strong>Delete Members</strong><br>
                        Remove members from the family tree
                    </div>
                </li>
                <li>
                    <span class="feature-icon">📧</span>
                    <div>
                        <strong>Send Invites</strong><br>
                        Invite family members to join with special links
                    </div>
                </li>
                <li>
                    <span class="feature-icon">👥</span>
                    <div>
                        <strong>User Management</strong><br>
                        Create users, assign roles (Member/Admin), reset passwords
                    </div>
                </li>
                <li>
                    <span class="feature-icon">📋</span>
                    <div>
                        <strong>Activity Logs</strong><br>
                        View complete audit trail of all actions
                    </div>
                </li>
                <li>
                    <span class="feature-icon">🖼️</span>
                    <div>
                        <strong>Photo Gallery Management</strong><br>
                        Upload and manage photo galleries for each member
                    </div>
                </li>
                <li>
                    <span class="feature-icon">⚙️</span>
                    <div>
                        <strong>Settings Management</strong><br>
                        Configure WhatsApp number, SMTP email settings, and site preferences
                    </div>
                </li>
            </ul>
        </div>

        <!-- Tree Navigation Section -->
        <div class="help-section">
            <h2>🌳 Navigating the Family Tree</h2>
            
            <h3>Tree Controls</h3>
            <ul class="feature-list">
                <li>
                    <span class="feature-icon">🔍</span>
                    <div>
                        <strong>Search Box</strong> - Type a name to find family members quickly
                    </div>
                </li>
                <li>
                    <span class="feature-icon">📖</span>
                    <div>
                        <strong>Expand All</strong> - Show all member details at once
                    </div>
                </li>
                <li>
                    <span class="feature-icon">📕</span>
                    <div>
                        <strong>Collapse All</strong> - Hide all member details
                    </div>
                </li>
                <li>
                    <span class="feature-icon">➕➖</span>
                    <div>
                        <strong>Zoom</strong> - Zoom in/out for better viewing
                    </div>
                </li>
                <li>
                    <span class="feature-icon">📝</span>
                    <div>
                        <strong>Text View</strong> - Switch to simple text view (great for printing)
                    </div>
                </li>
                <li>
                    <span class="feature-icon">🖨️</span>
                    <div>
                        <strong>Print</strong> - Print a clean text version of the family tree
                    </div>
                </li>
            </ul>

            <h3>Understanding Relationship Lines</h3>
            <div class="legend-demo">
                <div class="legend-demo-item">
                    <div style="width: 30px; height: 3px; background: linear-gradient(to right, #00b2ff, #0088cc); border-radius: 2px;"></div>
                    <span>Blue = Children</span>
                </div>
                <div class="legend-demo-item">
                    <div style="width: 30px; height: 3px; background: linear-gradient(to right, #ff4757, #ee5a6f); border-radius: 2px;"></div>
                    <span>Red = Married</span>
                </div>
                <div class="legend-demo-item">
                    <div style="width: 30px; height: 3px; background: repeating-linear-gradient(to right, #ff4757 0px, #ff4757 4px, transparent 4px, transparent 8px);"></div>
                    <span>Dotted Red = Divorced</span>
                </div>
            </div>

            <h3>Member Cards</h3>
            <ul class="feature-list">
                <li>
                    <span class="feature-icon">👤</span>
                    <div>
                        <strong>Click to Expand</strong> - View full details (birth date, place, contact, bio)
                    </div>
                </li>
                <li>
                    <span class="feature-icon">❤️</span>
                    <div>
                        <strong>Heart Icon</strong> - Click to show/hide spouse information
                    </div>
                </li>
                <li>
                    <span class="feature-icon">📷</span>
                    <div>
                        <strong>Gallery Button</strong> - View photo gallery (if available)
                    </div>
                </li>
                <li>
                    <span class="feature-icon">⚫</span>
                    <div>
                        <strong>Double-Click to Collapse Branch</strong> - Double-click any card with children to collapse/expand all descendants below it (look for the cyan dot indicator)
                    </div>
                </li>
            </ul>

            <h3>View Modes</h3>
            <ul class="feature-list">
                <li>
                    <span class="feature-icon">🖼️</span>
                    <div>
                        <strong>GUI View (Default)</strong> - Beautiful visual cards with photos and relationship lines
                    </div>
                </li>
                <li>
                    <span class="feature-icon">📝</span>
                    <div>
                        <strong>Text View</strong> - Simple hierarchical text display with names, dates, and spouses. Perfect for printing!
                    </div>
                </li>
            </ul>

            <h3>Family Structure Support</h3>
            <ul class="feature-list">
                <li>
                    <span class="feature-icon">👨‍👩‍👧‍👦</span>
                    <div>
                        <strong>Father & Mother Tracking</strong> - Each child can have both father and mother defined, supporting complex family structures including multiple wives
                    </div>
                </li>
                <li>
                    <span class="feature-icon">💑</span>
                    <div>
                        <strong>Multiple Spouses</strong> - Support for polygamous relationships. A person can have multiple spouses, and children are tracked under their specific mother
                    </div>
                </li>
            </ul>
        </div>

        <!-- Getting Access Section -->
        <div class="help-section">
            <h2>🔑 Getting Access</h2>
            
            <h3>For Family Members</h3>
            <p>If you're a family member and want to access the family tree:</p>
            <ol style="margin-left: 20px; line-height: 2;">
                <li>Visit the <strong>Family Tree homepage</strong></li>
                <li>Click the <strong>WhatsApp icon</strong> or "Contact admin via WhatsApp" link</li>
                <li>Send a message requesting access with your email address</li>
                <li>The admin will create an account for you</li>
                <li>Check your email for login credentials</li>
                <li>Return to the homepage and <strong>Sign In</strong> with your email and password</li>
                <li>Change your password in your profile for security</li>
            </ol>

            <h3>How to Login</h3>
            <ol style="margin-left: 20px; line-height: 2;">
                <li>Go to the <strong>Family Tree homepage</strong> (index.php)</li>
                <li>Enter your <strong>email address</strong> in the login form</li>
                <li>Enter your <strong>password</strong></li>
                <li>Click <strong>"Sign In"</strong></li>
                <li>You will be redirected to the family tree view</li>
            </ol>

            <h3>Forgot Your Password?</h3>
            <p>You have two options to reset your password:</p>
            
            <h4>Option 1: Email Reset</h4>
            <ol style="margin-left: 20px; line-height: 2;">
                <li>Click <strong>"Forgot password?"</strong> on the login form</li>
                <li>Select <strong>📧 Email</strong> method</li>
                <li>Enter your registered email address</li>
                <li>Check your email for a password reset link</li>
                <li>Click the link and set a new password</li>
            </ol>
            
            <h4>Option 2: WhatsApp Reset</h4>
            <ol style="margin-left: 20px; line-height: 2;">
                <li>Click <strong>"Forgot password?"</strong> on the login form</li>
                <li>Toggle switch to <strong>💬 WhatsApp</strong> method</li>
                <li>Scan the QR code or click the WhatsApp button</li>
                <li>Message the admin with your registered email address</li>
                <li>The admin will verify and reset your password</li>
            </ol>

            <h3>For Administrators</h3>
            <p>To manage the family tree:</p>
            <ol style="margin-left: 20px; line-height: 2;">
                <li>Go to <strong>Admin Login</strong> page</li>
                <li>Enter admin credentials</li>
                <li>Access the Dashboard to manage members</li>
            </ol>
        </div>

        <!-- Features Section -->
        <div class="help-section">
            <h2>✨ Key Features</h2>
            <ul class="feature-list">
                <li>
                    <span class="feature-icon">📱</span>
                    <div>
                        <strong>Mobile Responsive</strong> - Works on phones, tablets, and desktops
                    </div>
                </li>
                <li>
                    <span class="feature-icon">🔒</span>
                    <div>
                        <strong>Privacy Protection</strong> - Photos blurred for public visitors
                    </div>
                </li>
                <li>
                    <span class="feature-icon">✂️</span>
                    <div>
                        <strong>Photo Cropping</strong> - Crop profile photos to focus on faces
                    </div>
                </li>
                <li>
                    <span class="feature-icon">🖼️</span>
                    <div>
                        <strong>Photo Galleries</strong> - Each member can have multiple photos
                    </div>
                </li>
                <li>
                    <span class="feature-icon">💑</span>
                    <div>
                        <strong>Spouse Support</strong> - Show married partners and multiple spouses
                    </div>
                </li>
                <li>
                    <span class="feature-icon">📊</span>
                    <div>
                        <strong>Activity Logging</strong> - Track all changes and logins
                    </div>
                </li>
            </ul>
        </div>

        <!-- FAQ Section -->
        <div class="help-section">
            <h2>❓ Frequently Asked Questions</h2>
            
            <h3>Why are photos blurred?</h3>
            <p>Photos are blurred for privacy protection. Only logged-in family members can view clear photos.</p>

            <h3>How do I add a new family member?</h3>
            <p>Only administrators can add members. Contact your family admin or use the WhatsApp button to request additions.</p>

            <h3>Can I upload photos?</h3>
            <p>Only admins can upload photos. Members can view galleries but cannot upload.</p>

            <h3>What if I forget my password?</h3>
            <p>Contact the admin via WhatsApp. They can reset your password from the admin panel.</p>

            <h3>Is my data secure?</h3>
            <p>Yes! All data is stored securely. Photos are protected and only visible to authorized family members.</p>
        </div>

        <!-- Contact Section -->
        <div class="help-section" style="text-align: center;">
            <h2>📞 Need More Help?</h2>
            <p>If you have questions or need assistance, click the WhatsApp button to contact the administrator.</p>
            <br>
            <a href="index.php" class="btn btn-primary">Back to Family Tree</a>
        </div>
    </div>
</body>
</html>
