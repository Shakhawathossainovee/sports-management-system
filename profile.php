<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
require_once 'includes/config.php';

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Get user data
$stmt = $conn->prepare("SELECT name, email, phone, profile_picture FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    if (empty($name)) $errors[] = "Name is required.";
    if (!empty($password) && $password !== $confirm_password) $errors[] = "Passwords do not match.";
    
    // Handle profile picture upload
    $profile_picture = $user['profile_picture'];
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_picture']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $new_filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
            $upload_path = 'uploads/' . $new_filename;
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                // Delete old picture if exists
                if ($user['profile_picture'] && file_exists($user['profile_picture'])) {
                    unlink($user['profile_picture']);
                }
                $profile_picture = $upload_path;
            }
        }
    }
    
    if (empty($errors)) {
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, password = ?, profile_picture = ? WHERE user_id = ?");
            $stmt->bind_param("ssssi", $name, $phone, $hashed_password, $profile_picture, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, profile_picture = ? WHERE user_id = ?");
            $stmt->bind_param("sssi", $name, $phone, $profile_picture, $user_id);
        }
        
        if ($stmt->execute()) {
            $_SESSION['user_name'] = $name;
            $user['name'] = $name;
            $user['phone'] = $phone;
            $user['profile_picture'] = $profile_picture;
            $message = "Profile updated successfully!";
            $message_type = "success";
        } else {
            $message = "Update failed. Please try again.";
            $message_type = "error";
        }
        $stmt->close();
    } else {
        $message = implode("<br>", $errors);
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profile | 🏆 KHELA HOBEE</title>
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
                    <li><a href="index.html">Home</a></li>
                    <li><a href="search.php">Turfs & Fields</a></li>
                    <li><a href="my-bookings.php">My Bookings</a></li>
                    <li><a href="profile.php" class="active">Profile</a></li>
                    <li><a href="notifications.php">Notifications</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div style="max-width:600px; margin:40px auto; padding:0 20px;">
        <div style="background:#1a1a1a; border-radius:16px; padding:40px; border:1px solid #2a2a2a;">
            <h2 style="color:#7CCB96; margin-bottom:10px;">
                <i class="fas fa-user"></i> My Profile
            </h2>
            <p style="color:#888; margin-bottom:25px;">Update your personal information.</p>

            <?php if ($message): ?>
                <div style="padding:15px; border-radius:8px; margin-bottom:15px; background:<?php echo $message_type == 'success' ? '#1a3a2a' : '#3a1a1a'; ?>; border:2px solid <?php echo $message_type == 'success' ? '#7CCB96' : '#ff6b6b'; ?>;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- ===== PROFILE PICTURE DISPLAY ===== -->
            <div style="text-align:center; margin-bottom:20px;">
                <?php if ($user['profile_picture'] && file_exists($user['profile_picture'])): ?>
                    <img src="<?php echo $user['profile_picture']; ?>" 
                         style="width:120px; height:120px; border-radius:50%; object-fit:cover; border:3px solid #7CCB96;">
                <?php else: ?>
                    <div style="width:120px; height:120px; border-radius:50%; background:#2a2a2a; display:inline-flex; align-items:center; justify-content:center; font-size:48px; color:#7CCB96; border:3px solid #7CCB96;">
                        <i class="fas fa-user"></i>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ===== FORM WITH FILE UPLOAD ===== -->
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label style="color:#ccc;">Profile Picture</label>
                    <input type="file" name="profile_picture" accept="image/*" 
                           style="width:100%; padding:10px; border:none; border-radius:8px; background:#202020; color:white;">
                </div>

                <div class="form-group">
                    <label style="color:#ccc;">Full Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required 
                           style="width:100%; padding:14px; border:none; border-radius:8px; background:#202020; color:white; font-size:15px;">
                </div>

                <div class="form-group">
                    <label style="color:#ccc;">Email</label>
                    <input type="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" disabled 
                           style="width:100%; padding:14px; border:none; border-radius:8px; background:#2a2a2a; color:#888; font-size:15px;">
                    <small style="color:#666;">Email cannot be changed.</small>
                </div>

                <div class="form-group">
                    <label style="color:#ccc;">Phone</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                           style="width:100%; padding:14px; border:none; border-radius:8px; background:#202020; color:white; font-size:15px;">
                </div>

                <div class="form-group">
                    <label style="color:#ccc;">New Password (leave blank to keep current)</label>
                    <input type="password" name="password" placeholder="Enter new password" 
                           style="width:100%; padding:14px; border:none; border-radius:8px; background:#202020; color:white; font-size:15px;">
                </div>

                <div class="form-group">
                    <label style="color:#ccc;">Confirm New Password</label>
                    <input type="password" name="confirm_password" placeholder="Confirm new password" 
                           style="width:100%; padding:14px; border:none; border-radius:8px; background:#202020; color:white; font-size:15px;">
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-save"></i> Update Profile
                </button>
            </form>

            <p style="margin-top:15px; text-align:center;">
                <a href="search.php" style="color:#7CCB96;">← Back to Search</a>
            </p>
        </div>
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