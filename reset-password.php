<?php
require_once 'includes/config.php';
require_once 'includes/logger.php';

$token = $_GET['token'] ?? '';
$message = '';
$message_type = '';
$reset_data = null;

if (!empty($token)) {
    $stmt = $conn->prepare("
        SELECT pr.*, u.user_id, u.name, u.email 
        FROM password_resets pr
        JOIN users u ON pr.user_id = u.user_id
        WHERE pr.token = ? AND pr.used = 0 AND pr.expires_at > NOW()
    ");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $reset_data = $result->fetch_assoc();
    $stmt->close();
    
    if (!$reset_data) {
        $message = "Invalid or expired reset link. Please request a new one.";
        $message_type = "error";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_token'])) {
    $token = $_POST['reset_token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($password) || empty($confirm_password)) {
        $message = "Please fill in all fields.";
        $message_type = "error";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $message_type = "error";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters.";
        $message_type = "error";
    } else {
        $stmt = $conn->prepare("
            SELECT pr.*, u.user_id 
            FROM password_resets pr
            JOIN users u ON pr.user_id = u.user_id
            WHERE pr.token = ? AND pr.used = 0 AND pr.expires_at > NOW()
        ");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $reset_data = $result->fetch_assoc();
        $stmt->close();
        
        if ($reset_data) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $update_stmt->bind_param("si", $hashed_password, $reset_data['user_id']);
            $update_stmt->execute();
            $update_stmt->close();
            
            $mark_stmt = $conn->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
            $mark_stmt->bind_param("s", $token);
            $mark_stmt->execute();
            $mark_stmt->close();
            
            logAction($reset_data['user_id'], 'Password Reset', 'Password reset successfully');
            
            $message = "✅ Password has been reset successfully! You can now login.";
            $message_type = "success";
        } else {
            $message = "Invalid or expired reset link.";
            $message_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password | Khela Hobee</title>
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
        <?php if ($message_type == 'success'): ?>
            <div style="background:#1a1a1a; border-radius:16px; padding:40px; text-align:center; border:1px solid #2a2a2a;">
                <div style="width:80px; height:80px; background:#1a3a2a; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
                    <i class="fas fa-check-circle" style="font-size:40px; color:#7CCB96;"></i>
                </div>
                <h2 style="color:#7CCB96; font-size:24px; margin-bottom:10px;">Password Reset!</h2>
                <p style="color:#bbb; font-size:15px;"><?php echo $message; ?></p>
                <a href="login.html" style="display:inline-block; background:#7CCB96; color:black; padding:12px 30px; border-radius:8px; text-decoration:none; font-weight:bold; margin-top:20px;">
                    <i class="fas fa-arrow-left"></i> Login Now
                </a>
            </div>
        <?php elseif ($message_type == 'error' && !$reset_data): ?>
            <div style="background:#1a1a1a; border-radius:16px; padding:40px; text-align:center; border:1px solid #2a2a2a;">
                <div style="font-size:60px; color:#ff6b6b;">❌</div>
                <h2 style="color:#ff6b6b; font-size:24px; margin-bottom:10px;">Invalid Link</h2>
                <p style="color:#bbb; font-size:15px;"><?php echo $message; ?></p>
                <a href="forgot-password.html" style="display:inline-block; background:#7CCB96; color:black; padding:12px 30px; border-radius:8px; text-decoration:none; font-weight:bold; margin-top:20px;">
                    <i class="fas fa-arrow-left"></i> Request New Link
                </a>
            </div>
        <?php elseif ($reset_data): ?>
            <div style="background:#1a1a1a; border-radius:16px; padding:40px; border:1px solid #2a2a2a;">
                <h2 style="color:#7CCB96; text-align:center; margin-bottom:10px;">🔑 Reset Password</h2>
                <p style="color:#bbb; text-align:center; margin-bottom:25px;">
                    Enter your new password for <strong><?php echo $reset_data['email']; ?></strong>
                </p>

                <?php if ($message): ?>
                    <div style="padding:15px; border-radius:8px; margin-bottom:15px; background:#3a1a1a; border:2px solid #ff6b6b; color:#ff6b6b;">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="reset_token" value="<?php echo $token; ?>">
                    <div class="form-group">
                        <input type="password" name="password" placeholder="New Password" required>
                    </div>
                    <div class="form-group">
                        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                    </div>
                    <button type="submit" class="submit-btn">Reset Password</button>
                </form>
            </div>
        <?php else: ?>
            <div style="background:#1a1a1a; border-radius:16px; padding:40px; text-align:center; border:1px solid #2a2a2a;">
                <div style="font-size:60px; color:#ff6b6b;">❌</div>
                <h2 style="color:#ff6b6b; font-size:24px; margin-bottom:10px;">Invalid Request</h2>
                <p style="color:#bbb; font-size:15px;">Please use the link from your email.</p>
                <a href="forgot-password.html" style="display:inline-block; background:#7CCB96; color:black; padding:12px 30px; border-radius:8px; text-decoration:none; font-weight:bold; margin-top:20px;">
                    <i class="fas fa-arrow-left"></i> Request New Link
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>