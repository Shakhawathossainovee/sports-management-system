<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Player-only page: owners/admins have their own dashboards.
$role = $_SESSION['user_role'] ?? 'player';
if ($role === 'owner') {
    header("Location: owner-dashboard.php");
    exit();
} elseif ($role === 'admin') {
    header("Location: admin-dashboard.php");
    exit();
}
require_once 'includes/config.php';

$booking_id = isset($_GET['booking_id']) ? $_GET['booking_id'] : 0;

// bKash goes through the real gateway flow, not the simulated form below.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['payment_method'] ?? '') === 'bkash') {
    // Real money, manual verification (no merchant API needed) — see bkash-manual.php.
    header("Location: bkash-manual.php?booking_id=" . (int) $booking_id);
    exit();
}
$user_id = $_SESSION['user_id'];

// Get booking details - FIXED with JOIN instead of subquery
$stmt = $conn->prepare("
    SELECT b.*, g.name as ground_name, g.location 
    FROM bookings b
    JOIN time_slots ts ON b.slot_id = ts.slot_id
    JOIN grounds g ON ts.ground_id = g.ground_id
    JOIN players p ON b.player_id = p.player_id
    WHERE b.booking_id = ? AND p.user_id = ?
");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    echo "<h1>❌ Booking not found</h1>";
    echo "<a href='my-bookings.php'>Back to My Bookings</a>";
    exit();
}

