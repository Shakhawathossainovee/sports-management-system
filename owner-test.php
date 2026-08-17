<?php
require_once 'includes/config.php';

$email = 'newowner@khela.com';
$password = 'password123';

echo "Testing login for: " . $email . "<br><br>";

$stmt = $conn->prepare("
    SELECT u.user_id, u.name, u.email, u.password, o.owner_id 
    FROM users u 
    JOIN ground_owners o ON u.user_id = o.user_id 
    WHERE u.email = ? AND u.role = 'owner'
");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
    echo "✅ User found!<br>";
    echo "Name: " . $user['name'] . "<br>";
    echo "Email: " . $user['email'] . "<br>";
    echo "Stored password hash: " . $user['password'] . "<br>";
    echo "Password entered: " . $password . "<br><br>";
    
    if (password_verify($password, $user['password'])) {
        echo "✅✅✅ PASSWORD MATCHES! Login would work!";
    } else {
        echo "❌❌❌ PASSWORD DOES NOT MATCH!";
    }
} else {
    echo "❌ User not found!";
}

$stmt->close();
$conn->close();
?>

