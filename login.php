<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/logger.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            
            logAction($user['user_id'], 'Login', 'User logged in successfully');
            
            // ===== REDIRECT TO SUCCESS PAGE =====
            header("Location: login-success.php");
            exit();
        } else {
            $error = "Invalid password. Please try again.";
            logAction(NULL, 'Login Failed', 'Failed login attempt for email: ' . $email);
        }
    } else {
        $error = "No account found with this email.";
        logAction(NULL, 'Login Failed', 'Failed login attempt for email: ' . $email);
    }
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>🏆 Khela Hobe | Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Russo+One&family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
    <style>
        .result-box {
            text-align: center;
        }
        .result-icon {
            font-size: 72px;
            margin-bottom: 15px;
            animation: popIn 0.5s ease-out;
        }
        .result-icon.success {
            color: #7CCB96;
        }
        .result-icon.error {
            color: #ff6b6b;
        }
        .result-box h2 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        .result-box h2.success {
            color: #7CCB96;
        }
        .result-box h2.error {
            color: #ff6b6b;
        }
        .result-box p {
            color: #bdbdbd;
            font-size: 16px;
            margin-bottom: 25px;
            line-height: 1.6;
        }
        .result-box p strong {
            color: #7CCB96;
            font-weight: 600;
        }
        .result-box .submit-btn {
            display: inline-block;
            text-align: center;
            text-decoration: none;
            width: auto;
            padding: 12px 40px;
            background: #7CCB96;
            color: #000;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: 0.3s;
        }
        .result-box .submit-btn:hover {
            background: #5a9e7a;
            transform: translateY(-2px);
        }
        @keyframes popIn {
            0% { transform: scale(0.5); opacity: 0; }
            70% { transform: scale(1.1); }
            100% { transform: scale(1); opacity: 1; }
        }
        .form-section {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 20px;
        }
        .form-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            align-items: center;
            gap: 60px;
            max-width: 1100px;
            width: 100%;
        }
        .form-box {
            max-width: 480px;
            width: 100%;
            background: rgba(11, 11, 11, 0.82);
            padding: 40px 35px;
            border-radius: 16px;
            border: 1px solid rgba(124, 203, 150, 0.12);
            backdrop-filter: blur(3px);
        }
        .hero-right {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .hero-right img {
            width: 85%;
            max-width: 480px;
            border-radius: 16px;
            filter: drop-shadow(0 20px 50px rgba(0, 0, 0, 0.5));
        }
        .login-link {
            margin-top: 15px;
            text-align: center;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.5);
        }
        .login-link a {
            color: #7CCB96;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .form-container {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            .hero-right {
                order: -1;
            }
            .hero-right img {
                width: 60%;
            }
        }
        @media (max-width: 480px) {
            .form-box {
                padding: 25px 20px;
            }
            .form-box h2 {
                font-size: 24px;
            }
            .hero-right img {
                width: 80%;
            }
        }
    </style>
</head>
<body>

<div class="home-page">

    <!-- ===== NAVBAR ===== -->
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

    <!-- ===== RESULT MESSAGE + PLAYER IMAGE ===== -->
    <section class="form-section">
        <div class="form-container">

            <!-- LEFT: RESULT MESSAGE -->
            <div class="form-box result-box">
                <?php if (isset($success)): ?>
                    <div class="result-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h2 class="success">Welcome Back!</h2>
                    <p>
                        Hello, <strong><?php echo htmlspecialchars($name); ?></strong>!<br>
                        You are being redirected...
                    </p>
                <?php elseif (isset($error)): ?>
                    <div class="result-icon error">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <h2 class="error">Login Failed</h2>
                    <p><?php echo htmlspecialchars($error); ?></p>
                    <a href="login.html" class="submit-btn">
                        <i class="fas fa-arrow-left"></i> Try Again
                    </a>
                <?php else: ?>
                    <div class="result-icon error">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <h2 class="error">Something Went Wrong</h2>
                    <p>Please try again later.</p>
                    <a href="login.html" class="submit-btn">
                        <i class="fas fa-arrow-left"></i> Go Back
                    </a>
                <?php endif; ?>
                <div class="login-link">
                    <a href="index.html">← Back to Home</a>
                </div>
            </div>

            <!-- RIGHT: PLAYER IMAGE -->
            <div class="hero-right">
                <img src="players.png" alt="Player" />
            </div>

        </div>
    </section>

</div>

</body>
</html>