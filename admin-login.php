<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/logger.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("
        SELECT u.user_id, u.name, u.email, u.password, u.role 
        FROM users u 
        WHERE u.email = ? AND u.role = 'admin'
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = 'admin';
            
            logAction($user['user_id'], 'Admin Login', 'Admin logged in successfully');
            
            header("Location: admin-dashboard.php");
            exit();
        } else {
            logAction(0, 'Admin Login Failed', 'Failed admin login attempt for email: ' . $email);
            $error = "Invalid password. Please try again.";
        }
    } else {
        logAction(0, 'Admin Login Failed', 'Failed admin login attempt for email: ' . $email);
        $error = "No admin account found with this email.";
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login | 🏆 KHELA HOBEE</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container navbar">
            <div class="logo"><h2>🏆 KHELA HOBEE</h2></div>
            <nav>
                <ul>
                    <li><a href="index.html" >Home</a></li>
                    <li><a href="search.php" >Turfs & Fields</a></li>
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
            <h2 style="color:#ff6b6b; margin:15px 0;">Admin Login Failed</h2>
            <p style="color:#bbb;"><?php echo $error ?? 'Please check your credentials.'; ?></p>
            <a href="admin-login.html" style="display:inline-block; background:#7CCB96; color:black; padding:12px 30px; border-radius:8px; text-decoration:none; font-weight:bold; margin-top:15px;">
                <i class="fas fa-arrow-left"></i> Try Again
            </a>
        </div>
    </div>
</body>
</html>

