<?php
// =============================================
// KHELA HOBEE - bKash Payment Initiation
// =============================================
// Creates a real bKash Tokenized Checkout payment for a booking
// and redirects the user to bKash's hosted checkout page.
// =============================================
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
require_once 'includes/bkash.php';

$booking_id = isset($_GET['booking_id']) ? (int) $_GET['booking_id'] : 0;
$user_id = $_SESSION['user_id'];

// Fetch booking and confirm it belongs to this user and is unpaid
$stmt = $conn->prepare("
    SELECT b.*, g.name as ground_name
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

// Store booking_id in session so the callback knows which booking this payment is for
$_SESSION['bkash_pending_booking_id'] = $booking_id;

$invoiceNumber = $booking['booking_reference'] ?: ('INV' . $booking_id . time());
$mobile = isset($_GET['mobile']) ? preg_replace('/[^0-9]/', '', $_GET['mobile']) : '';
$payerReference = !empty($mobile) ? $mobile : '01700000000';

$result = BkashPayment::createPayment($booking['total_amount'], $invoiceNumber, $payerReference);

if (!empty($result['bkashURL'])) {
    // Save the paymentID against the booking's pending payment attempt
    $_SESSION['bkash_payment_id'] = $result['paymentID'];
    header("Location: " . $result['bkashURL']);
    exit();
} else {
    $errMsg = $result['message'] ?? ($result['errorMessage'] ?? 'Unknown error creating bKash payment.');
    echo "<h1>&#10060; Could not start bKash payment</h1>";
    echo "<p>" . htmlspecialchars($errMsg) . "</p>";
    echo "<pre style='background:#111;color:#7CCB96;padding:15px;border-radius:8px;max-width:700px;overflow:auto;'>" .
         htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT)) . "</pre>";
    echo "<a href='payment.php?booking_id=" . (int) $booking_id . "'>Back to Payment</a>";
    exit();
}
?>
