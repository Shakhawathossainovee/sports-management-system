<?php
// =============================================
// KHELA HOBEE - Manual bKash Payment (real money)
// =============================================
// No merchant API required. The payer sends real money via their own
// bKash app's "Send Money" feature to BKASH_RECEIVE_NUMBER, then
// submits the Transaction ID (TrxID) bKash texts them as proof.
// =============================================
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
require_once 'includes/config.php';
require_once 'includes/bkash_config.php';
require_once 'includes/logger.php';

$booking_id = isset($_GET['booking_id']) ? (int) $_GET['booking_id'] : (int) ($_POST['booking_id'] ?? 0);
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT b.*, g.name as ground_name, g.location
    FROM bookings b
    JOIN time_slots ts ON b.slot_id = ts.slot_id
    JOIN grounds g ON ts.ground_id = g.ground_id
    JOIN players p ON b.player_id = p.player_id
    WHERE b.booking_id = ? AND p.user_id = ? AND b.status = 'pending'
");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) {
    echo "<h1>&#10060; Booking not found or already paid</h1>";
    echo "<a href='my-bookings.php'>Back to My Bookings</a>";
    exit();
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['trx_id'])) {
    $trxId = strtoupper(trim($_POST['trx_id']));
    $senderNumber = preg_replace('/[^0-9]/', '', $_POST['sender_number'] ?? '');

    if (empty($trxId) || strlen($trxId) < 8) {
        $message = "Please enter a valid bKash Transaction ID (check the SMS bKash sent you).";
        $message_type = 'error';
    } elseif (empty($senderNumber) || strlen($senderNumber) < 11) {
        $message = "Please enter the bKash number you sent the payment from.";
        $message_type = 'error';
    } else {
        // Prevent the same TrxID being reused across bookings
        $dupe_stmt = $conn->prepare("SELECT payment_id FROM payments WHERE transaction_id = ?");
        $dupe_stmt->bind_param("s", $trxId);
        $dupe_stmt->execute();
        $dupe = $dupe_stmt->get_result()->fetch_assoc();
        $dupe_stmt->close();

        if ($dupe) {
            $message = "This Transaction ID has already been used for another payment.";
            $message_type = 'error';
        } else {
            $note = json_encode([
                'type' => 'manual_bkash',
                'sender_number' => $senderNumber,
                'receive_number' => BKASH_RECEIVE_NUMBER,
                'reported_at' => date('c'),
            ]);

            // Payment goes to PENDING — it is NOT trusted automatically.
            // The owner/admin must open their own bKash app, confirm this
            // TrxID actually exists with the matching amount and sender,
            // and approve it manually before the booking is confirmed.
            $pay_stmt = $conn->prepare("INSERT INTO payments (booking_id, amount, method, status, transaction_id, payment_gateway_response) VALUES (?, ?, 'bkash', 'pending', ?, ?)");
            $pay_stmt->bind_param("idss", $booking_id, $booking['total_amount'], $trxId, $note);
            $pay_stmt->execute();
            $pay_stmt->close();

            logAction($user_id, 'bkash_manual_payment_submitted', "Booking #$booking_id, TrxID: $trxId, from: $senderNumber — awaiting admin verification");

            $message = "Payment submitted! We're verifying your Transaction ID against bKash records. Your booking will be confirmed shortly — TrxID: {$trxId}";
            $message_type = 'success';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>bKash Payment | KHELA HOBEE</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        .bkash-wrapper { max-width: 560px; margin: 60px auto; padding: 0 20px; }
        .bkash-box { background: rgba(11,11,11,0.9); border-radius: 16px; padding: 36px; border: 1px solid rgba(124,203,150,0.15); }
        .bkash-box h2 { color: #e2136e; font-family: 'Russo One', sans-serif; text-align: center; margin-bottom: 4px; }
        .bkash-box .sub { color: #999; text-align: center; font-size: 13px; margin-bottom: 22px; }
        .send-instructions { background: rgba(226,19,110,0.08); border: 1px solid rgba(226,19,110,0.25); border-radius: 12px; padding: 18px 20px; margin-bottom: 22px; }
        .send-instructions ol { color: #ddd; font-size: 14px; padding-left: 20px; line-height: 1.9; }
        .send-instructions .num { color: #7CCB96; font-weight: 700; font-size: 16px; }
        .send-instructions .amt { color: #7CCB96; font-weight: 700; }
        .form-group { margin-bottom: 14px; }
        .form-group label { display:block; color:#888; font-size:12px; margin-bottom:6px; }
        .form-group input { width:100%; padding:11px 14px; border:1px solid rgba(255,255,255,0.08); border-radius:10px; background:rgba(255,255,255,0.05); color:white; font-size:13px; box-sizing:border-box; }
        .btn-submit { width:100%; padding:13px; border:none; background:#e2136e; color:#fff; border-radius:10px; font-size:15px; font-weight:600; cursor:pointer; margin-top:8px; }
        .btn-submit:hover { background:#b60f58; }
        .msg { padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; font-size: 14px; }
        .msg.error { background: rgba(255,107,107,0.1); border: 1px solid rgba(255,107,107,0.3); color: #ff6b6b; }
        .msg.success { background: rgba(124,203,150,0.1); border: 1px solid rgba(124,203,150,0.3); color: #7CCB96; }
        .back-link { display:block; text-align:center; margin-top:16px; color:#7CCB96; font-size:13px; text-decoration:none; }
    </style>
</head>
<body>
<div class="home-page">
    <header>
        <div class="container navbar">
            <div class="logo"><h2><span class="logo-khela">Khela</span> <span class="logo-hobe">Hobe</span> 🏆</h2></div>
            <nav><ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="search.php">Turfs & Fields</a></li>
                <li><a href="my-bookings.php" class="active">My Bookings</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul></nav>
        </div>
    </header>

    <div class="bkash-wrapper">
        <div class="bkash-box">
            <h2><i class="fas fa-mobile-alt"></i> Pay with bKash</h2>
            <p class="sub"><?php echo htmlspecialchars($booking['ground_name']); ?> — ৳<?php echo number_format($booking['total_amount'], 0); ?></p>

            <?php if ($message): ?>
                <div class="msg <?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($message_type === 'success'): ?>
                <a href="my-bookings.php" class="btn-submit" style="display:block; text-align:center; text-decoration:none;">
                    <i class="fas fa-arrow-left"></i> Go to My Bookings
                </a>
            <?php else: ?>
                <div class="send-instructions">
                    <ol>
                        <li>Open your <strong>bKash app</strong></li>
                        <li>Tap <strong>Send Money</strong></li>
                        <li>Enter number: <span class="num"><?php echo htmlspecialchars(BKASH_RECEIVE_NUMBER); ?></span></li>
                        <li>Enter amount: <span class="amt">৳<?php echo number_format($booking['total_amount'], 0); ?></span></li>
                        <li>Complete with your bKash PIN</li>
                        <li>Copy the <strong>Transaction ID</strong> from the confirmation SMS and enter it below</li>
                    </ol>
                </div>

                <form method="POST">
                    <input type="hidden" name="booking_id" value="<?php echo (int) $booking_id; ?>">
                    <div class="form-group">
                        <label>bKash Transaction ID (TrxID)</label>
                        <input type="text" name="trx_id" placeholder="e.g. 8N7A1B2C3D" required>
                    </div>
                    <div class="form-group">
                        <label>Your bKash Number (the one you sent from)</label>
                        <input type="text" name="sender_number" placeholder="017XXXXXXXX" required>
                    </div>
                    <button type="submit" class="btn-submit"><i class="fas fa-check"></i> Confirm Payment</button>
                </form>
            <?php endif; ?>

            <a href="payment.php?booking_id=<?php echo (int) $booking_id; ?>" class="back-link"><i class="fas fa-arrow-left"></i> Choose a different method</a>
        </div>
    </div>
</div>
</body>
</html>
