<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Forgot Password | Khela Hobee</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
</head>
<body>

<header>
    <div class="container navbar">
        <div class="logo">
            <h2>🏆 KHELA HOBEE</h2>
        </div>
        <nav>
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="search.php">Turfs & Fields</a></li>
                <li><a href="privacy.html">Privacy Policy</a></li>
                <li><a href="terms.html">Terms & Conditions</a></li>
                <li><a href="contact.html">Contact Us</a></li>
                <li><a href="about.html">About Us</a></li>
            </ul>
        </nav>
        <div class="nav-btn">
            <a href="login.html" class="login-btn">Login</a>
            <a href="register.html" class="register-btn">Register</a>
        </div>
    </div>
</header>

<section class="form-section">
    <div class="container form-container">
        <div class="form-box">
            <h2>🔑 Reset Password</h2>
            <p>Enter your email address and we'll send you a link to reset your password.</p>

            <!-- ===== FORM WITH POPUP ===== -->
            <form id="resetForm" onsubmit="showPopup(event)">
                <div class="form-group">
                    <input type="email" id="email" placeholder="Email Address" required />
                </div>
                <button type="submit" class="submit-btn">Send Reset Link</button>
            </form>

            <div class="login-link">
                <a href="login.html">← Back to Login</a>
            </div>
        </div>

        <div class="hero-right">
            <img src="players.png" alt="Players" />
        </div>
    </div>
</section>

<!-- ===== POPUP MODAL ===== -->
<div id="popupModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.8); z-index:9999; display:none; align-items:center; justify-content:center;">
    <div style="background:#1a1a1a; max-width:450px; padding:40px; border-radius:16px; text-align:center; border:2px solid #7CCB96; position:relative;">
        <div style="width:80px; height:80px; background:#1a3a2a; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
            <i class="fas fa-check-circle" style="font-size:40px; color:#7CCB96;"></i>
        </div>
        <h2 style="color:#7CCB96; font-size:24px; margin-bottom:10px;">✅ Reset Link Sent!</h2>
        <p style="color:#bbb; font-size:15px; line-height:1.6;">
            A password reset link has been sent to <br>
            <strong id="popupEmail" style="color:#7CCB96;"></strong>
        </p>
        <p style="color:#888; font-size:13px; margin-top:10px;">
            Please check your email inbox and spam folder.
        </p>
        <button onclick="closePopup()" style="background:#7CCB96; color:black; padding:12px 30px; border-radius:8px; border:none; font-weight:bold; font-size:15px; cursor:pointer; margin-top:20px;">
            <i class="fas fa-check"></i> Okay, Got It!
        </button>
    </div>
</div>

<script>
function showPopup(event) {
    event.preventDefault();
    
    var email = document.getElementById('email').value;
    if (!email) {
        alert('Please enter your email address.');
        return;
    }
    
    // Show email in popup
    document.getElementById('popupEmail').textContent = email;
    
    // Show popup
    var modal = document.getElementById('popupModal');
    modal.style.display = 'flex';
    
    // Clear the form
    document.getElementById('resetForm').reset();
}

function closePopup() {
    document.getElementById('popupModal').style.display = 'none';
    window.location.href = 'login.html';
}

// Close popup when clicking outside
window.onclick = function(event) {
    var modal = document.getElementById('popupModal');
    if (event.target == modal) {
        modal.style.display = 'none';
        window.location.href = 'login.html';
    }
}
</script>

</body>
</html>