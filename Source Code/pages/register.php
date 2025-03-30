<?php
session_start();
include '../includes/config.php';
include '../includes/header.php';
include '../validation/password.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password_input = $_POST['password'];

    $validation_result = password_validation($password_input);
    if ($validation_result !== true) {
        echo "<script>alert('$validation_result');</script>";
    } else {
        $password = password_hash($password_input, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $password);

        if ($stmt->execute()) {
            echo "<script>alert('Registration successful! You can now login.'); window.location='login.php';</script>";
        } else {
            echo "<script>alert('Error! Please try again.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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

    .auth-container {
        background: #1c1c1c;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(255, 255, 255, 0.1);
        transition: all 0.3s ease-in-out;
    }

    .auth-container:hover {
        transform: scale(1.02);
        box-shadow: 0 6px 15px rgba(255, 255, 255, 0.2);
    }

    .form-control {
        background: #222;
        border: 1px solid #ff4b2b;
        color: #fff;
        transition: 0.3s;
    }

    .form-control:focus {
        border-color: #ff416c;
        box-shadow: 0 0 10px rgba(255, 65, 108, 0.5);
    }

    .btn-glow {
        background: linear-gradient(to right, #ff416c, #ff4b2b);
        color: #fff;
        padding: 12px 20px;
        border-radius: 6px;
        transition: 0.3s ease-in-out;
    }

    .btn-glow:hover {
        box-shadow: 0 0 15px rgba(255, 75, 43, 0.8);
        transform: scale(1.05);
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
        <h1 class="display-4 fw-bold">Register</h1>
        <p class="lead">Create an account to book your events</p>
    </div>
</section>

<section class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 fade-in">
            <div class="auth-container">
                <h3 class="text-center mb-4">Create an Account</h3>
                <form action="" method="post">
                    <div class="mb-3">
                        <label for="name" class="form-label fw-bold">Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter your full name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label fw-bold">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Create a password" required>
                        <p>Password must be 8+ characters, include an uppercase letter, lowercase letter and a number</p>
                    </div>
                    <button type="submit" class="btn btn-glow w-100 fw-bold">Register</button>
                </form>
                <p class="text-center mt-3">Already have an account? <a href="login.php" class="text-danger fw-bold">Login here</a></p>
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