<?php
session_start();
include 'C:\xampp\htdocs\E-BOOK--EVENT-MANAGEMENT-SYSTEM-\Source Code\includes\config.php';
require_once 'jwt_utilsBooking.php';

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['email'], $data['password'])) {
    echo json_encode(['error' => 'Email and password are required']);
    exit();
}

$email = trim($data['email']);
$password = trim($data['password']);

$stmt = $conn->prepare("SELECT id, name, password FROM admins WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $admin = $result->fetch_assoc();
    if (password_verify($password, $admin['password'])) {
        $token = JWTUtils::generateToken([
            'id' => $admin['id'],
            'is_admin' => true
        ]);

        $_SESSION['jwt_token'] = $token;

        echo json_encode([
            'token' => $token,
            'name' => $admin['name'],
            'is_admin' => true
        ]);
    } else {
        echo json_encode(['error' => 'Invalid password']);
    }
} else {
    echo json_encode(['error' => 'Admin not found']);
}
