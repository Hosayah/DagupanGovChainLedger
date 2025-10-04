<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "govledger";

$conn = mysqli_connect($host, $user, $pass, $db);

if (mysqli_connect_errno()) {
    echo "Connection failed". mysqli_connect_error();
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connection Success";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>