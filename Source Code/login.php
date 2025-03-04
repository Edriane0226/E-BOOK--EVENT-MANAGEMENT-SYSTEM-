<?php
session_start();
include 'config.php';
include 'navbar.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, username, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $username, $hashed_password, $role);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;

            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Ebook Planner</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #000; color: #fff; font-family: Arial, sans-serif; }
        .login-container { background-color: #111; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(255, 0, 0, 0.5); max-width: 400px; margin: 100px auto; }
        
        .form-label { font-weight: bold; color: white; }
        .form-control { background-color: #000; color: white; border: 1px solid red; font-size: 16px; font-weight: bold; }
        .form-control::placeholder { color: rgba(255, 255, 255, 0.6); font-size: 14px; font-weight: normal; }
        .form-control:focus { background-color: #222; color: white; border-color: #ff3c3c; box-shadow: 0 0 10px rgba(255, 60, 60, 0.8); }

        .btn-login { background: linear-gradient(to right, #ff3c3c, #ff0000); border: none; color: white; padding: 10px; font-size: 18px; border-radius: 5px; transition: 0.3s; }
        .btn-login:hover { background: linear-gradient(to right, #ff0000, #990000); box-shadow: 0 0 15px rgba(255, 0, 0, 0.7); }

        .register-link { color: #ff3c3c; font-weight: bold; }
        .register-link:hover { color: #ff0000; text-decoration: underline; }

        .error { color: red; text-align: center; font-weight: bold; margin-top: 10px; }
    </style>
</head>
<body>

    <div class="container mt-5">
        <div class="login-container">
            <h2 class="text-center">Sign In</h2>
            <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
            <form action="" method="POST">
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" name="email" placeholder="Enter your email" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn btn-login w-100">Login</button>
            </form>
            <p class="mt-3 text-center">Don't have an account? <a href="register.php" class="register-link">Register here</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
