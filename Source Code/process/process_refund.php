<?php
include '../includes/config.php';

// Authenticate user using JWT
$user_data = authenticateUser();

if (!$user_data) {
    header("Location: ../pages/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $user_data['id'];
    $booking_id = $_POST['booking_id'] ?? null;
    $reason = trim($_POST['reason'] ?? '');

    if (!$booking_id || empty($reason)) {
        // Redirect with error parameter instead of session
        header("Location: ../pages/req_refund.php?error=" . urlencode("Invalid refund request."));
        exit();
    }

    // Get latest eligible payment (within 24 hours, not refunded)
    $query = "
        SELECT p.id AS payment_id, p.payment_amount, p.payment_date
        FROM payments p
        JOIN bookings b ON p.booking_id = b.id
        WHERE b.user_id = ? AND p.booking_id = ? AND p.is_refunded = 0
        AND NOT EXISTS (
            SELECT 1 FROM refunds r WHERE r.payment_id = p.id
        )
        ORDER BY p.payment_date DESC
        LIMIT 1
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        header("Location: ../pages/req_refund.php?error=" . urlencode("No eligible payment found for refund."));
        exit();
    }

    $payment = $result->fetch_assoc();
    $payment_id = $payment['payment_id'];
    $payment_date = $payment['payment_date'];

    // Check 24-hour refund window
    $diffQuery = "SELECT TIMESTAMPDIFF(HOUR, ?, NOW()) AS hours_diff";
    $diffStmt = $conn->prepare($diffQuery);
    $diffStmt->bind_param("s", $payment_date);
    $diffStmt->execute();
    $diffResult = $diffStmt->get_result();
    $diff = $diffResult->fetch_assoc()['hours_diff'];

    if ($diff > 24) {
        header("Location: ../pages/req_refund.php?error=" . urlencode("Refunds can only be requested within 24 hours of payment."));
        exit();
    }

    // Check if refund request already exists for this payment
    $checkExisting = "SELECT id FROM refunds WHERE payment_id = ?";
    $checkStmt = $conn->prepare($checkExisting);
    $checkStmt->bind_param("i", $payment_id);
    $checkStmt->execute();
    $existingResult = $checkStmt->get_result();

    if ($existingResult->num_rows > 0) {
        header("Location: ../pages/req_refund.php?error=" . urlencode("A refund request already exists for this payment."));
        exit();
    }

    // Insert refund request
    $insert = "
        INSERT INTO refunds (payment_id, user_id, refund_amount, reason, refund_status, requested_at)
        VALUES (?, ?, ?, ?, 'pending', NOW())
    ";
    $refund_amount = $payment['payment_amount'];
    $insertStmt = $conn->prepare($insert);
    $insertStmt->bind_param("iids", $payment_id, $user_id, $refund_amount, $reason);

    if ($insertStmt->execute()) {
        // Log the refund request
        error_log("Refund request submitted - User ID: $user_id, Payment ID: $payment_id, Amount: $refund_amount");
        
        header("Location: ../pages/refund_history.php?success=" . urlencode("Refund request submitted successfully!"));
    } else {
        error_log("Failed to insert refund request - User ID: $user_id, Payment ID: $payment_id, Error: " . $conn->error);
        
        header("Location: ../pages/req_refund.php?error=" . urlencode("Failed to submit refund request. Please try again."));
    }

    exit();
} else {
    // If not POST request, redirect to refund request page
    header("Location: ../pages/req_refund.php");
    exit();
}
?>