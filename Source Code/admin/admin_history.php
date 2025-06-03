<?php
session_start();
include '../includes/config.php';

// Check for booking ID in query
if (!isset($_GET['booking_id']) || !is_numeric($_GET['booking_id'])) {
    echo "<p style='color: red; text-align: center;'>Invalid or missing booking ID.</p>";
    exit();
}

$booking_id = (int) $_GET['booking_id'];

// Fetch edit history for the specific booking with joined package names
$stmt = $conn->prepare("
    SELECT 
        r.*, 
        u.name AS user_name,
        p1.name AS new_package_name,
        p2.name AS old_package_name
    FROM 
        edit_requests r
    JOIN 
        users u ON r.user_id = u.id
    LEFT JOIN 
        packages p1 ON r.package_id = p1.id
    LEFT JOIN 
        packages p2 ON r.original_package_id = p2.id
    WHERE 
        r.booking_id = ? AND r.status IN ('approved', 'rejected')
    ORDER BY 
        r.requested_at DESC
");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Request History</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background: #0f0f0f;
            font-family: 'Poppins', sans-serif;
            color: #f8f9fa;
        }

        .container {
            margin-top: 50px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 234, 255, 0.2);
        }

        h2 {
            color: #00eaff;
            font-weight: bold;
        }

        .table {
            color: #fff;
            background-color: transparent;
        }

        .table th {
            background: linear-gradient(to right, #ff416c, #ff4b2b);
            color: white;
        }

        .table-striped > tbody > tr:nth-of-type(odd) {
            background-color: rgba(255, 255, 255, 0.05);
        }

        .table-hover tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .badge-approved {
            background: linear-gradient(to right, #28a745, #218838);
        }

        .badge-rejected {
            background: linear-gradient(to right, #dc3545, #c82333);
        }

        .table td, .table th {
            vertical-align: middle;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center mb-4">Edit Request History (Booking ID: <?= $booking_id ?>)</h2>

    <?php if ($result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped text-center">
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>User</th>
                        <th>Status</th>
                        <th>Original Date</th>
                        <th>New Date</th>
                        <th>Original Guests</th>
                        <th>New Guests</th>
                        <th>Original Package</th>
                        <th>New Package</th>
                        <th>Original Message</th>
                        <th>New Message</th>
                        <th>Requested At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['user_name']) ?></td>
                            <td>
                                <span class="badge <?= $row['status'] === 'approved' ? 'badge-approved' : 'badge-rejected' ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td><?= $row['original_event_date'] ?? 'N/A' ?></td>
                            <td><?= $row['event_date'] ?? 'N/A' ?></td>
                            <td><?= $row['original_guests'] ?? 'N/A' ?></td>
                            <td><?= $row['guests'] ?? 'N/A' ?></td>
                            <td><?= htmlspecialchars($row['old_package_name'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($row['new_package_name'] ?? 'N/A') ?></td>
                            <td><?= !empty($row['original_message']) ? nl2br(htmlspecialchars($row['original_message'])) : 'N/A' ?></td>
                            <td><?= !empty($row['message']) ? nl2br(htmlspecialchars($row['message'])) : 'N/A' ?></td>
                            <td><?= $row['requested_at'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-center text-warning">No edit requests found for this booking.</p>
    <?php endif; ?>
</div>

</body>
</html>
