<?php
session_start();
include("config.php");
include("../DAO/UserDao.php");

$user_id = 7;


$dao = new UserDAO($conn);
// Get user by ID
$user = $dao->getUserById($user_id);
// Determnine user account_type
if (!$user) {
    die("Not found");
} else {
    echo "<h3>User Details</h3>";
    echo "ID: " . htmlspecialchars($user['user_id']) . "<br>";
    echo "Name: " . htmlspecialchars($user['full_name']) . "<br>";
    echo "Email: " . htmlspecialchars($user['email']) . "<br>";
    echo "Account Type: " . htmlspecialchars($user['account_type']) . "<br>";
}
// Update query

?>