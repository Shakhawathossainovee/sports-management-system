<?php
session_start();
session_destroy();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Logout | 🏆 KHELA HOBEE</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
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
                    <li><a href="index.html">Home</a></li>
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
        <div style="background:#1a1a1a; border-radius:16px; padding:50px 40px; text-align:center; border:1px solid #2a2a2a;">
            <div style="width:80px; height:80px; background:#1a1a2a; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
                <i class="fas fa-sign-out-alt" style="font-size:40px; color:#ffd93d;"></i>
            </div>
            <h2 style="color:#ffd93d; font-size:28px; margin-bottom:8px;">Logged Out</h2>
            <p style="color:#bbb; font-size:16px;">You have been successfully logged out.</p>
            <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap; margin-top:20px;">
                <a href="login.html" style="background:#7CCB96; color:black; padding:12px 28px; border-radius:8px; text-decoration:none; font-weight:bold;">
                    <i class="fas fa-sign-in-alt"></i> Login Again
                </a>
                <a href="index.html" style="background:#2a2a2a; color:white; padding:12px 28px; border-radius:8px; text-decoration:none;">
                    <i class="fas fa-home"></i> Home
                </a>
            </div>
        </div>
    </div>

    <!-- ===== PLAYER IMAGES ===== -->
    <div class="player-image-left">
        <img src="players.png" alt="Player" />
    </div>
    <div class="player-image-right">
        <img src="players.png" alt="Player" />
    </div>

</div>
</body>
</html>