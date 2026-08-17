<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/logger.php';

// =============================================
// GOOGLE LOGIN - Initiate OAuth Flow
// =============================================

// Google OAuth Configuration
$client_id = '675590546313-fjmtjoddro2ol2nkk1cvtkjrcv5125re.apps.googleusercontent.com';
$redirect_uri = 'http://localhost/khela-hobee/google-callback.php';

// Build Google OAuth URL
$auth_url = 'https://accounts.google.com/o/oauth2/auth?' . http_build_query([
    'client_id' => $client_id,
    'redirect_uri' => $redirect_uri,
    'response_type' => 'code',
    'scope' => 'email profile',
    'access_type' => 'online'
]);

// Log the attempt
logAction(NULL, 'Google Login Attempt', 'User initiating Google OAuth login');

// Redirect to Google
header("Location: " . $auth_url);
exit();
?>