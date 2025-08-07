<?php


ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);
session_start(); // Start session to store OTP temporarily
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->email) || empty($data->email)) {
    echo json_encode(['success' => false, 'message' => 'Email is required']);
    exit;
}

$email = filter_var($data->email, FILTER_SANITIZE_EMAIL);

// Generate 6-digit OTP
$otp = random_int(100000, 999999);
$_SESSION['otp'] = $otp;
$_SESSION['otp_email'] = $email;
$_SESSION['otp_expiry'] = time() + 300; // valid for 5 minutes

file_put_contents("debug_log.txt", "SET: otp=" . $otp . ", email=" . $email);
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Your SMTP host
    $mail->SMTPAuth = true;
    $mail->Username = 'dumacc9000@gmail.com';
    $mail->Password = 'hsjc gogc zsgi smfv';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Recipients
    $mail->setFrom('dumacc9000@gmail.com', 'DICT Region 3 Office');
    $mail->addReplyTo('dumacc9000@gmail.com', 'No Reply');
    $mail->addAddress($email);


    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Your OTP Code';
    $mail->Body = '
<div style="font-family: Segoe UI, Roboto, Helvetica Neue, Arial, sans-serif; max-width: 600px; margin: auto; padding: 30px; border: 2px solid #eee; border-radius: 10px; background-color: #f9f9f9;">
    <div style="text-align: center;">
        <img src="https://raw.githubusercontent.com/jelodg9126/dictproj1/main/public/assets/images/dictStandard.png" alt="DICT Logo" style="max-width: 180px; margin-bottom: 10px;">
        <h2 style="color: #1a73e8; font-weight: 600; margin: 0;">DICT — One-Time Pin Verification</h2>
    </div>
    <p style="margin-top: 30px;">Dear Charo,</p>
    <p>Your One-Time Password (OTP) is:</p>
    <p style="font-size: 28px; font-weight: bold; color: #000000; text-align: center; margin: 20px 0;">' . $otp . '</p>
    <p style="color: #333;">This code is valid for <strong>5 minutes</strong>.</p>
    <p>If you did not request this, please ignore this message or contact support immediately.</p>
    <hr style="margin: 30px 0; border: none; border-top: 1px solid #ddd;">
    <p style="font-size: 12px; color: #888; text-align: center;">DICT Philippines &copy; ' . date("Y") . '. All rights reserved.</p>
    <p style="font-size: 12px; color: #999; text-align: center;">
        This is an automated message. Please do not reply to this email.
    </p>
</div>
';

    $mail->send();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $mail->ErrorInfo]);
}
