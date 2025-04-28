<?php
session_start();
include '../includes/config.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$id = $_GET['id'];
$query = "SELECT * FROM bookings WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_type = $_POST['event_type'];
    $event_date = $_POST['event_date'];
    $guests = $_POST['guests'];
    $package = $_POST['package'];
    $message = $_POST['message'];

    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("INSERT INTO edit_requests (booking_id, user_id, event_type, event_date, guests, package, message) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iississ", $id, $user_id, $event_type, $event_date, $guests, $package, $message);

    if ($stmt->execute()) {
        echo "<script>alert('Edit request sent. Awaiting admin approval.'); window.location='dashboard.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error submitting request. Try again.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Booking</title>

    <style>
    body {
        background-color: #121212;
        color: #f8f9fa;
        font-family: 'Poppins', sans-serif;
    }

    .booking-container {
        max-width: 500px;
        margin: auto;
        background: #1c1c1c;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease-in-out;
    }

    .booking-container:hover {
        transform: scale(1.02);
        box-shadow: 0 6px 15px rgba(255, 255, 255, 0.2);
    }

    .form-control {
        background: #222;
        border: 1px solid #ff4b2b;
        color: #fff;
        padding: 12px;
        border-radius: 8px;
        transition: 0.3s;
    }

    .form-control:focus {
        border-color: #ff416c;
        box-shadow: 0 0 10px rgba(255, 65, 108, 0.5);
    }

    .btn-custom {
        background: linear-gradient(45deg, #ff416c, #ff4b2b);
        border: none;
        padding: 12px;
        font-size: 16px;
        border-radius: 8px;
        width: 100%;
        transition: 0.3s;
    }

    .btn-custom:hover {
        background: linear-gradient(45deg, #2a5298, #1e3c72);
        transform: scale(1.05);
    }

    .page-header {
        background: linear-gradient(to right, #ff416c, #ff4b2b);
        padding: 60px 0;
        text-align: center;
    }

    .page-header h1 {
        font-size: 3rem;
        font-weight: bold;
    }
</style>
</head>
<body>
<section class="page-header text-white">
    <div class="container">
        <h1 class="display-4 fw-bold">Edit Booking</h1>
        <p class="lead">Modify your event details</p>
    </div>
</section>

<section class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="booking-container">
                <h3 class="text-center fw-bold text-primary mb-3">Update Your Event</h3>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Event Type</label>
                        <select name="event_type" class="form-control" required>
                            <option value="Wedding" <?= $booking['event_type'] === 'Wedding' ? 'selected' : '' ?>>Wedding</option>
                            <option value="Birthday" <?= $booking['event_type'] === 'Birthday' ? 'selected' : '' ?>>Birthday</option>
                            <option value="Conference" <?= $booking['event_type'] === 'Conference' ? 'selected' : '' ?>>Conference</option>
                            <option value="Debut" <?= $booking['event_type'] === 'Debut' ? 'selected' : '' ?>>Debut</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Event Date</label>
                        <input type="date" name="event_date" class="form-control" value="<?= htmlspecialchars($booking['event_date']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Number of Guests</label>
                        <input type="number" name="guests" class="form-control" value="<?= htmlspecialchars($booking['guests']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Package</label>
                        <select name="package" class="form-control" required>
                            <option value="Basic" <?= $booking['package'] === 'Basic' ? 'selected' : '' ?>>Basic</option>
                            <option value="Standard" <?= $booking['package'] === 'Standard' ? 'selected' : '' ?>>Standard</option>
                            <option value="Full Setup" <?= $booking['package'] === 'Full Setup' ? 'selected' : '' ?>>Full Setup</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Additional Message</label>
                        <textarea name="message" class="form-control"><?= htmlspecialchars($booking['message']); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-custom text-white fw-bold">Update Booking</button>
                </form>
            </div>
        </div>
    </div>
</section>
</body>
</html>
