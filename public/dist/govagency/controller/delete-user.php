<?php
session_start();
include("../../../../config/config.php");
include("../../../../DAO/UserDao.php");

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

if (!$action == 'delete') {
    die("Invalid action.");
}

$dao = new UserDAO($conn);
// Get user by ID
$user = $dao->getUserById($user_id);
// Determnine user account_type
if (!$user) {
    die("Not found");
} else {
    $type = $user['account_type'];
}
// Get table Name
$tableName = ($type === 'admin') ? 'admins' : (($type === 'agency') ? 'agencies': 'auditors');

// Update query
$stmt = $conn->prepare("DELETE FROM $tableName WHERE user_id = ?");
$stmt->bind_param("i", $user_id);


if ($stmt->execute()) {
    $_SESSION['flash'] = "✅ User #$user_id Deleted to $tableName.";
    $dao->deleteUserById($user_id);
} else {
    $_SESSION['flash'] = "❌ Failed to update user status.";
}

$stmt->close();
$location = $tableName === 'admins' ? '../remove-admins.php' : '../rejected-accounts.php';
header("Location: $location");
exit;
