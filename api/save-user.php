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

if (!$data) {
    echo json_encode(['error' => 'Invalid JSON data']);
    exit;
}

// Validate required fields
if (empty($data['name']) || empty($data['email'])) {
    echo json_encode(['error' => 'Name and email are required']);
    exit;
}

if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['error' => 'Invalid email address']);
    exit;
}

$users = json_decode(file_get_contents(USERS_FILE), true) ?: [];
$id = $data['id'] ?? null;
$isNew = !$id;

// Check for duplicate email on create
if ($isNew) {
    foreach ($users as $user) {
        if ($user['email'] === $data['email']) {
            echo json_encode(['error' => 'Email already exists']);
            exit;
        }
    }
}

// Prepare user data
$user = [
    'id' => $id ?? uniqid('user_'),
    'name' => sanitizeInput($data['name']),
    'email' => sanitizeInput($data['email']),
    'role' => in_array($data['role'], ['member', 'admin']) ? $data['role'] : 'member',
    'updatedAt' => date('Y-m-d H:i:s')
];

// Handle password
if ($isNew) {
    // New user - password required
    if (empty($data['password'])) {
        echo json_encode(['error' => 'Password is required for new users']);
        exit;
    }
    if (strlen($data['password']) < 6) {
        echo json_encode(['error' => 'Password must be at least 6 characters']);
        exit;
    }
    $user['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    $user['createdAt'] = date('Y-m-d H:i:s');
} else {
    // Existing user - update password only if provided
    if (!empty($data['password'])) {
        if (strlen($data['password']) < 6) {
            echo json_encode(['error' => 'Password must be at least 6 characters']);
            exit;
        }
        $user['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }
    
    // Preserve existing password and created date if not updating
    foreach ($users as $existing) {
        if ($existing['id'] === $id) {
            if (empty($data['password'])) {
                $user['password'] = $existing['password'];
            }
            $user['createdAt'] = $existing['createdAt'];
            break;
        }
    }
}

// Update or add user
$found = false;
foreach ($users as $key => $existing) {
    if ($existing['id'] === $id) {
        $users[$key] = $user;
        $found = true;
        break;
    }
}

if (!$found) {
    $users[] = $user;
}

file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT));

// Log activity
$action = $isNew ? 'user_created' : 'user_updated';
$roleText = $user['role'] === 'admin' ? 'ADMIN' : 'MEMBER';
logActivity($action, "{$roleText}: {$user['name']} ({$user['email']})");

echo json_encode(['success' => true, 'user' => $user]);
