<?php
session_start();

// Admin credentials - Change these for production
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'familytree2024');

// Data file paths
define('DATA_DIR', __DIR__ . '/../data/');
define('MEMBERS_FILE', DATA_DIR . 'members.json');
define('USERS_FILE', DATA_DIR . 'users.json');
define('INVITES_FILE', DATA_DIR . 'invites.json');
define('ACTIVITY_LOG_FILE', DATA_DIR . 'activity.log');

// Create data files if they don't exist
function ensureDataFiles() {
    if (!file_exists(DATA_DIR)) {
        mkdir(DATA_DIR, 0755, true);
    }
    
    $files = [MEMBERS_FILE, USERS_FILE, INVITES_FILE];
    foreach ($files as $file) {
        if (!file_exists($file)) {
            file_put_contents($file, json_encode([]));
        }
    }
    
    // Create activity log file if not exists
    if (!file_exists(ACTIVITY_LOG_FILE)) {
        file_put_contents(ACTIVITY_LOG_FILE, "=== Family Tree Activity Log ===\n");
    }
}

// Activity logging function
function logActivity($action, $details = '', $user = null) {
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    if ($user === null) {
        if (isAdmin()) {
            $user = 'ADMIN:' . ($_SESSION['admin_username'] ?? 'unknown');
        } elseif (isMember()) {
            $user = 'MEMBER:' . ($_SESSION['member_email'] ?? 'unknown');
        } else {
            $user = 'GUEST';
        }
    }
    
    $logEntry = sprintf(
        "[%s] [%s] [IP: %s] ACTION: %s | DETAILS: %s | UA: %s\n",
        $timestamp,
        $user,
        $ip,
        strtoupper($action),
        $details,
        substr($userAgent, 0, 100)
    );
    
    file_put_contents(ACTIVITY_LOG_FILE, $logEntry, FILE_APPEND | LOCK_EX);
}

// Get activity logs
function getActivityLogs($limit = 100, $search = '') {
    if (!file_exists(ACTIVITY_LOG_FILE)) {
        return [];
    }
    
    $logs = file(ACTIVITY_LOG_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $logs = array_reverse($logs); // Newest first
    
    if (!empty($search)) {
        $logs = array_filter($logs, function($log) use ($search) {
            return stripos($log, $search) !== false;
        });
    }
    
    return array_slice($logs, 0, $limit);
}

ensureDataFiles();

// Helper functions
function getMembers() {
    $content = file_get_contents(MEMBERS_FILE);
    return json_decode($content, true) ?: [];
}

function saveMembers($members) {
    file_put_contents(MEMBERS_FILE, json_encode($members, JSON_PRETTY_PRINT));
}

function getMember($id) {
    $members = getMembers();
    foreach ($members as $member) {
        if ($member['id'] === $id) {
            return $member;
        }
    }
    return null;
}

// Load settings from JSON file
function loadSettings() {
    $settingsFile = DATA_DIR . 'settings.json';
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
    
    if (file_exists($settingsFile)) {
        $settings = json_decode(file_get_contents($settingsFile), true);
        if (is_array($settings)) {
            return array_merge($defaults, $settings);
        }
    }
    
    return $defaults;
}

// Get a specific setting
function getSetting($key, $default = '') {
    $settings = loadSettings();
    return $settings[$key] ?? $default;
}

function generateId() {
    return uniqid('member_');
}

function isLoggedIn() {
    return (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) ||
           (isset($_SESSION['member_logged_in']) && $_SESSION['member_logged_in'] === true);
}

function isAdmin() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function isMember() {
    return isset($_SESSION['member_logged_in']) && $_SESSION['member_logged_in'] === true;
}

function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ../member/login.php');
        exit;
    }
}

function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
