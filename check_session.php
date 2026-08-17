<?php
session_start();
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Also check if player exists
require_once 'includes/config.php';
$user_id = $_SESSION['user_id'] ?? 0;
$stmt = $conn->prepare("SELECT * FROM players WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$player = $result->fetch_assoc();

echo "<br>Player record for user_id = $user_id:<br>";
print_r($player);
?>