<?php
include '../includes/config.php';
require 'C:\xampp\htdocs\E-BOOK--EVENT-MANAGEMENT-SYSTEM-\vendor\autoload.php'; // Adjust path if needed
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

// Authenticate user
$user_data = authenticateUser();
if (!$user_data) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}
$user_id = $user_data['id'];
$email = $_POST['email'] ?? '';

if (!$email) {
    echo json_encode(['success' => false, 'message' => 'No email provided']);
    exit();
}

// Get current user email to check if this is an email change
$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$current_user = $stmt->get_result()->fetch_assoc();
$current_email = $current_user['email'];

$is_email_change = ($email !== $current_email);

// Generate code and save to DB
$code = rand(100000, 999999);

if ($is_email_change) {
    // If changing email, store new email in pending_email and set verification code
    $stmt = $conn->prepare("UPDATE users SET pending_email=?, email_verification_code=?, email_verified=0 WHERE id=?");
    $stmt->bind_param("ssi", $email, $code, $user_id);
} else {
    // If verifying current email, just update verification code
    $stmt = $conn->prepare("UPDATE users SET email_verification_code=?, email_verified=0 WHERE id=?");
    $stmt->bind_param("si", $code, $user_id);
}
$stmt->execute();

// Send email
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'edriane.bangonon0226@gmail.com'; // Change this
    $mail->Password = 'biufkkeebyxwnkyh'; // Change this
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('edriane.bangonon0226@gmail.com', 'E-Book Event Management');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Your Email Verification Code';
    
    if ($is_email_change) {
        $mail->Body = "<h2>Email Change Verification</h2><p>You requested to change your email address. Your verification code is: <b>$code</b></p><p>Please enter this code to confirm your new email address.</p>";
    } else {
        $mail->Body = "<h2>Email Verification</h2><p>Your verification code is: <b>$code</b></p><p>Please enter this code to verify your email address.</p>";
    }

    $mail->send();
    
    $message = $is_email_change ? 'Verification email sent to your new email address!' : 'Verification email sent!';
    echo json_encode([
        'success' => true, 
        'message' => $message,
        'is_email_change' => $is_email_change
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to send email: ' . $e->getMessage()]);
}