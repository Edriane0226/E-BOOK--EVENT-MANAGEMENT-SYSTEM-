<?php
include '../includes/config.php';

// Authenticate user using JWT
$user_data = authenticateUser();

if (!$user_data) {
    header("Location: login.php");
    exit();
}

$user_id = $user_data['id'];

// 1. Get total paid amount per booking
$total_paid_per_booking = "
    SELECT 
        booking_id, 
        IFNULL(SUM(payment_amount), 0) as total_paid
    FROM payments 
    WHERE status = 'completed' 
    GROUP BY booking_id
";

// 2. Get bookings with approved refunds (to exclude)
$approved_refund_bookings = "
    SELECT DISTINCT p.booking_id
    FROM refunds r
    INNER JOIN payments p ON r.payment_id = p.id
    WHERE r.refund_status = 'approved'
";

// 3. Main payment history query combining all subqueries
$payment_query = "
    SELECT 
        b.id as booking_id,
        b.event_type,
        b.event_date,
        b.status as booking_status,
        pmt.id as payment_id,
        pmt.payment_amount,
        pmt.payment_date,
        pmt.payment_type,
        pmt.status as payment_status,
        pmt.paypal_payment_id,
        pmt.paypal_transaction_id,
        pmt.processed_at,
        pkg.name AS package_name,
        pkg.price,
        IFNULL(tpb.total_paid, 0) as total_paid
    FROM payments pmt
    INNER JOIN bookings b ON pmt.booking_id = b.id
    INNER JOIN packages pkg ON b.package_id = pkg.id
    LEFT JOIN ($total_paid_per_booking) tpb ON b.id = tpb.booking_id
    WHERE b.user_id = ?
      AND pmt.status = 'completed'
      AND pmt.is_refunded = 0
      AND b.id NOT IN (
          SELECT booking_id FROM ($approved_refund_bookings) arb
      )
    ORDER BY pmt.payment_date DESC
";

$stmt = $conn->prepare($payment_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #121212;
            color: white;
            font-family: 'Poppins', sans-serif;
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

        .dashboard-container {
            max-width: 1400px;
            margin: auto;
            padding: 0 20px 40px 20px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeIn 0.8s forwards ease-in-out;
        }

        .card-custom {
            background: #1e1e1e;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
        }

        .table {
            color: white;
            background: transparent;
        }

        .table th {
            background: linear-gradient(to right, #ff416c, #ff4b2b);
            color: white;
            border: none;
            font-weight: bold;
            text-align: center;
            padding: 15px 10px;
        }

        .table td {
            border-color: #444;
            text-align: center;
            vertical-align: middle;
            padding: 12px 10px;
        }

        .btn-custom {
            background: linear-gradient(45deg, #ff416c, #ff4b2b);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
        }

        .btn-back {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            margin-bottom: 20px;
            display: inline-block;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-completed {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
            border: 1px solid #28a745;
        }

        .payment-type-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }

        .payment-paypal {
            background: rgba(0, 48, 135, 0.2);
            color: #0070ba;
            border: 1px solid #0070ba;
        }

        .payment-cash {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
            border: 1px solid #28a745;
        }

        .transaction-id {
            font-family: monospace;
            font-size: 11px;
            color: #17a2b8;
            background: rgba(23, 162, 184, 0.1);
            padding: 2px 6px;
            border-radius: 4px;
        }

        .no-payments {
            text-align: center;
            padding: 40px;
            color: #888;
            font-style: italic;
        }

        .payment-amount {
            font-weight: bold;
            color: #28a745;
        }

        .payment-progress {
            font-size: 12px;
            color: #ffc107;
        }

        .success-note {
            background: rgba(40, 167, 69, 0.1);
            border: 1px solid #28a745;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <section class="page-header">
            <div class="container">
                <h1>Payment History</h1>
                <p>Track all your successful payment transactions</p>
            </div>
        </section>

        <div class="dashboard-container">
            <a href="dashboard.php" class="btn-back">
                <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
            </a>

            <div class="success-note">
                <i class="bi bi-check-circle-fill me-2"></i>
                <strong>Note:</strong> This page only shows successfully completed payments. Cancelled or pending payments are not displayed.
            </div>

            <div class="card-custom">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0" style="color: #ff4b2b;">
                        <i class="bi bi-credit-card-fill me-2"></i>Completed Payment Records
                    </h4>
                    <div>
                        <a href="req_refund.php" class="btn-custom me-2">
                            <i class="bi bi-arrow-return-left me-2"></i>Request Refund
                        </a>
                        <a href="refund_history.php" class="btn-custom">
                            <i class="bi bi-clock-history me-2"></i>Refund History
                        </a>
                    </div>
                </div>

                <div>
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Event Details</th>
                                <th>Package & Price</th>
                                <th>Payment Amount</th>
                                <th>Payment Type</th>
                                <th>Transaction ID</th>
                                <th>Payment Date</th>
                                <th>Status</th>
                                <th>Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <?php
                                    $payment_percentage = ($row['total_paid'] / $row['price']) * 100;
                                    $is_fully_paid = $payment_percentage >= 100;
                                    ?>
                                    <tr>
                                        <td>
                                            <strong>#<?php echo $row['booking_id']; ?></strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($row['event_type']); ?></strong><br>
                                                <small class="text-muted"><?php echo date('M d, Y', strtotime($row['event_date'])); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <?php echo htmlspecialchars($row['package_name']); ?><br>
                                                <small class="text-muted">₱<?php echo number_format($row['price'], 2); ?></small>
                                            </div>
                                        </td>
                                        <td class="payment-amount">
                                            ₱<?php echo number_format($row['payment_amount'], 2); ?>
                                        </td>
                                        <td>
                                            <span class="payment-type-badge payment-<?php echo strtolower($row['payment_type'] ?? 'paypal'); ?>">
                                                <?php echo ucfirst($row['payment_type'] ?? 'PayPal'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($row['paypal_transaction_id']): ?>
                                                <div class="transaction-id" title="<?php echo htmlspecialchars($row['paypal_transaction_id']); ?>">
                                                    <?php echo substr($row['paypal_transaction_id'], 0, 12) . '...'; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div>
                                                <?php echo date('M d, Y', strtotime($row['payment_date'])); ?><br>
                                                <small class="text-muted"><?php echo date('h:i A', strtotime($row['payment_date'])); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class='status-badge status-completed'>
                                                <i class="bi bi-check-circle-fill me-1"></i>Completed
                                            </span>
                                        </td>
                                        <td>
                                            <div class="payment-progress">
                                                <?php if ($is_fully_paid): ?>
                                                    <span style="color: #28a745;">
                                                        <i class="bi bi-check-circle-fill me-1"></i>100% Paid
                                                    </span>
                                                <?php else: ?>
                                                    <?php echo number_format($payment_percentage, 1); ?>%
                                                <?php endif; ?>
                                                <br>
                                                <small>(₱<?php echo number_format($row['total_paid'], 2); ?> of ₱<?php echo number_format($row['price'], 2); ?>)</small>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="no-payments">
                                        <i class="bi bi-credit-card display-4 mb-3 d-block"></i>
                                        No completed payment records found. 
                                        <br><br>
                                        <a href="booking.php" class="btn-custom">
                                            <i class="bi bi-plus-circle me-1"></i>Make a Booking
                                        </a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>