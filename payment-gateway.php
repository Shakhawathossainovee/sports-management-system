<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
require_once 'includes/config.php';

$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
$user_id = $_SESSION['user_id'];

// Get booking details
$stmt = $conn->prepare("
    SELECT b.*, g.name as ground_name, g.location 
    FROM bookings b
    JOIN time_slots ts ON b.slot_id = ts.slot_id
    JOIN grounds g ON ts.ground_id = g.ground_id
    WHERE b.booking_id = ? AND b.player_id = (SELECT player_id FROM players WHERE user_id = ?) AND b.status = 'pending'
");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    echo "<h1>❌ Booking not found or already paid</h1>";
    echo "<a href='my-bookings.php'>Back to My Bookings</a>";
    exit();
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_method = $_POST['payment_method'] ?? 'bkash';
    
    // Simulate payment processing
    $transaction_id = 'TXN' . date('Ymd') . rand(1000, 9999);
    $status = 'success';
    
    // Update booking status
    $update_stmt = $conn->prepare("UPDATE bookings SET status = 'confirmed' WHERE booking_id = ?");
    $update_stmt->bind_param("i", $booking_id);
    $update_stmt->execute();
    $update_stmt->close();
    
    // Save payment record
    $pay_stmt = $conn->prepare("INSERT INTO payments (booking_id, amount, method, status, transaction_id) VALUES (?, ?, ?, ?, ?)");
    $pay_stmt->bind_param("idsss", $booking_id, $booking['total_amount'], $payment_method, $status, $transaction_id);
    $pay_stmt->execute();
    $pay_stmt->close();
    
    $message = "✅ Payment successful! Your booking is now confirmed.";
    $message_type = "success";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment | Khela Hobee</title>
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
                    <li><a href="search.php">Search</a></li>
                    <li><a href="my-bookings.php">My Bookings</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div style="max-width:500px; margin:60px auto; padding:0 20px;">
        <?php if ($message_type == 'success'): ?>
            <div style="background:#1a1a1a; border-radius:16px; padding:40px; text-align:center; border:2px solid #7CCB96;">
                <div style="font-size:60px;">✅</div>
                <h2 style="color:#7CCB96;">Payment Successful!</h2>
                <p style="color:#bbb;"><?php echo $message; ?></p>
                <a href="my-bookings.php" style="display:inline-block; background:#7CCB96; color:black; padding:12px 30px; border-radius:8px; text-decoration:none; font-weight:bold; margin-top:15px;">
                    <i class="fas fa-arrow-left"></i> My Bookings
                </a>
            </div>
        <?php else: ?>
            <div style="background:#1a1a1a; border-radius:16px; padding:40px; border:1px solid #2a2a2a;">
                <h2 style="color:#7CCB96; text-align:center;">💳 Pay for Booking</h2>
                <p style="color:#888; text-align:center; font-size:14px;">
                    <?php echo $booking['ground_name']; ?> — ৳<?php echo $booking['total_amount']; ?>
                </p>
                <hr style="border-color:#2a2a2a; margin:15px 0;">
                <form method="POST">
                    <div class="form-group">
                        <label style="color:#ccc;">Select Payment Method</label>
                        <select name="payment_method" style="width:100%; padding:12px; border:none; border-radius:8px; background:#0b0b0b; color:white;">
                            <option value="bkash">bKash</option>
                            <option value="nagad">Nagad</option>
                            <option value="rocket">Rocket</option>
                            <option value="cash">Cash at Ground</option>
                        </select>
                    </div>
                    <button type="submit" class="submit-btn">Pay Now</button>
                </form>
                <p style="text-align:center; margin-top:15px;">
                    <a href="my-bookings.php" style="color:#888;">Cancel</a>
                </p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>