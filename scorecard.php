<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
require_once 'includes/config.php';

$booking_id = isset($_GET['booking_id']) ? $_GET['booking_id'] : 0;
$user_id = $_SESSION['user_id'];

// Get booking details
$stmt = $conn->prepare("
    SELECT b.*, g.name as ground_name 
    FROM bookings b
    JOIN time_slots ts ON b.slot_id = ts.slot_id
    JOIN grounds g ON ts.ground_id = g.ground_id
    WHERE b.booking_id = ? AND b.player_id = (SELECT player_id FROM players WHERE user_id = ?)
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

// Check if scorecard already exists
$check_stmt = $conn->prepare("SELECT scorecard_id FROM scorecards WHERE booking_id = ?");
$check_stmt->bind_param("i", $booking_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$already_recorded = $check_result->num_rows > 0;
$check_stmt->close();

$message = '';
$message_type = '';

// Handle scorecard submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $team1 = trim($_POST['team1']);
    $team2 = trim($_POST['team2']);
    $score1 = (int)$_POST['score1'];
    $score2 = (int)$_POST['score2'];
    $winner = trim($_POST['winner']);
    $man_of_match = trim($_POST['man_of_match']);
    $recorded_by = $booking['player_id'];
    
    if (empty($team1) || empty($team2)) {
        $message = "Team names are required.";
        $message_type = "error";
    } else {
        $stmt = $conn->prepare("INSERT INTO scorecards (booking_id, team_1_name, team_2_name, team_1_score, team_2_score, winner, man_of_the_match, recorded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issiissi", $booking_id, $team1, $team2, $score1, $score2, $winner, $man_of_match, $recorded_by);
        
        if ($stmt->execute()) {
            $message = "Scorecard recorded successfully!";
            $message_type = "success";
        } else {
            $message = "Failed to record scorecard: " . $stmt->error;
            $message_type = "error";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Scorecard | 🏆 KHELA HOBEE</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container navbar">
            <div class="logo"><h2>🏆 KHELA HOBEE</h2></div>
            <nav>
                <ul>
                    <li><a href="index.html" >Home</a></li>
                    <li><a href="search.php" >Turfs & Fields</a></li>
                    <li><a href="my-bookings.php" >My Bookings</a></li>
                    <li><a href="profile.php" >Profile</a></li>
                    <li><a href="notifications.php" >Notifications</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container form-container">
        <div class="form-box">
            <?php if ($already_recorded): ?>
                <div style="background:#1a3a2a; padding:30px; border-radius:12px; text-align:center; border:2px solid #7CCB96;">
                    <div style="font-size:60px;">📊</div>
                    <h2 style="color:#7CCB96;">Scorecard Already Recorded!</h2>
                    <p style="color:#bbb;">You have already recorded the scorecard for this booking.</p>
                    <a href="my-bookings.php" style="display:inline-block; background:#7CCB96; color:black; padding:12px 30px; border-radius:8px; text-decoration:none; font-weight:bold; margin-top:15px;">
                        <i class="fas fa-arrow-left"></i> My Bookings
                    </a>
                </div>
            <?php elseif ($message_type == 'success'): ?>
                <div style="background:#1a3a2a; padding:30px; border-radius:12px; text-align:center; border:2px solid #7CCB96;">
                    <div style="font-size:60px;">✅</div>
                    <h2 style="color:#7CCB96;">Scorecard Recorded!</h2>
                    <p style="color:#bbb;"><?php echo $message; ?></p>
                    <a href="my-bookings.php" style="display:inline-block; background:#7CCB96; color:black; padding:12px 30px; border-radius:8px; text-decoration:none; font-weight:bold; margin-top:15px;">
                        <i class="fas fa-arrow-left"></i> My Bookings
                    </a>
                </div>
            <?php elseif ($message_type == 'error'): ?>
                <div style="background:#3a1a1a; padding:30px; border-radius:12px; text-align:center; border:2px solid #ff6b6b;">
                    <div style="font-size:60px;">❌</div>
                    <h2 style="color:#ff6b6b;">Failed</h2>
                    <p style="color:#bbb;"><?php echo $message; ?></p>
                    <a href="scorecard.php?booking_id=<?php echo $booking_id; ?>" style="display:inline-block; background:#7CCB96; color:black; padding:12px 30px; border-radius:8px; text-decoration:none; font-weight:bold; margin-top:15px;">
                        <i class="fas fa-redo"></i> Try Again
                    </a>
                </div>
            <?php else: ?>
                <h2>📊 Record Scorecard</h2>
                <p>Record match scores for <strong><?php echo $booking['ground_name']; ?></strong></p>

                <form method="POST">
                    <div class="form-group">
                        <label style="color:#ccc;">Team 1 Name</label>
                        <input type="text" name="team1" placeholder="e.g., Dhaka Tigers" required>
                    </div>
                    <div class="form-group">
                        <label style="color:#ccc;">Team 1 Score</label>
                        <input type="number" name="score1" placeholder="0" value="0">
                    </div>
                    <div class="form-group">
                        <label style="color:#ccc;">Team 2 Name</label>
                        <input type="text" name="team2" placeholder="e.g., Chittagong Kings" required>
                    </div>
                    <div class="form-group">
                        <label style="color:#ccc;">Team 2 Score</label>
                        <input type="number" name="score2" placeholder="0" value="0">
                    </div>
                    <div class="form-group">
                        <label style="color:#ccc;">Winner</label>
                        <input type="text" name="winner" placeholder="Winning team name">
                    </div>
                    <div class="form-group">
                        <label style="color:#ccc;">Man of the Match</label>
                        <input type="text" name="man_of_match" placeholder="Player name">
                    </div>
                    <button type="submit" class="submit-btn">Save Scorecard</button>
                </form>

                <p style="margin-top:15px; text-align:center;">
                    <a href="my-bookings.php">← Back to My Bookings</a>
                </p>
            <?php endif; ?>
        </div>
        <div class="hero-right">
            <img src="players.png" alt="Scorecard">
        </div>
    </div>
</body>
</html>

