<?php
include("../../../config/config.php");
include("../../../DAO/UserDao.php");

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? '');
    $password = trim($_POST["password"] ?? '');
    $repo = new UserDAO($conn);

    if (empty($email) || empty($password)) {
        $msg = "❌ Please fill in all fields.";
        return;
    }

    $user = $repo->findByEmail($email);
    if (!$user) {
        $msg = "❌ User not found.";
        return;
    }

    // Check status
    switch ($user['status']) {
        case 'pending':
            $msg = "⚠️ Your account is still pending approval.";
            return;
        case 'rejected':
            $msg = "❌ Your account was rejected.";
            return;
        case 'suspended':
            $msg = "⛔ Your account is suspended.";
            return;
    }

    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        $msg = "❌ Invalid password.";
        return;
    }

    // Create session
    $_SESSION['user'] = [
        'id' => $user['user_id'],
        'email' => $user['email'],
        'account_type' => $user['account_type'],
        'name' => $user['full_name'],
        'role' => $user['role'],
        'status' => $user['status']
    ];

    $accountType = $user['account_type'] ?? '';

    // If admin
    if ($accountType === 'admin') {
        $_SESSION['user']['access_level'] = $repo->getAdminAccessLevel($user['user_id']) ?? 'review_admin';
    }

    // If agency or auditor
    if (in_array($accountType, ['agency', 'auditor'])) {
        $_SESSION['user']['wallet_address'] = $repo->getWalletAddress($user['user_id'], $accountType);
    }

    // Redirect by account type
    $redirects = [
        'agency' => '../govagency/dashboard.php',
        'auditor' => '../auditor/dashboard.php',
        'admin' => '../admin/dashboard.php',
        'citizen' => '../citizen/dashboard.php'
    ];

    header("Location: " . ($redirects[$accountType] ?? '../citizen/dashboard.php'));
    exit;
}
