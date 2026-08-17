<?php
session_start();
if (!isset($_SESSION['owner_id'])) {
    header("Location: owner-login.html");
    exit();
}
require_once 'includes/config.php';

// Include Dompdf
require_once 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

$owner_id = $_SESSION['owner_id'];
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Date condition
$date_condition = "";
if ($filter == 'today') {
    $date_condition = "AND DATE(b.booking_date) = CURDATE()";
} elseif ($filter == 'week') {
    $date_condition = "AND YEARWEEK(b.booking_date) = YEARWEEK(CURDATE())";
} elseif ($filter == 'month') {
    $date_condition = "AND MONTH(b.booking_date) = MONTH(CURDATE()) AND YEAR(b.booking_date) = YEAR(CURDATE())";
}

// Get bookings
$stmt = $conn->prepare("
    SELECT b.*, g.name as ground_name, u.name as player_name 
    FROM bookings b
    JOIN time_slots ts ON b.slot_id = ts.slot_id
    JOIN grounds g ON ts.ground_id = g.ground_id
    JOIN players p ON b.player_id = p.player_id
    JOIN users u ON p.user_id = u.user_id
    WHERE g.owner_id = ?
    $date_condition
    ORDER BY b.booking_date DESC
");
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$result = $stmt->get_result();

// Calculate totals
$total_bookings = 0;
$total_revenue = 0;
$bookings_data = [];

while ($row = $result->fetch_assoc()) {
    $bookings_data[] = $row;
    $total_bookings++;
    if ($row['status'] == 'confirmed' || $row['status'] == 'completed') {
        $total_revenue += $row['total_amount'];
    }
}

// ===== GENERATE PDF =====
$options = new Options();
$options->set('defaultFont', 'Courier');
$dompdf = new Dompdf($options);

$html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background: #fff; color: #333; padding: 20px; }
        h1 { color: #7CCB96; border-bottom: 2px solid #7CCB96; padding-bottom: 10px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { border-bottom: none; }
        .header p { color: #888; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #7CCB96; color: #000; padding: 10px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #ddd; }
        .total { margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 8px; }
        .total span { font-weight: bold; color: #7CCB96; }
        .footer { text-align: center; margin-top: 30px; color: #888; font-size: 12px; border-top: 1px solid #ddd; padding-top: 15px; }
        .status-pending { color: #FFA500; }
        .status-confirmed { color: #7CCB96; }
        .status-completed { color: #4ECDC4; }
        .status-cancelled { color: #FF6B6B; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏆 KHELA HOBEE</h1>
        <h2>Booking Report</h2>
        <p>Generated: ' . date('d M Y, h:i A') . '</p>
        <p>Filter: ' . ucfirst($filter) . '</p>
    </div>

    <div class="total">
        <p>📊 Total Bookings: <span>' . $total_bookings . '</span></p>
        <p>💰 Total Revenue: <span>৳' . number_format($total_revenue, 2) . '</span></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Ref</th>
                <th>Ground</th>
                <th>Player</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>';

if (count($bookings_data) > 0) {
    foreach ($bookings_data as $row) {
        $status_color = 'status-' . $row['status'];
        $html .= '
            <tr>
                <td>' . $row['booking_reference'] . '</td>
                <td>' . $row['ground_name'] . '</td>
                <td>' . $row['player_name'] . '</td>
                <td>৳' . $row['total_amount'] . '</td>
                <td class="' . $status_color . '">' . ucfirst($row['status']) . '</td>
                <td>' . date('d M Y', strtotime($row['booking_date'])) . '</td>
            </tr>';
    }
} else {
    $html .= '
            <tr>
                <td colspan="6" style="text-align:center; padding:20px; color:#888;">No bookings found</td>
            </tr>';
}

$html .= '
        </tbody>
    </table>

    <div class="footer">
        © 2026 Khela Hobee | Generated from Owner Dashboard
    </div>
</body>
</html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("booking-report-" . date('Y-m-d') . ".pdf", array("Attachment" => 1));
?>