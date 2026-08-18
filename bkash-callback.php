<?php
// =============================================
// KHELA HOBEE - bKash Payment Callback
// =============================================
// bKash redirects the user's browser here after they complete
// (or cancel) payment on bKash's page. This finalizes the payment.
// =============================================
session_start();
require_once 'includes/config.php';
require_once 'includes/bkash.php';
require_once 'includes/logger.php';

$paymentID = $_GET['paymentID'] ?? '';
$status = $_GET['status'] ?? ''; // 'success', 'failure', or 'cancel'
$booking_id = $_SESSION['bkash_pending_booking_id'] ?? 0;
$user_id = $_SESSION['user_id'] ?? 0;

function renderResult($success, $title, $message, $booking_id)
{
    $icon = $success ? '&#9989;' : '&#10060;';
    $color = $success ? '#7CCB96' : '#ff6b6b';
    echo "<!DOCTYPE html><html><head><title>{$title} | KHELA HOBEE</title>
    <link rel='stylesheet' href='style.css'></head><body>
    <div style='max-width:500px;margin:80px auto;padding:0 20px;text-align:center;'>
        <div style='background:#1a1a1a;border-radius:16px;padding:40px;border:2px solid {$color};'>
            <div style='font-size:60px;'>{$icon}</div>
            <h2 style='color:{$color};'>{$title}</h2>
            <p style='color:#bbb;'>" . htmlspecialchars($message) . "</p>
            <a href='payment.php?booking_id=" . (int) $booking_id . "' style='display:inline-block;background:#7CCB96;color:black;padding:12px 30px;border-radius:8px;text-decoration:none;font-weight:bold;margin:8px;'>Try Again</a>
            <a href='my-bookings.php' style='display:inline-block;background:#333;color:white;padding:12px 30px;border-radius:8px;text-decoration:none;font-weight:bold;margin:8px;'>My Bookings</a>
        </div>
    </div></body></html>";
    exit();
}

if (empty($booking_id) || empty($user_id)) {
    renderResult(false, 'Session Expired', 'Your payment session expired. Please try again from My Bookings.', 0);
}

// User cancelled or bKash reported failure before execute step
if ($status === 'cancel' || $status === 'failure') {
    logAction($user_id, 'bkash_payment_' . $status, "Booking #$booking_id, paymentID: $paymentID");
    renderResult(false, 'Payment ' . ucfirst($status), 'Your bKash payment was not completed.', $booking_id);
}

if (empty($paymentID)) {
    renderResult(false, 'Payment Error', 'Missing payment reference from bKash.', $booking_id);
}

// Finalize the payment
$execResult = BkashPayment::executePayment($paymentID);
$transactionStatus = $execResult['transactionStatus'] ?? '';

if ($transactionStatus === 'Completed') {
    $trxID = $execResult['trxID'] ?? $paymentID;
    $amount = $execResult['amount'] ?? 0;

    // Verify this payment matches an actual pending booking for this user
    $stmt = $conn->prepare("
        SELECT b.booking_id, b.total_amount
        FROM bookings b
        JOIN players p ON b.player_id = p.player_id
        WHERE b.booking_id = ? AND p.user_id = ? AND b.status = 'pending'
    ");
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$booking) {
        renderResult(false, 'Booking Mismatch', 'Could not match this payment to a pending booking. Contact support with reference: ' . htmlspecialchars($trxID), $booking_id);
    }

    // Record the payment
    $pay_stmt = $conn->prepare("INSERT INTO payments (booking_id, amount, method, status, transaction_id, payment_gateway_response) VALUES (?, ?, 'bkash', 'success', ?, ?)");
    $responseJson = json_encode($execResult);
    $pay_stmt->bind_param("idss", $booking_id, $booking['total_amount'], $trxID, $responseJson);
    $pay_stmt->execute();
    $pay_stmt->close();

    // Confirm the booking
    $update_stmt = $conn->prepare("UPDATE bookings SET status = 'confirmed' WHERE booking_id = ?");
    $update_stmt->bind_param("i", $booking_id);
    $update_stmt->execute();
    $update_stmt->close();

    // Notify user
    $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, 'Payment Successful', ?, 'payment')");
    $notifMsg = "Your bKash payment of {$booking['total_amount']} Tk has been received (Ref: {$trxID}). Booking confirmed!";
    $notif_stmt->bind_param("is", $user_id, $notifMsg);
    $notif_stmt->execute();
    $notif_stmt->close();

    logAction($user_id, 'bkash_payment_success', "Booking #$booking_id, trxID: $trxID");

    unset($_SESSION['bkash_pending_booking_id'], $_SESSION['bkash_payment_id']);

    renderResult(true, 'Payment Successful!', "Your bKash payment was received. Transaction ID: {$trxID}. Booking confirmed!", $booking_id);
} else {
    $errMsg = $execResult['statusMessage'] ?? ($execResult['message'] ?? 'Payment could not be completed.');

    // Record the failed attempt for audit purposes
    $pay_stmt = $conn->prepare("INSERT INTO payments (booking_id, amount, method, status, transaction_id, payment_gateway_response) VALUES (?, 0, 'bkash', 'failed', ?, ?)");
    $responseJson = json_encode($execResult);
    $pay_stmt->bind_param("iss", $booking_id, $paymentID, $responseJson);
    $pay_stmt->execute();
    $pay_stmt->close();

    logAction($user_id, 'bkash_payment_failed', "Booking #$booking_id, paymentID: $paymentID, reason: $errMsg");

    renderResult(false, 'Payment Failed', $errMsg, $booking_id);
}
?>
