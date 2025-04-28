<?php
session_start();
include '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

if (!isset($_GET['id']) || !isset($_GET['booking_id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$edit_request_id = $_GET['id'];
$booking_id = $_GET['booking_id'];

$query = "SELECT * FROM edit_requests WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $edit_request_id);
$stmt->execute();
$result = $stmt->get_result();
$edit_request = $result->fetch_assoc();

$query = "SELECT * FROM bookings WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$booking_result = $stmt->get_result();
$booking = $booking_result->fetch_assoc();

if (!$booking) {
    header("Location: admin_dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_type = $edit_request['event_type'];
    
    $event_date = $edit_request['event_date'];

    $guests = $edit_request['guests'];
    $package = $edit_request['package'];
    $message = $edit_request['message'];

    $update_query = "UPDATE bookings SET event_type = ?, event_date = ?, guests = ?, package = ?, message = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ssisss", $event_type, $event_date, $guests, $package, $message, $booking_id);

    if ($update_stmt->execute()) {
        $delete_query = "DELETE FROM edit_requests WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("i", $edit_request_id);
        $delete_stmt->execute();

        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "<script>alert('Error confirming edit request: " . $update_stmt->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Confirm Edit Request</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background: #121212;
            font-family: 'Inter', sans-serif;
            color: white;
            margin: 0;
            min-height: 100vh;
        }

        .main-content {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .edit-container {
            width: 100%;
            max-width: 700px; /* Increased the width */
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

        select.form-control {
            appearance: none;
            -webkit-appearance: none;
        }

        table {
            width: 100%;
            font-size: 1.1em;
        }

        th, td {
            padding: 15px;
        }

    </style>
</head>
<body>
    <div class="main-content">
        <div class="edit-container">
            <h2>Confirm Edit for Booking</h2>
            <p>Are you sure you want to confirm the following changes for the booking?</p>
            <form method="post">
                <table class="table table-bordered">
                    <tr>
                        <th>Event Type</th>
                        <td><?php echo htmlspecialchars($edit_request['event_type']); ?></td>
                    </tr>
                    <tr>
                        <th>Event Date</th>
                        <td><?php echo htmlspecialchars($edit_request['event_date']); ?></td>
                    </tr>
                    <tr>
                        <th>Guests</th>
                        <td><?php echo htmlspecialchars($edit_request['guests']); ?></td>
                    </tr>
                    <tr>
                        <th>Package</th>
                        <td><?php echo htmlspecialchars($edit_request['package']); ?></td>
                    </tr>
                    <tr>
                        <th>Message</th>
                        <td><?php echo htmlspecialchars($edit_request['message']); ?></td>
                    </tr>
                </table>
                <button type="submit" class="btn btn-save">Confirm Edit</button>
                <a href="admin_dashboard.php" class="btn btn-cancel">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html>
