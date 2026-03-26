<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

// Check if user is logged in (member or admin)
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$currentPassword = $data['currentPassword'] ?? '';
$newPassword = $data['newPassword'] ?? '';

if (empty($currentPassword) || empty($newPassword)) {
    echo json_encode(['error' => 'Current and new password are required']);
    exit;
}

if (strlen($newPassword) < 6) {
    echo json_encode(['error' => 'New password must be at least 6 characters']);
    exit;
}

$users = json_decode(file_get_contents(USERS_FILE), true) ?: [];

// Get current user ID
$userId = $_SESSION['member_id'] ?? null;
if (!$userId) {
    echo json_encode(['error' => 'User session not found']);
    exit;
}

// Find and update user
$found = false;
foreach ($users as &$user) {
    if ($user['id'] === $userId) {
        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            echo json_encode(['error' => 'Current password is incorrect']);
            exit;
        }
        
        // Update password
        $user['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        $user['updatedAt'] = date('Y-m-d H:i:s');
        $found = true;
        break;
    }
}

if ($found) {
    file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT));
    logActivity('password_changed', "User changed password: {$user['email']}");
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'User not found']);
}
