<?php
session_start();
include 'C:\xampp\htdocs\E-BOOK--EVENT-MANAGEMENT-SYSTEM-\Source Code\includes\config.php';
require_once 'jwt_utils.php';

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['email'], $data['password'])) {
    echo json_encode(['error' => 'Email and password are required']);
    exit();
}

$email = trim($data['email']);
$password = trim($data['password']);

$stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        $token = generateJWT($user['id'], 'user'); // <<< pass 'user' as the role
        echo json_encode(['token' => $token, 'name' => $user['name']]);
    } else {
        echo json_encode(['error' => 'Invalid password']);
    }
} else {
    echo json_encode(['error' => 'User not found']);
}
?>
