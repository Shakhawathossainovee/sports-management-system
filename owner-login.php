<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/logger.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("
        SELECT u.user_id, u.name, u.password, u.role, go.owner_id, go.verification_status
        FROM users u
        JOIN ground_owners go ON go.user_id = u.user_id
        WHERE u.email = ? AND u.role = 'owner'
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $owner = $result->fetch_assoc();
        if (password_verify($password, $owner['password'])) {
            $_SESSION['user_id'] = $owner['user_id'];
            $_SESSION['user_name'] = $owner['name'];
            $_SESSION['user_role'] = 'owner';
            $_SESSION['owner_id'] = $owner['owner_id'];

            logAction($owner['user_id'], 'Owner Login', 'Owner logged in successfully');

            header("Location: owner-dashboard.php");
            exit();
        } else {
            $error = "Invalid password. Please try again.";
            logAction(NULL, 'Owner Login Failed', 'Failed owner login attempt for email: ' . $email);
        }
    } else {
        $error = "No owner account found with this email.";
        logAction(NULL, 'Owner Login Failed', 'Failed owner login attempt for email: ' . $email);
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Owner Login | 🏆 KHELA HOBEE</title>
    <link rel="stylesheet" href="style.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <style>
        .form-box h2 {
            color: #7CCB96;
            font-size: 32px;
            margin-bottom: 4px;
        }

        .form-box p {
            color: #bdbdbd;
            margin-bottom: 32px;
            font-size: 14px;
        }

        .form-box .form-group {
            margin-bottom: 15px;
        }

        .form-box .login-link {
            margin-top: 18px;
            text-align: center;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
        }

        .form-box .login-link a {
            color: #7CCB96;
            font-weight: 500;
        }

        .form-box .login-link a:hover {
            text-decoration: underline;
        }

        .error-box {
            background: rgba(255,107,107,0.1);
            border: 1px solid rgba(255,107,107,0.3);
            color: #ff6b6b;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 18px;
        }

        @media (max-width: 480px) {
            .form-box h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>

<div class="home-page">

<header>
    <div class="container navbar">
        <div class="logo">
            <h2>
                <span class="logo-khela">Khela</span>
                <span class="logo-hobe">Hobe</span>
                <span class="logo-trophy">🏆</span>
            </h2>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
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

<section class="form-section">
    <div class="form-container">
        <div class="form-box">
            <h2>Owner Login</h2>
            <p>Manage your grounds and bookings.</p>

            <?php if ($error): ?>
                <div class="error-box"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="owner-login.php" method="POST">
                <div class="form-group">
                    <input type="email" name="email" placeholder="Email Address" required />
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" required />
                </div>
                <button type="submit" class="submit-btn">Login</button>
            </form>

            <div class="login-link">
                Don't have an account? <a href="register.html" >Register</a>
            </div>
        </div>

        <div class="hero-right">
            <img src="players.png" alt="Players" />
        </div>
    </div>
</section>

</div>

</body>
</html>