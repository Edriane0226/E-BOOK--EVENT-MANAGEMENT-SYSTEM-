<?php
include '../includes/config.php';

// Check if user is logged in (but don't redirect)
$user_data = authenticateUser();
$is_logged_in = ($user_data !== false);

// Include header for all users
include '../includes/header.php';
?>

<style>
    body {
        background-color: #121212;
        color: #f8f9fa;
        font-family: 'Poppins', sans-serif;
    }

    .page-header {
        background: linear-gradient(to right, #ff416c, #ff4b2b);
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

    .login-prompt {
        background: #2a2a2a;
        border-radius: 10px;
        padding: 20px;
        margin: 20px 0;
        text-align: center;
        border-left: 4px solid #ff416c;
    }

    .login-prompt h5 {
        color: #ff416c;
        margin-bottom: 10px;
    }

    .btn-custom {
        background: linear-gradient(to right, #ff416c, #ff4b2b);
        border: none;
        color: white;
        padding: 10px 25px;
        border-radius: 25px;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s;
        margin: 0 5px;
    }

    .btn-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 65, 108, 0.4);
        color: white;
        text-decoration: none;
    }

    .btn-book {
        background: linear-gradient(to right, #28a745, #20c997);
        border: none;
        color: white;
        padding: 8px 20px;
        border-radius: 25px;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s;
    }

    .btn-book:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        color: white;
        text-decoration: none;
    }
</style>

<section class="page-header text-white">
    <div class="container">
        <h1 class="display-4 fw-bold">Our Services</h1>
        <p class="lead">Discover our premium event planning solutions.</p>
        
        <?php if (!$is_logged_in): ?>
        <!-- Login prompt for non-authenticated users -->
        <div class="login-prompt">
            <h5><i class="bi bi-info-circle me-2"></i>Ready to Book?</h5>
            <p class="mb-3">Create an account or login to start booking our amazing services!</p>
            <a href="login.php" class="btn-custom">
                <i class="bi bi-box-arrow-in-right me-2"></i>Login
            </a>
            <a href="register.php" class="btn-custom">
                <i class="bi bi-person-plus me-2"></i>Sign Up
            </a>
        </div>
        <?php else: ?>
        <!-- Welcome message for logged-in users -->
        <div class="login-prompt">
            <h5><i class="bi bi-person-check-fill me-2"></i>Welcome back, <?php echo htmlspecialchars($user_data['name']); ?>!</h5>
            <p class="mb-3">Ready to book your next amazing event?</p>
            <a href="booking.php" class="btn-book">
                <i class="bi bi-calendar-plus me-2"></i>Book Now
            </a>
            <a href="dashboard.php" class="btn-custom">
                <i class="bi bi-speedometer2 me-2"></i>Go to Dashboard
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="container my-5">
    <div class="row text-center">
        <div class="col-md-4 fade-in">
            <div class="service-card">
                <i class="bi bi-heart-fill text-danger display-4 mb-3"></i>
                <h4>Wedding Planning</h4>
                <p>Make your wedding day truly magical with our expert event planning services.</p>
                <?php if ($is_logged_in): ?>
                    <a href="booking.php" class="btn-book mt-2">Book Now</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-danger mt-2">Login to Book</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-4 fade-in">
            <div class="service-card">
                <i class="bi bi-gift-fill text-success display-4 mb-3"></i>
                <h4>Birthday Parties</h4>
                <p>Celebrate your special day with a perfectly curated birthday event.</p>
                <?php if ($is_logged_in): ?>
                    <a href="booking.php" class="btn-book mt-2">Book Now</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-success mt-2">Login to Book</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-4 fade-in">
            <div class="service-card">
                <i class="bi bi-briefcase-fill text-primary display-4 mb-3"></i>
                <h4>Corporate Events</h4>
                <p>Professional event management tailored to your business needs.</p>
                <?php if ($is_logged_in): ?>
                    <a href="booking.php" class="btn-book mt-2">Book Now</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-primary mt-2">Login to Book</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="container my-5">
    <h2 class="text-center mb-4 fw-bold">Additional Services</h2>
    <div class="row">
        <div class="col-md-6 fade-in">
            <div class="service-card">
                <i class="bi bi-music-note-beamed text-warning display-4 mb-3"></i>
                <h4>Live Entertainment</h4>
                <p>Enhance your event with live music, DJs, and performers.</p>
                <?php if ($is_logged_in): ?>
                    <a href="booking.php" class="btn-book mt-2">Book Now</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-warning mt-2">Login to Book</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-6 fade-in">
            <div class="service-card">
                <i class="bi bi-camera-reels-fill text-info display-4 mb-3"></i>
                <h4>Photography & Videography</h4>
                <p>Capture every moment with our professional photography and video services.</p>
                <?php if ($is_logged_in): ?>
                    <a href="booking.php" class="btn-book mt-2">Book Now</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-info mt-2">Login to Book</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php if (!$is_logged_in): ?>
<section class="container my-5 text-center">
    <div class="login-prompt">
        <h3><i class="bi bi-star-fill text-warning me-2"></i>Join Our Community</h3>
        <p class="lead">Don't have an account yet? Sign up now and get access to exclusive offers!</p>
        <a href="register.php" class="btn-custom btn-lg">
            <i class="bi bi-person-plus me-2"></i>Create Account Now
        </a>
    </div>
</section>
<?php endif; ?>

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