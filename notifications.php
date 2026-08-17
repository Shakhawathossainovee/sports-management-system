<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
require_once 'includes/config.php';

$user_id = $_SESSION['user_id'];

// Mark all as read
if (isset($_GET['mark_read'])) {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: notifications.php");
    exit();
}

// Get notifications
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Notifications | 🏆 KHELA HOBEE</title>
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
                    <li><a href="profile.php" >Profile</a></li>
                    <li><a href="notifications.php" class="active">Notifications</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div style="max-width:800px; margin:40px auto; padding:0 20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 style="color:#7CCB96;">
                <i class="fas fa-bell"></i> Notifications
            </h2>
            <a href="notifications.php?mark_read=1" style="color:#7CCB96; text-decoration:none; font-size:14px;">
                Mark all as read
            </a>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($notification = $result->fetch_assoc()): ?>
                <div style="background:<?php echo $notification['is_read'] ? '#1a1a1a' : '#1a2a2a'; ?>; padding:15px 20px; margin:10px 0; border-radius:8px; border-left:4px solid <?php echo $notification['is_read'] ? '#555' : '#7CCB96'; ?>;">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap;">
                        <div>
                            <h4 style="color:#fff; margin:0;"><?php echo $notification['title']; ?></h4>
                            <p style="color:#bbb; margin:5px 0; font-size:14px;"><?php echo $notification['message']; ?></p>
                            <p style="color:#666; font-size:12px; margin:0;">
                                <i class="fas fa-clock"></i> <?php echo date('d M Y, h:i A', strtotime($notification['created_at'])); ?>
                            </p>
                        </div>
                        <?php if (!$notification['is_read']): ?>
                            <span style="background:#7CCB96; color:black; padding:2px 10px; border-radius:20px; font-size:11px; font-weight:bold;">New</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="background:#1a1a1a; padding:50px; border-radius:12px; text-align:center;">
                <div style="font-size:60px;">🔔</div>
                <p style="color:#888; font-size:16px;">No notifications yet.</p>
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