<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
require_once 'includes/config.php';
require_once 'includes/logger.php';

$booking_id = isset($_GET['booking_id']) ? $_GET['booking_id'] : 0;
$user_id = $_SESSION['user_id'];

// Verify booking belongs to this user and is pending
$stmt = $conn->prepare("
    SELECT b.booking_id, b.slot_id, b.status, b.booking_reference
    FROM bookings b
    JOIN players p ON b.player_id = p.player_id
    WHERE b.booking_id = ? AND p.user_id = ? AND b.status = 'pending'
");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $booking = $result->fetch_assoc();
    
    // Update booking status to cancelled
    $update_stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE booking_id = ?");
    $update_stmt->bind_param("i", $booking_id);
    $update_stmt->execute();
    $update_stmt->close();
    
    // Free up the time slot
    $slot_stmt = $conn->prepare("UPDATE time_slots SET is_available = 1 WHERE slot_id = ?");
    $slot_stmt->bind_param("i", $booking['slot_id']);
    $slot_stmt->execute();
    $slot_stmt->close();
    
    // ===== AUDIT LOG =====
    logAction($user_id, 'Cancel Booking', 'Booking cancelled: ' . $booking['booking_reference']);
    
    $message = "Booking cancelled successfully!";
    $message_type = "success";
} else {
    $message = "Booking not found or cannot be cancelled.";
    $message_type = "error";
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cancel Booking | 🏆 KHELA HOBEE</title>
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
                    <li><a href="index.php">Home</a></li>
                    <li><a href="search.php">Turfs & Fields</a></li>
                    <li><a href="my-bookings.php" class="active">My Bookings</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="notifications.php">Notifications</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div style="max-width:500px; margin:60px auto; padding:20px; position:relative; z-index:2;">
        <div style="background:rgba(11,11,11,0.9); padding:40px; border-radius:12px; text-align:center; border:2px solid <?php echo $message_type == 'success' ? '#7CCB96' : '#ff6b6b'; ?>;">
            <?php if ($message_type == 'success'): ?>
                <div style="font-size:60px;">✅</div>
                <h2 style="color:#7CCB96;">Booking Cancelled!</h2>
                <p style="color:#bbb; font-size:16px;"><?php echo $message; ?></p>
            <?php else: ?>
                <div style="font-size:60px;">❌</div>
                <h2 style="color:#ff6b6b;">Cancellation Failed</h2>
                <p style="color:#bbb; font-size:16px;"><?php echo $message; ?></p>
            <?php endif; ?>
            <a href="my-bookings.php" style="display:inline-block; background:#7CCB96; color:black; padding:12px 30px; border-radius:8px; text-decoration:none; font-weight:bold; margin-top:15px;">
                <i class="fas fa-arrow-left"></i> Back to My Bookings
            </a>
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

