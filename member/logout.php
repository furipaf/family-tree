<?php
require_once '../includes/config.php';

$email = $_SESSION['member_email'] ?? 'unknown';
logActivity('member_logout', "Member logged out: {$email}");

session_destroy();
header('Location: ../index.php');
exit;
