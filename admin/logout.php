<?php
require_once '../includes/auth.php';

$username = $_SESSION['admin_username'] ?? 'unknown';
logActivity('admin_logout', "Admin logged out: {$username}");

logout();
