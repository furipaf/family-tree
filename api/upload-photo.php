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

if (!isset($_FILES['photo'])) {
    echo json_encode(['error' => 'No file uploaded']);
    exit;
}

$file = $_FILES['photo'];
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$maxSize = 5 * 1024 * 1024; // 5MB

if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['error' => 'Invalid file type. Only JPG, PNG, GIF, WebP allowed']);
    exit;
}

if ($file['size'] > $maxSize) {
    echo json_encode(['error' => 'File too large. Max 5MB']);
    exit;
}

$uploadDir = '../assets/images/members/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$filename = uniqid() . '_' . basename($file['name']);
$filepath = $uploadDir . $filename;

if (move_uploaded_file($file['tmp_name'], $filepath)) {
    $webPath = 'assets/images/members/' . $filename;
    echo json_encode(['success' => true, 'path' => $webPath]);
} else {
    echo json_encode(['error' => 'Failed to upload file']);
}
