<?php
include '../includes/config.php';

// Check if user is logged in using JWT
$user_data = authenticateUser();
$isLoggedIn = ($user_data !== false);
$user_name = $isLoggedIn ? $user_data['name'] : '';

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ebook Event Planner - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
    body {
        background-color: #121212 !important;
        color: #f8f9fa !important;
        font-family: 'Poppins', sans-serif !important;
        margin: 0;
        padding: 0; /* Remove padding-top */
    }

    .hero {
        background: linear-gradient(135deg, #ff416c, #ff4b2b);
        color: #fff;
        padding: 80px 0;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.1);
        z-index: 1;
    }

    .hero .container {
        position: relative;
        z-index: 2;
    }

    .hero h2 {
        font-size: 3.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }

    .hero .lead {
        font-size: 1.3rem;
        margin-bottom: 2rem;
        opacity: 0.95;
    }

    .welcome-message {
        background: linear-gradient(45deg, #28a745, #20c997);
        color: white;
        padding: 15px 0;
        text-align: center;
        margin-bottom: 0;
    }

    .service-card {
        background: linear-gradient(145deg, #1c1c1c, #2a2a2a);
        color: #fff;
        border-radius: 15px;
        padding: 40px 30px;
        text-align: center;
        transition: all 0.4s ease-in-out;
        border: 1px solid #333;
        height: 100%;
        position: relative;
        overflow: hidden;
    }

    .service-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
        transition: left 0.5s;
    }

    .service-card:hover::before {
        left: 100%;
    }

    .service-card:hover {
        transform: translateY(-15px) scale(1.02);
        box-shadow: 0 15px 30px rgba(255, 65, 108, 0.3);
        border-color: #ff416c;
    }

    .service-card i {
        margin-bottom: 20px;
        transition: transform 0.3s ease;
    }

    .service-card:hover i {
        transform: scale(1.2);
    }

    .service-card h4 {
        font-weight: 600;
        margin-bottom: 15px;
        color: #ff416c;
    }

    .fade-in {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.8s ease-in-out;
    }

    .fade-in.visible {
        opacity: 1;
        transform: translateY(0);
    }

    .cta {
        background: linear-gradient(135deg, #ff416c, #ff4b2b);
        color: #fff;
        padding: 80px 0;
        text-align: center;
        position: relative;
    }

    .cta::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="white" opacity="0.1"/><circle cx="80" cy="80" r="2" fill="white" opacity="0.1"/><circle cx="40" cy="60" r="1" fill="white" opacity="0.1"/></svg>');
    }

    .cta .container {
        position: relative;
        z-index: 2;
    }

    .btn-custom {
        background: linear-gradient(45deg, #fff, #f8f9fa);
        color: #ff416c;
        border: none;
        padding: 15px 30px;
        font-weight: 600;
        border-radius: 50px;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
    }

    .btn-custom:hover {
        background: linear-gradient(45deg, #f8f9fa, #e9ecef);
        color: #ff416c;
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        text-decoration: none;
    }

    .section-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #ff416c;
        margin-bottom: 3rem;
        position: relative;
    }

    .section-title::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 3px;
        background: linear-gradient(45deg, #ff416c, #ff4b2b);
        border-radius: 2px;
    }

    .services-section {
        padding: 80px 0;
        background: #0f0f0f;
    }

    @media (max-width: 768px) {
        .hero h2 {
            font-size: 2.5rem;
        }
        
        .hero .lead {
            font-size: 1.1rem;
        }
        
        .service-card {
            margin-bottom: 30px;
        }
    }
    </style>
</head>
<body>
    <div class="main-content">
        <?php if ($isLoggedIn): ?>
        <div class="welcome-message">
            <div class="container">
                <h5 class="mb-0">
                    <i class="bi bi-person-circle me-2"></i>
                    Welcome back, <?php echo htmlspecialchars($user_name); ?>! 
                    <a href="dashboard.php" class="text-white ms-3" style="text-decoration: underline;">Go to Dashboard</a>
                </h5>
            </div>
        </div>
        <?php endif; ?>

        <section class="hero">
            <div class="container">
                <h2>Welcome to Ebook Event Planner</h2>
                <p class="lead">Your ultimate event planning solution for weddings, birthday parties, corporate events, and more. Creating unforgettable moments with professional expertise.</p>
                
                <?php if ($isLoggedIn): ?>
                    <a href="dashboard.php" class="btn btn-custom btn-lg mt-3">
                        <i class="bi bi-speedometer2 me-2"></i>View Dashboard
                    </a>
                    <a href="booking.php" class="btn btn-custom btn-lg mt-3 ms-3">
                        <i class="bi bi-plus-circle me-2"></i>Book New Event
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-custom btn-lg mt-3">
                        <i class="bi bi-calendar-event me-2"></i>Start Planning Now
                    </a>
                    <a href="register.php" class="btn btn-custom btn-lg mt-3 ms-3">
                        <i class="bi bi-person-plus me-2"></i>Join Us
                    </a>
                <?php endif; ?>
            </div>
        </section>

        <section class="services-section">
            <div class="container">
                <h2 class="section-title text-center">Featured Events</h2>
                <div class="row g-4">
                    <div class="col-lg-4 col-md-6 fade-in">
                        <div class="service-card">
                            <i class="bi bi-heart-fill text-danger display-3 mb-3"></i>
                            <h4>Weddings</h4>
                            <p>Make your wedding day unforgettable with our expert planning services. From intimate ceremonies to grand celebrations.</p>
                            <a href="services.php#weddings" class="btn btn-outline-danger mt-3">
                                <i class="bi bi-arrow-right me-1"></i>Learn More
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 fade-in">
                        <div class="service-card">
                            <i class="bi bi-gift-fill text-success display-3 mb-3"></i>
                            <h4>Birthday Parties</h4>
                            <p>Celebrate in style with customized themes and fun-filled activities. Creating magical moments for all ages.</p>
                            <a href="services.php#birthday" class="btn btn-outline-success mt-3">
                                <i class="bi bi-arrow-right me-1"></i>Learn More
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 fade-in">
                        <div class="service-card">
                            <i class="bi bi-briefcase-fill text-primary display-3 mb-3"></i>
                            <h4>Corporate Events</h4>
                            <p>Professional management for your business needs, ensuring success. From conferences to team building events.</p>
                            <a href="services.php#corporate" class="btn btn-outline-primary mt-3">
                                <i class="bi bi-arrow-right me-1"></i>Learn More
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="cta">
            <div class="container">
                <h3 class="display-5 fw-bold mb-4">Let Us Plan Your Next Event</h3>
                <p class="lead mb-4">Explore our services and book an event planner today for a seamless experience! Professional planning, exceptional results.</p>
                <?php if ($isLoggedIn): ?>
                    <a href="dashboard.php" class="btn btn-custom btn-lg">
                        <i class="bi bi-speedometer2 me-2"></i>Manage Your Events
                    </a>
                <?php else: ?>
                    <a href="register.php" class="btn btn-custom btn-lg me-3">
                        <i class="bi bi-person-plus me-2"></i>Get Started
                    </a>
                    <a href="login.php" class="btn btn-custom btn-lg">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                    </a>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const fadeInElements = document.querySelectorAll('.fade-in');

            function revealOnScroll() {
                fadeInElements.forEach((el, index) => {
                    if (el.getBoundingClientRect().top < window.innerHeight - 100) {
                        setTimeout(() => {
                            el.classList.add('visible');
                        }, index * 200); // Staggered animation
                    }
                });
            }

            window.addEventListener("scroll", revealOnScroll);
            revealOnScroll();
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>