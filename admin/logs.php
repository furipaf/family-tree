<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
checkAuth();

$search = $_GET['search'] ?? '';
$limit = intval($_GET['limit'] ?? 100);
$logs = getActivityLogs($limit, $search);

// Get log stats
$allLogs = getActivityLogs(10000);
$stats = [
    'total' => count($allLogs),
    'admin_login' => 0,
    'member_login' => 0,
    'member_created' => 0,
    'gallery_photo_added' => 0
];

foreach ($allLogs as $log) {
    if (strpos($log, 'ACTION: ADMIN_LOGIN') !== false) $stats['admin_login']++;
    if (strpos($log, 'ACTION: MEMBER_LOGIN') !== false) $stats['member_login']++;
    if (strpos($log, 'ACTION: MEMBER_CREATED') !== false) $stats['member_created']++;
    if (strpos($log, 'ACTION: GALLERY_PHOTO_ADDED') !== false) $stats['gallery_photo_added']++;
}

// Handle log download
if (isset($_GET['download'])) {
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="activity-log-' . date('Y-m-d') . '.txt"');
    readfile(ACTIVITY_LOG_FILE);
    exit;
}

// Handle log clear
if (isset($_POST['clear_logs']) && isset($_POST['confirm_clear'])) {
    file_put_contents(ACTIVITY_LOG_FILE, "=== Family Tree Activity Log ===\n[CLEARED] " . date('Y-m-d H:i:s') . " by " . ($_SESSION['admin_username'] ?? 'admin') . "\n\n");
    header('Location: logs.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs - Family Tree Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        .log-entry {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            padding: 12px 16px;
            border-bottom: 1px solid var(--glass-border);
            white-space: pre-wrap;
            word-break: break-all;
            line-height: 1.5;
        }
        .log-entry:nth-child(even) {
            background: rgba(255, 255, 255, 0.02);
        }
        .log-entry:hover {
            background: rgba(0, 178, 255, 0.05);
        }
        .log-timestamp {
            color: var(--accent-cyan);
            font-weight: 600;
        }
        .log-user {
            color: var(--success);
        }
        .log-action {
            color: #ffa502;
            font-weight: 600;
        }
        .log-ip {
            color: var(--text-muted);
            font-size: 11px;
        }
        .logs-container {
            max-height: 60vh;
            overflow-y: auto;
            background: var(--bg-secondary);
            border-radius: var(--radius-md);
            border: 1px solid var(--glass-border);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            padding: 16px;
            text-align: center;
        }
        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--accent-cyan);
        }
        .stat-label {
            font-size: 12px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 4px;
        }
        .log-filters {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .log-filters form {
            display: flex;
            gap: 8px;
            flex: 1;
        }
        .danger-zone {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 2px solid rgba(255, 71, 87, 0.3);
        }
    </style>
</head>
<body class="admin-page">
    <nav class="admin-nav">
        <div class="nav-brand">
            <svg width="32" height="32" viewBox="0 0 48 48" fill="none">
                <circle cx="24" cy="16" r="8" stroke="#00b2ff" stroke-width="2"/>
                <circle cx="12" cy="36" r="6" stroke="#00b2ff" stroke-width="2"/>
                <circle cx="36" cy="36" r="6" stroke="#00b2ff" stroke-width="2"/>
                <path d="M20 22L14 30" stroke="#00b2ff" stroke-width="2"/>
                <path d="M28 22L34 30" stroke="#00b2ff" stroke-width="2"/>
            </svg>
            <span>Family Tree Admin</span>
        </div>
        <div class="nav-actions">
            <a href="dashboard.php" class="btn btn-secondary">Dashboard</a>
            <a href="users.php" class="btn btn-secondary">👥 Users</a>
            <a href="settings.php" class="btn btn-secondary">⚙️ Settings</a>
            <a href="../index.php" target="_blank" class="btn btn-secondary">View Tree</a>
            <a href="logout.php" class="btn btn-outline">Logout</a>
        </div>
    </nav>

    <main class="admin-main">
        <div class="admin-header">
            <h1>Activity Logs</h1>
            <div>
                <a href="?download=1" class="btn btn-secondary">📥 Download Log</a>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card glass-card">
                <div class="stat-value"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total Entries</div>
            </div>
            <div class="stat-card glass-card">
                <div class="stat-value"><?php echo $stats['admin_login']; ?></div>
                <div class="stat-label">Admin Logins</div>
            </div>
            <div class="stat-card glass-card">
                <div class="stat-value"><?php echo $stats['member_login']; ?></div>
                <div class="stat-label">Member Logins</div>
            </div>
            <div class="stat-card glass-card">
                <div class="stat-value"><?php echo $stats['member_created']; ?></div>
                <div class="stat-label">Members Added</div>
            </div>
            <div class="stat-card glass-card">
                <div class="stat-value"><?php echo $stats['gallery_photo_added']; ?></div>
                <div class="stat-label">Gallery Photos</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="log-filters">
            <form method="GET">
                <input type="text" name="search" class="glass-input" placeholder="Search logs..." 
                       value="<?php echo htmlspecialchars($search); ?>" style="flex: 1;">
                <select name="limit" class="glass-input" style="width: 120px;">
                    <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50 entries</option>
                    <option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100 entries</option>
                    <option value="250" <?php echo $limit == 250 ? 'selected' : ''; ?>>250 entries</option>
                    <option value="500" <?php echo $limit == 500 ? 'selected' : ''; ?>>500 entries</option>
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
                <?php if ($search): ?>
                    <a href="logs.php" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Logs -->
        <div class="logs-container">
            <?php if (empty($logs)): ?>
                <div class="empty-state">
                    <p>No logs found</p>
                </div>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                    <?php
                    // Color-code the log entry
                    $coloredLog = preg_replace('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', '<span class="log-timestamp">[$1]</span>', htmlspecialchars($log));
                    $coloredLog = preg_replace('/\[(ADMIN:[^\]]+)\]/', '[<span class="log-user">$1</span>]', $coloredLog);
                    $coloredLog = preg_replace('/\[(MEMBER:[^\]]+)\]/', '[<span class="log-user">$1</span>]', $coloredLog);
                    $coloredLog = preg_replace('/\[IP: ([^\]]+)\]/', '[<span class="log-ip">IP: $1</span>]', $coloredLog);
                    $coloredLog = preg_replace('/ACTION: ([A-Z_]+)/', 'ACTION: <span class="log-action">$1</span>', $coloredLog);
                    ?>
                    <div class="log-entry"><?php echo $coloredLog; ?></div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Danger Zone -->
        <div class="danger-zone">
            <h3 style="color: var(--error); margin-bottom: 16px;">⚠️ Danger Zone</h3>
            <form method="POST" onsubmit="return confirm('Are you sure you want to clear all logs? This cannot be undone.');">
                <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px; cursor: pointer;">
                    <input type="checkbox" name="confirm_clear" required>
                    <span style="font-size: 13px; color: var(--text-secondary);">I understand this will permanently delete all log entries</span>
                </label>
                <button type="submit" name="clear_logs" class="btn" style="background: rgba(255, 71, 87, 0.2); color: var(--error); border: 1px solid var(--error);">
                    🗑️ Clear All Logs
                </button>
            </form>
        </div>
    </main>
</body>
</html>
