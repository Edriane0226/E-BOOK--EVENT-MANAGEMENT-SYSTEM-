<?php
// admin edit requests
session_start();
include '../includes/config.php';
include '../includes/header.php';

// Approve request
if (isset($_GET['approve'])) {
    $req_id = $_GET['approve'];

    // Fetch the edit request
    $stmt = $conn->prepare("SELECT * FROM edit_requests WHERE id = ? AND status = 'pending'");
    $stmt->bind_param("i", $req_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $request = $result->fetch_assoc();

    if ($request) {
        // Fetch original booking
        $stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ?");
        $stmt->bind_param("i", $request['booking_id']);
        $stmt->execute();
        $booking_result = $stmt->get_result();
        $booking = $booking_result->fetch_assoc();

        if ($booking) {
            // BEGIN TRANSACTION
            $conn->begin_transaction();

            try {
                // Update edit request with original values
                $stmt = $conn->prepare("
                    UPDATE edit_requests 
                    SET status = 'approved', 
                        original_event_date = ?, 
                        original_guests = ?, 
                        original_package = ?, 
                        original_message = ?
                    WHERE id = ?
                ");
                $stmt->bind_param("sissi",
                    $booking['event_date'],
                    $booking['guests'],
                    $booking['package'],
                    $booking['message'],
                    $req_id
                );
                $stmt->execute();

                // Update bookings table
                $stmt = $conn->prepare("
                    UPDATE bookings 
                    SET event_type = ?, event_date = ?, guests = ?, package = ?, message = ?
                    WHERE id = ?
                ");
                $stmt->bind_param("ssissi",
                    $request['event_type'],
                    $request['event_date'],
                    $request['guests'],
                    $request['package'],
                    $request['message'],
                    $request['booking_id']
                );
                $stmt->execute();

                // COMMIT the transaction
                $conn->commit();

                echo "<script>alert('Request approved and booking updated.'); window.location='admin_edit_request.php';</script>";
                exit;

            } catch (Exception $e) {
                // ROLLBACK if something fails
                $conn->rollback();
                echo "<script>alert('Transaction failed: " . $conn->error . "');</script>";
            }
        }
    }
}

// Reject request
if (isset($_GET['reject'])) {
    $req_id = $_GET['reject'];
    $stmt = $conn->prepare("UPDATE edit_requests SET status = 'rejected' WHERE id = ?");
    $stmt->bind_param("i", $req_id);
    $stmt->execute();

    echo "<script>alert('Request rejected.'); window.location='admin_edit_request.php';</script>";
    exit;
}

// Fetch all requests with current booking data
$query = "
    SELECT 
        r.*, 
        b.event_date AS current_event_date 
    FROM 
        edit_requests r 
    JOIN 
        bookings b ON r.booking_id = b.id 
    ORDER BY 
        r.created_at DESC
";
$requests = $conn->query($query);
?>

<h2 class="text-center my-4">Edit Requests</h2>
<div class="container">
    <table class="table table-dark table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Booking ID</th>
                <th>Old Date</th>
                <th>New Date</th>
                <th>Old Guests</th>
                <th>New Guests</th>
                <th>Old Package</th>
                <th>New Package</th>
                <th>Status</th>
                <th>Requested At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $requests->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['booking_id'] ?></td>
                    <td><?= $row['original_event_date'] ?? $row['current_event_date'] ?></td>
                    <td><?= $row['event_date'] ?></td>
                    <td><?= $row['original_guests'] ?? 'N/A' ?></td>
                    <td><?= $row['guests'] ?></td>
                    <td><?= $row['original_package'] ?? 'N/A' ?></td>
                    <td><?= $row['package'] ?></td>
                    <td><?= ucfirst($row['status']) ?></td>
                    <td><?= $row['requested_at'] ?></td>
                    <td>
                        <?php if ($row['status'] === 'pending'): ?>
                            <a href="?approve=<?= $row['id'] ?>" class="btn btn-success btn-sm">Approve</a>
                            <a href="?reject=<?= $row['id'] ?>" class="btn btn-danger btn-sm">Reject</a>
                        <?php else: ?>
                            <em>No action</em>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
