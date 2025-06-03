<?php
include '../includes/config.php';

// Authenticate user using JWT
$user_data = authenticateUser();

if (!$user_data) {
    header("Location: login.php");
    exit();
}

$user_id = $user_data['id'];

// Separate queries for better readability and maintainability

// 1. Get bookings that already have refund requests
$bookings_with_refunds = "
    SELECT DISTINCT b2.id
    FROM refunds r
    JOIN payments p2 ON r.payment_id = p2.id
    JOIN bookings b2 ON p2.booking_id = b2.id
    WHERE b2.user_id = ?
";

// 2. Get payment summary per booking
$payment_summary = "
    SELECT 
        p.booking_id,
        MAX(p.payment_date) AS latest_payment_date,
        SUM(p.payment_amount) AS total_payment,
        GROUP_CONCAT(DISTINCT p.payment_type SEPARATOR ', ') AS payment_types,
        TIMESTAMPDIFF(HOUR, MAX(p.payment_date), NOW()) AS hours_since_payment
    FROM payments p
    WHERE p.is_refunded = 0
    GROUP BY p.booking_id
";

// 3. Main query combining all subqueries
$main_query = "
    SELECT 
        b.id AS booking_id,
        b.event_type,
        ps.latest_payment_date,
        ps.total_payment,
        ps.payment_types,
        pk.price AS package_price,
        ps.hours_since_payment
    FROM bookings b
    INNER JOIN packages pk ON b.package_id = pk.id
    INNER JOIN ($payment_summary) ps ON b.id = ps.booking_id
    WHERE b.user_id = ?
      AND b.id NOT IN (
          SELECT id FROM ($bookings_with_refunds) bwr
      )
      AND ps.hours_since_payment <= 24
    ORDER BY ps.latest_payment_date DESC
";

$stmt = $conn->prepare($main_query);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Refund</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #121212 !important;
            color: white !important;
            font-family: 'Poppins', sans-serif !important;
            margin: 0;
        }

        .main-content {
            min-height: 100vh;
        }

        .page-header {
            background: linear-gradient(to right, #ff416c, #ff4b2b);
            padding: 60px 0;
            text-align: center;
            margin-bottom: 40px;
        }

        .page-header h1 {
            font-size: 3rem;
            font-weight: bold;
            margin: 0;
            color: white;
        }

        .page-header p {
            color: white;
            opacity: 0.9;
            margin: 10px 0 0 0;
        }

        .refund-container {
            max-width: 700px;
            margin: 0 auto 40px auto;
            padding: 40px;
            background-color: #1e1e1e;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(255, 75, 43, 0.3);
            opacity: 0;
            transform: translateY(20px);
            animation: fadeIn 0.8s forwards ease-in-out;
        }

        .form-section {
            background: #2a2a2a;
            padding: 30px;
            border-radius: 12px;
            border-left: 4px solid #ff4b2b;
        }

        h2 {
            color: #ff4b2b;
            text-align: center;
            margin-bottom: 30px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-top: 25px;
            margin-bottom: 10px;
            color: #ff4b2b;
        }

        select,
        textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #444;
            border-radius: 8px;
            background-color: #2c2c2c;
            color: white;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .btn-submit {
            margin-top: 30px;
            width: 100%;
            padding: 15px;
            background: linear-gradient(45deg, #ff416c, #ff4b2b);
            color: white;
            font-size: 18px;
            font-weight: bold;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-back {
            display: inline-block;
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            transition: all 0.3s ease;
            margin-bottom: 30px;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #888;
            font-style: italic;
            background: #2a2a2a;
            border-radius: 12px;
            border: 2px dashed #444;
        }

        .refund-info {
            background: rgba(255, 193, 7, 0.1);
            border: 1px solid #ffc107;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .refund-info i {
            color: #ffc107;
        }

        .refund-info h6 {
            color: #ffc107;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .refund-info ul {
            margin-bottom: 0;
            padding-left: 20px;
        }

        .refund-info li {
            margin-bottom: 5px;
        }

        .time-remaining {
            font-size: 12px;
            color: #ffc107;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <section class="page-header">
            <div class="container">
                <h1>Request Refund</h1>
                <p>Submit a refund request for eligible payments</p>
            </div>
        </section>

        <div class="container">
            <a href="payment.php" class="btn-back">
                <i class="bi bi-arrow-left me-2"></i>Back to Payment History
            </a>

            <div class="refund-container">
                <div class="refund-info">
                    <h6><i class="bi bi-info-circle me-2"></i>Refund Policy</h6>
                    <ul>
                        <li>Refunds can only be requested within <strong>24 hours</strong> of payment</li>
                        <li>Processing time may take 3-5 business days</li>
                        <li>You will receive email notifications about your refund status</li>
                        <li>Partial refunds may apply based on cancellation timing</li>
                    </ul>
                </div>

                <div class="form-section">
                    <h2>
                        <i class="bi bi-arrow-return-left"></i>
                        Request a Refund
                    </h2>

                    <?php if ($result->num_rows > 0): ?>
                        <form method="post" action="/E-BOOK--EVENT-MANAGEMENT-SYSTEM-/Source%20Code/process/process_refund.php">
                            <label for="booking_id">
                                <i class="bi bi-calendar-event me-2"></i>Select Booking:
                            </label>
                            <select name="booking_id" required>
                                <option value="">Choose a booking to refund...</option>
                                <?php while ($row = $result->fetch_assoc()) :
                                    $status = $row['total_payment'] < $row['package_price'] ? 'Partially Paid' : 'Fully Paid';
                                    $hours_remaining = 24 - $row['hours_since_payment'];
                                    $time_text = $hours_remaining > 0 ? "({$hours_remaining} hours left)" : "(Expired)";
                                ?>
                                    <option value="<?= $row['booking_id'] ?>">
                                        <?= htmlspecialchars($row['event_type']) ?> - <?= htmlspecialchars($row['payment_types']) ?> - ₱<?= number_format($row['total_payment'], 2) ?> (<?= $status ?>) on <?= date('M d, Y h:i A', strtotime($row['latest_payment_date'])) ?> <?= $time_text ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>

                            <label for="reason">
                                <i class="bi bi-chat-text me-2"></i>Refund Reason:
                            </label>
                            <textarea name="reason" rows="6" required placeholder="Please provide a detailed explanation for your refund request. Include any relevant information about why you need to cancel or request a refund..."></textarea>

                            <button type="submit" class="btn-submit">
                                <i class="bi bi-send me-2"></i>Submit Refund Request
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="no-data">
                            <i class="bi bi-exclamation-triangle display-4 mb-3 d-block"></i>
                            <h5>No Eligible Bookings Found</h5>
                            <p class="mb-0">
                                No refundable bookings found within 24 hours of latest payment.<br>
                                <a href="booking.php" class="text-decoration-none" style="color: #ff4b2b;">
                                    Make a new booking
                                </a> or 
                                <a href="payment.php" class="text-decoration-none" style="color: #ff4b2b;">
                                    view payment history
                                </a>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>