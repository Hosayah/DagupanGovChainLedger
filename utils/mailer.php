<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//include("../../../vendor/phpmailer/phpmailer/src/Exception.php");
require "../../../vendor/phpmailer/phpmailer/src/Exception.php";
require '../../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../../../vendor/phpmailer/phpmailer/src/SMTP.php';

// xsmtpsib-a79335d8c8a5cbd5f752f751c5884b1749addb8746ebde7e0597649d12402ea1-NlOE0w4B3V9PwaEO

function send_otp($toEmail, $otp) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'dagupangovledger@gmail.com';  // your gmail
        $mail->Password   = 'mvwl zkyw tqlx sszh';     // your app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('dagupangovledger@gmail.com', 'Dagupan GovChain Support');
        $mail->addAddress($toEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Password Reset OTP';
        $mail->Body    = "
            <h3>Password Reset Request</h3>
            <p>Your OTP code is: <b>$otp</b></p>
            <p>This code will expire in 5 minutes.</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        echo "<pre>Mailer Error: " . htmlspecialchars($mail->ErrorInfo) . "</pre>";
        return false;
    }
}
