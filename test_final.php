<?php
require_once 'includes/config.php';

// The email and password we are testing
$email = 'owner_new123@khela.com';
$password = 'password123'; // The plain text password you are typing

echo "🔍 Testing login for: " . $email . "<br><br>";

// 1. Get the user from the database
$stmt = $conn->prepare("SELECT user_id, name, email, password FROM users WHERE email = ? AND role = 'owner'");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
    echo "✅ User found!<br>";
    echo "Name: " . $user['name'] . "<br>";
    echo "Email: " . $user['email'] . "<br>";
    echo "Stored hash: " . $user['password'] . "<br><br>";

    // 2. Verify the password
    if (password_verify($password, $user['password'])) {
        echo "✅✅✅ PASSWORD MATCHES! Login would work.";
    } else {
        echo "❌❌❌ PASSWORD DOES NOT MATCH!<br><br>";
        
        // 3. Let's try to recreate the hash and compare
        $new_hash = password_hash($password, PASSWORD_DEFAULT);
        echo "New hash for 'password123': " . $new_hash . "<br>";
        echo "This is what the hash in the database should look like.<br>";
        echo "If they look different, the hash in the database is for a different password.";
    }
} else {
    echo "❌ User not found!";
}

$stmt->close();
$conn->close();
?>

