<?php
session_start();
include 'C:\xampp\htdocs\E-BOOK--EVENT-MANAGEMENT-SYSTEM-\Source Code\API\bookingConfig.php';
require_once 'jwt_utilsBooking.php';

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!isset($data['email'], $data['password'])) {
    echo json_encode(['error' => 'Email and password are required']);
    exit();
}

$email = trim($data['email']);
$password = trim($data['password']);

// Check if user exists
$stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Verify password
    if (password_verify($password, $user['password'])) {
        // Generate JWT token
        $token = JWTUtils::generateToken([
            'id' => $user['id'],
            'is_admin' => false
        ]);

        $_SESSION['jwt_token'] = $token; // Optional

        echo json_encode([
            'token' => $token,
            'name' => $user['name'],
            'is_admin' => false
        ]);
    } else {
        echo json_encode(['error' => 'Invalid password']);
    }
} else {
    echo json_encode(['error' => 'User not found']);
}
?>
