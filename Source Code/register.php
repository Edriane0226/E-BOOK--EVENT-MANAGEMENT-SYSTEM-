<?php
session_start();
include 'config.php';
include 'navbar.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $role = trim($_POST['role']);

    if (!in_array($role, ['admin', 'user'])) {
        $error = "Invalid role selection!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Registration successful! You can now log in.";
            header("Location: login.php");
            exit();
        } else {
            $error = "Email already exists!";
        }

        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Ebook Planner</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #000; color: #fff; font-family: Arial, sans-serif; }
        .register-container { background-color: #111; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(255, 0, 0, 0.5); max-width: 400px; margin: 100px auto; }
        
        .form-label { font-weight: bold; color: white; }
        .form-control, .form-select { background-color: #000; color: white; border: 1px solid red; font-size: 16px; font-weight: bold; }
        .form-control::placeholder { color: rgba(255, 255, 255, 0.6); font-size: 14px; font-weight: normal; }
        .form-control:focus, .form-select:focus { background-color: #222; color: white; border-color: #ff3c3c; box-shadow: 0 0 10px rgba(255, 60, 60, 0.8); }

        .btn-register { background: linear-gradient(to right, #ff3c3c, #ff0000); border: none; color: white; padding: 10px; font-size: 18px; border-radius: 5px; transition: 0.3s; }
        .btn-register:hover { background: linear-gradient(to right, #ff0000, #990000); box-shadow: 0 0 15px rgba(255, 0, 0, 0.7); }

        .login-link { color: #ff3c3c; font-weight: bold; }
        .login-link:hover { color: #ff0000; text-decoration: underline; }

        .error { color: red; text-align: center; font-weight: bold; margin-top: 10px; }
    </style>
</head>
<body>

    <div class="container mt-5">
        <div class="register-container">
            <h2 class="text-center">Create an Account</h2>
            <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
            <form action="" method="POST">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" name="username" placeholder="Enter your username" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" name="email" placeholder="Enter your email" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" placeholder="Enter your password" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" name="confirm_password" placeholder="Confirm your password" required>
                </div>
                <div class="mb-3">
    <label class="form-label">Select Role</label>
    <select name="role" class="form-select" required>
        <option value="" selected disabled>-- Select Role --</option>
        <option value="user">User</option>
        <option value="admin">Admin</option>
    </select>
</div>
                <button type="submit" class="btn btn-register w-100">Register</button>
            </form>
            <p class="mt-3 text-center">Already have an account? <a href="login.php" class="login-link">Login here</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
