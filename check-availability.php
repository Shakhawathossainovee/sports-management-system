<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}
require_once 'includes/config.php';

$ground_id = isset($_GET['ground_id']) ? (int)$_GET['ground_id'] : 0;
$date = isset($_GET['date']) ? $_GET['date'] : '';

if (!$ground_id || !$date) {
    echo json_encode(['error' => 'Missing parameters']);
    exit();
}

// Get available slots for this ground and date
$stmt = $conn->prepare("
    SELECT slot_id, start_time, end_time, is_available
    FROM time_slots 
    WHERE ground_id = ? AND date = ? AND is_available = 1
    ORDER BY start_time
");
$stmt->bind_param("is", $ground_id, $date);
$stmt->execute();
$result = $stmt->get_result();

$slots = [];
while ($row = $result->fetch_assoc()) {
    $slots[] = [
        'id' => $row['slot_id'],
        'start' => date('h:i A', strtotime($row['start_time'])),
        'end' => date('h:i A', strtotime($row['end_time'])),
        'available' => (bool)$row['is_available']
    ];
}

echo json_encode(['success' => true, 'slots' => $slots]);
?>