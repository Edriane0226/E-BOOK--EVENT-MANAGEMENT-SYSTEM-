<?php
include '../includes/config.php';
header('Content-Type: application/json');

// Authenticate user
$user_data = authenticateUser();
if (!$user_data) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}
$user_id = $user_data['id'];
$code = $_POST['code'] ?? '';

if (!$code) {
    echo json_encode(['success' => false, 'message' => 'No code provided']);
    exit();
}

// Check code and get pending email
$stmt = $conn->prepare("SELECT email_verification_code, pending_email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user && $user['email_verification_code'] == $code) {
    // Update email if there's a pending email, otherwise just verify current email
    if (!empty($user['pending_email'])) {
        $stmt = $conn->prepare("UPDATE users SET email = pending_email, email_verified=1, email_verification_code=NULL, pending_email=NULL WHERE id=?");
    } else {
        $stmt = $conn->prepare("UPDATE users SET email_verified=1, email_verification_code=NULL WHERE id=?");
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    echo json_encode(['success' => true, 'message' => 'Email verified!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid verification code.']);
}