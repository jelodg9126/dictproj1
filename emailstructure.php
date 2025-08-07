<?php
$otp = "123456";
$userName = "Juan Dela Cruz";

$mailBody = '
    <div style="font-family: Segoe UI, Roboto, Helvetica Neue, Arial, sans-serif; max-width: 600px; margin: auto; padding: 30px; border: 2px solid #eee; border-radius: 10px; background-color: #f9f9f9;">
        <div style="text-align: center;">
            <img src="/dictproj1/public/assets/images/dictStandard.png" alt="DICT Logo" style="max-width: 180px; margin-bottom: 20px;">
            <h2 style="color: #1a73e8;">DICT - One-Time Pin Verification</h2>
        </div>
        <p>Dear Charo,</p>
        <p>Your One-Time Password (OTP) is:</p>
        <p style="font-size: 28px; font-weight: bold; color: #000000ff; text-align: center; margin: 20px 0;">' . $otp . '</p>
        <p style="color: #333;">This code is valid for <strong>5 minutes</strong>.</p>
        <p>If you did not request this, please ignore this message or contact support immediately.</p>
        <hr style="margin: 30px 0;">
        <p style="font-size: 12px; color: #888; text-align: center;">DICT Philippines &copy; ' . date("Y") . '. All rights reserved.</p>
        <p style="font-size: 12px; color: #999; text-align: center;">
            This is an automated message. Please do not reply to this email.
        </p>
    </div>
';

echo $mailBody;
?>