$message = '';
$message_type = '';

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_method = $_POST['payment_method'];
    $mobile_number = $_POST['mobile_number'] ?? '';
    $amount = $booking['total_amount'];
    $status = 'success';
    $transaction_id = 'TXN' . date('Ymd') . rand(1000, 9999);
    
    // Card payment validation
    $error = '';
    if ($payment_method == 'card') {
        $card_number = $_POST['card_number'] ?? '';
        $expiry = $_POST['expiry'] ?? '';
        $cvv = $_POST['cvv'] ?? '';
        $cardholder_name = $_POST['cardholder_name'] ?? '';
        
        if (empty($card_number) || empty($expiry) || empty($cvv) || empty($cardholder_name)) {
            $error = "Please fill in all card details.";
        } elseif (strlen($cvv) < 3) {
            $error = "Invalid CVV.";
        }
    }
    
    if (empty($error)) {
        // Insert payment
        $pay_stmt = $conn->prepare("INSERT INTO payments (booking_id, amount, method, status, transaction_id) VALUES (?, ?, ?, ?, ?)");
        $pay_stmt->bind_param("idsss", $booking['booking_id'], $amount, $payment_method, $status, $transaction_id);
        
        if ($pay_stmt->execute()) {
            // Update booking status to confirmed
            $update_stmt = $conn->prepare("UPDATE bookings SET status = 'confirmed' WHERE booking_id = ?");
            $update_stmt->bind_param("i", $booking['booking_id']);
            $update_stmt->execute();
            $update_stmt->close();
            
            // Add notification
            $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, 'Payment Successful', 'Your payment of " . $amount . " Tk for " . $booking['ground_name'] . " has been received. Booking confirmed!', 'payment')");
            $notif_stmt->bind_param("i", $user_id);
            $notif_stmt->execute();
            $notif_stmt->close();
            
            $message = "Payment successful! Booking confirmed.";
            $message_type = "success";
        } else {
            $message = "Payment failed. Please try again.";
            $message_type = "error";
        }
        $pay_stmt->close();
    } else {
        $message = $error;
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment | 🏆 KHELA HOBEE</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        /* ============================================================ */
        /* PAYMENT PAGE LAYOUT — MATCHES BOOKING.PHP DIALOG STYLE */
        /* ============================================================ */
        .payment-wrapper {
            padding: 40px 20px 80px;
        }
        .payment-container {
            max-width: 1100px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 50px;
            background: rgba(11, 11, 11, 0.85);
            padding: 40px 45px;
            border-radius: 16px;
            border: 1px solid rgba(124, 203, 150, 0.12);
            backdrop-filter: blur(3px);
        }
        .payment-left h2 {
            font-family: 'Russo One', sans-serif;
            font-size: 26px;
            color: #7CCB96;
            margin-bottom: 6px;
        }
        .payment-left > p {
            color: #999;
            font-size: 14px;
            margin-bottom: 22px;
        }

        /* ===== GROUND SUMMARY CARD ===== */
        .ground-summary {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 12px;
            padding: 20px 22px;
            margin-bottom: 26px;
        }
        .ground-summary h3 {
            color: #fff;
            font-size: 18px;
            font-family: 'Russo One', sans-serif;
            margin-bottom: 10px;
        }
        .ground-summary .summary-row {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #bbb;
            font-size: 13px;
            margin: 6px 0;
        }
        .ground-summary .summary-row i { color: #7CCB96; width: 14px; }
        .ground-summary .summary-row .amount { color: #7CCB96; font-weight: 700; }

        /* ===== FORM ===== */
        .payment-left .field-label {
            display: block;
            color: #888;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }
        .payment-left .form-group { margin-bottom: 14px; }
        .payment-left .form-group input {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            outline: none;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.05);
            color: white;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            transition: 0.3s;
            box-sizing: border-box;
        }
        .payment-left .form-group input:focus {
            border-color: #7CCB96;
            background: rgba(255, 255, 255, 0.08);
        }
        .payment-left .form-group label:not(.field-label) {
            display: block;
            color: #888;
            font-size: 12px;
            margin-bottom: 6px;
        }

        .payment-icon {
            font-size: 22px;
            width: 34px;
            text-align: center;
            flex-shrink: 0;
        }
        .payment-method-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            transition: 0.3s;
            cursor: pointer;
            margin-bottom: 10px;
        }
        .payment-method-card:hover {
            border-color: rgba(124, 203, 150, 0.25);
            background: rgba(124, 203, 150, 0.04);
        }
        .payment-method-card.selected {
            border-color: #7CCB96;
            background: rgba(124, 203, 150, 0.08);
        }
        .payment-method-card input[type="radio"] {
            accent-color: #7CCB96;
            width: 17px;
            height: 17px;
            flex-shrink: 0;
        }
        .payment-method-card label {
            color: #fff;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            flex: 1;
            margin: 0;
        }
        .payment-method-card label span { color: #888; font-size: 12px; font-weight: 400; }
        .bkash-icon { color: #e2136e; }
        .nagad-icon { color: #ff6600; }
        .rocket-icon { color: #4CAF50; }
        .card-icon { color: #FFD700; }
        .cash-icon { color: #7CCB96; }

        #card-details {
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 16px;
            border-radius: 10px;
            margin: 12px 0;
            background: rgba(255, 255, 255, 0.02);
        }
        #card-details h4 {
            color: #7CCB96;
            font-size: 14px;
            margin-bottom: 12px;
            font-family: 'Russo One', sans-serif;
        }
        .card-row { display: flex; gap: 12px; }
        .card-row .form-group { flex: 1; }

        .btn-pay-now {
            width: 100%;
            padding: 13px;
            border: none;
            background: #7CCB96;
            color: #000;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            font-family: 'Poppins', sans-serif;
            margin-top: 8px;
        }
        .btn-pay-now:hover {
            background: #5a9e7a;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(124, 203, 150, 0.25);
        }
        .payment-left .back-link {
            display: block;
            text-align: center;
            margin-top: 16px;
            color: #7CCB96;
            font-size: 13px;
            text-decoration: none;
        }
        .payment-left .back-link:hover { color: #5a9e7a; }

        /* ===== HERO IMAGE ===== */
        .payment-hero {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .payment-hero img {
            width: 100%;
            max-width: 380px;
            filter: drop-shadow(0 20px 50px rgba(0, 0, 0, 0.5));
        }

        /* ===== SUCCESS / ERROR ===== */
        .result-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 20px 100px;
        }
        .result-box {
            max-width: 500px;
            width: 100%;
            background: rgba(11, 11, 11, 0.85);
            padding: 45px 40px;
            border-radius: 16px;
            border: 1px solid rgba(124, 203, 150, 0.12);
            backdrop-filter: blur(3px);
            text-align: center;
        }
        .result-box.error { border-color: rgba(255, 107, 107, 0.2); }
        .result-box .icon-circle {
            width: 90px;
            height: 90px;
            background: rgba(124, 203, 150, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            border: 2px solid rgba(124, 203, 150, 0.2);
            font-size: 42px;
        }
        .result-box.error .icon-circle {
            background: rgba(255, 107, 107, 0.1);
            border-color: rgba(255, 107, 107, 0.2);
        }
        .result-box h2 { font-size: 26px; margin-bottom: 10px; font-family: 'Russo One', sans-serif; color: #7CCB96; }
        .result-box.error h2 { color: #ff6b6b; }
        .result-box p { color: #bbb; font-size: 15px; margin-bottom: 10px; }
        .result-box a.btn-primary {
            display: inline-block;
            margin-top: 15px;
            background: #7CCB96;
            color: #000;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }
        .result-box a.btn-primary:hover { background: #5a9e7a; transform: translateY(-2px); }

        @media (max-width: 768px) {
            .payment-container { grid-template-columns: 1fr; padding: 28px; }
            .payment-hero { order: -1; }
            .payment-hero img { max-width: 220px; }
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
                    <li><a href="index.php">Home</a></li>
                    <li><a href="search.php">Turfs & Fields</a></li>
                    <li><a href="player-matching.php">Find Players</a></li>
                    <li><a href="my-bookings.php" class="active">My Bookings</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="notifications.php">Notifications</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <?php if ($message_type == 'success'): ?>
        <div class="result-wrapper">
            <div class="result-box">
                <div class="icon-circle">✅</div>
                <h2>Payment Successful!</h2>
                <p><?php echo htmlspecialchars($message); ?></p>
                <a href="my-bookings.php" class="btn-primary"><i class="fas fa-arrow-left"></i> My Bookings</a>
            </div>
        </div>
    <?php elseif ($message_type == 'error'): ?>
        <div class="result-wrapper">
            <div class="result-box error">
                <div class="icon-circle">❌</div>
                <h2>Payment Failed</h2>
                <p><?php echo htmlspecialchars($message); ?></p>
                <a href="payment.php?booking_id=<?php echo (int)$booking_id; ?>" class="btn-primary"><i class="fas fa-redo"></i> Try Again</a>
            </div>
        </div>
    <?php else: ?>
        <div class="payment-wrapper">
            <div class="payment-container">
                <div class="payment-left">
                    <h2><i class="fas fa-credit-card"></i> Complete Payment</h2>
                    <p>Pay securely for your booking using your preferred method.</p>

                    <div class="ground-summary">
                        <h3><?php echo htmlspecialchars($booking['ground_name']); ?></h3>
                        <p class="summary-row"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($booking['location']); ?></p>
                        <p class="summary-row"><i class="fas fa-money-bill"></i> Amount: <span class="amount">৳<?php echo number_format($booking['total_amount'], 0); ?></span></p>
                        <p class="summary-row"><i class="fas fa-calendar"></i> <?php echo date('d M Y, h:i A', strtotime($booking['booking_date'])); ?></p>
                    </div>

                    <form method="POST" id="paymentForm">
                        <label class="field-label">Select Payment Method</label>

                        <div class="payment-method-card" onclick="document.getElementById('bkash').checked=true; toggleFields();">
                            <input type="radio" name="payment_method" id="bkash" value="bkash" onchange="toggleFields()">
                            <span class="payment-icon bkash-icon"><i class="fas fa-mobile-alt"></i></span>
                            <label for="bkash"><strong>bKash</strong> <span>Mobile Banking</span></label>
                        </div>

                        <div class="payment-method-card" onclick="document.getElementById('nagad').checked=true; toggleFields();">
                            <input type="radio" name="payment_method" id="nagad" value="nagad" onchange="toggleFields()">
                            <span class="payment-icon nagad-icon"><i class="fas fa-mobile-alt"></i></span>
                            <label for="nagad"><strong>Nagad</strong> <span>Mobile Banking</span></label>
                        </div>

                        <div class="payment-method-card" onclick="document.getElementById('rocket').checked=true; toggleFields();">
                            <input type="radio" name="payment_method" id="rocket" value="rocket" onchange="toggleFields()">
                            <span class="payment-icon rocket-icon"><i class="fas fa-rocket"></i></span>
                            <label for="rocket"><strong>Rocket</strong> <span>Mobile Banking</span></label>
                        </div>

                        <div class="payment-method-card" onclick="document.getElementById('card').checked=true; toggleFields();">
                            <input type="radio" name="payment_method" id="card" value="card" onchange="toggleFields()">
                            <span class="payment-icon card-icon"><i class="fas fa-credit-card"></i></span>
                            <label for="card"><strong>Credit / Debit Card</strong> <span>Visa, Mastercard, Amex</span></label>
                        </div>

                        <div class="payment-method-card" onclick="document.getElementById('cash').checked=true; toggleFields();">
                            <input type="radio" name="payment_method" id="cash" value="cash" onchange="toggleFields()">
                            <span class="payment-icon cash-icon"><i class="fas fa-money-bill-wave"></i></span>
                            <label for="cash"><strong>Cash</strong> <span>Pay at the ground</span></label>
                        </div>

                        <div class="form-group" id="mobile-field" style="display:none; margin-top:14px;">
                            <label>Mobile Number</label>
                            <input type="text" name="mobile_number" placeholder="Enter mobile number (e.g., 017XXXXXXXX)">
                        </div>

                        <div id="card-details" style="display:none;">
                            <h4><i class="fas fa-credit-card"></i> Card Details</h4>
                            <div class="form-group">
                                <label>Card Number</label>
                                <input type="text" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19">
                            </div>
                            <div class="card-row">
                                <div class="form-group">
                                    <label>Expiry Date</label>
                                    <input type="text" name="expiry" placeholder="MM/YY">
                                </div>
                                <div class="form-group">
                                    <label>CVV</label>
                                    <input type="password" name="cvv" placeholder="***" maxlength="4">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Cardholder Name</label>
                                <input type="text" name="cardholder_name" placeholder="Name on card">
                            </div>
                        </div>

                        <button type="submit" class="btn-pay-now">
                            <i class="fas fa-lock"></i> Pay Now
                        </button>
                    </form>

                    <a href="my-bookings.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to My Bookings</a>
                </div>

                <div class="payment-hero">
                    <img src="players.png" alt="Payment">
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script>
        function toggleFields() {
            var selected = document.querySelector('input[name="payment_method"]:checked');
            var mobileField = document.getElementById('mobile-field');
            var cardDetails = document.getElementById('card-details');

            document.querySelectorAll('.payment-method-card').forEach(function(card) {
                card.classList.remove('selected');
            });
            if (selected) {
                selected.closest('.payment-method-card').classList.add('selected');

                var value = selected.value;
                if (value === 'bkash' || value === 'nagad' || value === 'rocket') {
                    mobileField.style.display = 'block';
                    cardDetails.style.display = 'none';
                } else if (value === 'card') {
                    mobileField.style.display = 'none';
                    cardDetails.style.display = 'block';
                } else {
                    mobileField.style.display = 'none';
                    cardDetails.style.display = 'none';
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            toggleFields();
        });
    </script>

</div>

</body>
</html>