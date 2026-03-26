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
$memberId = $data['memberId'] ?? '';
$photoPath = $data['photoPath'] ?? '';

if (empty($memberId) || empty($photoPath)) {
    echo json_encode(['error' => 'Member ID and photo path are required']);
    exit;
}

// Delete file
$fullPath = '../' . $photoPath;
if (file_exists($fullPath)) {
    unlink($fullPath);
}

// Update member's gallery
$members = getMembers();
$memberName = '';
foreach ($members as &$member) {
    if ($member['id'] === $memberId && isset($member['gallery'])) {
        $member['gallery'] = array_filter($member['gallery'], function($path) use ($photoPath) {
            return $path !== $photoPath;
        });
        $member['gallery'] = array_values($member['gallery']);
        $memberName = $member['firstName'] . ' ' . $member['lastName'];
        break;
    }
}
saveMembers($members);

logActivity('gallery_photo_deleted', "Deleted gallery photo for {$memberName} (Member ID: {$memberId})");

echo json_encode(['success' => true]);
