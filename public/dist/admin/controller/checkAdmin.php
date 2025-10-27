<?php 
if (!isset($_SESSION['user']) || $_SESSION['user']['account_type'] !== 'admin') {
    http_response_code(403);
    header('Location: ../auth/login.php');
  }
// Check access level
$accessLevel = $_SESSION['user']['access_level'] ?? null;
if (!in_array($accessLevel, ['super_admin', 'review_admin'])) {
    http_response_code(403);
    header('Location: ../auth/login.php');
}