<?php
// =============================================
// KHELA HOBEE - Email Test Script
// =============================================

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Load PHPMailer
require_once 'includes/mailer.php';

// Send test email
$result = sendEmail(
    'ggp9073@gmail.com',  // ← Change this to your email
    'PHP Mail Test Script',
    '<h1>✅ Test Successful!</h1>
    <p>If you are reading this, your PHP mail configuration is working perfectly.</p>
    <p>Sent via Gmail SMTP with PHPMailer.</p>
    <p><strong>Time:</strong> ' . date('Y-m-d H:i:s') . '</p>'
);

// Display result
if ($result['success']) {
    echo "<h3>✅ Success! The email was sent successfully.</h3>";
    echo "<p>Check your Gmail inbox: ggp9073@gmail.com</p>";
} else {
    echo "<h3>❌ Error! The email could not be sent.</h3>";
    echo "<p>Error: " . $result['message'] . "</p>";
}
?>