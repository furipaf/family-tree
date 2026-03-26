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
    echo json_encode(['error' => 'Member ID is required']);
    exit;
}

$members = getMembers();
$initialCount = count($members);

// Remove member and update children to remove parent reference
$members = array_filter($members, function($member) use ($id) {
    return $member['id'] !== $id;
});

// Update children to remove this parent
foreach ($members as &$member) {
    if ($member['parentId'] === $id) {
        $member['parentId'] = null;
    }
    if ($member['spouseId'] === $id) {
        $member['spouseId'] = null;
    }
}

$members = array_values($members); // Re-index array

if (count($members) < $initialCount) {
    saveMembers($members);
    logActivity('member_deleted', "Deleted member ID: {$id}");
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Member not found']);
}
