<?php
// Strengthen session security
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

session_start();

// Read POST data
$data = json_decode(file_get_contents("php://input"), true);

$inputOtp = $data["otp"] ?? '';
$email = $data["email"] ?? '';

// Log request input and session values
file_put_contents("debug_log.txt", "CHECK: inputOtp=$inputOtp, email=$email, session=" . print_r($_SESSION, true), FILE_APPEND);
file_put_contents("debug_log.txt", "COMPARING: inputOtp=" . $inputOtp . " vs storedOtp=" . ($_SESSION['otp'] ?? 'null') . " | email=" . $email . " vs storedEmail=" . ($_SESSION['otp_email'] ?? 'null') . "\n", FILE_APPEND);

header("Content-Type: application/json");

// Validate input
if (!$inputOtp || !$email) {
    echo json_encode([
        "success" => false,
        "message" => "Missing OTP or email."
    ]);
    exit;
}

// Check if OTP and email exist in session
if (!isset($_SESSION["otp"]) || !isset($_SESSION["otp_email"])) {
    echo json_encode([
        "success" => false,
        "message" => "Session expired. Please request OTP again."
    ]);
    exit;
}

// Get stored session values
$storedOtp = $_SESSION["otp"];
$storedEmail = $_SESSION["otp_email"];

// Use loose comparison for OTP and case-insensitive comparison for email
if ((string)$inputOtp == (string)$storedOtp && strtolower($email) === strtolower($storedEmail)) {
    // Clear OTP-related session data
    unset($_SESSION["otp"]);
    unset($_SESSION["otp_email"]);
    unset($_SESSION["otp_expiry"]);

    echo json_encode([
        "success" => true,
        "message" => "OTP verified successfully."
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid OTP or email."
    ]);
}
