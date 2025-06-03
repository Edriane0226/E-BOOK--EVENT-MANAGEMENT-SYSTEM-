<?php
include '../includes/config.php';

// Authenticate the user using JWT
$user_data = authenticateUser();

if (!$user_data) {
    header("Location: login.php");
    exit();
}

$user_id = $user_data['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_type = $_POST['event_type'];
    $event_date = $_POST['event_date'];
    $guests = $_POST['guests'];
    $package_id = $_POST['package'];
    $message = $_POST['message'];

    $package_query = $conn->prepare("SELECT price FROM packages WHERE id = ?");
    $package_query->bind_param("i", $package_id);
    $package_query->execute();
    $package_result = $package_query->get_result();
    $package_data = $package_result->fetch_assoc();
    $payment_amount = $package_data['price'];

    $stmt = $conn->prepare("INSERT INTO bookings (user_id, event_type, event_date, guests, package_id, message, payment_amount) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issiisd", $user_id, $event_type, $event_date, $guests, $package_id, $message, $payment_amount);

    if ($stmt->execute()) {
        echo "<script>alert('Booking successful!'); window.location='dashboard.php';</script>";
        exit();
    } else {
        echo "<script>alert('Booking failed!');</script>";
    }
}
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking</title>
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
        <h1 class="display-4 fw-bold">Book an Event</h1>
        <p class="lead">Reserve your event with ease</p>
    </div>
</section>

<section class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 fade-in">
            <div class="booking-container">
                <h3 class="text-center fw-bold text-primary mb-3">Book Your Event</h3>

                <form action="" method="post">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Event Type</label>
                        <select name="event_type" class="form-control" required>
                            <option value="Wedding">Wedding</option>
                            <option value="Birthday">Birthday</option>
                            <option value="Conference">Conference</option>
                            <option value="Debut">Debut</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Event Date</label>
                        <input type="date" name="event_date" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Number of Guests</label>
                        <input type="number" name="guests" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Package</label>
                        <select name="package" class="form-control" required>
                            <?php
                            $result = $conn->query("SELECT id, name, price FROM packages");
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['id']}'>{$row['name']} - ₱{$row['price']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Additional Message</label>
                        <textarea name="message" class="form-control"></textarea>
                    </div>

                    <button type="submit" class="btn btn-custom w-100 text-white fw-bold">Submit Booking</button>
                </form>
            </div>
        </div>
    </div>
</section>
</body>
</html>