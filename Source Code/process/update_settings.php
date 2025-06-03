<?php
include '../includes/config.php';

header('Content-Type: application/json');

// Authenticate user using JWT
$user_data = authenticateUser();
if (!$user_data) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $user_data['id'];

// Get form data
$first_name = $_POST['first_name'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$age = $_POST['age'] ?? '';
$birthday = $_POST['birthday'] ?? '';
$address = $_POST['address'] ?? '';
$contact_number = $_POST['contact_number'] ?? '';
$company_name = $_POST['company_name'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$profile_pic = '';

// Get current user email to check if it's being changed
$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$current_user = $stmt->get_result()->fetch_assoc();
$current_email = $current_user['email'];

$email_changed = ($email !== $current_email);

// Handle profile picture upload
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
    $target_dir = "../uploads/";
    $filename = basename($_FILES["profile_pic"]["name"]);
    $target_file = $target_dir . uniqid() . "_" . $filename;
    if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
        $profile_pic = basename($target_file);
    }
}

// Build SQL query
$sql = "UPDATE users SET 
    first_name = ?, 
    last_name = ?, 
    age = ?, 
    birthday = ?, 
    address = ?, 
    contact_number = ?, 
    company_name = ?";

$params = [$first_name, $last_name, $age, $birthday, $address, $contact_number, $company_name];
$types = "ssissss";

// Handle email change
if ($email_changed) {
    // Generate verification code
    $verification_code = rand(100000, 999999);
    
    // Store new email as pending and set verification code
    $sql .= ", pending_email = ?, email_verification_code = ?, email_verified = 0";
    $params[] = $email;
    $params[] = $verification_code;
    $types .= "ssi";
    
} else {
    // If email isn't changing, keep current email
    $sql .= ", email = ?";
    $params[] = $email;
    $types .= "s";
}

if (!empty($password)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql .= ", password = ?";
    $params[] = $hashed_password;
    $types .= "s";
}

if (!empty($profile_pic)) {
    $sql .= ", profile_pic = ?";
    $params[] = $profile_pic;
    $types .= "s";
}

$sql .= " WHERE id = ?";
$params[] = $user_id;
$types .= "i";

// Prepare and execute
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    // Fetch updated user data
    $stmt = $conn->prepare("SELECT first_name, last_name, age, birthday, address, contact_number, company_name, email, email_verified, pending_email FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $updated_user = $result->fetch_assoc();

    $message = 'Profile updated successfully';
    if ($email_changed) {
        $message .= '. A verification code has been sent to your new email address.';
    }

    echo json_encode([
        'success' => true,
        'message' => $message,
        'info' => $updated_user,
        'email_verification_required' => $email_changed
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Update failed']);
}

$conn->close();