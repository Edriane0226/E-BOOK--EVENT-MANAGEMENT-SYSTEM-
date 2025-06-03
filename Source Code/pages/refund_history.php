<?php
include '../includes/config.php';

// Authenticate user using JWT
$user_data = authenticateUser();

if (!$user_data) {
    header("Location: login.php");
    exit();
}

$user_id = $user_data['id'];

$sql = "
    SELECT 
        r.id,
        r.refund_amount,
        r.reason,
        r.refund_status,
        r.requested_at,
        r.processed_at,
        b.event_type,
        p.payment_type,
        p.payment_date
    FROM refunds r
    JOIN payments p ON r.payment_id = p.id
    JOIN bookings b ON p.booking_id = b.id
    WHERE r.user_id = ?
    ORDER BY r.requested_at DESC
";

$stmt = $conn->prepare($sql);
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
    <title>Refund History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #121212;
            color: white;
            font-family: 'Poppins', sans-serif ;
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
            max-width: 1200px;
            margin: 0 auto 40px auto;
            padding: 40px;
            background-color: #1e1e1e;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(255, 75, 43, 0.3);
            opacity: 0;
            transform: translateY(20px);
            animation: fadeIn 0.8s forwards ease-in-out;
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

        .table-hover tbody tr:hover {
            background: #2a2a2a;
            transition: background 0.3s ease;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
            border: 1px solid #ffc107;
        }

        .status-approved {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
            border: 1px solid #28a745;
        }

        .status-rejected {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
            border: 1px solid #dc3545;
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

        .btn-request {
            background: linear-gradient(45deg, #ff416c, #ff4b2b);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .reason-cell {
            max-width: 300px;
            word-wrap: break-word;
            white-space: pre-wrap;
        }

        .amount-cell {
            font-weight: bold;
            color: #ff4b2b;
        }

        .success-alert {
            background: rgba(40, 167, 69, 0.1);
            border: 1px solid #28a745;
            color: #28a745;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <section class="page-header">
            <div class="container">
                <h1>Refund History</h1>
                <p>Track your refund requests and their status</p>
            </div>
        </section>

        <div class="container">
            <a href="payment.php" class="btn-back">
                <i class="bi bi-arrow-left me-2"></i>Back to Payment History
            </a>

            <div class="refund-container">
                <?php if (isset($_GET['success'])): ?>
                    <div class="success-alert">
                        <i class="bi bi-check-circle me-2"></i>
                        <?= htmlspecialchars($_GET['success']) ?>
                    </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">
                        <i class="bi bi-clock-history"></i>
                        Refund Request History
                    </h2>
                    <a href="req_refund.php" class="btn-request">
                        <i class="bi bi-plus-circle me-2"></i>New Refund Request
                    </a>
                </div>

                <?php if ($result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Event Type</th>
                                    <th>Payment Type</th>
                                    <th>Refund Amount</th>
                                    <th>Status</th>
                                    <th>Reason</th>
                                    <th>Requested At</th>
                                    <th>Processed At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['event_type']) ?></td>
                                        <td><?= htmlspecialchars($row['payment_type'] ?? 'N/A') ?></td>
                                        <td class="amount-cell">₱<?= number_format($row['refund_amount'], 2) ?></td>
                                        <td>
                                            <span class="status-badge status-<?= $row['refund_status'] ?>">
                                                <?= ucfirst($row['refund_status']) ?>
                                            </span>
                                        </td>
                                        <td class="reason-cell"><?= nl2br(htmlspecialchars($row['reason'])) ?></td>
                                        <td><?= date('M d, Y h:i A', strtotime($row['requested_at'])) ?></td>
                                        <td>
                                            <?= $row['processed_at'] ? date('M d, Y h:i A', strtotime($row['processed_at'])) : '<span class="text-muted">—</span>' ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <i class="bi bi-inbox display-4 mb-3 d-block"></i>
                        <h5>No Refund Requests Found</h5>
                        <p class="mb-3">You have not made any refund requests yet.</p>
                        <a href="req_refund.php" class="btn-request">
                            <i class="bi bi-plus-circle me-2"></i>Request Your First Refund
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>