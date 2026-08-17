<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}
require_once 'includes/config.php';

$ground_id = isset($_GET['ground_id']) ? (int)$_GET['ground_id'] : 0;

if (!$ground_id) {
    echo json_encode(['error' => 'Missing ground_id']);
    exit();
}

// Get every distinct future date that has at least one open slot for this ground
$stmt = $conn->prepare("
    SELECT DISTINCT date
    FROM time_slots
    WHERE ground_id = ? AND is_available = 1 AND date >= CURDATE()
    ORDER BY date ASC
");
$stmt->bind_param("i", $ground_id);
$stmt->execute();
$result = $stmt->get_result();

$dates = [];
while ($row = $result->fetch_assoc()) {
    $dates[] = $row['date'];
}
$stmt->close();
$conn->close();

echo json_encode(['success' => true, 'available_dates' => $dates]);
?>
