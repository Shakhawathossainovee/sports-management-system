<?php
// =============================================
// KHELA HOBEE - Email Sending Helper
// =============================================

require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// ===== CONFIGURATION =====
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'your_email@gmail.com');  // ← CHANGE THIS
define('SMTP_PASSWORD', 'your_app_password');     // ← CHANGE THIS
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('FROM_EMAIL', 'info@khelahobee.com');
define('FROM_NAME', 'Khela Hobee');

function sendEmail($to, $subject, $body, $is_html = true) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($to);
        $mail->addReplyTo(FROM_EMAIL, FROM_NAME);
        
        // Content
        $mail->isHTML($is_html);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);
        
        $mail->send();
        return ['success' => true, 'message' => 'Email sent successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $mail->ErrorInfo];
    }
}

// ===== TEMPLATES =====

function getBookingConfirmationEmail($name, $booking_ref, $ground_name, $date, $amount) {
    return "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #1a1a1a; color: #fff; padding: 30px; border-radius: 12px;'>
        <h2 style='color: #7CCB96;'>🏆 Khela Hobee</h2>
        <h3 style='color: #7CCB96;'>Booking Confirmation</h3>
        <p>Hello <strong>$name</strong>,</p>
        <p>Your booking has been <strong style='color: #7CCB96;'>confirmed</strong>!</p>
        <div style='background: #0b0b0b; padding: 15px; border-radius: 8px; margin: 15px 0;'>
            <p><strong>Booking Reference:</strong> $booking_ref</p>
            <p><strong>Ground:</strong> $ground_name</p>
            <p><strong>Amount:</strong> ৳$amount</p>
            <p><strong>Date:</strong> $date</p>
        </div>
        <p>Thank you for using Khela Hobee!</p>
        <p style='color: #888; font-size: 12px;'>© 2026 Khela Hobee</p>
    </div>
    ";
}

function getPasswordResetEmail($name, $reset_link) {
    return "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #1a1a1a; color: #fff; padding: 30px; border-radius: 12px;'>
        <h2 style='color: #7CCB96;'>🏆 Khela Hobee</h2>
        <h3 style='color: #7CCB96;'>Password Reset Request</h3>
        <p>Hello <strong>$name</strong>,</p>
        <p>We received a request to reset your password. Click the button below to reset it:</p>
        <p style='text-align: center;'>
            <a href='$reset_link' style='background: #7CCB96; color: #000; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: bold; display: inline-block;'>
                Reset Password
            </a>
        </p>
        <p style='color: #888; font-size: 14px;'>This link will expire in 1 hour.</p>
        <p>If you didn't request this, please ignore this email.</p>
        <p style='color: #888; font-size: 12px;'>© 2026 Khela Hobee</p>
    </div>
    ";
}

function getContactEmail($name, $email, $subject, $message) {
    return "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #1a1a1a; color: #fff; padding: 30px; border-radius: 12px;'>
        <h2 style='color: #7CCB96;'>📬 New Contact Message</h2>
        <p><strong>From:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Subject:</strong> $subject</p>
        <p><strong>Message:</strong></p>
        <p style='background: #0b0b0b; padding: 15px; border-radius: 8px;'>$message</p>
        <p style='color: #888; font-size: 12px;'>© 2026 Khela Hobee</p>
    </div>
    ";
}