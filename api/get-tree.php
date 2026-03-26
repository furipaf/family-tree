<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

$members = getMembers();

// Build tree structure supporting both father (parentId) and mother (motherId)
function buildTree($members, $parentId = null, $excludeIds = []) {
    $branch = [];
    
    // Build spouse lookup - who is spouse of whom
    $spouseMap = []; // personId => [spouseIds]
    foreach ($members as $m) {
        if ($m['spouseId']) {
            if (!isset($spouseMap[$m['id']])) {
                $spouseMap[$m['id']] = [];
            }
            if (!in_array($m['spouseId'], $spouseMap[$m['id']])) {
                $spouseMap[$m['id']][] = $m['spouseId'];
            }
        }
    }
    
    // Collect all spouse IDs that should be excluded from root level
    $allSpouseIds = [];
    foreach ($spouseMap as $personId => $spouses) {
        foreach ($spouses as $spouseId) {
            if (!in_array($spouseId, $allSpouseIds)) {
                $allSpouseIds[] = $spouseId;
            }
        }
    }
    
    foreach ($members as $member) {
        // Skip if this person is in the exclude list (they're someone's spouse)
        if (in_array($member['id'], $excludeIds)) {
            continue;
        }
        
        // At root level (parentId = null), exclude anyone who is a spouse
        // unless they also have children (they're the "main" person)
        if ($parentId === null && in_array($member['id'], $allSpouseIds)) {
            // Check if this person has children - if so, they're the main person
            $hasChildren = false;
            foreach ($members as $m) {
                if ($m['parentId'] === $member['id'] || $m['motherId'] === $member['id']) {
                    $hasChildren = true;
                    break;
                }
            }
            // If no children, they're just a spouse - skip them at root level
            if (!$hasChildren) {
                continue;
            }
        }
        
        // Check if this member belongs to this parent (father)
        if ($member['parentId'] === $parentId) {
            // Get children - check both parentId (father) and motherId
            $children = buildTree($members, $member['id'], $excludeIds);
            if ($children) {
                $member['children'] = $children;
            }
            
            // Add ALL spouses info if exists (support multiple spouses)
            if (isset($spouseMap[$member['id']]) && !empty($spouseMap[$member['id']])) {
                $member['spouses'] = [];
                foreach ($spouseMap[$member['id']] as $spouseId) {
                    foreach ($members as $spouse) {
                        if ($spouse['id'] === $spouseId) {
                            $member['spouses'][] = $spouse;
                            if (!in_array($spouseId, $excludeIds)) {
                                $excludeIds[] = $spouseId;
                            }
                            break;
                        }
                    }
                }
            }
            
            $branch[] = $member;
        }
    }
    
    return $branch;
}

$tree = buildTree($members);

echo json_encode([
    'success' => true,
    'tree' => $tree,
    'members' => $members
]);
