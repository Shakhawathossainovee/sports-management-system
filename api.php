<?php
header('Content-Type: application/json');

// =============================================
// KHELA HOBEE - API Endpoints
// For Postman Testing
// =============================================

// Get the action from URL
$action = isset($_GET['action']) ? $_GET['action'] : '';

// =============================================
// 1. GET /api.php?action=grounds
//    Returns all active grounds
// =============================================
if ($action == 'grounds') {
    require_once 'includes/config.php';
    $result = $conn->query("SELECT ground_id, name, location, sport_type, rental_fee_per_hour, grade FROM grounds WHERE status = 'active'");
    $grounds = [];
    while ($row = $result->fetch_assoc()) {
        $grounds[] = $row;
    }
    echo json_encode([
        'success' => true,
        'count' => count($grounds),
        'data' => $grounds
    ]);
    exit();
}

// =============================================
// 2. GET /api.php?action=ground&id=1
//    Returns specific ground details
// =============================================
if ($action == 'ground') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    require_once 'includes/config.php';
    $stmt = $conn->prepare("SELECT * FROM grounds WHERE ground_id = ? AND status = 'active'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $ground = $result->fetch_assoc();
    if ($ground) {
        echo json_encode(['success' => true, 'data' => $ground]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ground not found']);
    }
    exit();
}

// =============================================
// 3. GET /api.php?action=slots&ground_id=1&date=2026-08-20
//    Returns available slots for a ground on a date
// =============================================
if ($action == 'slots') {
    $ground_id = isset($_GET['ground_id']) ? (int)$_GET['ground_id'] : 0;
    $date = isset($_GET['date']) ? $_GET['date'] : '';
    require_once 'includes/config.php';
    $stmt = $conn->prepare("SELECT slot_id, start_time, end_time, is_available FROM time_slots WHERE ground_id = ? AND date = ? AND is_available = 1");
    $stmt->bind_param("is", $ground_id, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $slots = [];
    while ($row = $result->fetch_assoc()) {
        $slots[] = $row;
    }
    echo json_encode(['success' => true, 'count' => count($slots), 'data' => $slots]);
    exit();
}

// =============================================
// 4. GET /api.php?action=sports
//    Returns all sports types
// =============================================
if ($action == 'sports') {
    require_once 'includes/config.php';
    $result = $conn->query("SELECT DISTINCT sport_type FROM grounds WHERE status = 'active'");
    $sports = [];
    while ($row = $result->fetch_assoc()) {
        $sports[] = $row['sport_type'];
    }
    echo json_encode(['success' => true, 'data' => $sports]);
    exit();
}

// =============================================
// 5. GET /api.php?action=stats
//    Returns platform statistics
// =============================================
if ($action == 'stats') {
    require_once 'includes/config.php';
    $grounds = $conn->query("SELECT COUNT(*) as total FROM grounds WHERE status = 'active'")->fetch_assoc()['total'];
    $users = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
    $bookings = $conn->query("SELECT COUNT(*) as total FROM bookings")->fetch_assoc()['total'];
    echo json_encode([
        'success' => true,
        'data' => [
            'total_grounds' => (int)$grounds,
            'total_users' => (int)$users,
            'total_bookings' => (int)$bookings
        ]
    ]);
    exit();
}

// =============================================
// 6. POST /api.php?action=login
//    (POST request with email & password)
//    Test in Postman with form-data or JSON
// =============================================
if ($action == 'login') {
    require_once 'includes/config.php';
    $input = json_decode(file_get_contents('php://input'), true);
    if ($input) {
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';
    } else {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
    }
    
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email and password required']);
        exit();
    }
    
    $stmt = $conn->prepare("SELECT user_id, name, email, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user) {
        // In real API, verify password here
        echo json_encode(['success' => true, 'message' => 'Login successful', 'data' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
    exit();
}

// =============================================
// 7. Default — If no valid action
// =============================================
echo json_encode([
    'success' => false,
    'message' => 'Invalid API action',
    'available_actions' => [
        'grounds' => 'GET /api.php?action=grounds',
        'ground' => 'GET /api.php?action=ground&id=1',
        'slots' => 'GET /api.php?action=slots&ground_id=1&date=2026-08-20',
        'sports' => 'GET /api.php?action=sports',
        'stats' => 'GET /api.php?action=stats',
        'login' => 'POST /api.php?action=login (with email & password)'
    ]
]);
?>