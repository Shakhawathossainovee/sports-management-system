<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
require_once 'includes/config.php';
require_once 'includes/logger.php';
require_once 'includes/mailer.php';

$ground_id = isset($_GET['ground_id']) ? $_GET['ground_id'] : 0;

// Get ground details
$stmt = $conn->prepare("SELECT * FROM grounds WHERE ground_id = ?");
$stmt->bind_param("i", $ground_id);
$stmt->execute();
$result = $stmt->get_result();
$ground = $result->fetch_assoc();

if (!$ground) {
    echo "<h1>❌ Ground not found</h1>";
    echo "<a href='search.php'>Back to Search</a>";
    exit();
}

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $slot_id = $_POST['slot_id'];
    $user_id = $_SESSION['user_id'];
    $booking_date = date('Y-m-d H:i:s');
    $status = 'pending';
    
    // =============================================
    // FIX: Get OR Create player_id for ANY user
    // =============================================
    
    // First, check if user has a player record
    $player_stmt = $conn->prepare("SELECT player_id FROM players WHERE user_id = ?");
    $player_stmt->bind_param("i", $user_id);
    $player_stmt->execute();
    $player_result = $player_stmt->get_result();
    
    if ($player_result->num_rows == 0) {
        // No player record — create one automatically
        $insert_stmt = $conn->prepare("INSERT INTO players (user_id, favorite_sports, total_bookings) VALUES (?, 'Football', 0)");
        $insert_stmt->bind_param("i", $user_id);
        $insert_stmt->execute();
        $insert_stmt->close();
        
        // Fetch the new player record
        $player_stmt = $conn->prepare("SELECT player_id FROM players WHERE user_id = ?");
        $player_stmt->bind_param("i", $user_id);
        $player_stmt->execute();
        $player_result = $player_stmt->get_result();
    }
    
    $player = $player_result->fetch_assoc();
    $player_id_db = $player['player_id'];
    $player_stmt->close();
    
    // Lock the slot
    $lock_stmt = $conn->prepare("UPDATE time_slots SET is_available = 0 WHERE slot_id = ? AND is_available = 1");
    $lock_stmt->bind_param("i", $slot_id);
    $lock_stmt->execute();
    
    if ($lock_stmt->affected_rows > 0) {
        $booking_ref = 'BK' . date('Ymd') . rand(1000, 9999);
        
        $booking_stmt = $conn->prepare("INSERT INTO bookings (player_id, slot_id, booking_reference, total_amount, status, booking_date) VALUES (?, ?, ?, ?, ?, ?)");
        $booking_stmt->bind_param("iissss", $player_id_db, $slot_id, $booking_ref, $ground['rental_fee_per_hour'], $status, $booking_date);
        
        if ($booking_stmt->execute()) {
            $booking_ref_display = $booking_ref;
            $ground_name = $ground['name'];
            $ground_location = $ground['location'];
            $amount = $ground['rental_fee_per_hour'];
            
            // ===== NOTIFICATION =====
            $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, 'Booking Request Sent', 'Your booking at " . $ground['name'] . " has been submitted. Waiting for owner approval.', 'booking')");
            $notif_stmt->bind_param("i", $user_id);
            $notif_stmt->execute();
            $notif_stmt->close();
            
            // ===== AUDIT LOG =====
            logAction($user_id, 'Booking', 'Booking created for ' . $ground['name'] . ' (Ref: ' . $booking_ref . ')');
            
            // ===== SEND EMAIL =====
            $user_stmt = $conn->prepare("SELECT email FROM users WHERE user_id = ?");
            $user_stmt->bind_param("i", $user_id);
            $user_stmt->execute();
            $user_result = $user_stmt->get_result();
            $user_data = $user_result->fetch_assoc();
            $user_stmt->close();
            
            if ($user_data) {
                $email_body = getBookingConfirmationEmail(
                    $_SESSION['user_name'],
                    $booking_ref,
                    $ground['name'],
                    date('d M Y, h:i A'),
                    $ground['rental_fee_per_hour']
                );
                sendEmail($user_data['email'], 'Booking Confirmation - Khela Hobee', $email_body);
            }
            
        } else {
            $error = "Booking failed: " . $booking_stmt->error;
        }
        $booking_stmt->close();
    } else {
        $error = "Slot already booked!";
    }
    $lock_stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Booking | 🏆 KHELA HOBEE</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    
    <!-- ===== FLATPICKR CSS ===== -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
    
    <style>
        /* ============================================================ */
        /* BOOKING SECTION - FIXED HEIGHT (NO SCROLL) */
        /* ============================================================ */
        .booking-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 20px 40px;
            min-height: calc(100vh - 120px);
            max-height: calc(100vh - 120px);
            overflow: hidden;
        }
        .booking-container {
            max-width: 1100px;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            background: rgba(11, 11, 11, 0.85);
            padding: 30px 35px;
            border-radius: 16px;
            border: 1px solid rgba(124, 203, 150, 0.12);
            backdrop-filter: blur(3px);
            max-height: calc(100vh - 180px);
            overflow-y: auto;
        }
        .booking-container::-webkit-scrollbar {
            width: 4px;
        }
        .booking-container::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 4px;
        }
        .booking-container::-webkit-scrollbar-thumb {
            background: #7CCB96;
            border-radius: 4px;
        }

        /* ============================================================ */
        /* PLAYER IMAGE - OUTSIDE DIALOG BOX (LEFT SIDE) */
        /* ============================================================ */
        .hero-left {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
        }
        .hero-left img {
            width: 100%;
            max-width: 450px;
            border-radius: 16px;
            filter: drop-shadow(0 20px 50px rgba(0, 0, 0, 0.5));
        }

        /* ===== LEFT COLUMN - GROUND INFO ===== */
        .ground-info h1 {
            font-family: 'Russo One', sans-serif;
            font-size: 24px;
            color: #7CCB96;
            margin-bottom: 3px;
        }
        .ground-info .location {
            color: #888;
            font-size: 13px;
            margin-bottom: 5px;
        }
        .ground-info .location i { color: #7CCB96; margin-right: 6px; }
        .ground-info .price {
            font-size: 20px;
            font-weight: 700;
            color: #7CCB96;
            margin-bottom: 12px;
        }
        .ground-info .price span {
            font-size: 13px;
            color: #888;
            font-weight: 400;
        }
        .ground-info .facilities {
            margin-bottom: 12px;
        }
        .ground-info .facilities p {
            color: #bbb;
            font-size: 13px;
            margin: 2px 0;
        }
        .ground-info .facilities i {
            color: #7CCB96;
            margin-right: 8px;
            font-size: 11px;
        }
        .ground-info .grade-badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 20px;
            background: rgba(124, 203, 150, 0.08);
            border: 1px solid rgba(124, 203, 150, 0.1);
            color: #7CCB96;
            font-size: 12px;
            font-weight: 600;
            margin-top: 5px;
        }
        .ground-info .back-link {
            display: inline-block;
            margin-top: 12px;
            color: #7CCB96;
            font-size: 13px;
            text-decoration: none;
            transition: 0.3s;
        }
        .ground-info .back-link:hover { color: #5a9e7a; }
        .ground-info .back-link i { margin-right: 6px; }

        /* ===== RIGHT COLUMN - BOOKING FORM ===== */
        .booking-form h3 {
            color: #7CCB96;
            font-size: 17px;
            margin-bottom: 12px;
            font-family: 'Russo One', sans-serif;
        }
        .booking-form .form-group {
            margin-bottom: 12px;
        }
        .booking-form .form-group label {
            display: block;
            color: #888;
            font-size: 11px;
            font-weight: 500;
            margin-bottom: 4px;
        }
        .booking-form .form-group label i { margin-right: 5px; color: #7CCB96; }
        .booking-form .form-group input {
            width: 100%;
            padding: 10px 14px;
            border: none;
            outline: none;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.05);
            color: white;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            transition: 0.3s;
            border: 1px solid rgba(255, 255, 255, 0.06);
        }
        .booking-form .form-group input:focus {
            border-color: #7CCB96;
            background: rgba(255, 255, 255, 0.08);
        }
        .booking-form .btn-book {
            width: 100%;
            padding: 12px;
            border: none;
            background: #7CCB96;
            color: #000;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            font-family: 'Poppins', sans-serif;
            margin-top: 5px;
        }
        .booking-form .btn-book:hover {
            background: #5a9e7a;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(124, 203, 150, 0.25);
        }
        .booking-form .btn-book i { margin-right: 8px; }

        /* ============================================================ */
        /* SLOTS */
        /* ============================================================ */
        .slots-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: 8px;
            margin-top: 8px;
        }
        .slot-item {
            background: rgba(255, 255, 255, 0.03);
            padding: 10px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.06);
            text-align: center;
            transition: 0.3s;
        }
        .slot-item:hover { border-color: rgba(124, 203, 150, 0.2); }
        .slot-item .time { color: #fff; font-weight: 600; font-size: 12px; }
        .slot-item .btn-slot {
            margin-top: 6px;
            background: #7CCB96;
            color: #000;
            border: none;
            padding: 4px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 11px;
            transition: 0.3s;
        }
        .slot-item .btn-slot:hover {
            background: #5a9e7a;
            transform: translateY(-1px);
        }

        /* ============================================================ */
        /* DATE SELECTION — PROFESSIONAL STYLE */
        /* ============================================================ */
        .date-field-label {
            display: block;
            color: #888;
            font-size: 11px;
            font-weight: 500;
            margin-bottom: 6px;
        }
        .date-field-label i { margin-right: 5px; color: #7CCB96; }

        .date-input-wrap {
            position: relative;
        }
        .date-input-wrap input {
            width: 100%;
            padding: 12px 42px 12px 14px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #fff;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: 0.3s;
        }
        .date-input-wrap input::placeholder { color: rgba(255, 255, 255, 0.35); }
        .date-input-wrap input:disabled {
            cursor: not-allowed;
            opacity: 0.5;
        }
        .date-input-wrap input:focus {
            border-color: #7CCB96;
            background: rgba(255, 255, 255, 0.08);
            outline: none;
        }
        .date-input-wrap .date-caret {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #7CCB96;
            font-size: 12px;
            pointer-events: none;
        }

        .date-legend {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 8px;
            font-size: 11px;
            color: #888;
        }
        .date-legend .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #7CCB96;
            display: inline-block;
        }

        .no-avail-box {
            margin-top: 10px;
            padding: 12px 14px;
            border-radius: 10px;
            background: rgba(255, 107, 107, 0.08);
            border: 1px solid rgba(255, 107, 107, 0.15);
            color: #ff9b9b;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Flatpickr day states — themed to match the site */
        .flatpickr-day.available {
            color: #7CCB96 !important;
            font-weight: 700 !important;
            position: relative;
        }
        .flatpickr-day.available::after {
            content: '';
            position: absolute;
            bottom: 5px;
            left: 50%;
            transform: translateX(-50%);
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: #7CCB96;
        }
        .flatpickr-day.available:hover {
            background: rgba(124, 203, 150, 0.15) !important;
            border-color: #7CCB96 !important;
        }
        .flatpickr-day.available.selected {
            background: #7CCB96 !important;
            color: #000 !important;
            border-color: #7CCB96 !important;
        }
        .flatpickr-day.selected::after { background: #000 !important; }
        .flatpickr-day.flatpickr-disabled {
            color: rgba(255, 255, 255, 0.15) !important;
        }

        /* ============================================================ */
        /* SUCCESS / ERROR */
        /* ============================================================ */
        .result-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px 100px;
        }
        .result-box {
            max-width: 550px;
            width: 100%;
            background: rgba(11, 11, 11, 0.85);
            padding: 50px 40px;
            border-radius: 16px;
            border: 1px solid rgba(124, 203, 150, 0.12);
            backdrop-filter: blur(3px);
            text-align: center;
        }
        .result-box .icon-circle {
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
        .result-box .icon-circle i { font-size: 48px; color: #7CCB96; }
        .result-box .icon-circle.error {
            border-color: rgba(255, 107, 107, 0.2);
            background: rgba(255, 107, 107, 0.1);
        }
        .result-box .icon-circle.error i { color: #ff6b6b; }
        .result-box h2 { font-size: 28px; margin-bottom: 8px; font-family: 'Russo One', sans-serif; }
        .result-box h2.success { color: #7CCB96; }
        .result-box h2.error { color: #ff6b6b; }
        .result-box p { color: #bbb; font-size: 16px; margin-bottom: 8px; line-height: 1.7; }
        .result-box .sub-text { color: #888; font-size: 14px; margin-bottom: 30px; }
        .result-box .btn-group {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .result-box .btn-group a {
            display: inline-block;
            padding: 12px 30px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            transition: 0.3s;
        }
        .result-box .btn-group .btn-primary { background: #7CCB96; color: #000; }
        .result-box .btn-group .btn-primary:hover {
            background: #5a9e7a;
            transform: translateY(-2px);
        }
        .result-box .btn-group .btn-secondary {
            background: rgba(255, 255, 255, 0.06);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .result-box .btn-group .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.12);
        }
        .result-box .booking-details {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 12px;
            padding: 20px;
            margin: 25px 0;
            text-align: left;
        }
        .result-box .booking-details .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .result-box .booking-details .detail-row:last-child { border-bottom: none; }
        .result-box .booking-details .label { color: #888; font-size: 13px; }
        .result-box .booking-details .value { color: #fff; font-weight: 600; font-size: 13px; }
        .result-box .booking-details .value.highlight { color: #7CCB96; }

        /* ============================================================ */
        /* RESPONSIVE */
        /* ============================================================ */
        @media (max-width: 768px) {
            .booking-container {
                grid-template-columns: 1fr;
                padding: 20px;
                gap: 20px;
                max-height: calc(100vh - 160px);
            }
            .hero-left { order: -1; }
            .hero-left img { width: 60%; }
            .ground-info h1 { font-size: 20px; }
            .ground-info .price { font-size: 18px; }
            .result-box { padding: 30px 25px; }
        }
        @media (max-width: 480px) {
            .booking-container { padding: 15px; }
            .ground-info h1 { font-size: 18px; }
            .ground-info .price { font-size: 16px; }
            .result-box { padding: 25px 18px; }
            .result-box .icon-circle { width: 70px; height: 70px; }
            .result-box .icon-circle i { font-size: 32px; }
            .result-box h2 { font-size: 24px; }
            .slots-grid { grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); }
        }
    </style>
</head>
<body>

<div class="home-page">

    <!-- ============================================================ -->
    <!-- NAVBAR (EXACTLY LIKE TERMS PAGE) -->
    <!-- ============================================================ -->
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
                    <li><a href="search.php" class="active">Turfs & Fields</a></li>
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

    <?php if (isset($booking_ref_display)): ?>
        <!-- ===== SUCCESS PAGE ===== -->
        <div class="result-wrapper">
            <div class="result-box">
                <div class="icon-circle">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2 class="success">Booking Request Sent! ✅</h2>
                <p>Your booking has been submitted successfully.</p>
                <p class="sub-text">Please wait for owner approval.</p>

                <div class="booking-details">
                    <div class="detail-row">
                        <span class="label">Booking Reference</span>
                        <span class="value"><?php echo $booking_ref_display; ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Ground</span>
                        <span class="value"><?php echo $ground_name; ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Location</span>
                        <span class="value"><?php echo $ground_location; ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Amount</span>
                        <span class="value highlight">৳<?php echo $amount; ?></span>
                    </div>
                </div>

                <div class="btn-group">
                    <a href="my-bookings.php" class="btn-primary"><i class="fas fa-list"></i> My Bookings</a>
                    <a href="search.php" class="btn-secondary"><i class="fas fa-search"></i> Back to Search</a>
                </div>
            </div>
        </div>

    <?php elseif (isset($error)): ?>
        <!-- ===== ERROR PAGE ===== -->
        <div class="result-wrapper">
            <div class="result-box">
                <div class="icon-circle error">
                    <i class="fas fa-times-circle"></i>
                </div>
                <h2 class="error">Booking Failed ❌</h2>
                <p><?php echo $error; ?></p>
                <div class="btn-group" style="margin-top:20px;">
                    <a href="search.php" class="btn-primary"><i class="fas fa-search"></i> Back to Search</a>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- ============================================================ -->
        <!-- BOOKING FORM - PLAYER IMAGE OUTSIDE (LEFT SIDE) -->
        <!-- ============================================================ -->
        <div class="booking-wrapper">
            <div class="booking-container">

                <!-- LEFT: PLAYER IMAGE (OUTSIDE DIALOG BOX) -->
                <div class="hero-left">
                    <img src="players.png" alt="Player" />
                </div>

                <!-- RIGHT: GROUND INFO + BOOKING FORM (INSIDE DIALOG BOX) -->
                <div class="ground-info">
                    <h1>📅 <?php echo htmlspecialchars($ground['name']); ?></h1>
                    <p class="location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($ground['location']); ?>, <?php echo htmlspecialchars($ground['city']); ?></p>
                    <div class="price">
                        ৳<?php echo number_format($ground['rental_fee_per_hour'], 0); ?> <span>/ hour</span>
                    </div>
                    <div class="facilities">
                        <?php
                        $facilities = explode(',', $ground['facilities'] ?? '');
                        foreach ($facilities as $facility):
                            $facility = trim($facility);
                            if (!empty($facility)):
                        ?>
                            <p><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($facility); ?></p>
                        <?php
                            endif;
                        endforeach;
                        ?>
                    </div>
                    <?php if ($ground['grade']): ?>
                        <div class="grade-badge">
                            <i class="fas fa-star"></i> Grade: <?php echo htmlspecialchars($ground['grade']); ?>
                        </div>
                    <?php endif; ?>
                    <a href="search.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Search</a>

                    <!-- Booking Form -->
                    <div class="booking-form" style="margin-top:15px; padding-top:15px; border-top:1px solid rgba(255,255,255,0.06);">
                        <h3><i class="fas fa-calendar-plus"></i> Book This Ground</h3>

                        <div class="form-group">
                            <label class="date-field-label"><i class="fas fa-calendar-day"></i> Select a Date</label>
                            <div class="date-input-wrap">
                                <input type="text" id="check-date" name="check_date" placeholder="Loading available dates..." disabled />
                                <i class="fas fa-chevron-down date-caret"></i>
                            </div>
                            <div class="date-legend">
                                <span class="dot"></span> Dates with open slots are shown in green
                            </div>
                            <div id="no-availability-msg" style="display:none;" class="no-avail-box">
                                <i class="fas fa-calendar-times"></i>
                                <span>No upcoming availability for this ground right now. Please check back later.</span>
                            </div>
                        </div>

                        <div id="availability-message" style="margin-bottom:8px;"></div>

                        <div id="loading-spinner" style="display:none; text-align:center; padding:10px;">
                            <i class="fas fa-spinner fa-spin" style="color:#7CCB96; font-size:18px;"></i>
                            <p style="color:#888; font-size:12px; margin-top:3px;">Checking availability...</p>
                        </div>

                        <div id="slots-container" style="display:none;"></div>

                        <form method="POST" id="booking-form">
                            <input type="hidden" name="slot_id" id="selected-slot" value="" />
                            <button type="submit" id="book-btn" style="display:none; width:100%; padding:12px; border:none; background:#7CCB96; color:#000; border-radius:10px; font-size:15px; font-weight:600; cursor:pointer; transition:0.3s; font-family:'Poppins',sans-serif; margin-top:12px;">
                                <i class="fas fa-credit-card"></i> Confirm Booking
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    <?php endif; ?>

    <!-- ============================================================ -->
    <!-- FLATPICKR JS -->
    <!-- ============================================================ -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
    // ============================================================
    // LOAD AVAILABLE DATES, THEN BUILD THE CALENDAR
    // Only dates with open slots can be selected — no more picking
    // a date and finding out afterwards that nothing is free.
    // ============================================================
    const groundId = <?php echo (int)$ground_id; ?>;
    const dateInput = document.getElementById('check-date');
    const noAvailMsg = document.getElementById('no-availability-msg');

    fetch(`get_available_dates.php?ground_id=${groundId}`)
        .then(response => response.json())
        .then(data => {
            const availableDates = data.available_dates || [];

            if (availableDates.length === 0) {
                dateInput.placeholder = "No dates available";
                noAvailMsg.style.display = 'flex';
                return;
            }

            dateInput.disabled = false;
            dateInput.placeholder = "Choose an available date";

            flatpickr(dateInput, {
                dateFormat: "Y-m-d",
                minDate: "today",
                maxDate: new Date().fp_incr(30),
                disableMobile: true,
                allowInput: false,
                enable: availableDates,
                onDayCreate: function(dObj, dStr, fp, dayElem) {
                    if (!dayElem.dateObj) return;
                    const d = flatpickr.formatDate(dayElem.dateObj, 'Y-m-d');
                    if (availableDates.includes(d)) {
                        dayElem.classList.add('available');
                    }
                },
                onChange: function(selectedDates, dateStr) {
                    if (dateStr) {
                        checkAvailability();
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error fetching available dates:', error);
            dateInput.placeholder = "Unable to load dates — try refreshing";
        });

    function checkAvailability() {
        const date = document.getElementById('check-date').value;
        const groundId = <?php echo $ground_id; ?>;
        const msgDiv = document.getElementById('availability-message');
        const slotsDiv = document.getElementById('slots-container');
        const bookBtn = document.getElementById('book-btn');
        const spinner = document.getElementById('loading-spinner');

        if (!date) {
            slotsDiv.style.display = 'none';
            bookBtn.style.display = 'none';
            msgDiv.innerHTML = '';
            return;
        }

        spinner.style.display = 'block';
        slotsDiv.style.display = 'none';
        bookBtn.style.display = 'none';
        msgDiv.innerHTML = '';

        fetch(`check-availability.php?ground_id=${groundId}&date=${date}`)
            .then(response => response.json())
            .then(data => {
                spinner.style.display = 'none';

                if (data.error) {
                    msgDiv.innerHTML = `<div style="background:rgba(255,107,107,0.1); padding:10px 14px; border-radius:10px; color:#ff6b6b; border:1px solid rgba(255,107,107,0.1); font-size:13px;">❌ ${data.error}</div>`;
                    return;
                }

                if (data.slots.length === 0) {
                    msgDiv.innerHTML = `<div style="background:rgba(255,107,107,0.1); padding:10px 14px; border-radius:10px; color:#ff6b6b; border:1px solid rgba(255,107,107,0.1); font-size:13px;">❌ No available slots for this date.</div>`;
                    slotsDiv.style.display = 'none';
                    bookBtn.style.display = 'none';
                    return;
                }

                msgDiv.innerHTML = `<div style="background:rgba(124,203,150,0.1); padding:10px 14px; border-radius:10px; color:#7CCB96; border:1px solid rgba(124,203,150,0.1); font-size:13px;">✅ ${data.slots.length} slot(s) available!</div>`;

                let html = `<div style="background:rgba(255,255,255,0.03); padding:12px; border-radius:10px; margin-top:8px;">
                    <h4 style="color:#7CCB96; margin-bottom:8px; font-family:'Russo One',sans-serif; font-size:14px;">
                        <i class="fas fa-clock"></i> Available Slots
                    </h4>
                    <div class="slots-grid">`;

                data.slots.forEach(slot => {
                    html += `
                        <div class="slot-item">
                            <div class="time">${slot.start} - ${slot.end}</div>
                            <button class="btn-slot" onclick="selectSlot(${slot.id})">Book Now</button>
                        </div>`;
                });

                html += `</div></div>`;
                slotsDiv.innerHTML = html;
                slotsDiv.style.display = 'block';
            })
            .catch(error => {
                spinner.style.display = 'none';
                msgDiv.innerHTML = `<div style="background:rgba(255,107,107,0.1); padding:10px 14px; border-radius:10px; color:#ff6b6b; border:1px solid rgba(255,107,107,0.1); font-size:13px;">❌ Error checking availability. Please try again.</div>`;
            });
    }

    function selectSlot(slotId) {
        document.getElementById('selected-slot').value = slotId;
        const bookBtn = document.getElementById('book-btn');
        bookBtn.style.display = 'block';
        bookBtn.scrollIntoView({ behavior: 'smooth' });
    }
    </script>

</div>

</body>
</html>