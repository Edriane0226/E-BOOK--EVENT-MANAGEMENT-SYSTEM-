<?php
session_start();
include '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

$query = "
    SELECT payments.*, users.name 
    FROM payments 
    JOIN bookings ON payments.booking_id = bookings.id
    JOIN users ON bookings.user_id = users.id
    WHERE booking_id = ?
    ORDER BY payments.payment_date DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Payment History</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-dark text-white">
    <div class="container mt-5">
        <h2 class="mb-4">Payment History for Booking #<?php echo $booking_id; ?></h2>
        <a href="admin_dashboard.php" class="btn btn-secondary mb-3">← Back to Dashboard</a>
        <table class="table table-bordered table-hover table-dark">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Amount (₱)</th>
                    <th>Status</th>
                    <th>Payment Method</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): 
                    while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo number_format($row['payment_amount'], 2); ?></td>
                            <td><?php echo ucfirst($row['status']); ?></td>
                            <td><?php echo htmlspecialchars($row['payment_type'] ?? 'N/A'); ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($row['payment_date'])); ?></td>
                        </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="5" class="text-center">No payment history found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
