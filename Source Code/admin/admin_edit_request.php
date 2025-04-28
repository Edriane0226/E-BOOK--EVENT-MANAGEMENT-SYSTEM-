<?php
session_start();
include '../includes/config.php';
include '../includes/header.php';

if (isset($_GET['approve'])) {
    $req_id = $_GET['approve'];

    $stmt = $conn->prepare("SELECT * FROM edit_requests WHERE id=? AND status='Pending'");
    $stmt->bind_param("i", $req_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $request = $result->fetch_assoc();

    if ($request) {
        $stmt = $conn->prepare("UPDATE bookings SET event_type=?, event_date=?, guests=?, package=?, message=? WHERE id=?");
        $stmt->bind_param("ssissi", $request['event_type'], $request['event_date'], $request['guests'], $request['package'], $request['message'], $request['booking_id']);
        $stmt->execute();

        $conn->query("UPDATE edit_requests SET status='Approved' WHERE id=$req_id");
        echo "<script>alert('Request approved and booking updated.'); window.location='admin_edit_requests.php';</script>";
    }
}

if (isset($_GET['reject'])) {
    $req_id = $_GET['reject'];
    $conn->query("UPDATE edit_requests SET status='Rejected' WHERE id=$req_id");
    echo "<script>alert('Request rejected.'); window.location='admin_edit_requests.php';</script>";
}

$requests = $conn->query("SELECT r.*, b.event_date AS current_date FROM edit_requests r JOIN bookings b ON r.booking_id = b.id ORDER BY r.created_at DESC");
?>

<h2 class="text-center my-4">Edit Requests</h2>
<div class="container">
    <table class="table table-dark table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Booking ID</th>
                <th>New Date</th>
                <th>Guests</th>
                <th>Package</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $requests->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['booking_id'] ?></td>
                    <td><?= $row['event_date'] ?></td>
                    <td><?= $row['guests'] ?></td>
                    <td><?= $row['package'] ?></td>
                    <td><?= $row['status'] ?></td>
                    <td>
                        <?php if ($row['status'] === 'Pending'): ?>
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