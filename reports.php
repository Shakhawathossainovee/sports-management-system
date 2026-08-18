<?php
session_start();
if (!isset($_SESSION['owner_id'])) {
    header("Location: owner-login.php");
    exit();
}
require_once 'includes/config.php';

$owner_id = $_SESSION['owner_id'];
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$view = isset($_GET['view']) ? $_GET['view'] : 'bookings';

// Date condition
$date_condition = "";
if ($filter == 'today') {
    $date_condition = "AND DATE(b.booking_date) = CURDATE()";
} elseif ($filter == 'week') {
    $date_condition = "AND YEARWEEK(b.booking_date) = YEARWEEK(CURDATE())";
} elseif ($filter == 'month') {
    $date_condition = "AND MONTH(b.booking_date) = MONTH(CURDATE()) AND YEAR(b.booking_date) = YEAR(CURDATE())";
}

// Revenue Query
$revenue_stmt = $conn->prepare("
    SELECT SUM(b.total_amount) as total_revenue, COUNT(b.booking_id) as total_bookings
    FROM bookings b
    JOIN time_slots ts ON b.slot_id = ts.slot_id
    JOIN grounds g ON ts.ground_id = g.ground_id
    WHERE g.owner_id = ? AND b.status IN ('confirmed', 'completed')
    $date_condition
");
$revenue_stmt->bind_param("i", $owner_id);
$revenue_stmt->execute();
$revenue_result = $revenue_stmt->get_result();
$revenue_data = $revenue_result->fetch_assoc();

// Bookings Query
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

// Occupancy Query
$occ_stmt = $conn->prepare("
    SELECT 
        g.name as ground_name,
        COUNT(b.booking_id) as total_bookings,
        SUM(b.total_amount) as total_revenue,
        COUNT(DISTINCT b.player_id) as unique_players
    FROM grounds g
    LEFT JOIN time_slots ts ON g.ground_id = ts.ground_id
    LEFT JOIN bookings b ON ts.slot_id = b.slot_id AND b.status IN ('confirmed', 'completed')
    WHERE g.owner_id = ?
    GROUP BY g.ground_id
    ORDER BY total_bookings DESC
");
$occ_stmt->bind_param("i", $owner_id);
$occ_stmt->execute();
$occ_result = $occ_stmt->get_result();

// Cancellations Query
$cancel_stmt = $conn->prepare("
    SELECT b.*, g.name as ground_name, u.name as player_name 
    FROM bookings b
    JOIN time_slots ts ON b.slot_id = ts.slot_id
    JOIN grounds g ON ts.ground_id = g.ground_id
    JOIN players p ON b.player_id = p.player_id
    JOIN users u ON p.user_id = u.user_id
    WHERE g.owner_id = ? AND b.status = 'cancelled'
    $date_condition
    ORDER BY b.booking_date DESC
");
$cancel_stmt->bind_param("i", $owner_id);
$cancel_stmt->execute();
$cancel_result = $cancel_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reports | 🏆 Khela Hobe</title>
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
                    <li><a href="owner-dashboard.php">Dashboard</a></li>
                    <li><a href="add-ground.php">Add Ground</a></li>
                    <li><a href="add-slot.php">Add Slot</a></li>
                    <li><a href="reports.php" class="active">Reports</a></li>
                </ul>
            </nav>
            <div class="nav-btn">
                <a href="logout.php" class="login-btn">Logout</a>
            </div>
        </div>
    </header>

    <div style="max-width:1100px; margin:30px auto; padding:0 20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; margin-bottom:15px;">
            <h2 style="color:#7CCB96; margin:0;">📊 Reports</h2>
            
            <!-- ===== PDF DOWNLOAD BUTTON ===== -->
            <a href="generate-pdf.php?filter=<?php echo $filter; ?>" 
               style="background:#FF6B6B; color:white; padding:10px 24px; border-radius:8px; text-decoration:none; font-weight:bold; display:inline-flex; align-items:center; gap:8px;">
                <i class="fas fa-file-pdf"></i> Download PDF
            </a>
        </div>
        
        <!-- View Tabs -->
        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:15px;">
            <a href="reports.php?view=bookings&filter=<?php echo $filter; ?>" style="background:<?php echo $view=='bookings'?'#7CCB96':'#1a1a1a'; ?>; color:<?php echo $view=='bookings'?'black':'white'; ?>; padding:8px 20px; border-radius:8px; text-decoration:none;">📋 Bookings</a>
            <a href="reports.php?view=revenue&filter=<?php echo $filter; ?>" style="background:<?php echo $view=='revenue'?'#7CCB96':'#1a1a1a'; ?>; color:<?php echo $view=='revenue'?'black':'white'; ?>; padding:8px 20px; border-radius:8px; text-decoration:none;">💰 Revenue</a>
            <a href="reports.php?view=occupancy&filter=<?php echo $filter; ?>" style="background:<?php echo $view=='occupancy'?'#7CCB96':'#1a1a1a'; ?>; color:<?php echo $view=='occupancy'?'black':'white'; ?>; padding:8px 20px; border-radius:8px; text-decoration:none;">📈 Occupancy</a>
            <a href="reports.php?view=cancellations&filter=<?php echo $filter; ?>" style="background:<?php echo $view=='cancellations'?'#7CCB96':'#1a1a1a'; ?>; color:<?php echo $view=='cancellations'?'black':'white'; ?>; padding:8px 20px; border-radius:8px; text-decoration:none;">❌ Cancellations</a>
        </div>

        <!-- Filter Tabs -->
        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px;">
            <a href="reports.php?view=<?php echo $view; ?>&filter=all" style="background:<?php echo $filter=='all'?'#7CCB96':'#1a1a1a'; ?>; color:<?php echo $filter=='all'?'black':'white'; ?>; padding:8px 20px; border-radius:8px; text-decoration:none;">All</a>
            <a href="reports.php?view=<?php echo $view; ?>&filter=today" style="background:<?php echo $filter=='today'?'#7CCB96':'#1a1a1a'; ?>; color:<?php echo $filter=='today'?'black':'white'; ?>; padding:8px 20px; border-radius:8px; text-decoration:none;">Today</a>
            <a href="reports.php?view=<?php echo $view; ?>&filter=week" style="background:<?php echo $filter=='week'?'#7CCB96':'#1a1a1a'; ?>; color:<?php echo $filter=='week'?'black':'white'; ?>; padding:8px 20px; border-radius:8px; text-decoration:none;">This Week</a>
            <a href="reports.php?view=<?php echo $view; ?>&filter=month" style="background:<?php echo $filter=='month'?'#7CCB96':'#1a1a1a'; ?>; color:<?php echo $filter=='month'?'black':'white'; ?>; padding:8px 20px; border-radius:8px; text-decoration:none;">This Month</a>
        </div>

        <?php if ($view == 'revenue'): ?>
            <!-- ===== REVENUE VIEW ===== -->
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px; margin-bottom:30px;">
                <div style="background:#1a1a1a; border-radius:12px; padding:20px; text-align:center; border:1px solid #2a2a2a;">
                    <p style="color:#888; font-size:14px;">Total Revenue</p>
                    <h2 style="color:#7CCB96; font-size:32px;">৳<?php echo number_format($revenue_data['total_revenue'] ?? 0, 2); ?></h2>
                </div>
                <div style="background:#1a1a1a; border-radius:12px; padding:20px; text-align:center; border:1px solid #2a2a2a;">
                    <p style="color:#888; font-size:14px;">Total Bookings</p>
                    <h2 style="color:#fff; font-size:32px;"><?php echo $revenue_data['total_bookings'] ?? 0; ?></h2>
                </div>
                <div style="background:#1a1a1a; border-radius:12px; padding:20px; text-align:center; border:1px solid #2a2a2a;">
                    <p style="color:#888; font-size:14px;">Average Revenue</p>
                    <h2 style="color:#FFD700; font-size:32px;">৳<?php 
                        $avg = ($revenue_data['total_bookings'] ?? 0) > 0 ? ($revenue_data['total_revenue'] / $revenue_data['total_bookings']) : 0;
                        echo number_format($avg, 2); 
                    ?></h2>
                </div>
            </div>

        <?php elseif ($view == 'occupancy'): ?>
            <!-- ===== OCCUPANCY VIEW ===== -->
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px,1fr)); gap:20px;">
                <?php if ($occ_result && $occ_result->num_rows > 0): ?>
                    <?php while ($ground = $occ_result->fetch_assoc()): ?>
                        <div style="background:#1a1a1a; border-radius:12px; padding:20px; border:1px solid #2a2a2a;">
                            <h3 style="color:#7CCB96; margin-bottom:10px;"><?php echo $ground['ground_name']; ?></h3>
                            <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #2a2a2a;">
                                <span style="color:#888;">Total Bookings</span>
                                <span style="color:#fff; font-weight:bold;"><?php echo $ground['total_bookings'] ?? 0; ?></span>
                            </div>
                            <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #2a2a2a;">
                                <span style="color:#888;">Revenue</span>
                                <span style="color:#7CCB96; font-weight:bold;">৳<?php echo number_format($ground['total_revenue'] ?? 0, 2); ?></span>
                            </div>
                            <div style="display:flex; justify-content:space-between; padding:8px 0;">
                                <span style="color:#888;">Unique Players</span>
                                <span style="color:#FFD700; font-weight:bold;"><?php echo $ground['unique_players'] ?? 0; ?></span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="background:#1a1a1a; padding:40px; border-radius:12px; text-align:center; border:1px solid #2a2a2a; grid-column:1/-1;">
                        <p style="color:#888;">No occupancy data found.</p>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($view == 'cancellations'): ?>
            <!-- ===== CANCELLATIONS VIEW ===== -->
            <div style="background:#1a1a1a; border-radius:12px; padding:20px; overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; color:white;">
                    <thead>
                        <tr style="border-bottom:2px solid #FF6B6B;">
                            <th style="padding:12px; text-align:left;">Ref</th>
                            <th style="padding:12px; text-align:left;">Ground</th>
                            <th style="padding:12px; text-align:left;">Player</th>
                            <th style="padding:12px; text-align:left;">Amount</th>
                            <th style="padding:12px; text-align:left;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($cancel_result && $cancel_result->num_rows > 0): ?>
                            <?php while ($row = $cancel_result->fetch_assoc()): ?>
                                <tr style="border-bottom:1px solid #2a2a2a;">
                                    <td style="padding:12px;"><?php echo $row['booking_reference']; ?></td>
                                    <td style="padding:12px;"><?php echo $row['ground_name']; ?></td>
                                    <td style="padding:12px;"><?php echo $row['player_name']; ?></td>
                                    <td style="padding:12px; color:#FF6B6B;"><?php echo $row['total_amount']; ?> Tk</td>
                                    <td style="padding:12px; color:#888;"><?php echo date('d M Y', strtotime($row['booking_date'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="padding:30px; text-align:center; color:#888;">No cancelled bookings found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>
            <!-- ===== BOOKINGS VIEW ===== -->
            <div style="background:#1a1a1a; border-radius:12px; padding:20px; overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; color:white;">
                    <thead>
                        <tr style="border-bottom:2px solid #7CCB96;">
                            <th style="padding:12px; text-align:left;">Ref</th>
                            <th style="padding:12px; text-align:left;">Ground</th>
                            <th style="padding:12px; text-align:left;">Player</th>
                            <th style="padding:12px; text-align:left;">Amount</th>
                            <th style="padding:12px; text-align:left;">Status</th>
                            <th style="padding:12px; text-align:left;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr style="border-bottom:1px solid #2a2a2a;">
                                    <td style="padding:12px;"><?php echo $row['booking_reference']; ?></td>
                                    <td style="padding:12px;"><?php echo $row['ground_name']; ?></td>
                                    <td style="padding:12px;"><?php echo $row['player_name']; ?></td>
                                    <td style="padding:12px; color:#7CCB96;"><?php echo $row['total_amount']; ?> Tk</td>
                                    <td style="padding:12px;">
                                        <span style="background:<?php echo $row['status']=='confirmed'?'#7CCB96':($row['status']=='pending'?'#FFA500':'#FF6B6B'); ?>; color:black; padding:2px 12px; border-radius:20px; font-size:11px; font-weight:bold;">
                                            <?php echo $row['status']; ?>
                                        </span>
                                    </td>
                                    <td style="padding:12px; color:#888;"><?php echo date('d M Y', strtotime($row['booking_date'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="padding:30px; text-align:center; color:#888;">No bookings found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

</body>
</html>