<?php
session_start();
include '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

if (!isset($_GET['id']) || !isset($_GET['action'])) {
    header("Location: admin_refunds.php?error=missing_params");
    exit();
}

$refund_id = (int) $_GET['id'];
$actionParam = $_GET['action'];

if ($actionParam === 'approve') {
    $action = 'approved';
} elseif ($actionParam === 'reject') {
    $action = 'rejected';
} else {
    header("Location: admin_refunds.php?error=invalid_action");
    exit();
}

$conn->begin_transaction();

try {
    // 1. Update refund status
    $updateRefund = $conn->prepare("UPDATE refunds SET refund_status = ?, processed_at = NOW() WHERE id = ?");
    $updateRefund->bind_param("si", $action, $refund_id);

    if (!$updateRefund->execute()) {
        throw new Exception("Refund update failed: " . $updateRefund->error);
    }

    // 2. If approved, mark payment as refunded
    if ($action === 'approved') {
        $getPaymentId = $conn->prepare("SELECT payment_id FROM refunds WHERE id = ?");
        $getPaymentId->bind_param("i", $refund_id);
        $getPaymentId->execute();
        $result = $getPaymentId->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("Refund record not found.");
        }

        $row = $result->fetch_assoc();
        $payment_id = $row['payment_id'];

        // Ensure payment exists before updating
        $checkPayment = $conn->prepare("SELECT id FROM payments WHERE id = ? AND is_refunded = 0");
        $checkPayment->bind_param("i", $payment_id);
        $checkPayment->execute();
        $checkResult = $checkPayment->get_result();

        if ($checkResult->num_rows === 0) {
            throw new Exception("Payment already refunded or not found.");
        }

        $updatePayment = $conn->prepare("UPDATE payments SET is_refunded = 1 WHERE id = ?");
        $updatePayment->bind_param("i", $payment_id);

        if (!$updatePayment->execute()) {
            throw new Exception("Payment update failed: " . $updatePayment->error);
        }
    }

    $conn->commit();
    header("Location: admin_refunds.php?success=$action");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    echo "Error: " . $e->getMessage();
    error_log("Refund processing failed: " . $e->getMessage());
    exit();
}
