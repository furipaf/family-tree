<?php
require_once 'includes/config.php';

// Require login for tree view
if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$isLoggedIn = true;
$currentUser = null;

if (isMember()) {
    $users = json_decode(file_get_contents(USERS_FILE), true) ?: [];
    foreach ($users as $user) {
        if ($user['id'] === $_SESSION['member_id']) {
            $currentUser = $user;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family Tree</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body class="tree-page" data-logged-in="<?php echo $isLoggedIn ? 'true' : 'false'; ?>">
    <header class="tree-header">
        <h1>
            <svg width="32" height="32" viewBox="0 0 48 48" fill="none">
                <circle cx="24" cy="16" r="8" stroke="#00b2ff" stroke-width="2"/>
                <circle cx="12" cy="36" r="6" stroke="#00b2ff" stroke-width="2"/>
                <circle cx="36" cy="36" r="6" stroke="#00b2ff" stroke-width="2"/>
                <path d="M20 22L14 30" stroke="#00b2ff" stroke-width="2"/>
                <path d="M28 22L34 30" stroke="#00b2ff" stroke-width="2"/>
            </svg>
            Our Family Tree
        </h1>
        <div class="tree-controls">
            <div class="search-box">
                <input type="text" id="searchInput" class="glass-input" placeholder="Search family members..." 
                       onkeyup="searchMembers(this.value)">
            </div>
            <button class="btn btn-secondary" onclick="expandAll()" title="Expand All">📖</button>
            <button class="btn btn-secondary" onclick="collapseAll()" title="Collapse All">📕</button>
            <button class="btn btn-secondary" onclick="zoomOut()" title="Zoom Out">➖</button>
            <button class="btn btn-secondary" onclick="resetZoom()" title="Reset Zoom">🔍</button>
            <button class="btn btn-secondary" onclick="zoomIn()" title="Zoom In">➕</button>
            <button class="btn btn-secondary" id="viewToggleBtn" onclick="toggleViewMode()" title="Toggle Text View">📝 Text</button>
            <button class="btn btn-secondary" onclick="printTree()" title="Print Tree">🖨️ Print</button>
            <a href="help.php" class="btn btn-secondary" title="Help & Tutorial">❓ Help</a>
            <?php if ($isLoggedIn): ?>
                <?php if (isAdmin()): ?>
                    <a href="admin/dashboard.php" class="btn btn-primary">Admin</a>
                <?php else: ?>
                    <a href="member/profile.php" class="btn btn-secondary" style="margin-right: 8px;">👤 Profile</a>
                    <a href="member/logout.php" class="btn btn-outline">Logout</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="member/login.php" class="btn btn-primary">Member Login</a>
            <?php endif; ?>
        </div>
    </header>

    <main class="tree-container">
        <div id="treeRoot">
            <div class="empty-state">
                <div class="empty-icon">🌳</div>
                <h3>Loading family tree...</h3>
            </div>
        </div>
    </main>

    <?php if (!$isLoggedIn): ?>
    <div class="login-prompt">
        <span>🔒 Photos are blurred for privacy</span>
        <a href="member/login.php" class="btn btn-primary" style="padding: 8px 16px; font-size: 13px;">Member Login</a>
    </div>
    <?php endif; ?>

    <!-- Relationship Legend (Retractable) -->
    <div id="legendBar" class="relationship-legend collapsed">
        <div class="legend-content">
            <div class="legend-title">Relationship Guide</div>
            <div class="legend-items">
                <div class="legend-item">
                    <div class="legend-line children"></div>
                    <span>Children</span>
                </div>
                <div class="legend-item">
                    <div class="legend-line married"></div>
                    <span>Married</span>
                </div>
                <div class="legend-item">
                    <div class="legend-line divorced"></div>
                    <span>Divorced</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Legend Toggle Button (Bulb) -->
    <button id="legendToggle" class="legend-toggle-btn" title="Toggle Relationship Guide">
        💡
    </button>

    <!-- WhatsApp Floating Button -->
    <a href="https://wa.me/<?php echo getSetting('whatsapp_number', '923227778881'); ?>?text=<?php echo urlencode(getSetting('whatsapp_message', 'Hi, I need help with the Family Tree.')); ?>" target="_blank" class="whatsapp-float" title="Contact Admin for Access">
        <svg viewBox="0 0 32 32" width="32" height="32">
            <path fill="currentColor" d="M16 2C8.268 2 2 8.268 2 16c0 2.585.66 5.03 1.82 7.168L2 30l6.832-1.82A13.957 13.957 0 0016 30c7.732 0 14-6.268 14-14S23.732 2 16 2zm0 25.2a11.2 11.2 0 01-5.72-1.568l-.408-.244-4.072 1.084 1.084-4.072-.244-.408A11.2 11.2 0 1116 27.2zm6.16-8.48c-.336-.168-1.992-.984-2.304-1.096-.312-.112-.536-.168-.76.168-.224.336-.872 1.096-1.064 1.32-.2.224-.392.248-.728.08-.336-.168-1.416-.52-2.696-1.656-1-.888-1.672-1.984-1.872-2.32-.2-.336-.02-.52.152-.688.152-.152.336-.392.504-.584.168-.2.224-.336.336-.56.112-.224.056-.416-.028-.584-.08-.168-.76-1.832-1.04-2.504-.272-.656-.552-.568-.76-.576-.2-.008-.424-.008-.648-.008-.224 0-.584.08-.888.416-.304.336-1.16 1.136-1.16 2.768 0 1.64 1.192 3.224 1.36 3.448.168.224 2.352 3.592 5.704 5.04.8.344 1.424.552 1.912.704.8.256 1.528.22 2.104.136.64-.096 1.992-.816 2.272-1.6.28-.784.28-1.456.2-1.6-.08-.144-.304-.224-.64-.392z"/>
        </svg>
    </a>

    <!-- Gallery Modal -->
    <div id="galleryModal" class="modal">
        <div class="modal-content glass-card gallery-modal-content">
            <div class="gallery-header">
                <h2>
                    📷 <span id="galleryTitle">Gallery</span>
                    <span class="gallery-count" id="galleryCount">0 photos</span>
                </h2>
                <button class="modal-close" onclick="closeGallery()">&times;</button>
            </div>
            <div id="galleryGrid" class="gallery-grid">
                <!-- Gallery items will be inserted here -->
            </div>
        </div>
    </div>

    <!-- Full Image Modal -->
    <div id="fullImageModal" class="modal full-image-modal">
        <div class="modal-content">
            <div class="full-image-container">
                <button class="full-image-nav prev" onclick="prevImage()">‹</button>
                <img id="fullImage" src="" alt="Full size">
                <button class="full-image-nav next" onclick="nextImage()">›</button>
                <button class="full-image-close" onclick="closeFullImage()">&times;</button>
                <div class="full-image-counter" id="imageCounter">1 / 10</div>
            </div>
        </div>
    </div>

    <script src="assets/js/tree.js"></script>
    <script>
        // Legend Toggle Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const legendToggle = document.getElementById('legendToggle');
            const legendBar = document.getElementById('legendBar');
            
            if (legendToggle && legendBar) {
                legendToggle.addEventListener('click', function() {
                    legendBar.classList.toggle('collapsed');
                    legendToggle.classList.toggle('active');
                    
                    // Update title based on state
                    if (legendBar.classList.contains('collapsed')) {
                        legendToggle.title = 'Show Relationship Guide';
                    } else {
                        legendToggle.title = 'Hide Relationship Guide';
                    }
                });
            }
        });
    </script>
</body>
</html>
