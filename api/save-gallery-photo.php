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

$memberId = $_POST['memberId'] ?? '';

if (empty($memberId)) {
    echo json_encode(['error' => 'Member ID is required']);
    exit;
}

if (!isset($_FILES['photo'])) {
    echo json_encode(['error' => 'No file uploaded']);
    exit;
}

$file = $_FILES['photo'];
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$maxSize = 10 * 1024 * 1024; // 10MB for gallery photos

if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['error' => 'Invalid file type. Only JPG, PNG, GIF, WebP allowed']);
    exit;
}

if ($file['size'] > $maxSize) {
    echo json_encode(['error' => 'File too large. Max 10MB']);
    exit;
}

// Create gallery directory for member
$uploadDir = "../assets/images/gallery/{$memberId}/";
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$filename = uniqid() . '_' . basename($file['name']);
$filepath = $uploadDir . $filename;

if (move_uploaded_file($file['tmp_name'], $filepath)) {
    $webPath = "assets/images/gallery/{$memberId}/" . $filename;
    
    // Update member's gallery in data
    $members = getMembers();
    $memberName = '';
    foreach ($members as &$member) {
        if ($member['id'] === $memberId) {
            if (!isset($member['gallery'])) {
                $member['gallery'] = [];
            }
            $member['gallery'][] = $webPath;
            $memberName = $member['firstName'] . ' ' . $member['lastName'];
            break;
        }
    }
    saveMembers($members);
    
    logActivity('gallery_photo_added', "Added gallery photo for {$memberName} (Member ID: {$memberId})");
    
    echo json_encode(['success' => true, 'path' => $webPath]);
} else {
    echo json_encode(['error' => 'Failed to upload file']);
}
