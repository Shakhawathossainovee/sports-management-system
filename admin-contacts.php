<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.html");
    exit();
}
require_once 'includes/config.php';

$contacts = $conn->query("SELECT * FROM contacts ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Contacts | 🏆 Khela Hobe</title>
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
                    <li><a href="admin-contacts.php" class="active">Contacts</a></li>
                    <li><a href="audit_logs.php">Audit Logs</a></li>
                </ul>
            </nav>
            <div class="nav-btn">
                <a href="logout.php" class="login-btn">Logout</a>
            </div>
        </div>
    </header>

    <div style="max-width:1100px; margin:30px auto; padding:0 20px;">
        <h2 style="color:#7CCB96;">📬 Contact Messages</h2>
        <p style="color:#888; margin-bottom:20px;">View all messages sent through the contact form.</p>

        <div style="background:#1a1a1a; border-radius:12px; padding:20px; overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; color:white;">
                <thead>
                    <tr style="border-bottom:2px solid #7CCB96;">
                        <th style="padding:12px; text-align:left;">Name</th>
                        <th style="padding:12px; text-align:left;">Email</th>
                        <th style="padding:12px; text-align:left;">Subject</th>
                        <th style="padding:12px; text-align:left;">Message</th>
                        <th style="padding:12px; text-align:left;">Status</th>
                        <th style="padding:12px; text-align:left;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($contacts && $contacts->num_rows > 0): ?>
                        <?php while ($row = $contacts->fetch_assoc()): ?>
                            <tr style="border-bottom:1px solid #2a2a2a;">
                                <td style="padding:12px;"><?php echo htmlspecialchars($row['name']); ?></td>
                                <td style="padding:12px;"><?php echo htmlspecialchars($row['email']); ?></td>
                                <td style="padding:12px;"><?php echo htmlspecialchars($row['subject']); ?></td>
                                <td style="padding:12px; max-width:200px; word-wrap:break-word;"><?php echo htmlspecialchars(substr($row['message'], 0, 50)) . '...'; ?></td>
                                <td style="padding:12px;">
                                    <span style="background:<?php echo $row['status']=='unread'?'#FF6B6B':($row['status']=='read'?'#FFD700':'#7CCB96'); ?>; color:black; padding:2px 12px; border-radius:20px; font-size:11px; font-weight:bold;">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                                <td style="padding:12px; color:#888;"><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="padding:30px; text-align:center; color:#888;">No messages found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

</body>
</html>