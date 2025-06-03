<?php
include '../includes/config.php';

// Authenticate user using JWT
$user_data = authenticateUser();

if (!$user_data) {
    header("Location: login.php");
    exit();
}

$user_id = $user_data['id'];

$stmt = $conn->prepare("
    SELECT 
        b.*, 
        p.name AS package_name, 
        p.price,
        (
            SELECT er.status 
            FROM edit_requests er 
            WHERE er.booking_id = b.id 
            ORDER BY er.requested_at DESC 
            LIMIT 1
        ) AS edit_status,
        (
            SELECT IFNULL(SUM(CAST(payment_amount AS DECIMAL(10,2))), 0) 
            FROM payments 
            WHERE booking_id = b.id AND status = 'completed'
        ) AS total_paid,
        (
            SELECT r.refund_status
            FROM refunds r
            INNER JOIN payments pay ON r.payment_id = pay.id
            WHERE pay.booking_id = b.id
            ORDER BY r.requested_at DESC 
            LIMIT 1
        ) AS refund_status
    FROM bookings b 
    LEFT JOIN packages p ON b.package_id = p.id 
    WHERE b.user_id = ? 
    AND (
        SELECT r.refund_status
        FROM refunds r
        INNER JOIN payments pay ON r.payment_id = pay.id
        WHERE pay.booking_id = b.id
        ORDER BY r.requested_at DESC 
        LIMIT 1
    ) IS NULL OR (
        SELECT r.refund_status
        FROM refunds r
        INNER JOIN payments pay ON r.payment_id = pay.id
        WHERE pay.booking_id = b.id
        ORDER BY r.requested_at DESC 
        LIMIT 1
    ) != 'approved'
    ORDER BY b.event_date ASC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Include header after authentication
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #121212 !important;
            color: white !important;
            font-family: 'Poppins', sans-serif !important;
            margin: 0;
        }
        
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
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
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            background: #1e1e1e;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
        }
        
        th, td {
            padding: 15px 10px;
            border-bottom: 1px solid #444;
            text-align: center;
            color: white;
        }
        
        th {
            background: linear-gradient(to right, #ff416c, #ff4b2b);
            color: white;
            font-weight: bold;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            color: white;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            transition: all 0.3s ease;
            margin: 2px;
        }
        
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-edit {
            background: #ff9500;
        }
        
        
        .btn-pay {
            background: #28a745;
        }
        
        .btn-back {
            background: linear-gradient(45deg, #ff416c, #ff4b2b);
            margin-bottom: 20px;
            color: white;
        }
        
        .status-label {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-label.pending {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
            border: 1px solid #ffc107;
        }
        
        .status-label.approved {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
            border: 1px solid #28a745;
        }
        
        .status-label.rejected {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
            border: 1px solid #dc3545;
        }
        
        .status-label.none {
            background: rgba(108, 117, 125, 0.2);
            color: #6c757d;
            border: 1px solid #6c757d;
        }
        
        .priceMargin {
            margin-bottom: 10px;
            font-size: 13px;
            color: #ccc;
        }
        
        .no-bookings {
            text-align: center;
            padding: 40px;
            color: #888;
            font-style: italic;
        }
        
        .actions-cell {
            min-width: 200px;
        }
    </style>
</head>
<body>
    <section class="page-header">
        <div class="container">
            <h1>Edit & Payment Center</h1>
            <p class="lead">Manage your bookings and payments</p>
        </div>
    </section>

    <div class="main-container">
        <a href="dashboard.php" class="btn btn-back">← Back to Dashboard</a>
        
        <table>
            <thead>
            <tr>
                <th>Event Type</th>
                <th>Event Date</th>
                <th>Guests</th>
                <th>Package</th>
                <th>Price</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                        $amount_paid = floatval($row['total_paid']);
                        $total_price = isset($row['price']) ? floatval($row['price']) : 0;
                        $package_name = isset($row['package_name']) ? $row['package_name'] : 'No Package';
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['event_type']) ?></td>
                        <td><?= htmlspecialchars($row['event_date']) ?></td>
                        <td><?= htmlspecialchars($row['guests']) ?></td>
                        <td><?= htmlspecialchars($package_name) ?></td>
                        <td>₱<?= number_format($total_price, 2) ?></td>
                        <td>
                            <?php
                            $status = $row['edit_status'] ?? 'none';
                            $class = match (strtolower($status)) {
                                'pending' => 'pending',
                                'approved' => 'approved',
                                'rejected' => 'rejected',
                                default => 'none',
                            };
                            echo "<span class='status-label $class'>" . ucfirst($status) . "</span>";
                            ?>
                        </td>
                        <td class="actions-cell">
                            <div class="priceMargin">Paid: ₱<?= number_format($amount_paid, 2) ?> of ₱<?= number_format($total_price, 2) ?></div>
                            <a href="edit_booking.php?id=<?= $row['id'] ?>" class="btn btn-edit">Edit</a>
                            <?php if ($amount_paid >= $total_price && $total_price > 0): ?>
                                <span class="btn btn-secondary" style="cursor: default;">Fully Paid</span>
                            <?php else: ?>
                                <a href="payment_center.php?booking_id=<?= $row['id'] ?>" class="btn btn-pay">Pay Now</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7" class="no-bookings">No bookings found. <a href="booking.php" class="btn btn-pay" style="margin-left: 10px;">Make a Booking</a></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>