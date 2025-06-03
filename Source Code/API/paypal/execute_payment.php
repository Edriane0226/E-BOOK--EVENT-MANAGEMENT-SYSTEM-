<?php
include '../../includes/config.php';
$config = include 'paypal_config.php';

header('Content-Type: application/json');

// Authenticate user using JWT
$user_data = authenticateUser();

if (!$user_data) {
    http_response_code(401);
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$user_id = $user_data['id'];

// Get payment ID and payer ID from request
$payment_id = $_GET['paymentId'] ?? null;
$payer_id = $_GET['PayerID'] ?? null;
$booking_id = $_GET['booking_id'] ?? null;

if (!$payment_id || !$payer_id || !$booking_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing payment parameters']);
    exit;
}

error_log("Payment ID: $payment_id");
error_log("Payer ID: $payer_id");
error_log("Booking ID: $booking_id");
error_log("User ID: $user_id");

// Validate booking belongs to user
$stmt = $conn->prepare("
    SELECT b.id, b.event_type, p.price, p.name as package_name
    FROM bookings b 
    JOIN packages p ON b.package_id = p.id 
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    error_log("Booking not found or mismatch for booking_id=$booking_id and user_id=$user_id");
    http_response_code(404);
    echo json_encode(['error' => 'Booking not found']);
    exit;
}

$booking = $result->fetch_assoc();
$stmt->close();

// Get PayPal access token
function getPayPalAccessToken($config) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $config['api_url'] . '/v1/oauth2/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_USERPWD, $config['client_id'] . ':' . $config['client_secret']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Accept-Language: en_US'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        error_log("Failed to get PayPal access token: HTTP $httpCode - $response");
        return false;
    }
    
    $data = json_decode($response, true);
    return $data['access_token'] ?? false;
}

// Execute PayPal payment
function executePayPalPayment($config, $accessToken, $paymentId, $payerId) {
    $executeData = [
        'payer_id' => $payerId
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $config['api_url'] . '/v1/payments/payment/' . $paymentId . '/execute');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($executeData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        error_log("PayPal payment execution failed: HTTP $httpCode - $response");
        return false;
    }
    
    return json_decode($response, true);
}

// Get access token
$accessToken = getPayPalAccessToken($config);

if (!$accessToken) {
    http_response_code(500);
    echo json_encode(['error' => 'PayPal authentication failed']);
    exit;
}

// Execute the payment
$executedPayment = executePayPalPayment($config, $accessToken, $payment_id, $payer_id);

if (!$executedPayment) {
    http_response_code(500);
    echo json_encode(['error' => 'PayPal payment execution failed']);
    exit;
}

// Check payment state
if ($executedPayment['state'] !== 'approved') {
    error_log("Payment not approved: " . json_encode($executedPayment));
    http_response_code(400);
    echo json_encode(['error' => 'Payment not approved']);
    exit;
}

// Get transaction details
$transaction = $executedPayment['transactions'][0] ?? null;
if (!$transaction) {
    error_log("No transaction found in executed payment");
    http_response_code(500);
    echo json_encode(['error' => 'Invalid payment response']);
    exit;
}

$amount = floatval($transaction['amount']['total']);
$currency = $transaction['amount']['currency'];
$transaction_id = $transaction['related_resources'][0]['sale']['id'] ?? null;

// Update payment status in database
$stmt = $conn->prepare("
    UPDATE payments 
    SET status = 'completed', 
        paypal_transaction_id = ?,
        processed_at = NOW()
    WHERE paypal_payment_id = ? AND booking_id = ?
");

if ($stmt) {
    $stmt->bind_param("ssi", $transaction_id, $payment_id, $booking_id);
    $success = $stmt->execute();
    $stmt->close();
    
    if (!$success) {
        error_log("Failed to update payment status in database");
    }
} else {
    error_log("Failed to prepare payment update statement: " . $conn->error);
}

// Check if booking is fully paid
$stmt = $conn->prepare("
    SELECT IFNULL(SUM(payment_amount), 0) as total_paid
    FROM payments 
    WHERE booking_id = ? AND status = 'completed'
");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();
$payment_data = $result->fetch_assoc();
$total_paid = $payment_data['total_paid'];
$stmt->close();

// Update booking status if fully paid
if ($total_paid >= $booking['price']) {
    // Use 'Approved' since that's one of the valid ENUM values
    $stmt = $conn->prepare("UPDATE bookings SET status = 'Approved' WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
    if ($stmt->execute()) {
        error_log("Booking status updated to Approved for booking ID: $booking_id");
    } else {
        error_log("Failed to update booking status: " . $stmt->error);
    }
    $stmt->close();
}

// Log successful payment
error_log("Payment completed successfully - Booking ID: $booking_id, Amount: $amount, Transaction ID: $transaction_id");

// Redirect to success page
$success_url = "/E-BOOK--EVENT-MANAGEMENT-SYSTEM-/Source%20Code/pages/payment_success.php?booking_id=$booking_id&amount=$amount&transaction_id=$transaction_id";
header("Location: $success_url");
exit;
?>