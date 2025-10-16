<?php 
if (!isset($_SESSION['user']) || $_SESSION['user']['account_type'] !== 'auditor') {
    http_response_code(403);
    die("Access denied. Auditors only.");
  }
