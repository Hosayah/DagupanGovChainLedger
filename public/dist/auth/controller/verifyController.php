<?php
include("../../../config/config.php");
include("../../../DAO/UserDao.php");
require_once("../../../utils/mailer.php");

$msg = '';
$step = 1; // Step 1 = enter email, Step 2 = enter OTP

$repo = new UserDAO($conn);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? '';

    // --- Step 1: Send OTP ---
    if ($action === "send_otp") {
        $email = trim($_POST["email"] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $msg = "❌ Please enter a valid email address.";
            return;
        }

        $user = $repo->findByEmail($email);

        if (!$user) {
            $msg = "❌ Email not found.";
            return;
        }

        if ($user['status'] !== 'approved') {
            $msg = "⚠️ Account is not active.";
            return;
        }

        // Generate OTP
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_email'] = $email;
        $_SESSION['otp_expiry'] = time() + 300; // 5 minutes

        if (send_otp($email, $otp)) {
            $msg = "✅ OTP sent to your email.";
            $step = 2;
        } else {
            $msg = "❌ Failed to send OTP. Please try again.";
        }
    }

    // --- Step 2: Verify OTP ---
    if ($action === "verify_otp") {
        $enteredOtp = trim($_POST["otp"] ?? '');

        if (empty($enteredOtp)) {
            $msg = "❌ Please enter your OTP.";
            $step = 2;
            return;
        }

        // Check OTP session
        if (!isset($_SESSION['otp'], $_SESSION['otp_email'], $_SESSION['otp_expiry'])) {
            $msg = "❌ No OTP request found. Please resend.";
            return;
        }

        if (time() > $_SESSION['otp_expiry']) {
            $msg = "⚠️ OTP expired. Please resend.";
            unset($_SESSION['otp'], $_SESSION['otp_email'], $_SESSION['otp_expiry']);
            return;
        }

        if ($enteredOtp == $_SESSION['otp']) {
            $_SESSION['verified_email'] = $_SESSION['otp_email'];

            unset($_SESSION['otp'], $_SESSION['otp_email'], $_SESSION['otp_expiry']);

            header("Location: ./forgotPassword.php");
            exit();
        } else {
            $msg = "❌ Invalid OTP.";
            $step = 2;
        }
    }
}
?>
