<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.html");
    exit();
}
require_once 'includes/config.php';
require_once 'includes/logger.php';

// Get counts
$user_count = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$ground_count = $conn->query("SELECT COUNT(*) as total FROM grounds")->fetch_assoc()['total'];
$booking_count = $conn->query("SELECT COUNT(*) as total FROM bookings")->fetch_assoc()['total'];
$owner_count = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'owner'")->fetch_assoc()['total'];
$pending_count = $conn->query("SELECT COUNT(*) as total FROM grounds WHERE status = 'pending'")->fetch_assoc()['total'];

// ===== HANDLE DELETE USER =====
if (isset($_GET['delete_user'])) {
    $id = (int)$_GET['delete_user'];
    
    $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE user_id = ?");
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        logAction($_SESSION['user_id'], 'Delete User', 'Deleted user ID: ' . $id);
        
        $delete_stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $delete_stmt->bind_param("i", $id);
        $delete_stmt->execute();
        $delete_stmt->close();
        
        header("Location: admin-dashboard.php?msg=User deleted successfully");
    } else {
        header("Location: admin-dashboard.php?msg=User not found");
    }
    $check_stmt->close();
    exit();
}

// ===== HANDLE DELETE GROUND =====
if (isset($_GET['delete_ground'])) {
    $id = (int)$_GET['delete_ground'];
    logAction($_SESSION['user_id'], 'Delete Ground', 'Deleted ground ID: ' . $id);
    
    $delete_stmt = $conn->prepare("DELETE FROM grounds WHERE ground_id = ?");
    $delete_stmt->bind_param("i", $id);
    $delete_stmt->execute();
    $delete_stmt->close();
    
    header("Location: admin-dashboard.php?msg=Ground deleted");
    exit();
}

// ===== HANDLE APPROVE GROUND =====
if (isset($_GET['approve_ground'])) {
    $id = (int)$_GET['approve_ground'];
    logAction($_SESSION['user_id'], 'Approve Ground', 'Approved ground ID: ' . $id);
    
    $update_stmt = $conn->prepare("UPDATE grounds SET status = 'active' WHERE ground_id = ?");
    $update_stmt->bind_param("i", $id);
    $update_stmt->execute();
    $update_stmt->close();
    
    header("Location: admin-dashboard.php?msg=Ground approved");
    exit();
}

// ===== HANDLE REJECT GROUND =====
if (isset($_GET['reject_ground'])) {
    $id = (int)$_GET['reject_ground'];
    logAction($_SESSION['user_id'], 'Reject Ground', 'Rejected ground ID: ' . $id);
    
    $update_stmt = $conn->prepare("UPDATE grounds SET status = 'rejected' WHERE ground_id = ?");
    $update_stmt->bind_param("i", $id);
    $update_stmt->execute();
    $update_stmt->close();
    
    header("Location: admin-dashboard.php?msg=Ground rejected");
    exit();
}

// ===== HANDLE GRADE UPDATE =====
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['grade'])) {
    $ground_id = (int)$_POST['ground_id'];
    $grade = $_POST['grade'];
    logAction($_SESSION['user_id'], 'Update Grade', 'Set grade ' . $grade . ' for ground ID: ' . $ground_id);
    
    $update_stmt = $conn->prepare("UPDATE grounds SET grade = ? WHERE ground_id = ?");
    $update_stmt->bind_param("si", $grade, $ground_id);
    $update_stmt->execute();
    $update_stmt->close();
    
    header("Location: admin-dashboard.php?msg=Grade updated");
    exit();
}

// Get users
$users = $conn->query("SELECT user_id, name, email, role FROM users ORDER BY user_id DESC LIMIT 20");

// Get grounds
$grounds = $conn->query("SELECT ground_id, name, location, sport_type, status, grade FROM grounds ORDER BY ground_id DESC LIMIT 20");

// Get pending grounds for inspection
$pending_grounds = $conn->query("SELECT * FROM grounds WHERE status = 'pending'");

