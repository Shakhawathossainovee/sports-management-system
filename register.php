<?php
require_once 'includes/config.php';
require_once 'includes/logger.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first = $_POST['firstname'];
    $last = $_POST['lastname'];
    $name = $first . ' ' . $last;
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $role = $_POST['role'] ?? 'player';
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $error = "This email is already registered. Please login.";
        $show_error = true;
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $phone, $password, $role);

        if ($stmt->execute()) {
            $user_id = $conn->insert_id;
            
            if ($role == 'player') {
                $player_stmt = $conn->prepare("INSERT INTO players (user_id) VALUES (?)");
                $player_stmt->bind_param("i", $user_id);
                $player_stmt->execute();
                $player_stmt->close();
            } elseif ($role == 'owner') {
                $owner_stmt = $conn->prepare("INSERT INTO ground_owners (user_id, verification_status) VALUES (?, 'pending')");
                $owner_stmt->bind_param("i", $user_id);
                $owner_stmt->execute();
                $owner_stmt->close();
            } elseif ($role == 'admin') {
                $admin_stmt = $conn->prepare("INSERT INTO administrators (user_id, access_level) VALUES (?, 'standard')");
                $admin_stmt->bind_param("i", $user_id);
                $admin_stmt->execute();
                $admin_stmt->close();
            }
            
            logAction($user_id, 'Register', 'New user registered as ' . $role);
            $success = true;
            $user_role = $role;
        } else {
            $error = $stmt->error;
            $show_error = true;
        }
        $stmt->close();
    }
    $check_stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>🏆 Khela Hobe | Registration</title>
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
        }
        @keyframes popIn {
            0% { transform: scale(0.5); opacity: 0; }
            70% { transform: scale(1.1); }
            100% { transform: scale(1); opacity: 1; }
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
                    <h2 class="success">Registration Successful!</h2>
                    <p>
                        Your account has been created successfully as
                        <strong><?php echo ucfirst($user_role); ?></strong>.
                        <br>Please login to continue.
                    </p>
                    <a href="login.html" class="submit-btn">
                        <i class="fas fa-sign-in-alt"></i> Login Now
                    </a>
                <?php elseif (isset($show_error)): ?>
                    <div class="result-icon error">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <h2 class="error">Registration Failed</h2>
                    <p><?php echo $error; ?></p>
                    <a href="register.html" class="submit-btn">
                        <i class="fas fa-arrow-left"></i> Try Again
                    </a>
                <?php endif; ?>
                <div class="login-link" style="margin-top:15px;">
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