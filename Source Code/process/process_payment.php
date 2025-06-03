<?php
session_start();
include '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Invalid request method.";
    exit();
}

$booking_id = $_POST['booking_id'] ?? null;
$payment_amount = $_POST['payment_amount'] ?? null;
$user_id = $_SESSION['user_id'];
$payment_type = "PayPal"; // Hardcoded

if (!$booking_id || !$payment_amount || $payment_amount <= 0) {
    echo "Please fill all fields correctly.";
    exit();
}

// Get booking and package price
$stmt = $conn->prepare("
    SELECT b.*, p.price 
    FROM bookings b
    LEFT JOIN packages p ON b.package_id = p.id
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Booking not found.";
    exit();
}

$booking = $result->fetch_assoc();
$package_price = $booking['price'];
$required_downpayment = $package_price * 0.3;

// Check if payment amount is at least the required downpayment
if ($payment_amount < $required_downpayment) {
    echo "Payment must be at least 30% of the total package price (₱" . number_format($required_downpayment, 2) . ").";
    exit();
}

// Insert payment record (initially with pending status)
$stmt = $conn->prepare("
    INSERT INTO payments (booking_id, user_id, payment_type, payment_amount, payment_date, status)
    VALUES (?, ?, ?, ?, NOW(), 'pending')
");
$stmt->bind_param("iisd", $booking_id, $user_id, $payment_type, $payment_amount);

if (!$stmt->execute()) {
    echo "Payment failed. Please try again.";
    exit();
}

// Sum total payments for the booking
$stmt = $conn->prepare("SELECT SUM(payment_amount) AS total_paid FROM payments WHERE booking_id = ?");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();
$total_paid = $result->fetch_assoc()['total_paid'] ?? 0;

// Update all payments' status to 'completed' if fully paid
if ($total_paid >= $package_price) {
    $stmt = $conn->prepare("UPDATE payments SET status = 'completed' WHERE booking_id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
}

header("Location: ../pages/dashboard.php?success=1");
exit();
?>