<?php
include '../includes/config.php';

// Authenticate user using JWT
$user_data = authenticateUser();

if (!$user_data) {
    header("Location: login.php");
    exit();
}

$user_id = $user_data['id'];
$booking_id = $_GET['booking_id'] ?? null;

if (!$booking_id) {
    echo "Invalid booking.";
    exit();
}

$stmt = $conn->prepare("
    SELECT b.*, p.name AS package_name, p.price 
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
$total_amount = $booking['price'];
$downpayment = $total_amount * 0.3;

// Calculate total payments made
$stmt_payments = $conn->prepare("
    SELECT COALESCE(SUM(payment_amount), 0) as total_paid 
    FROM payments 
    WHERE booking_id = ? AND status = 'completed'
");
$stmt_payments->bind_param("i", $booking_id);
$stmt_payments->execute();
$payment_result = $stmt_payments->get_result();
$payment_data = $payment_result->fetch_assoc();
$total_paid = $payment_data['total_paid'];

// Calculate remaining balance
$remaining_balance = $total_amount - $total_paid;

// Check if already fully paid
if ($remaining_balance <= 0) {
    echo "<div class='alert alert-success'>This booking is already fully paid!</div>";
    echo "<a href='edit_center.php' class='btn btn-primary'>Back to Edit Center</a>";
    exit();
}

// Calculate minimum payment (downpayment if no payments made, or any amount if partial payments exist)
$min_payment = ($total_paid == 0) ? $downpayment : 0.01;

// Get JWT token from cookie for API calls
$jwt_token = $_COOKIE['jwt_token'] ?? '';

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<style>
        body {
            background: #121212;
            color: #fff;
            min-height: 100vh;
            font-family: 'Arial', sans-serif;
        }

        .payment-page {
            padding: 20px 0;
        }

        .page-header {
            text-align: center;
            color: white;
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
            background: linear-gradient(to right, #ff416c, #ff4b2b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            color: #fff;
        }

        .payment-container {
            max-width: 600px;
            margin: 0 auto;
            background: #1e1e1e;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        .payment-container h2 {
            color: #ff4b2b;
            margin-bottom: 25px;
            font-weight: bold;
        }

        .payment-booking-details {
            background: #2a2a2a;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #ff4b2b;
        }

        .payment-booking-details p {
            margin-bottom: 8px;
            color: #ccc;
        }

        .payment-booking-details span {
            color: #fff;
            font-weight: 600;
        }

        .payment-progress {
            background: #2a2a2a;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #ff416c;
            color: white;
        }

        .progress-bar-custom {
            background: #333;
            height: 20px;
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(to right, #ff416c, #ff4b2b);
            transition: width 0.3s ease;
        }

        #paymentForm {
            margin-top: 20px;
        }

        #paymentForm label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #fff;
        }

        #payment_amount {
            width: 100%;
            padding: 12px;
            border: 2px solid #444;
            border-radius: 8px;
            font-size: 1.1rem;
            margin-bottom: 15px;
            transition: border-color 0.3s;
            background: #2a2a2a;
            color: #fff;
        }

        #payment_amount:focus {
            outline: none;
            border-color: #ff4b2b;
            box-shadow: 0 0 0 3px rgba(255,75,43,0.2);
        }

        .payment-amount-suggestions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .payment-amount-btn {
            background: #2a2a2a;
            border: 2px solid #444;
            padding: 8px 15px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.9rem;
            color: #fff;
        }

        .payment-amount-btn:hover {
            background: linear-gradient(to right, #ff416c, #ff4b2b);
            color: white;
            border-color: #ff4b2b;
        }

        .payment-info {
            background: #2a2a2a;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #ff416c;
        }

        .payment-info p {
            margin: 0;
            color: #ccc;
            font-size: 0.9rem;
        }

        .payment-btn-pay {
            width: 100%;
            background: linear-gradient(to right, #ff416c, #ff4b2b);
            border: none;
            padding: 15px;
            border-radius: 8px;
            color: white;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 15px;
        }

        .payment-btn-pay:hover {
            background: linear-gradient(to right, #ff4b2b, #ff1a1a);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,75,43,0.4);
        }

        .payment-btn-pay:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .payment-btn-back {
            display: inline-block;
            background: #444;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s;
        }

        .payment-btn-back:hover {
            background: #555;
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #2a4a2a;
            color: #4ade80;
            border: 1px solid #166534;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-primary {
            background: linear-gradient(to right, #ff416c, #ff4b2b);
            color: white;
            border: 1px solid #ff4b2b;
        }

        .btn-primary:hover {
            background: linear-gradient(to right, #ff4b2b, #ff1a1a);
            border-color: #ff4b2b;
            color: white;
            text-decoration: none;
        }

        @media (max-width: 768px) {
            .payment-container {
                margin: 0 15px;
                padding: 20px;
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .payment-amount-suggestions {
                flex-direction: column;
            }

            .payment-amount-btn {
                text-align: center;
            }
        }
</style>
<body>
    <div class="payment-page">
        <section class="page-header">
            <div class="container-fluid">
                <h1>Payment Center</h1>
                <p>Secure payment processing for your event booking</p>
            </div>
        </section>

        <div class="payment-container">
            <h2><i class="bi bi-credit-card me-2"></i>Make Payment</h2>

            <div class="payment-booking-details">
                <p><strong>Event:</strong> <span><?php echo htmlspecialchars($booking['event_type']); ?></span></p>
                <p><strong>Package:</strong> <span><?php echo htmlspecialchars($booking['package_name']); ?></span></p>
                <p><strong>Total Amount:</strong> <span>₱<?php echo number_format($total_amount, 2); ?></span></p>
                <p><strong>Required Downpayment (30%):</strong> <span>₱<?php echo number_format($downpayment, 2); ?></span></p>
            </div>

            <div class="payment-progress">
                <p><strong>Payment Progress:</strong></p>
                <div class="progress-bar-custom">
                    <div class="progress-fill" style="width: <?php echo ($total_paid / $total_amount) * 100; ?>%"></div>
                </div>
                <p><strong>Amount Paid:</strong> <span style="color: #28a745;">₱<?php echo number_format($total_paid, 2); ?></span></p>
                <p><strong>Remaining Balance:</strong> <span style="color: #ff4b2b;">₱<?php echo number_format($remaining_balance, 2); ?></span></p>
            </div>

            <form id="paymentForm">
                <input type="hidden" id="booking_id" value="<?php echo $booking['id']; ?>">
                <input type="hidden" id="remaining_balance" value="<?php echo $remaining_balance; ?>">
                
                <label for="payment_amount">
                    <i class="bi bi-currency-dollar me-2"></i>Amount to Pay:
                </label>
                <input type="number" 
                       id="payment_amount" 
                       min="<?php echo $min_payment; ?>" 
                       max="<?php echo $remaining_balance; ?>" 
                       step="0.01" 
                       value="<?php echo ($total_paid == 0) ? $downpayment : $remaining_balance; ?>" 
                       required>
                
                <div class="payment-amount-suggestions">
                    <?php if ($total_paid == 0): ?>
                        <button type="button" class="payment-amount-btn" onclick="setAmount(<?php echo $downpayment; ?>)">
                            Downpayment (₱<?php echo number_format($downpayment, 2); ?>)
                        </button>
                    <?php endif; ?>
                    
                    <?php if ($remaining_balance > $downpayment): ?>
                        <button type="button" class="payment-amount-btn" onclick="setAmount(<?php echo $remaining_balance; ?>)">
                            Full Balance (₱<?php echo number_format($remaining_balance, 2); ?>)
                        </button>
                    <?php endif; ?>
                </div>

                <div class="payment-info">
                    <p class="mb-0">
                        <i class="bi bi-shield-check me-2"></i>
                        <strong>Secure Payment:</strong> You can pay any amount between ₱<?php echo number_format($min_payment, 2); ?> and ₱<?php echo number_format($remaining_balance, 2); ?>. Your payment is processed securely through PayPal.
                    </p>
                </div>

                <button type="submit" class="payment-btn-pay">
                    <i class="bi bi-paypal me-2"></i>Pay with PayPal
                </button>
            </form>

            <div class="text-center">
                <a href="edit_center.php" class="payment-btn-back">
                    <i class="bi bi-arrow-left me-2"></i>Back to Edit Center
                </a>
            </div>
        </div>
    </div>

    <script>
    function setAmount(amount) {
        const remainingBalance = parseFloat(document.getElementById("remaining_balance").value);
        const finalAmount = Math.min(amount, remainingBalance);
        document.getElementById("payment_amount").value = finalAmount;
    }

    document.getElementById("paymentForm").addEventListener("submit", function (e) {
        e.preventDefault();

        const bookingId = document.getElementById("booking_id").value;
        const amount = parseFloat(document.getElementById("payment_amount").value);
        const remainingBalance = parseFloat(document.getElementById("remaining_balance").value);

        // Validate amount
        if (isNaN(amount) || amount <= 0) {
            alert("Please enter a valid amount");
            return;
        }

        if (amount > remainingBalance) {
            alert(`Amount cannot exceed remaining balance of ₱${remainingBalance.toFixed(2)}`);
            return;
        }

        const minPayment = <?php echo $min_payment; ?>;
        if (amount < minPayment) {
            alert(`Minimum payment amount is ₱${minPayment.toFixed(2)}`);
            return;
        }

        // Show loading state
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Processing...';
        submitBtn.disabled = true;

        fetch("../API/paypal/create_payment.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                booking_id: parseInt(bookingId),
                amount: amount
            })
        })
        .then(response => {
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Response is not JSON. Content-Type: ' + contentType);
            }
            return response.text();
        })
        .then(text => {
            try {
                const data = JSON.parse(text);
                
                if (data.success && data.approval_url) {
                    window.location.href = data.approval_url;
                } else {
                    throw new Error(data.error || "Payment creation failed");
                }
            } catch (parseError) {
                throw new Error("Invalid response format: " + text);
            }
        })
        .catch(err => {
            console.error("Payment request failed:", err);
            alert("Payment failed: " + err.message);
            
            // Restore button
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>