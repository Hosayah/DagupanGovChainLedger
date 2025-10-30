<?php
include("../../../config/config.php");
include("../../../DAO/UserDao.php");
include("../../../services/blockchain.php");

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $repo = new UserDAO($conn);

    // --- Sanitize inputs ---
    $accountType    = trim($_POST["user_type"] ?? '');
    $email          = trim($_POST["email"] ?? '');
    $password       = trim($_POST["password"] ?? '');
    $confirm        = trim($_POST["confirm"] ?? '');
    $name           = trim(preg_replace('/\s+/', ' ', $_POST["name"] ?? ''));
    $fullName       = trim(preg_replace('/\s+/', ' ', $_POST["fullName"] ?? '')); 
    $contact        = trim(preg_replace('/\s+/', ' ', $_POST["contact"] ?? ''));
    $officeCode     = trim($_POST["officeCode"] ?? '');
    $position       = trim($_POST["position"] ?? '');
    $govId          = trim($_POST["govId"] ?? '');
    $accreditation  = trim($_POST["accreditation"] ?? '');
    $walletAddress  = trim($_POST["wallet"] ?? '');

    // --- Basic validation ---
    if (empty($accountType) || empty($email) || empty($password) || empty($confirm) || empty($fullName)) {
        $msg = "❌ Please fill in all required fields.";
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "❌ Invalid email address.";
        return;
    }

    if ($password !== $confirm) {
        $msg = "❌ Passwords do not match.";
        return;
    }

    if (strlen($password) < 6 || preg_match('/\s+/', $password)) {
        $msg = "❌ Password must be at least 6 characters and contain no spaces.";
        return;
    }

    if (!empty($contact) && !preg_match('/^(09|\+639)\d{9}$/', $contact)) {
        $msg = "❌ Please enter a valid PH contact number.";
        return;
    }

    // --- Check if user already exists ---
    if ($repo->findByEmail($email)) {
        $msg = "⚠️ Email already registered.";
        return;
    }

    // --- Blockchain wallet validation ---
    if (!empty($walletAddress)) {
        $balance = getBalance($web3, $walletAddress);
        $hasGovRole = hasRole($contract, $govRole, $walletAddress);
        $hasAuditorRole = hasRole($contract, $auditorRole, $walletAddress);

        if ($hasGovRole || $hasAuditorRole) {
            $msg = "❌ Wallet address already registered.";
            return;
        }

        if (empty($balance)) {
            $msg = "❌ Wallet must have at least 1 ETH." . $balance;
            return;
        }
    }

    // --- Prepare data ---
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // --- Insert user (via DAO) ---
    $userId = $repo->insertUser([
        'account_type' => $accountType,
        'email' => $email,
        'password_hash' => $hashedPassword,
        'full_name' => $fullName, // ✅ FIXED HERE
        'contact_number' => $contact
    ]);

    if (!$userId) {
        $msg = "❌ Failed to register user.";
        return;
    }

    // --- Role-specific inserts ---
    switch ($accountType) {
        case 'agency':
            $repo->insertAgency($userId, [
                'agency_name' => $name,
                'office_code' => $officeCode,
                'position' => $position,
                'gov_id_number' => $govId,
                'wallet_address' => $walletAddress
            ]);
            $msg = "✅ Agency registered successfully. Awaiting admin approval.";
            break;

        case 'auditor':
            $repo->insertAuditor($userId, [
                'organization_name' => $name,
                'office_code' => $officeCode,
                'accreditation_number' => $accreditation,
                'wallet_address' => $walletAddress
            ]);
            $msg = "✅ Auditor registered successfully. Awaiting admin approval.";
            break;

        case 'citizen':
            $msg = "✅ Citizen registered successfully.";
            break;

        default:
            $msg = "✅ User registered.";
    }

    echo "<script>alert('$msg'); window.location='./login.php';</script>";
    exit;
}

?>
