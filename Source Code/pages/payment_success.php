<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'C:\xampp\htdocs\E-BOOK--EVENT-MANAGEMENT-SYSTEM-\vendor\autoload.php'; // Adjust path if needed

include '../includes/config.php';

// Authenticate user using JWT
$user_data = authenticateUser();

if (!$user_data) {
    header("Location: login.php");
    exit();
}

$user_id = $user_data['id'];
$booking_id = $_GET['booking_id'] ?? null;
$amount = $_GET['amount'] ?? null;
$transaction_id = $_GET['transaction_id'] ?? null;

if (!$booking_id) {
    echo "Invalid payment details.";
    exit();
}

// Get booking and payment details
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

// Get total payments made for this booking
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

$remaining_balance = $booking['price'] - $total_paid;
$is_fully_paid = $remaining_balance <= 0;

// Fetch user email and name
$stmt = $conn->prepare("SELECT email, name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_info = $user_result->fetch_assoc();
$user_email = $user_info['email'] ?? '';
$user_name = $user_info['name'] ?? '';

// Send billing email using PHPMailer
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com'; // SMTP server
    $mail->SMTPAuth   = true;
    $mail->Username   = 'edriane.bangonon0226@gmail.com'; // SMTP username
    $mail->Password   = 'biufkkeebyxwnkyh'; // SMTP password or app password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('your_email@gmail.com', 'E-Book Event Management');
    $mail->addAddress($user_email, $user_name);

    $mail->isHTML(true);
    $mail->Subject = 'Payment Receipt - Booking #' . $booking['id'];
    $mail->Body    = '<h2>Payment Receipt</h2>'
        . '<p>Hi ' . htmlspecialchars($user_name) . ',</p>'
        . '<p>Thank you for your payment. Here are your payment details:</p>'
        . '<ul>'
        . '<li><strong>Booking ID:</strong> #' . $booking['id'] . '</li>'
        . '<li><strong>Event:</strong> ' . htmlspecialchars($booking['event_type']) . '</li>'
        . '<li><strong>Package:</strong> ' . htmlspecialchars($booking['package_name']) . '</li>'
        . '<li><strong>Event Date:</strong> ' . date('M d, Y', strtotime($booking['event_date'])) . '</li>'
        . '<li><strong>Amount Paid:</strong> ₱' . number_format($amount, 2) . '</li>'
        . '<li><strong>Transaction ID:</strong> ' . htmlspecialchars($transaction_id) . '</li>'
        . '<li><strong>Status:</strong> ' . ($is_fully_paid ? 'Fully Paid' : 'Partially Paid') . '</li>'
        . '</ul>'
        . '<p>If you have questions, contact us anytime.</p>'
        . '<p>Regards,<br>E-Book Event Management Team</p>';

    $mail->send();
    // Optionally, you can set a flag to show a message that email was sent
} catch (Exception $e) {
    // Optionally log error: $mail->ErrorInfo
}

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .success-page {
            background-color: #121212;
            color: #fff;
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
        }

        .success-container {
            max-width: 700px;
            margin: auto;
            background-color: #1e1e1e;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(40, 167, 69, 0.3);
            margin-top: 40px;
            margin-bottom: 40px;
        }

        .success-page .page-header {
            background: linear-gradient(to right, #28a745, #20c997);
            padding: 60px 0;
            text-align: center;
            margin-bottom: 40px;
        }

        .success-page .page-header h1 {
            font-size: 3rem;
            font-weight: bold;
            margin: 0;
            color: white;
        }

        .success-page .page-header p {
            color: white;
            opacity: 0.9;
            margin: 10px 0 0 0;
        }

        .success-icon {
            text-align: center;
            margin-bottom: 30px;
        }

        .success-icon i {
            font-size: 5rem;
            color: #28a745;
            animation: successPulse 2s infinite;
        }

        @keyframes successPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .success-page h2 {
            text-align: center;
            color: #28a745;
            margin-bottom: 30px;
            font-weight: bold;
        }

        .payment-details {
            background: #2a2a2a;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 4px solid #28a745;
        }

        .payment-details h5 {
            color: #28a745;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .payment-details p {
            font-size: 16px;
            margin: 12px 0;
            display: flex;
            justify-content: space-between;
            color: #fff;
        }

        .payment-details strong {
            color: #28a745;
        }

        .booking-details {
            background: #2a2a2a;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 4px solid #ff4b2b;
        }

        .booking-details h5 {
            color: #ff4b2b;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .booking-details p {
            font-size: 16px;
            margin: 12px 0;
            display: flex;
            justify-content: space-between;
            color: #fff;
        }

        .booking-details strong {
            color: #ff4b2b;
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-fully-paid {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
            border: 1px solid #28a745;
        }

        .status-partial-paid {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
            border: 1px solid #ffc107;
        }

        .success-btn {
            display: inline-block;
            padding: 12px 25px;
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            transition: all 0.3s ease;
            margin: 10px;
        }

        .success-btn:hover {
            background: linear-gradient(45deg, #218838, #1ea085);
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }

        .secondary-btn {
            display: inline-block;
            padding: 12px 25px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            transition: all 0.3s ease;
            margin: 10px;
        }

        .secondary-btn:hover {
            background: #5a6268;
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
        }

        .action-buttons {
            text-align: center;
            margin-top: 30px;
        }

        .info-alert {
            background: rgba(23, 162, 184, 0.1);
            border: 1px solid #17a2b8;
            color: #17a2b8;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .info-alert i {
            color: #17a2b8;
        }

        @media (max-width: 768px) {
            .success-page .page-header h1 {
                font-size: 2rem;
            }
            
            .success-container {
                margin: 20px 15px;
                padding: 25px;
            }
            
            .success-icon i {
                font-size: 3.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="success-page">
        <section class="page-header">
            <div class="container-fluid">
                <h1>Payment Successful</h1>
                <p>Your payment has been processed successfully</p>
            </div>
        </section>

        <div class="success-container">
            <div class="success-icon">
                <i class="bi bi-check-circle-fill"></i>
            </div>

            <h2><i class="bi bi-check2-circle me-2"></i>Payment Completed!</h2>

            <div class="payment-details">
                <h5><i class="bi bi-credit-card me-2"></i>Payment Information</h5>
                <?php if ($amount): ?>
                    <p><strong>Amount Paid:</strong> <span>₱<?php echo number_format($amount, 2); ?></span></p>
                <?php endif; ?>
                <?php if ($transaction_id): ?>
                    <p><strong>Transaction ID:</strong> <span><?php echo htmlspecialchars($transaction_id); ?></span></p>
                <?php endif; ?>
                <p><strong>Payment Method:</strong> <span>PayPal</span></p>
                <p><strong>Payment Date:</strong> <span><?php echo date('M d, Y h:i A'); ?></span></p>
                <p><strong>Status:</strong> 
                    <span class="status-badge <?php echo $is_fully_paid ? 'status-fully-paid' : 'status-partial-paid'; ?>">
                        <?php echo $is_fully_paid ? 'Fully Paid' : 'Partially Paid'; ?>
                    </span>
                </p>
            </div>

            <div class="booking-details">
                <h5><i class="bi bi-calendar-event me-2"></i>Booking Details</h5>
                <p><strong>Booking ID:</strong> <span>#<?php echo $booking['id']; ?></span></p>
                <p><strong>Event:</strong> <span><?php echo htmlspecialchars($booking['event_type']); ?></span></p>
                <p><strong>Package:</strong> <span><?php echo htmlspecialchars($booking['package_name']); ?></span></p>
                <p><strong>Event Date:</strong> <span><?php echo date('M d, Y', strtotime($booking['event_date'])); ?></span></p>
                <p><strong>Total Package Price:</strong> <span>₱<?php echo number_format($booking['price'], 2); ?></span></p>
                <p><strong>Total Paid:</strong> <span>₱<?php echo number_format($total_paid, 2); ?></span></p>
                <?php if (!$is_fully_paid): ?>
                    <p><strong>Remaining Balance:</strong> <span style="color: #ffc107;">₱<?php echo number_format($remaining_balance, 2); ?></span></p>
                <?php endif; ?>
            </div>

            <?php if (!$is_fully_paid): ?>
                <div class="info-alert">
                    <p class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Remaining Balance:</strong> You still have a remaining balance of ₱<?php echo number_format($remaining_balance, 2); ?>. You can make additional payments anytime before your event date.
                    </p>
                </div>
            <?php else: ?>
                <div class="info-alert" style="background: rgba(40, 167, 69, 0.1); border-color: #28a745; color: #28a745;">
                    <p class="mb-0">
                        <i class="bi bi-check-circle me-2"></i>
                        <strong>Congratulations!</strong> Your booking is fully paid. You will receive a confirmation email shortly with all the details.
                    </p>
                </div>
            <?php endif; ?>

            <div class="action-buttons">
                <a href="dashboard.php" class="success-btn">
                    <i class="bi bi-house me-2"></i>Back to Dashboard
                </a>
                <a href="payment.php" class="secondary-btn">
                    <i class="bi bi-credit-card me-2"></i>Payment History
                </a>
                <?php if (!$is_fully_paid): ?>
                    <a href="payment_center.php?booking_id=<?php echo $booking['id']; ?>" class="success-btn">
                        <i class="bi bi-plus-circle me-2"></i>Make Another Payment
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>