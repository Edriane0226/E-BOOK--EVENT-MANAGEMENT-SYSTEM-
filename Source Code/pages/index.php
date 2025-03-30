<?php
session_start();

$isLoggedIn = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
?>

<?php include '../includes/header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>

    <style>
    body {
        background-color: #121212;
        color: #f8f9fa;
        font-family: 'Poppins', sans-serif;
    }

    .hero {
        background: linear-gradient(to right, #ff416c, #ff4b2b);
        color: #fff;
        padding: 60px 0;
        text-align: center;
    }

    .service-card {
        background: #1c1c1c;
        color: #fff;
        border-radius: 12px;
        padding: 30px;
        text-align: center;
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    }

    .service-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 8px 16px rgba(255, 255, 255, 0.1);
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

    .cta {
        background: #ff416c;
        color: #fff;
        padding: 60px 0;
        text-align: center;
    }
</style>
</head>
<body>
<section class="hero">
    <div class="container">
        <h2>Welcome to Ebook Planner</h2>
        <p class="lead">Your ultimate event planning solution for weddings, birthday parties, corporate events, and more.</p>
        
        <?php if ($isLoggedIn): ?>
            <a href="manage_bookings.php" class="btn btn-light btn-lg mt-3">Manage Bookings</a>
        <?php else: ?>
            <a href="login.php" class="btn btn-light btn-lg mt-3">Book Now</a>
        <?php endif; ?>
    </div>
</section>

<section class="container my-5">
    <h2 class="text-center mb-4">Featured Events</h2>
    <div class="row text-center">
        <div class="col-md-4 fade-in">
            <div class="service-card">
                <i class="bi bi-heart-fill text-danger display-4 mb-3"></i>
                <h4>Weddings</h4>
                <p>Make your wedding day unforgettable with our expert planning services.</p>
                <a href="services.php#weddings" class="btn btn-outline-danger mt-2">Learn More</a>
            </div>
        </div>
        <div class="col-md-4 fade-in">
            <div class="service-card">
                <i class="bi bi-gift-fill text-success display-4 mb-3"></i>
                <h4>Birthday Parties</h4>
                <p>Celebrate in style with customized themes and fun-filled activities.</p>
                <a href="services.php#birthday" class="btn btn-outline-success mt-2">Learn More</a>
            </div>
        </div>
        <div class="col-md-4 fade-in">
            <div class="service-card">
                <i class="bi bi-briefcase-fill text-primary display-4 mb-3"></i>
                <h4>Corporate Events</h4>
                <p>Professional management for your business needs, ensuring success.</p>
                <a href="services.php#corporate" class="btn btn-outline-primary mt-2">Learn More</a>
            </div>
        </div>
    </div>
</section>

<section class="cta">
    <div class="container">
        <h3>Let Us Plan Your Next Event</h3>
        <p class="lead">Explore our services and book an event planner today for a seamless experience!</p>
        <?php if ($isLoggedIn): ?>
            <a href="manage_bookings.php" class="btn btn-light btn-lg mt-3">Manage Bookings</a>
        <?php else: ?>
            <a href="login.php" class="btn btn-light btn-lg mt-3">Book Now</a>
        <?php endif; ?>
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

