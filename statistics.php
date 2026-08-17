<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.html");
    exit();
}
require_once 'includes/config.php';

// ===== USERS STATS =====
$total_users = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$total_players = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'player'")->fetch_assoc()['total'];
$total_owners = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'owner'")->fetch_assoc()['total'];
$total_admins = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin'")->fetch_assoc()['total'];

// ===== GROUNDS STATS =====
$total_grounds = $conn->query("SELECT COUNT(*) as total FROM grounds")->fetch_assoc()['total'];
$active_grounds = $conn->query("SELECT COUNT(*) as total FROM grounds WHERE status = 'active'")->fetch_assoc()['total'];
$pending_grounds = $conn->query("SELECT COUNT(*) as total FROM grounds WHERE status = 'pending'")->fetch_assoc()['total'];

// ===== BOOKINGS STATS =====
$total_bookings = $conn->query("SELECT COUNT(*) as total FROM bookings")->fetch_assoc()['total'];
$pending_bookings = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'pending'")->fetch_assoc()['total'];
$confirmed_bookings = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'confirmed'")->fetch_assoc()['total'];
$completed_bookings = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'completed'")->fetch_assoc()['total'];
$cancelled_bookings = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'cancelled'")->fetch_assoc()['total'];

// ===== REVENUE STATS =====
$today_revenue = $conn->query("SELECT SUM(total_amount) as total FROM bookings WHERE DATE(booking_date) = CURDATE() AND status IN ('confirmed', 'completed')")->fetch_assoc()['total'] ?? 0;
$month_revenue = $conn->query("SELECT SUM(total_amount) as total FROM bookings WHERE MONTH(booking_date) = MONTH(CURDATE()) AND YEAR(booking_date) = YEAR(CURDATE()) AND status IN ('confirmed', 'completed')")->fetch_assoc()['total'] ?? 0;
$total_revenue = $conn->query("SELECT SUM(total_amount) as total FROM bookings WHERE status IN ('confirmed', 'completed')")->fetch_assoc()['total'] ?? 0;

// ===== REVIEWS STATS =====
$total_reviews = $conn->query("SELECT COUNT(*) as total FROM reviews")->fetch_assoc()['total'];
$avg_rating = $conn->query("SELECT AVG(rating) as avg FROM reviews")->fetch_assoc()['avg'] ?? 0;

