<?php
session_start();
include '../includes/config.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $event_type = $_POST['event_type'];
    $event_date = $_POST['event_date'];
    $guests = $_POST['guests'];
    $package = $_POST['package'];
    $message = $_POST['message'];

    $stmt = $conn->prepare("INSERT INTO bookings (user_id, event_type, event_date, guests, package, message) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ississ", $user_id, $event_type, $event_date, $guests, $package, $message);

    if ($stmt->execute()) {
        echo "<script>alert('Booking successful!'); window.location='dashboard.php';</script>";
    } else {
        echo "<script>alert('Booking failed!');</script>";
    }
}
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
                            <option value="Basic">Basic</option>
                            <option value="Standard">Standard</option>
                            <option value="Full Setup">Full Setup</option>
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
