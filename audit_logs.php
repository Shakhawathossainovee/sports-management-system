<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.html");
    exit();
}
require_once 'includes/config.php';

$stmt = $conn->prepare("
    SELECT al.*, u.name as user_name, u.email as user_email 
    FROM audit_logs al
    LEFT JOIN users u ON al.user_id = u.user_id
    ORDER BY al.created_at DESC
    LIMIT 100
");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Audit Logs | 🏆 Khela Hobe</title>
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
                    <li><a href="statistics.php">Statistics</a></li>
                    <li><a href="admin-contacts.php">Contacts</a></li>
                    <li><a href="audit_logs.php" class="active">Audit Logs</a></li>
                </ul>
            </nav>
            <div class="nav-btn">
                <a href="logout.php" class="login-btn">Logout</a>
            </div>
        </div>
    </header>

    <div style="max-width:1100px; margin:30px auto; padding:0 20px;">
        <h2 style="color:#7CCB96; margin-bottom:10px;">📋 Audit Logs</h2>
        <p style="color:#888; margin-bottom:20px;">Track all user activities in the system.</p>

        <div style="background:#1a1a1a; border-radius:12px; padding:20px; overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; color:white;">
                <thead>
                    <tr style="border-bottom:2px solid #7CCB96;">
                        <th style="padding:12px; text-align:left;">User</th>
                        <th style="padding:12px; text-align:left;">Action</th>
                        <th style="padding:12px; text-align:left;">Details</th>
                        <th style="padding:12px; text-align:left;">IP</th>
                        <th style="padding:12px; text-align:left;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($log = $result->fetch_assoc()): ?>
                            <tr style="border-bottom:1px solid #2a2a2a;">
                                <td style="padding:12px;">
                                    <strong><?php echo $log['user_name'] ?? 'System'; ?></strong>
                                    <br><span style="color:#888; font-size:11px;"><?php echo $log['user_email'] ?? 'N/A'; ?></span>
                                </td>
                                <td style="padding:12px;">
                                    <span style="background:#7CCB96; color:black; padding:2px 10px; border-radius:20px; font-size:11px; font-weight:bold;">
                                        <?php echo $log['action']; ?>
                                    </span>
                                </td>
                                <td style="padding:12px; color:#bbb;"><?php echo $log['details']; ?></td>
                                <td style="padding:12px; color:#888;"><?php echo $log['ip_address']; ?></td>
                                <td style="padding:12px; color:#888;"><?php echo date('d M Y, h:i A', strtotime($log['created_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="padding:30px; text-align:center; color:#888;">No logs found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

</body>
</html>