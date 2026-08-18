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
require_once 'includes/logger.php';

$user_id = $_SESSION['user_id'];

// Get player_id
$player_stmt = $conn->prepare("SELECT player_id FROM players WHERE user_id = ?");
$player_stmt->bind_param("i", $user_id);
$player_stmt->execute();
$player_result = $player_stmt->get_result();
$player = $player_result->fetch_assoc();
$player_id = $player['player_id'] ?? 0;
$player_stmt->close();

$message = '';
$message_type = '';

// Handle match creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_match'])) {
    $ground_id = $_POST['ground_id'];
    $sport_type = $_POST['sport_type'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $players_needed = $_POST['players_needed'];
    
    $stmt = $conn->prepare("INSERT INTO player_matches (player_id, ground_id, sport_type, date, time, players_needed) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisssi", $player_id, $ground_id, $sport_type, $date, $time, $players_needed);
    
    if ($stmt->execute()) {
        logAction($user_id, 'Create Match', 'Created a new match request');
        $message = "Match created successfully!";
        $message_type = "success";
    } else {
        $message = "Failed to create match.";
        $message_type = "error";
    }
    $stmt->close();
}

// Handle joining a match
if (isset($_GET['join'])) {
    $match_id = $_GET['join'];
    $stmt = $conn->prepare("UPDATE player_matches SET players_joined = players_joined + 1 WHERE match_id = ? AND players_joined < players_needed AND status = 'open'");
    $stmt->bind_param("i", $match_id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        // Check if full
        $check_stmt = $conn->prepare("SELECT players_joined, players_needed FROM player_matches WHERE match_id = ?");
        $check_stmt->bind_param("i", $match_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $check_data = $check_result->fetch_assoc();
        $check_stmt->close();
        
        if ($check_data['players_joined'] >= $check_data['players_needed']) {
            $conn->query("UPDATE player_matches SET status = 'full' WHERE match_id = $match_id");
        }
        
        logAction($user_id, 'Join Match', 'Joined match ID: ' . $match_id);
        header("Location: player-matching.php?msg=Joined successfully");
        exit();
    } else {
        header("Location: player-matching.php?msg=Match is full or closed");
        exit();
    }
    $stmt->close();
}

// Get all open matches
$matches_stmt = $conn->prepare("
    SELECT pm.*, g.name as ground_name, g.location, u.name as creator_name
    FROM player_matches pm
    JOIN grounds g ON pm.ground_id = g.ground_id
    JOIN players p ON pm.player_id = p.player_id
    JOIN users u ON p.user_id = u.user_id
    WHERE pm.status = 'open'
    ORDER BY pm.date ASC, pm.time ASC
");
$matches_stmt->execute();
$matches_result = $matches_stmt->get_result();

// Get grounds for dropdown
$grounds_stmt = $conn->query("SELECT ground_id, name, sport_type FROM grounds WHERE status = 'active'");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Player Matching | 🏆 KHELA HOBEE</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
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
                    <li><a href="index.html" >Home</a></li>
                    <li><a href="search.php" >Turfs & Fields</a></li>
                    <li><a href="my-bookings.php" >My Bookings</a></li>
                    <li><a href="player-matching.php" class="active">Find Players</a></li>
                    <li><a href="profile.php" >Profile</a></li>
                    <li><a href="notifications.php" >Notifications</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div style="max-width:1400px; margin:30px auto; padding:0 20px;">
        <h2 style="color:#7CCB96; margin-bottom:10px;">👥 Find Players</h2>
        <p style="color:#888; margin-bottom:20px;">Create a match request or join an existing one.</p>

        <?php if (isset($_GET['msg'])): ?>
            <div style="background:#1a3a2a; border:2px solid #7CCB96; padding:12px 20px; border-radius:8px; color:#7CCB96; margin-bottom:20px;">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['msg']); ?>
            </div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div style="padding:15px; border-radius:8px; margin-bottom:15px; background:<?php echo $message_type == 'success' ? '#1a3a2a' : '#3a1a1a'; ?>; border:2px solid <?php echo $message_type == 'success' ? '#7CCB96' : '#ff6b6b'; ?>;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Create Match Form -->
        <div style="background:#1a1a1a; border-radius:12px; padding:20px; border:1px solid #2a2a2a; margin-bottom:30px;">
            <h3 style="color:#7CCB96; margin-bottom:15px;">📝 Create Match Request</h3>
            <form method="POST">
                <input type="hidden" name="create_match" value="1">
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px,1fr)); gap:15px;">
                    <div>
                        <label style="color:#ccc; font-size:13px;">Ground *</label>
                        <select name="ground_id" required style="width:100%; padding:10px; border:none; border-radius:8px; background:#0b0b0b; color:white;">
                            <option value="">Select Ground</option>
                            <?php while ($g = $grounds_stmt->fetch_assoc()): ?>
                                <option value="<?php echo $g['ground_id']; ?>"><?php echo $g['name']; ?> (<?php echo $g['sport_type']; ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label style="color:#ccc; font-size:13px;">Sport *</label>
                        <select name="sport_type" required style="width:100%; padding:10px; border:none; border-radius:8px; background:#0b0b0b; color:white;">
                            <option value="">Select Sport</option>
                            <option value="Football">⚽ Football</option>
                            <option value="Cricket">🏏 Cricket</option>
                            <option value="Basketball">🏀 Basketball</option>
                        </select>
                    </div>
                    <div>
                        <label style="color:#ccc; font-size:13px;">Date *</label>
                        <input type="date" name="date" required style="width:100%; padding:10px; border:none; border-radius:8px; background:#0b0b0b; color:white;">
                    </div>
                    <div>
                        <label style="color:#ccc; font-size:13px;">Time *</label>
                        <input type="time" name="time" required style="width:100%; padding:10px; border:none; border-radius:8px; background:#0b0b0b; color:white;">
                    </div>
                    <div>
                        <label style="color:#ccc; font-size:13px;">Players Needed</label>
                        <input type="number" name="players_needed" value="5" min="2" max="22" style="width:100%; padding:10px; border:none; border-radius:8px; background:#0b0b0b; color:white;">
                    </div>
                </div>
                <button type="submit" style="margin-top:15px; background:#7CCB96; color:black; padding:10px 25px; border:none; border-radius:8px; font-weight:bold; cursor:pointer;">Create Match</button>
            </form>
        </div>

        <!-- Open Matches -->
        <h3 style="color:#7CCB96; margin-bottom:15px;">📋 Open Matches</h3>
        <?php if ($matches_result && $matches_result->num_rows > 0): ?>
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px,1fr)); gap:20px;">
                <?php while ($match = $matches_result->fetch_assoc()): ?>
                    <div style="background:#1a1a1a; border-radius:12px; padding:20px; border:1px solid #2a2a2a;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                            <span style="background:#7CCB96; color:black; padding:2px 10px; border-radius:20px; font-size:11px; font-weight:bold;"><?php echo $match['sport_type']; ?></span>
                            <span style="color:#888; font-size:12px;"><?php echo $match['players_joined']; ?>/<?php echo $match['players_needed']; ?> players</span>
                        </div>
                        <h4 style="color:#fff; margin:0 0 4px;"><?php echo $match['ground_name']; ?></h4>
                        <p style="color:#888; font-size:13px; margin:0 0 4px;">📍 <?php echo $match['location']; ?></p>
                        <p style="color:#888; font-size:13px; margin:0 0 4px;">📅 <?php echo date('d M Y', strtotime($match['date'])); ?> at <?php echo date('h:i A', strtotime($match['time'])); ?></p>
                        <p style="color:#666; font-size:12px;">👤 Created by: <?php echo $match['creator_name']; ?></p>
                        <?php if ($match['player_id'] != $player_id): ?>
                            <a href="player-matching.php?join=<?php echo $match['match_id']; ?>" style="display:block; text-align:center; background:#7CCB96; color:black; padding:8px; border-radius:8px; text-decoration:none; font-weight:bold; font-size:13px; margin-top:10px;">
                                <i class="fas fa-user-plus"></i> Join Match
                            </a>
                        <?php else: ?>
                            <span style="display:block; text-align:center; background:#555; color:#888; padding:8px; border-radius:8px; font-size:13px; margin-top:10px;">Your match</span>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div style="background:#1a1a1a; padding:40px; border-radius:12px; text-align:center; border:1px solid #2a2a2a;">
                <div style="font-size:48px; color:#555;">🏟️</div>
                <p style="color:#888; font-size:16px;">No open matches available. Create one!</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- ===== PLAYER IMAGES ===== -->
    <div class="player-image-left">
        <img src="players.png" alt="Player" />
    </div>
    <div class="player-image-right">
        <img src="players.png" alt="Player" />
    </div>

</div>
</body>
</html>