// ===== RECENT BOOKINGS =====
$recent_bookings = $conn->query("
    SELECT b.booking_reference, b.total_amount, b.status, b.booking_date, 
           g.name as ground_name, u.name as player_name
    FROM bookings b
    JOIN time_slots ts ON b.slot_id = ts.slot_id
    JOIN grounds g ON ts.ground_id = g.ground_id
    JOIN players p ON b.player_id = p.player_id
    JOIN users u ON p.user_id = u.user_id
    ORDER BY b.booking_date DESC LIMIT 10
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Statistics | 🏆 Khela Hobe</title>
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
                    <li><a href="admin-dashboard.php">Dashboard</a></li>
                    <li><a href="statistics.php" class="active">Statistics</a></li>
                    <li><a href="admin-contacts.php">Contacts</a></li>
                    <li><a href="audit_logs.php">Audit Logs</a></li>
                </ul>
            </nav>
            <div class="nav-btn">
                <a href="logout.php" class="login-btn">Logout</a>
            </div>
        </div>
    </header>

    <div style="max-width:1200px; margin:30px auto; padding:0 20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 style="color:#7CCB96; margin:0;">
                <i class="fas fa-chart-bar"></i> Platform Statistics
            </h2>
            <p style="color:#888; font-size:14px; margin:0;">
                Last updated: <?php echo date('d M Y, h:i A'); ?>
            </p>
        </div>

        <!-- ===== SUMMARY CARDS ===== -->
        <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:20px; margin-bottom:30px;">
            <div style="background:#1a1a1a; border-radius:12px; padding:20px; text-align:center; border:1px solid #2a2a2a;">
                <p style="color:#888; font-size:13px;">Total Users</p>
                <h2 style="color:#7CCB96; font-size:32px;"><?php echo $total_users; ?></h2>
                <p style="color:#555; font-size:12px;">
                    👤 <?php echo $total_players; ?> Players | 🏢 <?php echo $total_owners; ?> Owners
                </p>
            </div>
            <div style="background:#1a1a1a; border-radius:12px; padding:20px; text-align:center; border:1px solid #2a2a2a;">
                <p style="color:#888; font-size:13px;">Total Grounds</p>
                <h2 style="color:#4ECDC4; font-size:32px;"><?php echo $total_grounds; ?></h2>
                <p style="color:#555; font-size:12px;">
                    ✅ <?php echo $active_grounds; ?> Active | ⏳ <?php echo $pending_grounds; ?> Pending
                </p>
            </div>
            <div style="background:#1a1a1a; border-radius:12px; padding:20px; text-align:center; border:1px solid #2a2a2a;">
                <p style="color:#888; font-size:13px;">Total Bookings</p>
                <h2 style="color:#FFD700; font-size:32px;"><?php echo $total_bookings; ?></h2>
                <p style="color:#555; font-size:12px;">
                    ⏳ <?php echo $pending_bookings; ?> Pending | ✅ <?php echo $confirmed_bookings; ?> Confirmed
                </p>
            </div>
            <div style="background:#1a1a1a; border-radius:12px; padding:20px; text-align:center; border:1px solid #2a2a2a;">
                <p style="color:#888; font-size:13px;">Total Revenue</p>
                <h2 style="color:#FF6B6B; font-size:32px;">৳<?php echo number_format($total_revenue, 2); ?></h2>
                <p style="color:#555; font-size:12px;">
                    📅 Today: ৳<?php echo number_format($today_revenue, 2); ?>
                </p>
            </div>
        </div>

        <!-- ===== DETAILED STATS ===== -->
        <div style="display:grid; grid-template-columns:2fr 1fr; gap:25px; margin-bottom:30px;">
            <!-- LEFT: Booking Stats -->
            <div style="background:#1a1a1a; border-radius:12px; padding:20px; border:1px solid #2a2a2a;">
                <h3 style="color:#7CCB96; margin-bottom:15px;">
                    <i class="fas fa-calendar-check"></i> Booking Summary
                </h3>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px;">
                    <div style="background:#0b0b0b; padding:15px; border-radius:8px; text-align:center;">
                        <p style="color:#888; font-size:12px;">Pending</p>
                        <h3 style="color:#FFA500; font-size:24px;"><?php echo $pending_bookings; ?></h3>
                    </div>
                    <div style="background:#0b0b0b; padding:15px; border-radius:8px; text-align:center;">
                        <p style="color:#888; font-size:12px;">Confirmed</p>
                        <h3 style="color:#7CCB96; font-size:24px;"><?php echo $confirmed_bookings; ?></h3>
                    </div>
                    <div style="background:#0b0b0b; padding:15px; border-radius:8px; text-align:center;">
                        <p style="color:#888; font-size:12px;">Completed</p>
                        <h3 style="color:#4ECDC4; font-size:24px;"><?php echo $completed_bookings; ?></h3>
                    </div>
                    <div style="background:#0b0b0b; padding:15px; border-radius:8px; text-align:center;">
                        <p style="color:#888; font-size:12px;">Cancelled</p>
                        <h3 style="color:#FF6B6B; font-size:24px;"><?php echo $cancelled_bookings; ?></h3>
                    </div>
                    <div style="background:#0b0b0b; padding:15px; border-radius:8px; text-align:center; grid-column:1/-1;">
                        <p style="color:#888; font-size:12px;">Average Rating</p>
                        <h3 style="color:#FFD700; font-size:24px;"><?php echo number_format($avg_rating, 1); ?> ⭐</h3>
                        <p style="color:#555; font-size:11px;">Based on <?php echo $total_reviews; ?> reviews</p>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Revenue Stats -->
            <div style="background:#1a1a1a; border-radius:12px; padding:20px; border:1px solid #2a2a2a;">
                <h3 style="color:#7CCB96; margin-bottom:15px;">
                    <i class="fas fa-money-bill-wave"></i> Revenue
                </h3>
                <div style="background:#0b0b0b; padding:15px; border-radius:8px; margin-bottom:10px;">
                    <p style="color:#888; font-size:12px;">Today</p>
                    <h3 style="color:#7CCB96; font-size:24px;">৳<?php echo number_format($today_revenue, 2); ?></h3>
                </div>
                <div style="background:#0b0b0b; padding:15px; border-radius:8px; margin-bottom:10px;">
                    <p style="color:#888; font-size:12px;">This Month</p>
                    <h3 style="color:#FFD700; font-size:24px;">৳<?php echo number_format($month_revenue, 2); ?></h3>
                </div>
                <div style="background:#0b0b0b; padding:15px; border-radius:8px;">
                    <p style="color:#888; font-size:12px;">Total Revenue</p>
                    <h3 style="color:#4ECDC4; font-size:24px;">৳<?php echo number_format($total_revenue, 2); ?></h3>
                </div>
            </div>
        </div>

        <!-- ===== RECENT BOOKINGS ===== -->
        <div style="background:#1a1a1a; border-radius:12px; padding:20px; border:1px solid #2a2a2a;">
            <h3 style="color:#7CCB96; margin-bottom:15px;">
                <i class="fas fa-clock"></i> Recent Bookings
            </h3>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; color:white; font-size:14px;">
                    <thead>
                        <tr style="border-bottom:2px solid #7CCB96;">
                            <th style="padding:10px; text-align:left;">Reference</th>
                            <th style="padding:10px; text-align:left;">Ground</th>
                            <th style="padding:10px; text-align:left;">Player</th>
                            <th style="padding:10px; text-align:left;">Amount</th>
                            <th style="padding:10px; text-align:left;">Status</th>
                            <th style="padding:10px; text-align:left;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recent_bookings && $recent_bookings->num_rows > 0): ?>
                            <?php while ($row = $recent_bookings->fetch_assoc()): 
                                $status_color = $row['status'] == 'confirmed' ? '#7CCB96' : ($row['status'] == 'pending' ? '#FFA500' : '#FF6B6B');
                            ?>
                                <tr style="border-bottom:1px solid #2a2a2a;">
                                    <td style="padding:10px;"><?php echo htmlspecialchars($row['booking_reference']); ?></td>
                                    <td style="padding:10px;"><?php echo htmlspecialchars($row['ground_name']); ?></td>
                                    <td style="padding:10px;"><?php echo htmlspecialchars($row['player_name']); ?></td>
                                    <td style="padding:10px; color:#7CCB96;">৳<?php echo htmlspecialchars($row['total_amount']); ?></td>
                                    <td style="padding:10px;">
                                        <span style="background:<?php echo $status_color; ?>; color:black; padding:2px 12px; border-radius:20px; font-size:11px; font-weight:bold;">
                                            <?php echo htmlspecialchars($row['status']); ?>
                                        </span>
                                    </td>
                                    <td style="padding:10px; color:#888;"><?php echo date('d M Y', strtotime($row['booking_date'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="padding:20px; text-align:center; color:#888;">No recent bookings</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

</body>
</html>