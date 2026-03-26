<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

requireAdmin();

header('Content-Type: text/plain');

echo "=== Rebuilding Gallery Data ===\n\n";

$members = getMembers();
$galleryDir = '../assets/images/gallery/';

if (!file_exists($galleryDir)) {
    echo "Gallery directory does not exist!\n";
    exit;
}

$updatedCount = 0;

foreach ($members as &$member) {
    $memberId = $member['id'];
    $memberGalleryDir = $galleryDir . $memberId . '/';
    
    // Check if gallery folder exists for this member
    if (file_exists($memberGalleryDir) && is_dir($memberGalleryDir)) {
        $files = glob($memberGalleryDir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
        
        if (!empty($files)) {
            // Initialize gallery array if not exists
            if (!isset($member['gallery'])) {
                $member['gallery'] = [];
            }
            
            // Clear existing gallery and rebuild from files
            $member['gallery'] = [];
            
            foreach ($files as $file) {
                $webPath = 'assets/images/gallery/' . $memberId . '/' . basename($file);
                $member['gallery'][] = $webPath;
                echo "Added: {$webPath} for {$member['firstName']} {$member['lastName']}\n";
            }
            
            $updatedCount++;
            echo "→ {$member['firstName']} {$member['lastName']}: " . count($files) . " photo(s)\n\n";
        }
    }
}

// Save updated members
saveMembers($members);

echo "=== Done ===\n";
echo "Updated {$updatedCount} member(s) with gallery data.\n";
echo "Total members: " . count($members) . "\n";
