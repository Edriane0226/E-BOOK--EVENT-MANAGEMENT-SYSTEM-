<?php
session_start();
include '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Delete related payments first
    $conn->query("DELETE FROM payments WHERE booking_id = $id");

    // Now delete the booking
    $conn->query("DELETE FROM bookings WHERE id = $id");
}

header("Location: admin_dashboard.php");
exit();
?>