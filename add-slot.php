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
$grounds_stmt = $conn->prepare("SELECT ground_id, name FROM grounds WHERE owner_id = ? AND status = 'active'");
$grounds_stmt->bind_param("i", $owner_id);
$grounds_stmt->execute();
$grounds_result = $grounds_stmt->get_result();

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ground_id = $_POST['ground_id'];
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    
    // Check if slot already exists
    $check_stmt = $conn->prepare("SELECT slot_id FROM time_slots WHERE ground_id = ? AND date = ? AND start_time = ?");
    $check_stmt->bind_param("iss", $ground_id, $date, $start_time);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $message = "This time slot already exists!";
        $message_type = "error";
    } else {
        $insert_stmt = $conn->prepare("INSERT INTO time_slots (ground_id, date, start_time, end_time, is_available) VALUES (?, ?, ?, ?, 1)");
        $insert_stmt->bind_param("isss", $ground_id, $date, $start_time, $end_time);
        
        if ($insert_stmt->execute()) {
            // ===== AUDIT LOG =====
            logAction($_SESSION['user_id'], 'Add Time Slot', 'Added slot for ground ID: ' . $ground_id . ' on ' . $date . ' at ' . $start_time);
            
            $message = "Time slot added successfully!";
            $message_type = "success";
        } else {
            $message = "Failed to add time slot.";
            $message_type = "error";
        }
        $insert_stmt->close();
    }
    $check_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add Time Slot | 🏆 Khela Hobe</title>
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
                    <li><a href="add-slot.php" class="active">Add Slot</a></li>
                    <li><a href="reports.php">Reports</a></li>
                </ul>
            </nav>
            <div class="nav-btn">
                <a href="logout.php" class="login-btn">Logout</a>
            </div>
        </div>
    </header>

    <!-- ===== ADD SLOT FORM + PLAYER IMAGE ===== -->
    <section class="form-section">
        <div class="form-container">
        <div class="form-box">
            <h2><i class="fas fa-clock"></i> Add Time Slot</h2>
            <p>Add available time slots for your grounds.</p>

            <?php if ($message): ?>
                <div style="padding:15px; border-radius:8px; margin-bottom:15px; background:<?php echo $message_type == 'success' ? '#1a3a2a' : '#3a1a1a'; ?>; border:2px solid <?php echo $message_type == 'success' ? '#7CCB96' : '#ff6b6b'; ?>;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label style="color:#ccc;">Select Ground *</label>
                    <select name="ground_id" required style="width:100%; padding:14px; border:none; border-radius:8px; background:#202020; color:white; font-size:15px;">
                        <option value="">Choose Ground</option>
                        <?php while ($ground = $grounds_result->fetch_assoc()): ?>
                            <option value="<?php echo $ground['ground_id']; ?>"><?php echo $ground['name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label style="color:#ccc;">Date *</label>
                    <input type="date" name="date" required>
                </div>

                <div class="form-group">
                    <label style="color:#ccc;">Start Time *</label>
                    <input type="time" name="start_time" required>
                </div>

                <div class="form-group">
                    <label style="color:#ccc;">End Time *</label>
                    <input type="time" name="end_time" required>
                </div>

                <button type="submit" class="submit-btn"><i class="fas fa-plus-circle"></i> Add Slot</button>
            </form>

            <p style="margin-top:15px; text-align:center;">
                <a href="owner-dashboard.php">← Back to Dashboard</a>
            </p>
        </div>

        <div class="hero-right">
            <img src="players.png" alt="Time Slots">
        </div>
        </div>
    </section>

</div>

</body>
</html>