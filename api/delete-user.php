<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

checkAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? '';

if (empty($id)) {
    echo json_encode(['error' => 'User ID is required']);
    exit;
}

// Prevent deleting yourself
if (isset($_SESSION['member_id']) && $_SESSION['member_id'] === $id) {
    echo json_encode(['error' => 'Cannot delete your own account']);
    exit;
}

$users = json_decode(file_get_contents(USERS_FILE), true) ?: [];
$initialCount = count($users);

// Find user for logging
$deletedUser = null;
foreach ($users as $user) {
    if ($user['id'] === $id) {
        $deletedUser = $user;
        break;
    }
}

// Remove user
$users = array_filter($users, function($user) use ($id) {
    return $user['id'] !== $id;
});

$users = array_values($users);

if (count($users) < $initialCount) {
    file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT));
    
    if ($deletedUser) {
        logActivity('user_deleted', "Deleted: {$deletedUser['name']} ({$deletedUser['email']})");
    }
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'User not found']);
}

