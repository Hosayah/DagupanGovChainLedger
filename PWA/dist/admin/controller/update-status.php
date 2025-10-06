<?php
session_start();
include("../../../../config/config.php");

// Check if admin is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['account_type'] !== 'admin') {
    http_response_code(403);
    die("Access denied.");
}

$accessLevel = $_SESSION['user']['access_level'] ?? null;
if (!in_array($accessLevel, ['super_admin', 'review_admin'])) {
    http_response_code(403);
    die("You do not have permission to perform this action.");
}

// Validate input
if (!isset($_GET['id']) || !isset($_GET['action'])) {
    die("Invalid request.");
}

$user_id = intval($_GET['id']);
$action = $_GET['action'];

if (!in_array($action, ['approve', 'reject'])) {
    die("Invalid action.");
}

// Determine new status
$newStatus = $action === 'approve' ? 'approved' : 'rejected';

// Update query
$stmt = $conn->prepare("UPDATE users SET status = ? WHERE user_id = ?");
$stmt->bind_param("si", $newStatus, $user_id);

if ($stmt->execute()) {
    $_SESSION['flash'] = "✅ User #$user_id successfully $newStatus.";
} else {
    $_SESSION['flash'] = "❌ Failed to update user status.";
}

$stmt->close();
header("Location: ../account-approval.php");
exit;
