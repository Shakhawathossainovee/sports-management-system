<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
require_once 'includes/config.php';
require_once 'dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
$user_id = $_SESSION['user_id'];

// Get booking details with payment info
$stmt = $conn->prepare("
    SELECT 
        b.*, 
        g.name as ground_name, 
        g.location, 
        u.name as player_name,
        p.payment_id,
        p.method as payment_method,
        p.transaction_id,
        p.invoice_number,
        p.payment_date,
        p.status as payment_status
    FROM bookings b
    JOIN time_slots ts ON b.slot_id = ts.slot_id
    JOIN grounds g ON ts.ground_id = g.ground_id
    JOIN players pl ON b.player_id = pl.player_id
    JOIN users u ON pl.user_id = u.user_id
    LEFT JOIN payments p ON b.booking_id = p.booking_id
    WHERE b.booking_id = ? AND u.user_id = ?
");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    die("❌ Booking not found or you don't have permission.");
}

// =============================================
// GENERATE PDF RECEIPT
// =============================================

$options = new Options();
$options->set('defaultFont', 'Courier');
$dompdf = new Dompdf($options);

$html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: #fff; 
            color: #333; 
            padding: 40px;
            margin: 0;
        }
        .receipt-container {
            max-width: 700px;
            margin: 0 auto;
            background: #ffffff;
            border: 2px solid #7CCB96;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #7CCB96;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #7CCB96;
            font-size: 28px;
            margin: 0;
        }
        .header p {
            color: #888;
            font-size: 14px;
            margin: 5px 0 0;
        }
        .receipt-title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #222;
            margin: 10px 0 20px;
        }
        .receipt-title span {
            color: #7CCB96;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .details-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #eee;
        }
        .details-table .label {
            color: #888;
            font-weight: 500;
            width: 40%;
        }
        .details-table .value {
            color: #222;
            font-weight: 600;
            width: 60%;
        }
        .amount-box {
            background: #f5f9f7;
            border: 2px solid #7CCB96;
            border-radius: 8px;
            padding: 15px 20px;
            text-align: center;
            margin: 20px 0;
        }
        .amount-box .amount {
            font-size: 32px;
            font-weight: bold;
            color: #7CCB96;
        }
        .amount-box .label {
            color: #888;
            font-size: 14px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-paid {
            background: #7CCB96;
            color: #000;
        }
        .status-pending {
            background: #FFA500;
            color: #000;
        }
        .footer {
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 20px;
            margin-top: 20px;
            color: #888;
            font-size: 12px;
        }
        .footer .brand {
            color: #7CCB96;
            font-weight: bold;
            font-size: 16px;
        }
        .print-date {
            text-align: right;
            color: #aaa;
            font-size: 11px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Header -->
        <div class="header">
            <h1>🏆 Khela Hobee</h1>
            <p>Payment Receipt</p>
        </div>

        <div class="receipt-title">
            📄 <span>Payment Receipt</span>
        </div>

        <!-- Status -->
        <div style="text-align:center; margin-bottom:15px;">
            <span class="status-badge ' . ($booking['payment_status'] == 'success' ? 'status-paid' : 'status-pending') . '">
                ' . strtoupper($booking['payment_status'] ?? 'PENDING') . '
            </span>
        </div>

        <!-- Details -->
        <table class="details-table">
            <tr>
                <td class="label">Receipt Number</td>
                <td class="value">' . ($booking['invoice_number'] ?? 'INV-' . date('Ymd') . '-' . $booking['booking_id']) . '</td>
            </tr>
            <tr>
                <td class="label">Booking Reference</td>
                <td class="value">' . $booking['booking_reference'] . '</td>
            </tr>
            <tr>
                <td class="label">Transaction ID</td>
                <td class="value">' . ($booking['transaction_id'] ?? 'N/A') . '</td>
            </tr>
            <tr>
                <td class="label">Payment Method</td>
                <td class="value">' . ucfirst($booking['payment_method'] ?? 'Cash') . '</td>
            </tr>
            <tr>
                <td class="label">Payment Date</td>
                <td class="value">' . date('d M Y, h:i A', strtotime($booking['payment_date'] ?? $booking['booking_date'])) . '</td>
            </tr>
            <tr>
                <td class="label">Ground</td>
                <td class="value">' . $booking['ground_name'] . '</td>
            </tr>
            <tr>
                <td class="label">Location</td>
                <td class="value">' . $booking['location'] . '</td>
            </tr>
            <tr>
                <td class="label">Booked By</td>
                <td class="value">' . $booking['player_name'] . '</td>
            </tr>
        </table>

        <!-- Amount -->
        <div class="amount-box">
            <div class="label">Total Amount Paid</div>
            <div class="amount">৳' . number_format($booking['total_amount'], 2) . '</div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="brand">🏆 Khela Hobee</div>
            <p>Thank you for booking with us!</p>
            <p>For any queries, contact us at info@khelahobee.com</p>
            <div class="print-date">Generated on: ' . date('d M Y, h:i A') . '</div>
        </div>
    </div>
</body>
</html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Output PDF
$dompdf->stream("receipt-" . $booking['booking_reference'] . ".pdf", array("Attachment" => 1));
exit();
?>