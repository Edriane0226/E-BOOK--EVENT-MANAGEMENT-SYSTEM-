<?php
include '../includes/config.php';

// Check if user is logged in - if yes, redirect to dashboard
$user_data = authenticateUser();

if ($user_data) {
    // User is logged in, redirect to dashboard
    header("Location: dashboard.php");
    exit();
}

// Only non-logged-in users can access this page
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

    .pricing-card {
        background: #1c1c1c;
        color: #fff;
        border-radius: 12px;
        padding: 40px;
        text-align: center;
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        position: relative;
        overflow: hidden;
    }
    .pricing-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 8px 16px rgba(255, 255, 255, 0.1);
    }
    .pricing-card .price {
        font-size: 2rem;
        font-weight: bold;
        color: #ff4b2b;
    }

    .btn-glow {
        background: linear-gradient(to right, #ff416c, #ff4b2b);
        color: #fff;
        padding: 12px 20px;
        border-radius: 6px;
        transition: 0.3s ease-in-out;
        text-decoration: none;
        display: inline-block;
    }
    .btn-glow:hover {
        box-shadow: 0 0 15px rgba(255, 75, 43, 0.8);
        transform: scale(1.05);
        color: #fff;
        text-decoration: none;
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
</style>

<section class="page-header text-white">
    <div class="container">
        <h1 class="display-4 fw-bold">Event Pricing</h1>
        <p class="lead">Discover our affordable event packages and start planning your perfect event!</p>
        
        <!-- Login prompt for non-authenticated users -->
        <div class="login-prompt">
            <h5><i class="bi bi-info-circle me-2"></i>Ready to Book?</h5>
            <p class="mb-3">Create an account or login to start booking our amazing packages!</p>
            <a href="login.php" class="btn-custom">
                <i class="bi bi-box-arrow-in-right me-2"></i>Login
            </a>
            <a href="register.php" class="btn-custom">
                <i class="bi bi-person-plus me-2"></i>Sign Up
            </a>
        </div>
    </div>
</section>

<section class="container my-5 text-center">
    <h2 class="fw-bold">Our Event Packages</h2>
    <p class="lead">Whether it's a birthday, wedding, conference, or any special event, we offer flexible event packages tailored to meet your needs. Browse through our packages and find the perfect match for your event.</p>
    
    <div class="row">
        <div class="col-md-3">
            <div class="pricing-card">
                <h3>Birthday Parties</h3>
                <p>Make your birthday an unforgettable celebration! From intimate gatherings to large-scale parties, we provide personalized event planning to make your special day extraordinary.</p>
                <ul class="list-unstyled">
                    <li>🎉 Customized Party Setup</li>
                    <li>🎈 Themed Decorations (Choose your preferred theme)</li>
                    <li>🎤 MC & Host Services</li>
                    <li>🍰 Catering Options (Buffet, Plated Meals, Dessert Stations)</li>
                    <li>🎶 Entertainment (DJ, Games, Live Music)</li>
                    <li>📸 Event Photography & Videography</li>
                </ul>
                <a href="login.php" class="btn btn-glow mt-3">Login to Book</a>
            </div>
        </div>

        <div class="col-md-3">
            <div class="pricing-card">
                <h3>Weddings</h3>
                <p>Your wedding day should be as unique and beautiful as your love story. We provide end-to-end wedding planning to make your day flawless and magical.</p>
                <ul class="list-unstyled">
                    <li>💍 Wedding Venue Setup (Indoor & Outdoor Options)</li>
                    <li>💐 Floral Arrangements (Custom Bouquets, Table Centerpieces)</li>
                    <li>🍽️ Catering Options (Plated Meals, Buffet, Custom Menus)</li>
                    <li>🎤 Wedding Ceremony Setup (Sound Systems, Microphones, Stage)</li>
                    <li>🎶 Live Music or DJ (Personalized Playlist)</li>
                    <li>🎥 Professional Photography & Videography</li>
                    <li>💅 Bride & Groom Makeup & Styling</li>
                </ul>
                <a href="login.php" class="btn btn-glow mt-3">Login to Book</a>
            </div>
        </div>

        <div class="col-md-3">
            <div class="pricing-card">
                <h3>Conferences</h3>
                <p>Whether it's a small seminar or a large corporate conference, we ensure every detail is handled professionally, allowing you to focus on your presentation.</p>
                <ul class="list-unstyled">
                    <li>🎤 Conference Setup (Podium, Sound Systems, Microphones)</li>
                    <li>💼 Corporate Venue (Comfortable Seating, Stage Setup)</li>
                    <li>🍽️ Catering Services (Coffee Breaks, Buffets, Full Meals)</li>
                    <li>🎥 Audio-Visual Equipment (Projectors, Screen Setup)</li>
                    <li>🎶 Background Music and Presentations</li>
                    <li>📱 Event App for Attendees (Schedules, Maps, Networking)</li>
                    <li>🎥 Live Streaming & Recording for Remote Attendees</li>
                </ul>
                <a href="login.php" class="btn btn-glow mt-3">Login to Book</a>
            </div>
        </div>

        <div class="col-md-3">
            <div class="pricing-card">
                <h3>Parties & Events</h3>
                <p>Get the party started! Whether it's a casual gathering or a high-energy celebration, we provide all the elements for an unforgettable experience.</p>
                <ul class="list-unstyled">
                    <li>🎉 Party Setup (Themed or Custom Decorations)</li>
                    <li>🍸 Drink Stations (Bar, Cocktail Options, Mocktails)</li>
                    <li>🎶 Entertainment (Live Music, DJ, Dance Floor)</li>
                    <li>🎤 Host & MC Services</li>
                    <li>📸 Event Photography & Videography</li>
                    <li>🍰 Dessert Stations (Custom Cakes, Pastries, Sweets)</li>
                    <li>💃 Dance Floor Lighting & Special Effects</li>
                </ul>
                <a href="login.php" class="btn btn-glow mt-3">Login to Book</a>
            </div>
        </div>
    </div>
</section>

<section class="container my-5">
    <div class="row text-center">
        <div class="col-md-4 fade-in">
            <div class="pricing-card">
                <h3>Basic Package</h3>
                <p class="price">₱27,944</p>
                <ul class="list-unstyled">
                    <li>🎉 Basic Event Setup</li>
                    <li>🍽️ Simple Catering (Buffet or Plated)</li>
                    <li>🎈 Simple Decorations (Balloons, Streamers)</li>
                    <li>🎤 Basic Entertainment (DJ or Playlist)</li>
                </ul>
                <a href="login.php" class="btn btn-glow mt-3">Login to Book</a>
            </div>
        </div>

        <div class="col-md-4 fade-in">
            <div class="pricing-card">
                <h3>Standard Package</h3>
                <p class="price">₱44,744</p>
                <ul class="list-unstyled">
                    <li>🎉 Full Event Setup</li>
                    <li>🍽️ Full Catering (Buffet or Plated with Options)</li>
                    <li>🎈 Premium Decorations (Themed, Customizable)</li>
                    <li>🎤 Professional Entertainment (DJ or Live Music)</li>
                    <li>📸 Event Photography & Videography</li>
                </ul>
                <a href="login.php" class="btn btn-glow mt-3">Login to Book</a>
            </div>
        </div>

        <div class="col-md-4 fade-in">
            <div class="pricing-card">
                <h3>Premium Package</h3>
                <p class="price">₱72,644</p>
                <ul class="list-unstyled">
                    <li>🎉 Luxury Event Setup (Customized, High-End Decorations)</li>
                    <li>🍽️ Custom Catering (Gourmet Options, Full Buffet)</li>
                    <li>🎈 Luxury Themed Decorations (Floral, Lighting, Drapery)</li>
                    <li>🎤 Premium Entertainment (Live Band, DJ, Special Performances)</li>
                    <li>📸 Professional Event Photography & Videography</li>
                    <li>🎥 Live Streaming for Remote Attendees</li>
                    <li>🎤 MC Services & On-site Event Coordination</li>
                </ul>
                <a href="login.php" class="btn btn-glow mt-3">Login to Book</a>
            </div>
        </div>
    </div>
</section>

<section class="container my-5 text-center">
    <h2 class="fw-bold">Custom Event Packages</h2>
    <p class="lead">We understand that every event is unique. If none of the packages listed above fit your needs, contact us for a personalized event package tailored just for you.</p>
    
    <div class="login-prompt">
        <h5><i class="bi bi-envelope-heart me-2"></i>Need a Custom Package?</h5>
        <p class="mb-3">Login or register to discuss your custom event requirements with our team!</p>
        <a href="login.php" class="btn-custom">
            <i class="bi bi-box-arrow-in-right me-2"></i>Login to Contact
        </a>
    </div>
</section>

<section class="container my-5 text-center">
    <div class="login-prompt">
        <h3><i class="bi bi-star-fill text-warning me-2"></i>Start Your Event Journey</h3>
        <p class="lead">Ready to turn your event dreams into reality? Join thousands of satisfied customers!</p>
        <a href="register.php" class="btn-custom btn-lg">
            <i class="bi bi-person-plus me-2"></i>Create Account Now
        </a>
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