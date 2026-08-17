<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/logger.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $msg = trim($_POST['message'] ?? '');
    $user_id = $_SESSION['user_id'] ?? NULL;
    
    $errors = [];
    if (empty($name)) $errors[] = "Name is required.";
    if (empty($email)) $errors[] = "Email is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
    if (empty($subject)) $errors[] = "Subject is required.";
    if (empty($msg)) $errors[] = "Message is required.";
    
    if (empty($errors)) {
        $insert_stmt = $conn->prepare("INSERT INTO contacts (name, email, subject, message, user_id) VALUES (?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("ssssi", $name, $email, $subject, $msg, $user_id);
        
        if ($insert_stmt->execute()) {
            logAction($user_id, 'Contact Form', "Message from $name ($email): $subject");
            $message = "Your message has been sent successfully!";
            $message_type = "success";
        } else {
            $message = "Failed to send message. Please try again.";
            $message_type = "error";
        }
        $insert_stmt->close();
    } else {
        $message = implode("<br>", $errors);
        $message_type = "error";
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>🏆 Khela Hobe | Contact</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Russo+One&family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
    <style>
        /* ===== PLAYER IMAGES - BOTH SIDES, FULL IMAGE (NO CROPPING) ===== */
        .player-image-left,
        .player-image-right {
            position: fixed;
            bottom: 0;
            z-index: 1;
            width: 220px;
            height: auto;
            pointer-events: none;
            animation: floatPlayer 4s ease-in-out infinite;
        }

        .player-image-left {
            left: 10px;
        }

        .player-image-right {
            right: 10px;
            animation-delay: 2s;
        }

        .player-image-left img,
        .player-image-right img {
            width: 100%;
            height: auto;
            display: block;
            filter: drop-shadow(0 10px 30px rgba(0, 0, 0, 0.6));
            border-radius: 12px;
        }

        @keyframes floatPlayer {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        /* ===== DIALOG BOX - 1 INCH DOWN ===== */
        .contact-wrapper {
            flex: 1;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 40px 20px 100px;
            min-height: 80vh;
            margin-top: 20px;
        }

        .contact-box {
            max-width: 550px;
            width: 100%;
            background: rgba(11, 11, 11, 0.88);
            padding: 50px 40px;
            border-radius: 20px;
            border: 1px solid rgba(124, 203, 150, 0.15);
            backdrop-filter: blur(10px);
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            position: relative;
            z-index: 2;
        }

        /* ===== FORM STYLES ===== */
        .contact-box .form-title {
            color: #7CCB96;
            font-size: 30px;
            margin-bottom: 4px;
            font-family: 'Russo One', sans-serif;
        }

        .contact-box .sub-title {
            color: #888;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .contact-box .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        .contact-box .form-group input,
        .contact-box .form-group textarea {
            width: 100%;
            padding: 14px 18px;
            border: none;
            outline: none;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.05);
            color: white;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            transition: 0.3s;
            border: 1px solid rgba(255, 255, 255, 0.06);
        }

        .contact-box .form-group input::placeholder,
        .contact-box .form-group textarea::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        .contact-box .form-group input:focus,
        .contact-box .form-group textarea:focus {
            border-color: #7CCB96;
            background: rgba(255, 255, 255, 0.08);
        }

        .contact-box .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .contact-box .submit-btn {
            width: 100%;
            padding: 16px;
            border: none;
            background: #7CCB96;
            color: #000;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            font-family: 'Poppins', sans-serif;
        }

        .contact-box .submit-btn:hover {
            transform: translateY(-2px);
            background: #5a9e7a;
            box-shadow: 0 8px 25px rgba(124, 203, 150, 0.25);
        }

        .contact-box .submit-btn i {
            margin-right: 8px;
        }

        .contact-box .contact-info {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 10px;
            color: #888;
            font-size: 12px;
        }

        .contact-box .contact-info i {
            color: #7CCB96;
            margin-right: 5px;
        }

        /* ===== SUCCESS MESSAGE ===== */
        .contact-box .result-icon {
            width: 100px;
            height: 100px;
            background: rgba(124, 203, 150, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            border: 2px solid rgba(124, 203, 150, 0.2);
        }

        .contact-box .result-icon i {
            font-size: 48px;
            color: #7CCB96;
        }

        .contact-box .result-icon.error {
            border-color: rgba(255, 107, 107, 0.2);
            background: rgba(255, 107, 107, 0.1);
        }

        .contact-box .result-icon.error i {
            color: #ff6b6b;
        }

        .contact-box .result-title {
            color: #7CCB96;
            font-size: 32px;
            margin-bottom: 8px;
            font-family: 'Russo One', sans-serif;
        }

        .contact-box .result-title.error {
            color: #ff6b6b;
        }

        .contact-box .result-message {
            color: #ddd;
            font-size: 16px;
            margin-bottom: 8px;
            line-height: 1.7;
        }

        .contact-box .result-sub {
            color: #888;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .contact-box .btn-group {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .contact-box .btn-group a {
            display: inline-block;
            padding: 12px 30px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            transition: 0.3s;
        }

        .contact-box .btn-group .btn-primary {
            background: #7CCB96;
            color: #000;
        }

        .contact-box .btn-group .btn-primary:hover {
            background: #5a9e7a;
            transform: translateY(-2px);
        }

        .contact-box .btn-group .btn-secondary {
            background: rgba(255, 255, 255, 0.06);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .contact-box .btn-group .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.12);
        }

        /* ===== NUMBER "10" AT BOTTOM RIGHT ===== */
        .bottom-number {
            position: fixed;
            bottom: 15px;
            right: 30px;
            z-index: 99;
            font-size: 80px;
            font-weight: 900;
            color: rgba(124, 203, 150, 0.08);
            font-family: 'Russo One', sans-serif;
            letter-spacing: -5px;
            user-select: none;
            pointer-events: none;
            line-height: 1;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 992px) {
            .player-image-left,
            .player-image-right {
                width: 160px;
            }
        }

        @media (max-width: 768px) {
            .player-image-left,
            .player-image-right {
                width: 120px;
            }
            .contact-wrapper {
                padding: 20px 15px 80px;
                margin-top: 10px;
            }
            .contact-box {
                padding: 30px 25px;
            }
            .bottom-number {
                font-size: 50px;
                bottom: 10px;
                right: 20px;
            }
        }

        @media (max-width: 480px) {
            .player-image-left,
            .player-image-right {
                width: 80px;
            }
            .contact-wrapper {
                padding: 10px 10px 60px;
                margin-top: 5px;
            }
            .contact-box {
                padding: 25px 18px;
            }
            .contact-box .result-title {
                font-size: 24px;
            }
            .contact-box .result-icon {
                width: 70px;
                height: 70px;
            }
            .contact-box .result-icon i {
                font-size: 32px;
            }
            .bottom-number {
                font-size: 35px;
                bottom: 8px;
                right: 15px;
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
                    <li><a href="contact.php" class="active">Contact Us</a></li>
                    <li><a href="about.html">About Us</a></li>
                </ul>
            </nav>
            <div class="nav-btn">
                <a href="login.html" class="login-btn">Login</a>
                <a href="register.html" class="register-btn">Register</a>
            </div>
        </div>
    </header>

    <!-- ===== CONTENT ===== -->
    <div class="contact-wrapper">

        <?php if ($message_type == 'success'): ?>
            <!-- ===== SUCCESS MESSAGE ===== -->
            <div class="contact-box">
                <div class="result-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2 class="result-title">Message Sent! ✅</h2>
                <p class="result-message"><?php echo htmlspecialchars($message); ?></p>
                <p class="result-sub">We'll get back to you within 24 hours.</p>
                <div class="btn-group">
                    <a href="contact.html" class="btn-primary"><i class="fas fa-arrow-left"></i> Back to Contact</a>
                    <a href="index.html" class="btn-secondary"><i class="fas fa-home"></i> Home</a>
                </div>
            </div>

        <?php elseif ($message_type == 'error'): ?>
            <!-- ===== ERROR MESSAGE ===== -->
            <div class="contact-box">
                <div class="result-icon error">
                    <i class="fas fa-times-circle"></i>
                </div>
                <h2 class="result-title error">Oops! Error</h2>
                <p class="result-message"><?php echo $message; ?></p>
                <div class="btn-group">
                    <a href="contact.html" class="btn-primary" style="background:#ff6b6b;"><i class="fas fa-arrow-left"></i> Try Again</a>
                    <a href="index.html" class="btn-secondary"><i class="fas fa-home"></i> Home</a>
                </div>
            </div>

        <?php else: ?>
            <!-- ===== CONTACT FORM ===== -->
            <div class="contact-box">
                <div style="text-align:center; margin-bottom:30px;">
                    <div style="font-size:40px; margin-bottom:8px;">📧</div>
                    <h2 class="form-title">Contact Us</h2>
                    <p class="sub-title">Have questions? We'd love to hear from you!</p>
                </div>

                <form method="POST">
                    <div class="form-group">
                        <input type="text" name="name" placeholder="Your Full Name" required />
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Your Email Address" required />
                    </div>
                    <div class="form-group">
                        <input type="text" name="subject" placeholder="Subject" required />
                    </div>
                    <div class="form-group">
                        <textarea name="message" rows="5" placeholder="Your Message..." required></textarea>
                    </div>
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </form>

                <div class="contact-info">
                    <div><i class="fas fa-map-marker-alt"></i> Dhaka, BD</div>
                    <div><i class="fas fa-envelope"></i> info@khelahobee.com</div>
                    <div><i class="fas fa-phone"></i> +880 1757-669073</div>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <!-- ===== PLAYER IMAGES - BOTH SIDES, FULL IMAGE, WITH MOTION ===== -->
    <div class="player-image-left">
        <img src="players.png" alt="Player" />
    </div>
    <div class="player-image-right">
        <img src="players.png" alt="Player" />
    </div>

    <!-- ===== NUMBER "10" AT BOTTOM RIGHT ===== -->
    <div class="bottom-number">10</div>

</div>

</body>
</html>