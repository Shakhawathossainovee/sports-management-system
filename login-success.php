<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_name = $_SESSION['user_name'] ?? 'User';
$user_role = $_SESSION['user_role'] ?? 'player';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>🏆 Khela Hobe | Welcome</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Russo+One&family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
    <style>
        .result-box {
            text-align: center;
            max-width: 480px;
            width: 100%;
            background: rgba(11, 11, 11, 0.82);
            padding: 40px 35px;
            border-radius: 16px;
            border: 1px solid rgba(124, 203, 150, 0.12);
            backdrop-filter: blur(3px);
        }
        .result-icon {
            font-size: 72px;
            margin-bottom: 15px;
            color: #7CCB96;
            animation: popIn 0.5s ease-out;
        }
        .result-box h2 {
            color: #7CCB96;
            font-size: 32px;
            margin-bottom: 10px;
            font-family: 'Russo One', sans-serif;
        }
        .result-box p {
            color: #bdbdbd;
            font-size: 16px;
            margin-bottom: 8px;
            line-height: 1.6;
        }
        .result-box p strong {
            color: #7CCB96;
            font-weight: 600;
        }
        .result-box .sub-text {
            color: #888;
            font-size: 14px;
            margin-bottom: 25px;
        }
        .result-box .home-btn {
            display: inline-block;
            background: #7CCB96;
            color: #000;
            padding: 12px 35px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 14px;
            text-decoration: none;
            transition: 0.3s;
            border: none;
            cursor: pointer;
        }
        .result-box .home-btn:hover {
            background: #5a9e7a;
            transform: translateY(-2px);
        }
        .result-box .login-link {
            margin-top: 15px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.5);
        }
        .result-box .login-link a {
            color: #7CCB96;
            text-decoration: none;
        }
        .result-box .login-link a:hover {
            text-decoration: underline;
        }
        .spinner {
            display: inline-block;
            width: 30px;
            height: 30px;
            border: 4px solid rgba(124, 203, 150, 0.2);
            border-top: 4px solid #7CCB96;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-top: 15px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
            .result-box {
                padding: 25px 20px;
            }
            .result-box h2 {
                font-size: 24px;
            }
            .hero-right img {
                width: 80%;
            }
        }
    </style>
    <!-- Auto-redirect after 3 seconds -->
    <meta http-equiv="refresh" content="3;url=<?php 
        if ($user_role == 'admin') {
            echo 'admin-dashboard.php';
        } elseif ($user_role == 'owner') {
            echo 'owner-dashboard.php';
        } else {
            echo 'search.php';
        }
    ?>">
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
                <a href="logout.php" class="login-btn" style="background:#ff6b6b !important;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </header>

    <!-- ===== SUCCESS MESSAGE + PLAYER IMAGE ===== -->
    <section class="form-section">
        <div class="form-container">

            <!-- LEFT: SUCCESS MESSAGE -->
            <div class="result-box">
                <div class="result-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2>Welcome Back!</h2>
                <p>
                    Hello, <strong><?php echo htmlspecialchars($user_name); ?></strong>!
                </p>
                <p class="sub-text">
                    You are being redirected...
                </p>
                <div class="spinner"></div>
                <div style="margin-top:20px;">
                    <a href="index.html" class="home-btn">
                        <i class="fas fa-arrow-left"></i> Back to Home
                    </a>
                </div>
                <div class="login-link">
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
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