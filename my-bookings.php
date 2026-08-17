<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
require_once 'includes/config.php';

$user_id = $_SESSION['user_id'];

// Get player_id from players table
$player_stmt = $conn->prepare("SELECT player_id FROM players WHERE user_id = ?");
$player_stmt->bind_param("i", $user_id);
$player_stmt->execute();
$player_result = $player_stmt->get_result();
$player = $player_result->fetch_assoc();
$player_id = $player['player_id'] ?? 0;
$player_stmt->close();

// Get bookings with ground details
$bookings_stmt = $conn->prepare("
    SELECT b.*, g.name as ground_name, g.location, g.sport_type
    FROM bookings b
    JOIN time_slots ts ON b.slot_id = ts.slot_id
    JOIN grounds g ON ts.ground_id = g.ground_id
    WHERE b.player_id = ?
    ORDER BY b.booking_date DESC
");
$bookings_stmt->bind_param("i", $player_id);
$bookings_stmt->execute();
$bookings_result = $bookings_stmt->get_result();

function sportIcon($sport) {
    switch ($sport) {
        case 'Football': return '⚽';
        case 'Cricket': return '🏏';
        case 'Basketball': return '🏀';
        default: return '🏟️';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Bookings | 🏆 KHELA HOBEE</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        .bookings-list { max-width: 1400px; margin: 40px auto; padding: 0 20px; }
        .bookings-list > h2 {
            color: #7CCB96;
            font-size: 28px;
            margin-bottom: 8px;
            font-family: 'Russo One', sans-serif;
        }
        .bookings-list > .subtitle { color: #888; margin-bottom: 30px; }

        /* ===== BOOKING CARD ===== */
        .booking-card {
            background: linear-gradient(135deg, rgba(26,26,26,0.95), rgba(15,15,15,0.95));
            padding: 22px 32px;
            margin: 16px 0;
            min-height: 150px;
            border-radius: 18px;
            border: 1px solid rgba(124, 203, 150, 0.14);
            border-left: 5px solid #7CCB96;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
            transition: 0.3s;
        }
        .booking-card:hover {
            border-color: rgba(124, 203, 150, 0.3);
            transform: translateY(-2px);
        }
        .booking-card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 20px;
            padding-bottom: 14px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }
        .booking-ground {
            display: flex;
            align-items: center;
            gap: 18px;
        }
        .booking-sport-icon {
            width: 72px;
            height: 72px;
            flex-shrink: 0;
            border-radius: 16px;
            background: rgba(124, 203, 150, 0.1);
            border: 1px solid rgba(124, 203, 150, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
        }
        .booking-ground h3 {
            color: #fff;
            font-size: 22px;
            font-family: 'Russo One', sans-serif;
            margin-bottom: 8px;
        }
        .booking-ground .location {
            color: #999;
            font-size: 15px;
        }
        .booking-ground .location i { color: #7CCB96; margin-right: 6px; }

        .booking-status-block { text-align: right; }
        .status-badge {
            padding: 8px 22px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }
        .booking-ref {
            font-size: 13px;
            color: #666;
            margin-top: 10px;
        }

        /* ===== STAT CHIPS ===== */
        .booking-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-top: 14px;
        }
        .stat-chip {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 12px;
            padding: 12px 20px;
            flex: 1;
            min-width: 200px;
        }
        .stat-chip .stat-label {
            color: #777;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .stat-chip .stat-value {
            color: #fff;
            font-size: 18px;
            font-weight: 600;
        }
        .stat-chip .stat-value i { color: #7CCB96; margin-right: 8px; }
        .stat-chip.highlight .stat-value { color: #7CCB96; }

        /* ===== SCORECARD ===== */
        .booking-scorecard {
            background: rgba(0, 0, 0, 0.35);
            padding: 12px 18px;
            border-radius: 12px;
            margin-top: 14px;
            border-left: 3px solid #FFD700;
        }
        .booking-scorecard .score-line { color: #FFD700; font-size: 15px; margin: 0; }
        .booking-scorecard .winner-line { color: #7CCB96; font-size: 14px; margin: 6px 0 0; }
        .booking-scorecard .motm-line { color: #999; font-size: 14px; margin: 4px 0 0; }

        /* ===== ACTIONS ===== */
        .booking-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 16px;
        }
        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 26px;
            border-radius: 25px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: 0.3s;
        }
        .action-btn:hover { transform: translateY(-2px); }
        .action-btn.btn-cancel { background: rgba(255,107,107,0.12); color: #FF6B6B; border: 1px solid rgba(255,107,107,0.3); }
        .action-btn.btn-cancel:hover { background: #FF6B6B; color: #fff; }
        .action-btn.btn-pay { background: #7CCB96; color: #000; }
        .action-btn.btn-pay:hover { background: #5a9e7a; }
        .action-btn.btn-receipt { background: rgba(255,215,0,0.12); color: #FFD700; border: 1px solid rgba(255,215,0,0.3); }
        .action-btn.btn-receipt:hover { background: #FFD700; color: #000; }
        .action-btn.btn-review { background: rgba(255,215,0,0.12); color: #FFD700; border: 1px solid rgba(255,215,0,0.3); }
        .action-btn.btn-review:hover { background: #FFD700; color: #000; }
        .action-btn.btn-scorecard { background: rgba(78,205,196,0.12); color: #4ECDC4; border: 1px solid rgba(78,205,196,0.3); }
        .action-btn.btn-scorecard:hover { background: #4ECDC4; color: #000; }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            background: rgba(26,26,26,0.9);
            padding: 60px 40px;
            border-radius: 18px;
            text-align: center;
            border: 1px solid rgba(124, 203, 150, 0.12);
        }
        .empty-state .icon { font-size: 60px; }
        .empty-state p { color: #888; font-size: 18px; margin: 15px 0; }
        .empty-state a {
            color: #000;
            background: #7CCB96;
            text-decoration: none;
            font-weight: 700;
            padding: 12px 30px;
            border-radius: 25px;
            display: inline-block;
            transition: 0.3s;
        }
        .empty-state a:hover { background: #5a9e7a; transform: translateY(-2px); }

        @media (max-width: 640px) {
            .booking-card { padding: 22px; }
            .booking-status-block { text-align: left; width: 100%; }
            .stat-chip { min-width: 130px; }
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
                    <li><a href="index.html">Home</a></li>
                    <li><a href="search.php">Turfs & Fields</a></li>
                    <li><a href="player-matching.php">Find Players</a></li>
                    <li><a href="my-bookings.php" class="active">My Bookings</a></li>
                    <li><a href="contact.html">Contact</a></li>
                    <li><a href="about.html">About</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="bookings-list">
        <h2><i class="fas fa-calendar-check"></i> My Bookings</h2>
        <p class="subtitle">View all your booking history here.</p>

        <?php if ($bookings_result && $bookings_result->num_rows > 0): ?>
            <?php while ($booking = $bookings_result->fetch_assoc()): ?>
                <div class="booking-card">

                    <?php
                    // ===== CHECK FOR SCORECARD =====
                    $scorecard_stmt = $conn->prepare("SELECT * FROM scorecards WHERE booking_id = ?");
                    $scorecard_stmt->bind_param("i", $booking['booking_id']);
                    $scorecard_stmt->execute();
                    $scorecard_result = $scorecard_stmt->get_result();
                    $scorecard = $scorecard_result->fetch_assoc();
                    $scorecard_stmt->close();

                    $status = $booking['status'];
                    $color = '#FFA500';
                    $icon = 'fa-clock';
                    if ($status == 'confirmed') {
                        $color = '#7CCB96';
                        $icon = 'fa-check-circle';
                    } elseif ($status == 'cancelled') {
                        $color = '#FF6B6B';
                        $icon = 'fa-times-circle';
                    } elseif ($status == 'completed') {
                        $color = '#4ECDC4';
                        $icon = 'fa-flag-checkered';
                    }
                    ?>

                    <!-- ===== TOP: GROUND INFO + STATUS ===== -->
                    <div class="booking-card-top">
                        <div class="booking-ground">
                            <div class="booking-sport-icon"><?php echo sportIcon($booking['sport_type'] ?? ''); ?></div>
                            <div>
                                <h3><?php echo htmlspecialchars($booking['ground_name']); ?></h3>
                                <p class="location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($booking['location']); ?></p>
                            </div>
                        </div>
                        <div class="booking-status-block">
                            <span class="status-badge" style="background:<?php echo $color; ?>; color:black;">
                                <i class="fas <?php echo $icon; ?>"></i> <?php echo htmlspecialchars($status); ?>
                            </span>
                            <p class="booking-ref"><i class="fas fa-hashtag"></i> <?php echo htmlspecialchars($booking['booking_reference']); ?></p>
                        </div>
                    </div>

                    <!-- ===== STAT CHIPS ===== -->
                    <div class="booking-stats">
                        <div class="stat-chip highlight">
                            <div class="stat-label">Amount</div>
                            <div class="stat-value"><i class="fas fa-money-bill"></i>৳<?php echo number_format($booking['total_amount'], 0); ?></div>
                        </div>
                        <div class="stat-chip">
                            <div class="stat-label">Date & Time</div>
                            <div class="stat-value"><i class="fas fa-calendar"></i><?php echo date('d M Y, h:i A', strtotime($booking['booking_date'])); ?></div>
                        </div>
                    </div>

                    <!-- ===== SCORECARD DISPLAY ===== -->
                    <?php if ($scorecard): ?>
                        <div class="booking-scorecard">
                            <p class="score-line">
                                📊 <strong><?php echo htmlspecialchars($scorecard['team_1_name']); ?></strong>
                                <?php echo $scorecard['team_1_score']; ?> -
                                <?php echo $scorecard['team_2_score']; ?>
                                <strong><?php echo htmlspecialchars($scorecard['team_2_name']); ?></strong>
                            </p>
                            <?php if ($scorecard['winner']): ?>
                                <p class="winner-line">🏆 Winner: <strong><?php echo htmlspecialchars($scorecard['winner']); ?></strong></p>
                            <?php endif; ?>
                            <?php if ($scorecard['man_of_the_match']): ?>
                                <p class="motm-line">⭐ Man of the Match: <strong><?php echo htmlspecialchars($scorecard['man_of_the_match']); ?></strong></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php
                    // Check if review already exists
                    $review_check = $conn->prepare("SELECT review_id FROM reviews WHERE booking_id = ?");
                    $review_check->bind_param("i", $booking['booking_id']);
                    $review_check->execute();
                    $review_check_result = $review_check->get_result();
                    $has_review = $review_check_result->num_rows > 0;
                    $review_check->close();

                    // Check if scorecard already exists
                    $scorecard_check = $conn->prepare("SELECT scorecard_id FROM scorecards WHERE booking_id = ?");
                    $scorecard_check->bind_param("i", $booking['booking_id']);
                    $scorecard_check->execute();
                    $scorecard_check_result = $scorecard_check->get_result();
                    $has_scorecard = $scorecard_check_result->num_rows > 0;
                    $scorecard_check->close();
                    ?>

                    <!-- ===== ACTIONS ===== -->
                    <div class="booking-actions">
                        <?php if ($booking['status'] == 'pending'): ?>
                            <a href="cancel-booking.php?booking_id=<?php echo $booking['booking_id']; ?>"
                               class="action-btn btn-cancel"
                               onclick="return confirm('Are you sure you want to cancel this booking?')">
                                <i class="fas fa-times"></i> Cancel Booking
                            </a>
                            <a href="payment.php?booking_id=<?php echo $booking['booking_id']; ?>" class="action-btn btn-pay">
                                <i class="fas fa-credit-card"></i> Pay Now
                            </a>
                        <?php endif; ?>

                        <?php if ($booking['status'] == 'confirmed' || $booking['status'] == 'completed'): ?>
                            <a href="receipt.php?booking_id=<?php echo $booking['booking_id']; ?>" class="action-btn btn-receipt">
                                <i class="fas fa-file-pdf"></i> Download Receipt
                            </a>
                        <?php endif; ?>

                        <?php if ($booking['status'] == 'completed'): ?>
                            <?php if (!$has_review): ?>
                                <a href="review.php?booking_id=<?php echo $booking['booking_id']; ?>" class="action-btn btn-review">
                                    <i class="fas fa-star"></i> Write Review
                                </a>
                            <?php endif; ?>
                            <?php if (!$has_scorecard): ?>
                                <a href="scorecard.php?booking_id=<?php echo $booking['booking_id']; ?>" class="action-btn btn-scorecard">
                                    <i class="fas fa-chart-bar"></i> Record Scorecard
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="icon">📭</div>
                <p>You have no bookings yet.</p>
                <a href="search.php"><i class="fas fa-arrow-right"></i> Start booking now</a>
            </div>
        <?php endif; ?>
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