// Get bookings
$bookings = $conn->query("
    SELECT b.booking_id, b.booking_reference, b.total_amount, b.status, g.name as ground_name, u.name as player_name 
    FROM bookings b
    JOIN time_slots ts ON b.slot_id = ts.slot_id
    JOIN grounds g ON ts.ground_id = g.ground_id
    JOIN players p ON b.player_id = p.player_id
    JOIN users u ON p.user_id = u.user_id
    ORDER BY b.booking_id DESC LIMIT 20
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard | 🏆 Khela Hobe</title>
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
                    <li><a href="admin-dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="statistics.php">Statistics</a></li>
                    <li><a href="admin-contacts.php">Contacts</a></li>
                    <li><a href="audit_logs.php">Audit Logs</a></li>
                </ul>
            </nav>
            <div class="nav-btn">
                <a href="logout.php" class="login-btn">Logout</a>
            </div>
        </div>
    </header>

    <div style="background:linear-gradient(135deg, #0b0b0b, #1a1a2e); padding:25px 20px; text-align:center; border-bottom:2px solid #7CCB96;">
        <h2 style="color:#7CCB96; font-size:24px; margin:0;">
            <i class="fas fa-user-shield"></i> Admin Dashboard
        </h2>
        <p style="color:#888; font-size:14px; margin:5px 0 0;">
            Welcome, <strong style="color:#fff;"><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>!
        </p>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div style="max-width:1100px; margin:15px auto; padding:0 20px;">
            <div style="background:#1a3a2a; border:2px solid #7CCB96; padding:12px 20px; border-radius:8px; color:#7CCB96;">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['msg']); ?>
            </div>
        </div>
    <?php endif; ?>

    <div style="max-width:1100px; margin:20px auto; padding:0 20px;">

        <!-- Stats -->
        <div style="display:grid; grid-template-columns:repeat(5, 1fr); gap:20px; margin-bottom:30px;">
            <div style="background:#1a1a1a; border-radius:12px; padding:20px; text-align:center; border:1px solid #2a2a2a;">
                <p style="color:#888;">Total Users</p>
                <h2 style="color:#7CCB96; font-size:28px;"><?php echo $user_count; ?></h2>
            </div>
            <div style="background:#1a1a1a; border-radius:12px; padding:20px; text-align:center; border:1px solid #2a2a2a;">
                <p style="color:#888;">Ground Owners</p>
                <h2 style="color:#FFD700; font-size:28px;"><?php echo $owner_count; ?></h2>
            </div>
            <div style="background:#1a1a1a; border-radius:12px; padding:20px; text-align:center; border:1px solid #2a2a2a;">
                <p style="color:#888;">Total Grounds</p>
                <h2 style="color:#4ECDC4; font-size:28px;"><?php echo $ground_count; ?></h2>
            </div>
            <div style="background:#1a1a1a; border-radius:12px; padding:20px; text-align:center; border:1px solid #2a2a2a;">
                <p style="color:#888;">Total Bookings</p>
                <h2 style="color:#FF6B6B; font-size:28px;"><?php echo $booking_count; ?></h2>
            </div>
            <div style="background:#1a1a1a; border-radius:12px; padding:20px; text-align:center; border:1px solid #2a2a2a;">
                <p style="color:#888;">Pending Inspection</p>
                <h2 style="color:#FFA500; font-size:28px;"><?php echo $pending_count; ?></h2>
            </div>
        </div>

        <!-- ===== PENDING GROUNDS (Inspection) ===== -->
        <h3 style="color:#FFA500; margin-bottom:10px;">🔍 Pending Inspection</h3>
        <div style="background:#1a1a1a; border-radius:12px; padding:15px; overflow-x:auto; margin-bottom:30px;">
            <table style="width:100%; border-collapse:collapse; color:white;">
                <thead>
                    <tr style="border-bottom:2px solid #FFA500;">
                        <th style="padding:10px; text-align:left;">ID</th>
                        <th style="padding:10px; text-align:left;">Name</th>
                        <th style="padding:10px; text-align:left;">Location</th>
                        <th style="padding:10px; text-align:left;">Sport</th>
                        <th style="padding:10px; text-align:left;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($pending_grounds && $pending_grounds->num_rows > 0): ?>
                        <?php while ($row = $pending_grounds->fetch_assoc()): ?>
                            <tr style="border-bottom:1px solid #2a2a2a;">
                                <td style="padding:10px;"><?php echo htmlspecialchars($row['ground_id']); ?></td>
                                <td style="padding:10px;"><?php echo htmlspecialchars($row['name']); ?></td>
                                <td style="padding:10px;"><?php echo htmlspecialchars($row['location']); ?></td>
                                <td style="padding:10px;"><?php echo htmlspecialchars($row['sport_type']); ?></td>
                                <td style="padding:10px;">
                                    <a href="admin-dashboard.php?approve_ground=<?php echo $row['ground_id']; ?>" style="background:#7CCB96; color:black; padding:4px 12px; border-radius:4px; text-decoration:none; margin-right:5px;">✅ Approve</a>
                                    <a href="admin-dashboard.php?reject_ground=<?php echo $row['ground_id']; ?>" style="background:#FF6B6B; color:white; padding:4px 12px; border-radius:4px; text-decoration:none;" onclick="return confirm('Reject this ground?')">❌ Reject</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="padding:20px; text-align:center; color:#888;">No pending grounds for inspection.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Users Table -->
        <h3 style="color:#7CCB96; margin-bottom:10px;">👥 Recent Users</h3>
        <div style="background:#1a1a1a; border-radius:12px; padding:15px; overflow-x:auto; margin-bottom:30px;">
            <table style="width:100%; border-collapse:collapse; color:white;">
                <thead>
                    <tr style="border-bottom:2px solid #7CCB96;">
                        <th style="padding:10px; text-align:left;">ID</th>
                        <th style="padding:10px; text-align:left;">Name</th>
                        <th style="padding:10px; text-align:left;">Email</th>
                        <th style="padding:10px; text-align:left;">Role</th>
                        <th style="padding:10px; text-align:left;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $users->fetch_assoc()): ?>
                        <tr style="border-bottom:1px solid #2a2a2a;">
                            <td style="padding:10px;"><?php echo htmlspecialchars($row['user_id']); ?></td>
                            <td style="padding:10px;"><?php echo htmlspecialchars($row['name']); ?></td>
                            <td style="padding:10px;"><?php echo htmlspecialchars($row['email']); ?></td>
                            <td style="padding:10px;"><span style="background:#7CCB96; color:black; padding:2px 10px; border-radius:20px; font-size:11px;"><?php echo htmlspecialchars($row['role']); ?></span></td>
                            <td style="padding:10px;"><a href="admin-dashboard.php?delete_user=<?php echo $row['user_id']; ?>" onclick="return confirm('Delete this user?')" style="color:#FF6B6B;">Delete</a></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Grounds Table -->
        <h3 style="color:#7CCB96; margin-bottom:10px;">🏟️ Recent Grounds</h3>
        <div style="background:#1a1a1a; border-radius:12px; padding:15px; overflow-x:auto; margin-bottom:30px;">
            <table style="width:100%; border-collapse:collapse; color:white;">
                <thead>
                    <tr style="border-bottom:2px solid #7CCB96;">
                        <th style="padding:10px; text-align:left;">ID</th>
                        <th style="padding:10px; text-align:left;">Name</th>
                        <th style="padding:10px; text-align:left;">Location</th>
                        <th style="padding:10px; text-align:left;">Sport</th>
                        <th style="padding:10px; text-align:left;">Status</th>
                        <th style="padding:10px; text-align:left;">Grade</th>
                        <th style="padding:10px; text-align:left;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $grounds->fetch_assoc()): ?>
                        <tr style="border-bottom:1px solid #2a2a2a;">
                            <td style="padding:10px;"><?php echo htmlspecialchars($row['ground_id']); ?></td>
                            <td style="padding:10px;"><?php echo htmlspecialchars($row['name']); ?></td>
                            <td style="padding:10px;"><?php echo htmlspecialchars($row['location']); ?></td>
                            <td style="padding:10px;"><?php echo htmlspecialchars($row['sport_type']); ?></td>
                            <td style="padding:10px;"><span style="background:<?php echo $row['status']=='active'?'#7CCB96':($row['status']=='pending'?'#FFA500':'#FF6B6B'); ?>; color:black; padding:2px 10px; border-radius:20px; font-size:11px;"><?php echo htmlspecialchars($row['status']); ?></span></td>
                            <td style="padding:10px;">
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="ground_id" value="<?php echo $row['ground_id']; ?>">
                                    <select name="grade" style="background:#0b0b0b; color:white; border:1px solid #2a2a2a; border-radius:4px; padding:4px;">
                                        <option value="" <?php echo $row['grade']==''?'selected':''; ?>>None</option>
                                        <option value="A" <?php echo $row['grade']=='A'?'selected':''; ?>>A</option>
                                        <option value="B" <?php echo $row['grade']=='B'?'selected':''; ?>>B</option>
                                        <option value="C" <?php echo $row['grade']=='C'?'selected':''; ?>>C</option>
                                    </select>
                                    <button type="submit" style="background:#7CCB96; color:black; padding:2px 10px; border:none; border-radius:4px; cursor:pointer;">Set</button>
                                </form>
                            </td>
                            <td style="padding:10px;"><a href="admin-dashboard.php?delete_ground=<?php echo $row['ground_id']; ?>" onclick="return confirm('Delete this ground?')" style="color:#FF6B6B;">Delete</a></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Bookings Table -->
        <h3 style="color:#7CCB96; margin-bottom:10px;">📋 Recent Bookings</h3>
        <div style="background:#1a1a1a; border-radius:12px; padding:15px; overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; color:white;">
                <thead>
                    <tr style="border-bottom:2px solid #7CCB96;">
                        <th style="padding:10px; text-align:left;">Ref</th>
                        <th style="padding:10px; text-align:left;">Ground</th>
                        <th style="padding:10px; text-align:left;">Player</th>
                        <th style="padding:10px; text-align:left;">Amount</th>
                        <th style="padding:10px; text-align:left;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $bookings->fetch_assoc()): ?>
                        <tr style="border-bottom:1px solid #2a2a2a;">
                            <td style="padding:10px;"><?php echo htmlspecialchars($row['booking_reference']); ?></td>
                            <td style="padding:10px;"><?php echo htmlspecialchars($row['ground_name']); ?></td>
                            <td style="padding:10px;"><?php echo htmlspecialchars($row['player_name']); ?></td>
                            <td style="padding:10px; color:#7CCB96;"><?php echo htmlspecialchars($row['total_amount']); ?> Tk</td>
                            <td style="padding:10px;"><span style="background:<?php echo $row['status']=='confirmed'?'#7CCB96':($row['status']=='pending'?'#FFA500':'#FF6B6B'); ?>; color:black; padding:2px 10px; border-radius:20px; font-size:11px;"><?php echo htmlspecialchars($row['status']); ?></span></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>

</div>

</body>
</html>