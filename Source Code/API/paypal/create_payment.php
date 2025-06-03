<?php
// Enable full error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../includes/config.php';
$config = include 'paypal_config.php';

header('Content-Type: application/json');

// 1. Authenticate user via JWT
$user_data = authenticateUser();

if (!$user_data) {
    http_response_code(401);
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$user_id = $user_data['id'];

// 2. Get booking ID and amount from request
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['booking_id']) || !isset($data['amount'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing booking ID or amount']);
    exit;
}

$booking_id = $data['booking_id'];
$amount = floatval($data['amount']);

if ($amount <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid amount']);
    exit;
}

error_log("Booking ID: $booking_id");
error_log("User ID: $user_id");
error_log("Amount: $amount");

// 3. Validate booking and get details
$stmt = $conn->prepare("
    SELECT b.id, b.event_type, p.price, p.name as package_name
    FROM bookings b 
    JOIN packages p ON b.package_id = p.id 
    WHERE b.id = ? AND b.user_id = ?
");

if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error (prepare failed)']);
    exit;
}

$stmt->bind_param("ii", $booking_id, $user_id);

if (!$stmt->execute()) {
    error_log("Execute failed: " . $stmt->error);
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error (execute failed)']);
    exit;
}

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    error_log("No matching booking found for booking_id = $booking_id and user_id = $user_id");
    http_response_code(404);
    echo json_encode(['error' => 'Invalid booking or mismatched user']);
    exit;
}

$booking = $result->fetch_assoc();
$stmt->close();

// 4. Validate amount doesn't exceed package price
if ($amount > $booking['price']) {
    http_response_code(400);
    echo json_encode(['error' => 'Amount exceeds package price']);
    exit;
}

// 5. Get PayPal access token
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
        return false;
    }
    
    $data = json_decode($response, true);
    return $data['access_token'] ?? false;
}

// 6. Create PayPal payment
function createPayPalPayment($config, $accessToken, $amount, $booking) {
    $paymentData = [
        'intent' => 'sale',
        'payer' => [
            'payment_method' => 'paypal'
        ],
        'transactions' => [[
            'amount' => [
                'total' => number_format($amount, 2, '.', ''),
                'currency' => 'PHP'
            ],
            'description' => "Payment for {$booking['event_type']} - {$booking['package_name']}",
            'item_list' => [
                'items' => [[
                    'name' => $booking['package_name'],
                    'description' => $booking['event_type'],
                    'quantity' => 1,
                    'price' => number_format($amount, 2, '.', ''),
                    'currency' => 'PHP'
                ]]
            ]
        ]],
        'redirect_urls' => [
            'return_url' => $config['return_url'] . '?booking_id=' . $booking['id'],
            'cancel_url' => $config['cancel_url'] . '?booking_id=' . $booking['id']
        ]
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $config['api_url'] . '/v1/payments/payment');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($paymentData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 201) {
        error_log("PayPal payment creation failed: HTTP $httpCode - $response");
        return false;
    }
    
    return json_decode($response, true);
}

// 7. Execute PayPal payment creation
$accessToken = getPayPalAccessToken($config);

if (!$accessToken) {
    error_log("Failed to get PayPal access token");
    http_response_code(500);
    echo json_encode(['error' => 'PayPal authentication failed']);
    exit;
}

$payment = createPayPalPayment($config, $accessToken, $amount, $booking);

if (!$payment) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create PayPal payment']);
    exit;
}

// 8. Store payment details in database
$payment_id = $payment['id'];
$approval_url = '';

foreach ($payment['links'] as $link) {
    if ($link['rel'] === 'approval_url') {
        $approval_url = $link['href'];
        break;
    }
}

if (empty($approval_url)) {
    error_log("No approval URL found in PayPal response");
    http_response_code(500);
    echo json_encode(['error' => 'Invalid PayPal response']);
    exit;
}

// Store pending payment in database
$stmt = $conn->prepare("
    INSERT INTO payments (booking_id, user_id, payment_amount, payment_type, status, payment_date) 
    VALUES (?, ?, ?, 'paypal', 'pending', NOW())
");

if ($stmt) {
    $stmt->bind_param("iid", $booking_id, $user_id, $amount);
    if ($stmt->execute()) {
        $db_payment_id = $conn->insert_id;
        
        // Try to update with PayPal payment ID if column exists
        $update_stmt = $conn->prepare("
            UPDATE payments 
            SET paypal_payment_id = ? 
            WHERE id = ?
        ");
        
        if ($update_stmt) {
            $update_stmt->bind_param("si", $payment_id, $db_payment_id);
            $update_stmt->execute();
            $update_stmt->close();
        }
        
        error_log("Payment stored successfully - DB ID: $db_payment_id, PayPal ID: $payment_id");
    } else {
        error_log("Failed to execute payment insert: " . $stmt->error);
    }
    $stmt->close();
} else {
    error_log("Failed to prepare payment insert statement: " . $conn->error);
}

// 9. Return success response with approval URL
echo json_encode([
    'success' => true,
    'payment_id' => $payment_id,
    'approval_url' => $approval_url,
    'booking_id' => $booking_id,
    'amount' => $amount
]);
?>