<?php
if (!isset($_SESSION['user'])) {
    header("Location: ../../dist/auth/login.php");
    exit;
} 