<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current page for navigation highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Check if user is authenticated via JWT
$user_data = authenticateUser();
$is_logged_in = ($user_data !== false);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navbar</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        .navbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: rgba(20, 20, 20, 0.9);
            backdrop-filter: blur(8px);
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease-in-out;
        }

        .navbar-brand {
            font-size: 1.8rem;
            font-weight: bold;
            letter-spacing: 1px;
            color: #ffffff !important;
            display: flex;
            align-items: center;
            transition: 0.3s ease-in-out;
        }

        .navbar-brand i {
            font-size: 2rem;
            margin-right: 10px;
            color: #ff4b2b;
        }

        .navbar-nav .nav-link {
            font-size: 1.2rem;
            color: white !important;
            margin: 0 15px;
            transition: all 0.3s ease-in-out;
            position: relative;
        }

        .navbar-nav .nav-link::after {
            content: "";
            position: absolute;
            left: 50%;
            bottom: -3px;
            width: 0%;
            height: 2px;
            background: #ff4b2b;
            transition: all 0.3s ease-in-out;
            transform: translateX(-50%);
        }

        .navbar-nav .nav-link:hover::after,
        .navbar-nav .nav-link.active::after {
            width: 100%;
        }

        .navbar-nav .nav-link:hover {
            color: #ff4b2b !important;
            transform: scale(1.1);
        }

        .btn-login, .btn-profile {
            border-radius: 30px;
            padding: 10px 20px;
            font-weight: bold;
            transition: all 0.3s ease-in-out;
            border: none;
        }

        .btn-login {
            background: linear-gradient(135deg, #ff9f1c, #ff4b2b);
            color: white;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #ff4b2b, #ff1a1a);
            transform: translateY(-3px);
        }

        .btn-profile {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            color: white;
        }

        .btn-profile:hover {
            background: linear-gradient(135deg, #2575fc, #1a75ff);
            transform: translateY(-3px);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-name {
            color: #ff4b2b;
            font-weight: bold;
        }

        .navbar-nav .nav-item {
            opacity: 0;
            transform: translateY(15px);
            animation: fadeIn 0.5s forwards ease-in-out;
        }

        .navbar-nav .nav-item:nth-child(1) { animation-delay: 0.2s; }
        .navbar-nav .nav-item:nth-child(2) { animation-delay: 0.3s; }
        .navbar-nav .nav-item:nth-child(3) { animation-delay: 0.4s; }
        .navbar-nav .nav-item:nth-child(4) { animation-delay: 0.5s; }
        .navbar-nav .nav-item:nth-child(5) { animation-delay: 0.6s; }
        .navbar-nav .nav-item:nth-child(6) { animation-delay: 0.7s; }
        .navbar-nav .nav-item:nth-child(7) { animation-delay: 0.8s; }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .navbar-toggler {
            border: none;
            outline: none;
        }

        .navbar-toggler-icon {
            filter: brightness(0) invert(1);
        }

        .dropdown-menu {
            background: rgba(20, 20, 20, 0.95);
            border: 1px solid #ff4b2b;
        }

        .dropdown-item {
            color: white !important;
            transition: all 0.3s ease;
        }

        .dropdown-item:hover {
            background: linear-gradient(135deg, #ff4b2b, #ff1a1a);
            color: white !important;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="bi bi-calendar-check-fill"></i> EBOOK PLANNER
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link <?php echo ($current_page == 'services.php') ? 'active' : ''; ?>" href="services.php">Services</a></li>
                <li class="nav-item"><a class="nav-link <?php echo ($current_page == 'pricing.php') ? 'active' : ''; ?>" href="pricing.php">Pricing</a></li>
                <li class="nav-item"><a class="nav-link <?php echo ($current_page == 'contact.php') ? 'active' : ''; ?>" href="contact.php">Contact</a></li>
                <li class="nav-item"><a class="nav-link <?php echo ($current_page == 'about.php') ? 'active' : ''; ?>" href="about.php">About</a></li>
                <li class="nav-item"><a class="nav-link <?php echo ($current_page == 'faq.php') ? 'active' : ''; ?>" href="faq.php">FAQ</a></li>

                <?php if ($is_logged_in): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle user-info" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                            <span class="user-name"><?php echo htmlspecialchars($user_data['name'] ?? 'User'); ?></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                            <li><a class="dropdown-item" href="booking.php"><i class="bi bi-calendar-plus"></i> New Booking</a></li>
                            <li><a class="dropdown-item" href="payment.php"><i class="bi bi-credit-card"></i> Payment History</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </li>
                <?php elseif ($current_page !== "login.php"): ?>
                    <li class="nav-item"><a class="nav-link btn btn-login" href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
