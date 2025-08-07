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
    $mail->setFrom('dumacc9000@gmail.com', 'Dummy Account');
    $mail->addAddress($email);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Your OTP Code';
    $mail->Body    = "
        <p>Dear user,</p>
        <p>Your One-Time Password (OTP) is:</p>
        <h2>$otp</h2>
        <p>This code is valid for 5 minutes.</p>
        <p>If you did not request this, please ignore this message.</p>
    ";
    $mail->send();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $mail->ErrorInfo]);
}
