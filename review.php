<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
require_once 'includes/config.php';

$booking_id = isset($_GET['booking_id']) ? $_GET['booking_id'] : 0;
$user_id = $_SESSION['user_id'];

// Get booking details to verify ownership and completion
$stmt = $conn->prepare("
    SELECT b.*, g.name as ground_name, g.ground_id 
    FROM bookings b
    JOIN time_slots ts ON b.slot_id = ts.slot_id
    JOIN grounds g ON ts.ground_id = g.ground_id
    WHERE b.booking_id = ? AND b.player_id = (SELECT player_id FROM players WHERE user_id = ?)
");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    echo "<h1>❌ Booking not found</h1>";
    echo "<a href='my-bookings.php'>Back to My Bookings</a>";
    exit();
}

// Check if already reviewed
$check_stmt = $conn->prepare("SELECT review_id FROM reviews WHERE booking_id = ?");
$check_stmt->bind_param("i", $booking_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$already_reviewed = $check_result->num_rows > 0;
$check_stmt->close();

$message = '';
$message_type = '';

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $comment = trim($_POST['comment'] ?? '');
    $player_id = $booking['player_id'];
    $ground_id = $booking['ground_id'];
    $is_verified = 1;
    
    if ($rating < 1 || $rating > 5) {
        $message = "Please select a rating (1-5 stars).";
        $message_type = "error";
    } elseif (empty($comment)) {
        $message = "Please write a comment.";
        $message_type = "error";
    } else {
        $stmt = $conn->prepare("INSERT INTO reviews (booking_id, player_id, ground_id, rating, comment, is_verified) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiisis", $booking_id, $player_id, $ground_id, $rating, $comment, $is_verified);
        
        if ($stmt->execute()) {
            // Add notification for review
            $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, 'New Review', 'You have received a new review for " . $booking['ground_name'] . ".', 'system')");
            $notif_stmt->bind_param("i", $user_id);
            $notif_stmt->execute();
            $notif_stmt->close();
            
            $message = "Review submitted successfully!";
            $message_type = "success";
        } else {
            $message = "Failed to submit review: " . $stmt->error;
            $message_type = "error";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Write Review | 🏆 KHELA HOBEE</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        .star {
            font-size: 36px;
            cursor: pointer;
            transition: 0.2s;
            user-select: none;
            display: inline-block;
            color: #555;
        }
        .star:hover {
            transform: scale(1.3);
            color: #FFD700 !important;
        }
        .star.active {
            color: #FFD700 !important;
            text-shadow: 0 0 15px #FFD700;
        }
        .star.active:hover {
            transform: scale(1.4);
        }
        #rating_text {
            color: #aaa;
            font-size: 14px;
            display: block;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <header>
        <h1>🏆 KHELA HOBEE</h1>
        <nav>
            <a href="index.html" >Home</a>
            <a href="search.php">Search</a>
            <a href="my-bookings.php" >My Bookings</a>
            <a href="profile.php" >Profile</a>
            <a href="notifications.php" >Notifications</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <?php if ($already_reviewed): ?>
        <div style="max-width:500px; margin:60px auto; padding:20px;">
            <div style="background:#1a1a1a; padding:40px; border-radius:12px; text-align:center; border:2px solid #FFD700;">
                <div style="font-size:60px;">⭐</div>
                <h2 style="color:#FFD700;">Already Reviewed!</h2>
                <p style="color:#bbb;">You have already submitted a review for this booking.</p>
                <a href="my-bookings.php" style="display:inline-block; background:#7CCB96; color:black; padding:12px 30px; border-radius:8px; text-decoration:none; font-weight:bold; margin-top:15px;">
                    <i class="fas fa-arrow-left"></i> My Bookings
                </a>
            </div>
        </div>
    <?php elseif ($message_type == 'success'): ?>
        <div style="max-width:500px; margin:60px auto; padding:20px;">
            <div style="background:#1a1a1a; padding:40px; border-radius:12px; text-align:center; border:2px solid #7CCB96;">
                <div style="font-size:60px;">✅</div>
                <h2 style="color:#7CCB96;">Review Submitted!</h2>
                <p style="color:#bbb;"><?php echo $message; ?></p>
                <a href="my-bookings.php" style="display:inline-block; background:#7CCB96; color:black; padding:12px 30px; border-radius:8px; text-decoration:none; font-weight:bold; margin-top:15px;">
                    <i class="fas fa-arrow-left"></i> My Bookings
                </a>
            </div>
        </div>
    <?php elseif ($message_type == 'error'): ?>
        <div style="max-width:500px; margin:60px auto; padding:20px;">
            <div style="background:#1a1a1a; padding:40px; border-radius:12px; text-align:center; border:2px solid #ff6b6b;">
                <div style="font-size:60px;">❌</div>
                <h2 style="color:#ff6b6b;">Review Failed</h2>
                <p style="color:#bbb;"><?php echo $message; ?></p>
                <a href="review.php?booking_id=<?php echo $booking_id; ?>" style="display:inline-block; background:#7CCB96; color:black; padding:12px 30px; border-radius:8px; text-decoration:none; font-weight:bold; margin-top:15px;">
                    <i class="fas fa-redo"></i> Try Again
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="container">
            <div class="form-box">
                <h2>⭐ Write a Review</h2>
                <p>Share your experience at <strong><?php echo $booking['ground_name']; ?></strong></p>

                <form method="POST">
                    <div class="form-group">
                        <label style="color:#ccc; font-weight:bold; display:block; margin-bottom:8px;">Your Rating</label>
                        
                        <div style="display:flex; gap:8px; font-size:36px; padding:5px 0; align-items:center;">
                            <span onclick="setRating(1)" id="star1" class="star">⭐</span>
                            <span onclick="setRating(2)" id="star2" class="star">⭐</span>
                            <span onclick="setRating(3)" id="star3" class="star">⭐</span>
                            <span onclick="setRating(4)" id="star4" class="star">⭐</span>
                            <span onclick="setRating(5)" id="star5" class="star">⭐</span>
                        </div>

                        <input type="hidden" name="rating" id="rating_value" value="0" required>
                        
                        <span id="rating_text">Click a star to rate</span>
                    </div>

                    <div class="form-group">
                        <label style="color:#ccc;">Your Comment</label>
                        <textarea name="comment" rows="5" placeholder="Write your review here..." style="width:100%; padding:14px; border:none; border-radius:8px; background:#2a2a2a; color:white; font-family:Arial;" required></textarea>
                    </div>

                    <button type="submit" class="submit-btn">Submit Review</button>
                </form>

                <p style="margin-top:15px; text-align:center;">
                    <a href="my-bookings.php">← Back to My Bookings</a>
                </p>
            </div>

            <div class="hero-right">
                <img src="players.png" alt="Review">
            </div>
        </div>
    <?php endif; ?>

    <script>
        function setRating(rating) {
            // Update hidden input
            document.getElementById('rating_value').value = rating;

            // Reset all stars
            for (let i = 1; i <= 5; i++) {
                let star = document.getElementById('star' + i);
                star.style.color = '#555';
                star.classList.remove('active');
            }

            // Highlight selected stars
            for (let i = 1; i <= rating; i++) {
                let star = document.getElementById('star' + i);
                star.style.color = '#FFD700';
                star.classList.add('active');
            }

            // Update text feedback
            let text = document.getElementById('rating_text');
            if (rating === 1) text.innerHTML = '⭐ Terrible — Needs improvement!';
            else if (rating === 2) text.innerHTML = '⭐⭐ Poor — Not great.';
            else if (rating === 3) text.innerHTML = '⭐⭐⭐ Average — It was okay.';
            else if (rating === 4) text.innerHTML = '⭐⭐⭐⭐ Good — Enjoyed it!';
            else if (rating === 5) text.innerHTML = '⭐⭐⭐⭐⭐ Excellent — Highly recommended!';
            else text.innerHTML = 'Click a star to rate';
        }
    </script>
</body>
</html>

