<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

$code = $_GET['code'] ?? '';

if (empty($code)) {
    echo json_encode(['error' => 'Invite code required']);
    exit;
}

$invites = json_decode(file_get_contents(INVITES_FILE), true) ?: [];

foreach ($invites as $invite) {
    if ($invite['code'] === $code) {
        if ($invite['used']) {
            echo json_encode(['error' => 'Invite already used']);
        } else {
            echo json_encode(['success' => true, 'invite' => $invite]);
        }
        exit;
    }
}

echo json_encode(['error' => 'Invalid invite code']);
