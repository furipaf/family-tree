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
$email = sanitizeInput($data['email'] ?? '');
$name = sanitizeInput($data['name'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['error' => 'Valid email is required']);
    exit;
}

$invites = json_decode(file_get_contents(INVITES_FILE), true) ?: [];

// Generate unique invite code
$code = bin2hex(random_bytes(16));
$invite = [
    'code' => $code,
    'email' => $email,
    'name' => $name,
    'createdAt' => date('Y-m-d H:i:s'),
    'used' => false,
    'usedAt' => null
];

$invites[] = $invite;
file_put_contents(INVITES_FILE, json_encode($invites, JSON_PRETTY_PRINT));

// Generate invite link
$protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$link = "$protocol://$host/Family-Tree/?invite=$code";

logActivity('invite_generated', "Generated invite for: {$email} (Name: {$name})");

echo json_encode([
    'success' => true,
    'invite' => $invite,
    'link' => $link
]);
