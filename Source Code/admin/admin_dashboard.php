<?php
session_start();
include '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// 1. Get latest edit requests per booking
$latest_edit_requests = "
    SELECT e1.*
    FROM edit_requests e1
    INNER JOIN (
        SELECT booking_id, MAX(requested_at) AS latest_request
        FROM edit_requests
        GROUP BY booking_id
    ) e2 ON e1.booking_id = e2.booking_id AND e1.requested_at = e2.latest_request
";

// 2. Get latest refund requests per booking
$latest_refund_requests = "
    SELECT r1.*, p.booking_id
    FROM refunds r1
    JOIN payments p ON r1.payment_id = p.id
    INNER JOIN (
        SELECT payment_id, MAX(requested_at) AS latest_request
        FROM refunds
        GROUP BY payment_id
    ) r2 ON r1.payment_id = r2.payment_id AND r1.requested_at = r2.latest_request
";

// 3. Get total payments per booking
$total_payments = "
    SELECT booking_id, SUM(payment_amount) AS total_paid
    FROM payments
    WHERE status IN ('completed', 'pending')
    GROUP BY booking_id
";

// 4. Main query combining all the subqueries
$query = "
    SELECT 
        bookings.*, 
        users.name, 
        users.email, 
        er.id AS edit_request_id, 
        er.status AS edit_status,
        IFNULL(paid.total_paid, 0) AS total_paid,
        packages.name AS package_name,
        packages.price,
        rr.id AS refund_request_id,
        rr.refund_status,
        rr.reason AS refund_reason
    FROM bookings 
    INNER JOIN users ON bookings.user_id = users.id 
    INNER JOIN packages ON bookings.package_id = packages.id
    LEFT JOIN ($latest_edit_requests) er ON bookings.id = er.booking_id
    LEFT JOIN ($latest_refund_requests) rr ON bookings.id = rr.booking_id
    LEFT JOIN ($total_payments) paid ON bookings.id = paid.booking_id
    WHERE rr.refund_status IS NULL OR rr.refund_status != 'approved'
    ORDER BY bookings.event_date ASC
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            background-color: #121212;
            color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
        }
        
        .dashboard-container {
            max-width: 95%;
            margin: 2rem auto;
            background: #1c1c1c;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease-in-out;
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(to right, #ff416c, #ff4b2b);
            color: white;
            padding: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h2 {
            margin: 0;
            font-size: 2rem;
            font-weight: bold;
        }
        
        .admin-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .admin-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
        }
        
        .content-section {
            padding: 2rem;
        }
        
        .welcome-text {
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            color: #ff416c;
            margin-bottom: 2rem;
        }
        
        .table-container {
            background: #222;
            border: 1px solid #ff4b2b;
            border-radius: 8px;
            overflow: hidden;
            transition: 0.3s;
        }
        
        .table {
            margin-bottom: 0;
            color: #f8f9fa;
        }
        
        .table th {
            background: linear-gradient(45deg, #ff416c, #ff4b2b);
            color: white;
            font-weight: 600;
            border: none;
            padding: 1rem 0.75rem;
        }
        
        .table td {
            padding: 0.75rem;
            border-color: #333;
            vertical-align: middle;
        }
        .btn-danger {
            background: #dc3545;
            border: none;
        }
        
        .btn-success {
            background: #28a745;
            border: none;
        }

        .btn-warning {
            background: #ffc107;
            border: none;
            color: #212529;
        }    
        .btn-info {
            background: #17a2b8;
            border: none;
        }
        .badge {
            font-size: 0.8rem;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .section-header h3 {
            color: #f8f9fa;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="dashboard-container fade-in">
        <div class="header">
            <h2><i class="fa fa-tachometer-alt me-3"></i>Admin Dashboard</h2>
            <div class="admin-info">
                <span class="admin-name">
                    <i class="fa fa-user-shield me-2"></i>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?>
                </span>
                <a href="admin_logout.php" class="btn btn-danger">
                    <i class="fa fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>

        <div class="content-section">
            <h3 class="welcome-text">Manage All Bookings & Requests</h3>
            
            <div class="section-header">
                <h3><i class="fa fa-calendar-check me-2"></i>Booking Management</h3>
                <a href="admin_refunds.php" class="btn btn-info">
                    <i class="fa fa-money-bill-wave me-2"></i>Refunds Only
                </a>
            </div>

            <div class="table-container">
                <div class="table-responsive">
                    <table class="table text-center">
                        <thead>
                            <tr>
                                <th>User Name</th>
                                <th>Email</th>
                                <th>Event Type</th>
                                <th>Event Date</th>
                                <th>Guests</th>
                                <th>Package</th>
                                <th>Message</th>
                                <th>Amount Paid</th>
                                <th>Remaining</th>
                                <th>Payment Status</th>
                                <th>Actions</th>
                                <th>Edit History</th>
                                <th>Payment History</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()):
                                $remaining = ($row['price'] ?? 0) - $row['total_paid'];
                                if ($row['total_paid'] == 0) {
                                    $statusBadge = '<span class="badge bg-danger">Not Paid</span>';
                                } elseif ($row['total_paid'] < ($row['price'] ?? 0)) {
                                    $statusBadge = '<span class="badge bg-warning text-dark">Partially Paid</span>';
                                } else {
                                    $statusBadge = '<span class="badge bg-success">Paid</span>';
                                }
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['event_type']); ?></td>
                                <td><?php echo htmlspecialchars($row['event_date']); ?></td>
                                <td><?php echo htmlspecialchars($row['guests']); ?></td>
                                <td><?php echo htmlspecialchars($row['package_name'] ?? 'N/A'); ?></td>
                                <td style="max-width: 150px;"><?php echo htmlspecialchars($row['message']); ?></td>
                                <td>₱<?php echo number_format($row['total_paid'], 2); ?></td>
                                <td>₱<?php echo number_format($remaining, 2); ?></td>
                                <td><?php echo $statusBadge; ?></td>
                                <td>
                                    <?php if ($row['edit_request_id']): ?>
                                        <?php if ($row['edit_status'] === 'pending'): ?>
                                            <a href="confirm_edit.php?id=<?php echo urlencode($row['edit_request_id']); ?>&booking_id=<?php echo urlencode($row['id']); ?>" class="btn btn-success btn-sm mb-1">
                                                <i class="fa fa-check"></i> Confirm Edit
                                            </a>
                                            <a href="reject_edit.php?id=<?php echo urlencode($row['edit_request_id']); ?>" class="btn btn-warning btn-sm mb-1" onclick="return confirm('Reject this edit request?');">
                                                <i class="fa fa-times"></i> Reject
                                            </a>
                                        <?php elseif ($row['edit_status'] === 'approved'): ?>
                                            <span class="text-success">Edit Approved</span>
                                        <?php elseif ($row['edit_status'] === 'rejected'): ?>
                                            <span class="text-danger">Edit Rejected</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-success">No edit request</span>
                                    <?php endif; ?>
                                    <br>
                                    <a href="delete_booking.php?id=<?php echo urlencode($row['id']); ?>" class="btn btn-danger btn-sm mt-1" onclick="return confirm('Are you sure you want to delete this booking?');">
                                        <i class="fa fa-trash"></i> Delete
                                    </a>
                                </td>
                                <td>
                                    <a href="admin_history.php?booking_id=<?php echo urlencode($row['id']); ?>" class="btn btn-info btn-sm">
                                        <i class="fa fa-clock-rotate-left"></i> View History
                                    </a>
                                </td>
                                <td>
                                    <a href="admin_payments.php?booking_id=<?php echo urlencode($row['id']); ?>" class="btn btn-primary btn-sm">
                                        <i class="fa fa-receipt"></i> View Payments
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const fadeInElements = document.querySelectorAll('.fade-in');

            function revealOnScroll() {
                fadeInElements.forEach(el => {
                    if (el.getBoundingClientRect().top < window.innerHeight - 50) {
                        el.classList.add('visible');
                    }
                });
            }

            window.addEventListener("scroll", revealOnScroll);
            revealOnScroll();
        });
    </script>
</body>
</html>