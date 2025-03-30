<?php
session_start();
include '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$id = $_GET['id'];
$result = $conn->query("SELECT * FROM bookings WHERE id = $id");
$booking = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_type = $_POST['event_type'];
    $event_date = $_POST['event_date'];
    $guests = $_POST['guests'];
    $package = $_POST['package'];
    $message = $_POST['message'];

    $conn->query("UPDATE bookings SET event_type='$event_type', event_date='$event_date', guests='$guests',
                                                         package='$package', message='$message' WHERE id=$id");

    header("Location: admin_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Booking</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            background: #121212;
            font-family: 'Inter', sans-serif;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .edit-container {
            width: 100%;
            max-width: 500px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
            text-align: center;
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .edit-container h2 {
            color: #ff4b2b;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.3);
            color: white;
        }

        .btn-save {
            background: linear-gradient(to right, #ff416c, #ff4b2b);
            border: none;
            font-weight: bold;
            padding: 10px;
            transition: 0.3s ease-in-out;
        }

        .btn-save:hover {
            background: linear-gradient(to right, #ff4b2b, #ff416c);
        }

        .btn-cancel {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 10px;
            transition: 0.3s ease-in-out;
        }

        .btn-cancel:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .input-group-text {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
        }
    </style>
</head>
<body>

<div class="edit-container">
    <h2><i class="fas fa-edit"></i> Edit Booking</h2>

    <form method="post">
        <div class="input-group mb-3">
            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
            <input type="text" name="event_type" class="form-control" value="<?php echo $booking['event_type']; ?>" required>
        </div>

        <div class="input-group mb-3">
            <span class="input-group-text"><i class="fas fa-clock"></i></span>
            <input type="date" name="event_date" class="form-control" value="<?php echo $booking['event_date']; ?>" required>
        </div>

        <div class="input-group mb-3">
            <span class="input-group-text"><i class="fas fa-users"></i></span>
            <input type="number" name="guests" class="form-control" value="<?php echo $booking['guests']; ?>" required>
        </div>

        <div class="input-group mb-3">
            <span class="input-group-text"><i class="fas fa-gift"></i></span>
            <input type="text" name="package" class="form-control" value="<?php echo $booking['package']; ?>" required>
        </div>

        <div class="input-group mb-3">
            <span class="input-group-text"><i class="fas fa-comment"></i></span>
            <textarea name="message" class="form-control" required><?php echo $booking['message']; ?></textarea>
        </div>

        <button type="submit" class="btn btn-save w-100"><i class="fas fa-save"></i> Update Booking</button>
        <a href="admin_dashboard.php" class="btn btn-cancel w-100 mt-2"><i class="fas fa-arrow-left"></i> Cancel</a>
    </form>
</div>

</body>
</html>
