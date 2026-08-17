<?php
session_start();
if (!isset($_SESSION['owner_id'])) {
    header("Location: owner-login.html");
    exit();
}
require_once 'includes/config.php';
require_once 'includes/logger.php';

$owner_id = $_SESSION['owner_id'];
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $location = trim($_POST['location']);
    $sport_type = $_POST['sport_type'];
    $facilities = trim($_POST['facilities']);
    $rental_fee = $_POST['rental_fee'];
    $status = 'pending'; // Pending inspection

    if (empty($name) || empty($location) || empty($sport_type) || empty($rental_fee)) {
        $message = "Please fill in all required fields.";
        $message_type = "error";
    } else {
        $stmt = $conn->prepare("INSERT INTO grounds (owner_id, name, location, sport_type, facilities, rental_fee_per_hour, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssds", $owner_id, $name, $location, $sport_type, $facilities, $rental_fee, $status);
        
        if ($stmt->execute()) {
            logAction($_SESSION['user_id'], 'Add Ground', 'Added new ground: ' . $name);
            $message = "Ground added successfully! Waiting for inspection approval.";
            $message_type = "success";
        } else {
            $message = "Failed to add ground: " . $stmt->error;
            $message_type = "error";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add Ground | 🏆 Khela Hobe</title>
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
                    <li><a href="add-ground.php" class="active">Add Ground</a></li>
                    <li><a href="add-slot.php">Add Slot</a></li>
                    <li><a href="reports.php">Reports</a></li>
                </ul>
            </nav>
            <div class="nav-btn">
                <a href="logout.php" class="login-btn">Logout</a>
            </div>
        </div>
    </header>

    <!-- ===== ADD GROUND FORM + PLAYER IMAGE ===== -->
    <section class="form-section">
        <div class="form-container">
        <div class="form-box">
            <h2><i class="fas fa-plus-circle"></i> Register New Ground</h2>
            <p>Add a new ground for inspection and approval.</p>

            <?php if ($message): ?>
                <div style="padding:15px; border-radius:8px; margin-bottom:15px; background:<?php echo $message_type == 'success' ? '#1a3a2a' : '#3a1a1a'; ?>; border:2px solid <?php echo $message_type == 'success' ? '#7CCB96' : '#ff6b6b'; ?>;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label style="color:#ccc;">Ground Name *</label>
                    <input type="text" name="name" placeholder="e.g., Dhaka Premier Turf" required>
                </div>
                <div class="form-group">
                    <label style="color:#ccc;">Location *</label>
                    <input type="text" name="location" placeholder="e.g., Gulshan, Dhaka" required>
                </div>
                <div class="form-group">
                    <label style="color:#ccc;">Sport Type *</label>
                    <select name="sport_type" required style="width:100%; padding:14px; border:none; border-radius:8px; background:#202020; color:white; font-size:15px;">
                        <option value="">Select Sport</option>
                        <option value="Football">⚽ Football</option>
                        <option value="Cricket">🏏 Cricket</option>
                        <option value="Basketball">🏀 Basketball</option>
                    </select>
                </div>
                <div class="form-group">
                    <label style="color:#ccc;">Facilities</label>
                    <input type="text" name="facilities" placeholder="e.g., Parking, Floodlights, Changing Room">
                </div>
                <div class="form-group">
                    <label style="color:#ccc;">Rental Fee (per hour) *</label>
                    <input type="number" name="rental_fee" placeholder="e.g., 600" required>
                </div>
                <button type="submit" class="submit-btn">Register Ground</button>
            </form>
        </div>
        <div class="hero-right">
            <img src="players.png" alt="Add Ground">
        </div>
        </div>
    </section>

</div>

</body>
</html>