<?php
session_start();
include 'config.php';
include 'navbar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email, role FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email, $role);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Ebook Planner</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { 
            background-color: #000; 
            color: #fff; 
            font-family: Arial, sans-serif; 
        }

        .dashboard-container { 
            background-color: #111; 
            padding: 30px; 
            border-radius: 10px; 
            box-shadow: 0 0 20px rgba(255, 0, 0, 0.5); 
            max-width: 600px; 
            margin: 100px auto; 
            text-align: center; 
            animation: fadeIn 0.5s ease-in-out;
        }

        .profile-icon {
            font-size: 50px;
            background: linear-gradient(to right, #ff3c3c, #ff0000);
            color: white;
            padding: 15px;
            border-radius: 50%;
            display: inline-block;
            margin-bottom: 15px;
            box-shadow: 0 0 15px rgba(255, 0, 0, 0.5);
        }

        .role-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            background: linear-gradient(to right, #ff0000, #990000);
            color: white;
        }

        .btn-action {
            display: block;
            background: linear-gradient(to right, #ff3c3c, #ff0000);
            border: none;
            color: white;
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            transition: 0.3s;
            text-decoration: none;
            margin: 5px 0;
        }

        .btn-action:hover {
            background: linear-gradient(to right, #ff0000, #990000);
            box-shadow: 0 0 15px rgba(255, 0, 0, 0.7);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <div class="container mt-5">
        <div class="dashboard-container">
            <div class="profile-icon">👤</div>
            <h2>Welcome, <span class="text-warning"><?php echo htmlspecialchars($username); ?></span>!</h2>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
            <p><strong>Role:</strong> <span class="role-badge"><?php echo ucfirst($role); ?></span></p>

            <div class="mt-4">
                <a href="view_bookings.php" class="btn-action">📅 View My Bookings</a>
                <a href="edit_profile.php" class="btn-action">⚙ Edit Profile</a>
                <a href="logout.php" class="btn-action">🚪 Logout</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
