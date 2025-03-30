<?php
session_start();
include '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$user_id = $_SESSION['user_id'];

$upload_dir = "../uploads/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_FILES['profile_pic'])) {
    $file = $_FILES['profile_pic'];

    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
    if (!in_array($file['type'], $allowed_types)) {
        die("Invalid file type. Only JPG and PNG are allowed.");
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        die("File is too large. Max size is 2MB.");
    }

    $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = "profile_" . $user_id . "_" . time() . "." . $file_ext;
    $target_file = $upload_dir . $new_filename;

    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        $query = "UPDATE users SET profile_pic = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $new_filename, $user_id);
        $stmt->execute();

        header("Location: dashboard.php?success=Profile updated!");
        exit();
    } else {
        die("File upload failed.");
    }
} else {
    die("No file uploaded.");
}
?>
