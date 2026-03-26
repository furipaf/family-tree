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

$members = getMembers();

// Validate required fields
if (empty($data['firstName']) || empty($data['lastName'])) {
    echo json_encode(['error' => 'First name and last name are required']);
    exit;
}

// Generate or use existing ID
$id = $data['id'] ?? generateId();

$member = [
    'id' => $id,
    'firstName' => sanitizeInput($data['firstName']),
    'lastName' => sanitizeInput($data['lastName']),
    'gender' => $data['gender'] ?? 'unknown',
    'birthDate' => $data['birthDate'] ?? '',
    'deathDate' => $data['deathDate'] ?? '',
    'birthPlace' => sanitizeInput($data['birthPlace'] ?? ''),
    'contactNumber' => sanitizeInput($data['contactNumber'] ?? ''),
    'address' => sanitizeInput($data['address'] ?? ''),
    'bio' => sanitizeInput($data['bio'] ?? ''),
    'photo' => $data['photo'] ?? '',
    'parentId' => $data['parentId'] ?? null,
    'motherId' => $data['motherId'] ?? null,
    'spouseId' => $data['spouseId'] ?? null,
    'createdAt' => $data['createdAt'] ?? date('Y-m-d H:i:s'),
    'updatedAt' => date('Y-m-d H:i:s')
];

// Update existing or add new
$found = false;
foreach ($members as $key => $existing) {
    if ($existing['id'] === $id) {
        $members[$key] = $member;
        $found = true;
        break;
    }
}

if (!$found) {
    $members[] = $member;
    logActivity('member_created', "Created member: {$member['firstName']} {$member['lastName']} (ID: {$id})");
} else {
    logActivity('member_updated', "Updated member: {$member['firstName']} {$member['lastName']} (ID: {$id})");
}

saveMembers($members);

echo json_encode(['success' => true, 'member' => $member]);
