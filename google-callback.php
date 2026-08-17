<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/logger.php';

// =============================================
// GOOGLE LOGIN - Callback Handler
// =============================================

// Google OAuth Configuration
$client_id = 'YOUR_GOOGLE_CLIENT_ID';
$client_secret = 'YOUR_GOOGLE_CLIENT_SECRET';$client_secret = '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';
$redirect_uri = 'http://localhost/khela-hobee/google-callback.php';

$error = '';

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    // Exchange code for access token
    $token_url = 'https://oauth2.googleapis.com/token';
    $post_data = [
        'code' => $code,
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri' => $redirect_uri,
        'grant_type' => 'authorization_code'
    ];
    
    $ch = curl_init($token_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $token_data = json_decode($response, true);
    
    if (isset($token_data['access_token'])) {
        // Get user info
        $userinfo_url = 'https://www.googleapis.com/oauth2/v2/userinfo';
        $ch = curl_init($userinfo_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token_data['access_token']]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $userinfo_response = curl_exec($ch);
        curl_close($ch);
        
        $userinfo = json_decode($userinfo_response, true);
        
        if (isset($userinfo['email'])) {
            // Check if user exists
            $stmt = $conn->prepare("SELECT user_id, name, email, role FROM users WHERE email = ?");
            $stmt->bind_param("s", $userinfo['email']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // User exists — login
                $user = $result->fetch_assoc();
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                
                logAction($user['user_id'], 'Google Login', 'User logged in via Google');
                
                header("Location: search.php");
                exit();
            } else {
                // User doesn't exist — create new account
                $name = $userinfo['name'] ?? $userinfo['email'];
                $email = $userinfo['email'];
                $password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
                
                $insert_stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'player')");
                $insert_stmt->bind_param("sss", $name, $email, $password);
                $insert_stmt->execute();
                $user_id = $conn->insert_id;
                $insert_stmt->close();
                
                // Create player record
                $player_stmt = $conn->prepare("INSERT INTO players (user_id) VALUES (?)");
                $player_stmt->bind_param("i", $user_id);
                $player_stmt->execute();
                $player_stmt->close();
                
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_role'] = 'player';
                
                logAction($user_id, 'Google Register', 'New user registered via Google');
                
                header("Location: search.php");
                exit();
            }
        } else {
            $error = "Could not fetch user information.";
        }
    } else {
        $error = "Failed to authenticate with Google.";
    }
} else {
    $error = "No authorization code received.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Google Login | Khela Hobee</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container navbar">
            <div class="logo"><h2>🏆 KHELA HOBEE</h2></div>
            <nav>
                <ul>
                    <li><a href="index.html">Home</a></li>
                    <li><a href="search.php">Turfs & Fields</a></li>
                    <li><a href="privacy.html">Privacy Policy</a></li>
                    <li><a href="terms.html">Terms & Conditions</a></li>
                    <li><a href="contact.html">Contact Us</a></li>
                    <li><a href="about.html">About Us</a></li>
                </ul>
            </nav>
            <div class="nav-btn">
                <a href="login.html" class="login-btn">Login</a>
                <a href="register.html" class="register-btn">Register</a>
            </div>
        </div>
    </header>

    <div style="max-width:500px; margin:60px auto; padding:0 20px;">
        <div style="background:#1a1a1a; border-radius:16px; padding:40px; text-align:center; border:1px solid #2a2a2a;">
            <div style="font-size:60px; color:#ff6b6b;">❌</div>
            <h2 style="color:#ff6b6b; font-size:24px; margin-bottom:10px;">Google Login Failed</h2>
            <p style="color:#bbb; font-size:15px;"><?php echo $error ?? 'Something went wrong.'; ?></p>
            <a href="login.html" style="display:inline-block; background:#7CCB96; color:black; padding:12px 30px; border-radius:8px; text-decoration:none; font-weight:bold; margin-top:20px;">
                <i class="fas fa-arrow-left"></i> Back to Login
            </a>
        </div>
    </div>
</body>
</html>