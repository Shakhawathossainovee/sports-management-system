<?php
session_start();
if (!isset($_SESSION['owner_id'])) {
    header("Location: owner-login.php");
    exit();
}
require_once 'includes/config.php';
require_once 'includes/logger.php';

$owner_id = $_SESSION['owner_id'];

// Get owner's grounds
$grounds_stmt = $conn->prepare("SELECT ground_id, name, location FROM grounds WHERE owner_id = ?");
$grounds_stmt->bind_param("i", $owner_id);
$grounds_stmt->execute();
$grounds_result = $grounds_stmt->get_result();

// Get ALL bookings for owner's grounds (pending, confirmed, completed)
$bookings_stmt = $conn->prepare("
    SELECT b.*, g.name as ground_name, u.name as player_name 
    FROM bookings b
    JOIN time_slots ts ON b.slot_id = ts.slot_id
    JOIN grounds g ON ts.ground_id = g.ground_id
    JOIN players p ON b.player_id = p.player_id
    JOIN users u ON p.user_id = u.user_id
    WHERE g.owner_id = ? 
    ORDER BY FIELD(b.status, 'pending', 'confirmed', 'completed', 'cancelled'), b.booking_date ASC
");
$bookings_stmt->bind_param("i", $owner_id);
$bookings_stmt->execute();
$bookings_result = $bookings_stmt->get_result();

// Handle approve/reject/complete
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $booking_id = $_POST['booking_id'];
    $action = $_POST['action'];
    
    // Get booking reference for logging
    $ref_stmt = $conn->prepare("SELECT booking_reference FROM bookings WHERE booking_id = ?");
    $ref_stmt->bind_param("i", $booking_id);
    $ref_stmt->execute();
    $ref_result = $ref_stmt->get_result();
    $ref_data = $ref_result->fetch_assoc();
    $ref_stmt->close();
    
    if ($action == 'approve') {
        $new_status = 'confirmed';
    } elseif ($action == 'reject') {
        $new_status = 'cancelled';
    } elseif ($action == 'complete') {
        $new_status = 'completed';
    }
    
    $update_stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE booking_id = ?");
    $update_stmt->bind_param("si", $new_status, $booking_id);
    
    if ($update_stmt->execute()) {
        logAction($_SESSION['user_id'], 'Booking ' . ucfirst($new_status), 'Booking ' . $new_status . ': ' . ($ref_data['booking_reference'] ?? 'N/A'));
        header("Location: owner-dashboard.php?msg=Booking+" . $new_status);
        exit();
    }
    $update_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Owner Dashboard | 🏆 Khela Hobe</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Russo+One&family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
</head>
<body>

<div class="home-page">

    <!-- ===== NAVBAR ===== -->
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
                    <li><a href="index.html">Home</a></li>
                    <li><a href="owner-dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="add-ground.php">Add Ground</a></li>
                    <li><a href="add-slot.php">Add Slot</a></li>
                    <li><a href="reports.php">Reports</a></li>
                </ul>
            </nav>
            <div class="nav-btn">
                <a href="logout.php" class="login-btn">Logout</a>
            </div>
        </div>
    </header>

    <!-- ===== WELCOME BANNER ===== -->
    <div style="background:linear-gradient(135deg, #0b0b0b, #1a1a2e); padding:25px 20px; text-align:center; border-bottom:2px solid #7CCB96;">
        <h2 style="color:#7CCB96; font-size:24px; margin:0;">
            <i class="fas fa-store"></i> Owner Dashboard
        </h2>
        <p style="color:#888; font-size:14px; margin:5px 0 0;">
            Welcome, <strong style="color:#fff;"><?php echo $_SESSION['user_name']; ?></strong>!
        </p>
    </div>

    <!-- ===== MESSAGE ===== -->
    <?php if (isset($_GET['msg'])): ?>
        <div style="max-width:1000px; margin:15px auto; padding:0 20px;">
            <div style="background:#1a3a2a; border:2px solid #7CCB96; padding:12px 20px; border-radius:8px; color:#7CCB96;">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['msg']); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- ===== DASHBOARD GRID ===== -->
    <div style="max-width:1100px; margin:20px auto; padding:0 20px;">
        <div style="display:grid; grid-template-columns:1fr 2fr; gap:25px;">

            <!-- LEFT: GROUNDS -->
            <div style="background:#1a1a1a; border-radius:12px; padding:20px; border:1px solid #2a2a2a;">
                <h3 style="color:#7CCB96; font-size:18px; margin:0 0 15px;">
                    <i class="fas fa-map-pin"></i> Your Grounds
                </h3>
                <?php if ($grounds_result->num_rows > 0): ?>
                    <?php while ($ground = $grounds_result->fetch_assoc()): ?>
                        <div style="background:#0b0b0b; padding:12px 15px; margin:8px 0; border-radius:8px; border-left:3px solid #7CCB96;">
                            <strong style="color:#fff;"><?php echo $ground['name']; ?></strong>
                            <p style="color:#888; font-size:12px; margin:3px 0 0;">
                                <i class="fas fa-map-marker-alt"></i> <?php echo $ground['location']; ?>
                            </p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color:#888; font-size:14px;">No grounds added yet.</p>
                <?php endif; ?>
                <a href="add-ground.php" style="display:block; text-align:center; background:#7CCB96; color:black; padding:10px; border-radius:8px; text-decoration:none; font-weight:bold; font-size:14px; margin-top:15px;">
                    <i class="fas fa-plus-circle"></i> Register New Ground
                </a>
            </div>

            <!-- RIGHT: BOOKINGS -->
            <div style="background:#1a1a1a; border-radius:12px; padding:20px; border:1px solid #2a2a2a;">
                <h3 style="color:#7CCB96; font-size:18px; margin:0 0 15px;">
                    <i class="fas fa-clock"></i> Bookings
                </h3>
                <?php if ($bookings_result->num_rows > 0): ?>
                    <?php while ($booking = $bookings_result->fetch_assoc()): 
                        $status = $booking['status'];
                        $border_color = '#FFA500';
                        if ($status == 'pending') $border_color = '#FFA500';
                        elseif ($status == 'confirmed') $border_color = '#7CCB96';
                        elseif ($status == 'completed') $border_color = '#4ECDC4';
                        elseif ($status == 'cancelled') $border_color = '#FF6B6B';
                    ?>
                        <div style="background:#0b0b0b; padding:15px; margin:10px 0; border-radius:8px; border-left:3px solid <?php echo $border_color; ?>;">
                            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap;">
                                <div>
                                    <strong style="color:#fff;"><?php echo $booking['ground_name']; ?></strong>
                                    <p style="color:#888; font-size:13px; margin:3px 0;">
                                        <i class="fas fa-user"></i> <?php echo $booking['player_name']; ?>
                                    </p>
                                    <p style="color:#888; font-size:13px; margin:3px 0;">
                                        <i class="fas fa-money-bill"></i> ৳<?php echo $booking['total_amount']; ?>
                                    </p>
                                    <p style="color:#555; font-size:11px; margin:3px 0;">
                                        <i class="fas fa-hashtag"></i> <?php echo $booking['booking_reference']; ?>
                                    </p>
                                    <p style="color:#777; font-size:11px; margin:3px 0;">
                                        <i class="fas fa-tag"></i> Status: <strong style="color:<?php echo $border_color; ?>;"><?php echo strtoupper($status); ?></strong>
                                    </p>
                                </div>
                                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                    <?php if ($status == 'pending'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" style="background:#7CCB96; color:black; padding:6px 16px; border:none; border-radius:6px; cursor:pointer; font-weight:bold; font-size:13px;">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" style="background:#FF6B6B; color:white; padding:6px 16px; border:none; border-radius:6px; cursor:pointer; font-weight:bold; font-size:13px;">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </form>
                                    <?php elseif ($status == 'confirmed'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                            <input type="hidden" name="action" value="complete">
                                            <button type="submit" style="background:#4ECDC4; color:black; padding:6px 16px; border:none; border-radius:6px; cursor:pointer; font-weight:bold; font-size:13px;">
                                                <i class="fas fa-check-double"></i> Complete
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="text-align:center; padding:30px 0;">
                        <div style="font-size:40px; color:#555;">✅</div>
                        <p style="color:#888; font-size:15px;">No bookings found.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

</body>
</html>