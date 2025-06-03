<?php
include '../includes/config.php'; // database connection

$error = '';
$success = false;

// Handle form submission (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Email and password required';
    } else {
        // Fetch id, password, and name from users table
        $stmt = $conn->prepare("SELECT id, password, name FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $error = 'Invalid credentials';
        } else {
            $user = $result->fetch_assoc();
            
            if (!password_verify($password, $user['password'])) {
                $error = 'Incorrect password';
            } else {
                // Login successful - generate JWT token
                $user_data = [
                    'id' => $user['id'],
                    'email' => $email,
                    'name' => $user['name']
                ];
                
                $jwt_token = generateJWT($user_data);
                
                // Store JWT in session
                $_SESSION['jwt_token'] = $jwt_token;
                
                // Debug: Check if token is generated (remove this after testing)
                error_log("JWT token generated for user: " . $user['name']);
                
                // Redirect to dashboard
                header("Location: dashboard.php");
                exit;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Login</title>
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

    .login-form {
        background: #1c1c1c;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(255, 255, 255, 0.1);
        transition: all 0.3s ease-in-out;
    }
    .login-form:hover {
        transform: scale(1.02);
        box-shadow: 0 6px 15px rgba(255, 255, 255, 0.2);
    }
    .login-form input {
        background: #222;
        border: 1px solid #ff4b2b;
        color: #fff;
        transition: 0.3s;
    }
    .login-form input:focus {
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
        <h1 class="display-4 fw-bold">Login</h1>
        <p class="lead">Access your bookings and manage your events</p>
    </div>
</section>

<section class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 fade-in">
            <div class="login-form">
                <h3 class="text-center mb-4">Sign In</h3>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form action="" method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="Enter your email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label fw-bold">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                    </div>
                    <button type="submit" class="btn btn-glow w-100 fw-bold">Login</button>
                </form>
                <p class="text-center mt-3">Don't have an account? <a href="register.php" class="text-danger fw-bold">Register here</a></p>
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