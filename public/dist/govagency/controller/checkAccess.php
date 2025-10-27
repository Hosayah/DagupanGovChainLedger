<?php 
if (!isset($_SESSION['user']) || $_SESSION['user']['account_type'] !== 'agency') {
    http_response_code(403);
    header('Location: ../auth/login.php');
  }
