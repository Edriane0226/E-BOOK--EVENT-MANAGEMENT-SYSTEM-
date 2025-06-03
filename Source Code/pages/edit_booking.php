<?php
session_start();
include '../includes/config.php';

// Authenticate user using JWT
$user_data = authenticateUser();

if (!$user_data || !isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$user_id = $user_data['id'];
$id = $_GET['id'];

// Verify that this booking belongs to the authenticated user
$query = "SELECT * FROM bookings WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_type = $_POST['event_type'];
    $event_date = $_POST['event_date'];
    $guests = $_POST['guests'];
    $package_id = $_POST['package'];
    $message = $_POST['message'] ?? '';

    // Assign original values to variables FIRST
    $original_event_type = $booking['event_type'];
    $original_event_date = $booking['event_date'];
    $original_guests = $booking['guests'];
    $original_message = isset($booking['message']) ? $booking['message'] : '';
    $original_package_id = $booking['package_id'];

    $stmt = $conn->prepare("
        INSERT INTO edit_requests 
        (booking_id, user_id, event_type, event_date, guests, package_id, message,
         original_event_type, original_event_date, original_guests, original_message, original_package_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    // Use the separate variables here
    $stmt->bind_param(
        "iississssssi",
        $id,
        $user_id,
        $event_type,
        $event_date,
        $guests,
        $package_id,
        $message,
        $original_event_type,
        $original_event_date,
        $original_guests,
        $original_message,
        $original_package_id
    );

    if ($stmt->execute()) {
        echo "<script>alert('Edit request sent. Awaiting admin approval.'); window.location='dashboard.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error submitting request. Try again.');</script>";
    }
}

// Fetch packages for dropdown
$packages_result = $conn->query("SELECT id, name, price FROM packages");

// Include header AFTER all authentication and form processing
include '../includes/header.php';
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

    .fade-in {
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.6s ease-in-out;
    }

    .fade-in.visible {
        opacity: 1;
        transform: translateY(0);
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
        <div class="col-md-6 fade-in">
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
                            <?php while ($row = $packages_result->fetch_assoc()): ?>
                                <option value="<?= $row['id'] ?>" <?= ($row['id'] == $booking['package_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['name']) ?> - ₱<?= $row['price'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Additional Message</label>
                        <textarea name="message" class="form-control"><?= htmlspecialchars($booking['message']); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-custom text-white fw-bold">Update Booking</button>
                    <a href="dashboard.php" class="btn btn-secondary w-100 mt-2">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</section>

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
