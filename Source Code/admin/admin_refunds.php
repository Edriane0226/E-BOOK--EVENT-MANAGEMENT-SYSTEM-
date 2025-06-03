<?php
session_start();
include '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch all refund requests (latest per payment), joined with related booking and user details
$query = "
    SELECT 
        rr.id AS refund_id,
        rr.refund_status AS refund_status,
        rr.reason AS refund_reason,
        rr.requested_at,
        users.name AS user_name,
        users.email,
        bookings.event_type,
        bookings.event_date,
        packages.name AS package_name,
        payments.payment_amount,
        payments.status AS payment_status,
        bookings.id AS booking_id
    FROM refunds rr
    INNER JOIN payments ON rr.payment_id = payments.id
    INNER JOIN bookings ON payments.booking_id = bookings.id
    INNER JOIN users ON bookings.user_id = users.id
    INNER JOIN packages ON bookings.package_id = packages.id
    WHERE rr.id IN (
        SELECT MAX(id)
        FROM refunds
        GROUP BY payment_id
    )
    ORDER BY rr.requested_at DESC
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Refund Requests</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            background: black;
            color: white;
            font-family: 'Poppins', sans-serif;
        }
        .container {
            margin-top: 40px;
            background: rgba(255, 255, 255, 0.05);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0, 255, 255, 0.2);
        }
        .btn-custom {
            background: linear-gradient(to right, #ff416c, #ff4b2b);
            color: white;
            border: none;
            transition: 0.3s;
        }
        .btn-custom:hover {
            box-shadow: 0 0 10px rgba(255, 65, 108, 0.8);
            transform: scale(1.05);
        }
        table {
            color: white;
        }
        .badge {
            font-size: 0.9em;
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="text-center mb-4">Refund Requests</h2>
    <div class="mb-3 text-end">
        <a href="admin_dashboard.php" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back to Dashboard</a>
    </div>
    <?php if ($result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover text-center table-bordered">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Event</th>
                        <th>Date</th>
                        <th>Package</th>
                        <th>Paid</th>
                        <th>Payment Status</th>
                        <th>Reason</th>
                        <th>Requested At</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['event_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['event_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['package_name']); ?></td>
                            <td>₱<?php echo number_format($row['payment_amount'], 2); ?></td>
                            <td>
                                <?php
                                $badge = match ($row['payment_status']) {
                                    'completed' => '<span class="badge bg-success">Completed</span>',
                                    'pending' => '<span class="badge bg-warning text-dark">Pending</span>',
                                    default => '<span class="badge bg-secondary">Other</span>',
                                };
                                echo $badge;
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['refund_reason']); ?></td>
                            <td><?php echo htmlspecialchars($row['requested_at']); ?></td>
                            <td>
                                <?php
                                $status = $row['refund_status'];
                                echo match ($status) {
                                    'pending' => '<span class="badge bg-warning text-dark">Pending</span>',
                                    'approved' => '<span class="badge bg-success">Approved</span>',
                                    'rejected' => '<span class="badge bg-danger">Rejected</span>',
                                    default => '<span class="badge bg-secondary">Unknown</span>',
                                };
                                ?>
                            </td>
                            <td>
                                <?php if ($row['refund_status'] === 'pending'): ?>
                                    <a href="proc_refunds.php?id=<?php echo $row['refund_id']; ?>&action=approve" class="btn btn-success btn-sm mb-1">
                                        <i class="fa fa-check"></i> Approve
                                    </a>
                                    <a href="proc_refunds.php?id=<?php echo $row['refund_id']; ?>&action=reject" class="btn btn-warning btn-sm" onclick="return confirm('Reject this refund request?');">
                                        <i class="fa fa-times"></i> Reject
                                    </a>
                                <?php else: ?>
                                    <em>No Action</em>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-center text-muted">No refund requests found.</p>
    <?php endif; ?>
</div>
</body>
</html>
