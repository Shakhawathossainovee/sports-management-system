<?php
// =============================================
// KHELA HOBEE - Logger (Fixed - handles any user_id)
// =============================================

function logAction($user_id, $action, $details = '') {
    global $conn;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    
    // Check if user_id exists in users table
    if (!empty($user_id) && $user_id > 0) {
        $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE user_id = ?");
        $check_stmt->bind_param("i", $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $user_exists = $check_result->num_rows > 0;
        $check_stmt->close();
        
        if ($user_exists) {
            // Valid user — log with user_id
            $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $user_id, $action, $details, $ip);
        } else {
            // User doesn't exist — log with NULL
            $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (NULL, ?, ?, ?)");
            $stmt->bind_param("sss", $action, $details, $ip);
        }
    } else {
        // No valid user_id — log with NULL
        $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (NULL, ?, ?, ?)");
        $stmt->bind_param("sss", $action, $details, $ip);
    }
    
    $stmt->execute();
    $stmt->close();
}
?>