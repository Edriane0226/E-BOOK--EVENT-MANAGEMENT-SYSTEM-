<?php
session_start();
include '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$query = "SELECT bookings.*, users.name, users.email, edit_requests.id AS edit_request_id FROM bookings 
          INNER JOIN users ON bookings.user_id = users.id 
          LEFT JOIN edit_requests ON bookings.id = edit_requests.booking_id 
          ORDER BY bookings.event_date ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            background: black;
            font-family: 'Poppins', sans-serif;
            color: white;
        }

        .dashboard-container {
            max-width: 90%;
            margin: 40px auto;
            padding: 20px;
            background: rgba(0, 0, 0, 0.7);
            border-radius: 12px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 10px rgba(255, 255, 255, 0.2);
            animation: fadeIn 0.8s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .admin-name {
            font-size: 18px;
            font-weight: bold;
            color: #00eaff;
        }

        .table {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .table th {
            background: linear-gradient(to right, #ff416c, #ff4b2b);
            color: white;
        }

        .table-hover tbody tr:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .btn-custom {
            background: linear-gradient(to right, #ff416c, #ff4b2b);
            color: white;
            transition: all 0.3s ease-in-out;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
        }

        .btn-custom:hover {
            box-shadow: 0 0 10px rgba(255, 65, 108, 0.8);
            transform: scale(1.05);
        }

        .btn-danger {
            background: #ff1e56;
            border: none;
        }

        .btn-danger:hover {
            background: #ff004c;
        }

        .welcome-text {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            background: linear-gradient(to right, #00eaff, #ff00ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <h2>Admin Dashboard</h2>
            <div class="admin-info">
                <span class="admin-name"><i class="fa fa-user-shield"></i> Welcome, <?php echo $_SESSION['admin_name']; ?></span>
                <a href="admin_logout.php" class="btn btn-danger"><i class="fa fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <h3 class="welcome-text">Welcome to Your Admin Panel</h3>

        <h3 class="text-center mb-4">Manage Bookings</h3>
        <div class="table-responsive">
            <table class="table table-hover text-center">
                <thead>
                    <tr>
                        <th>User Name</th>
                        <th>Email</th>
                        <th>Event Type</th>
                        <th>Event Date</th>
                        <th>Guests</th>
                        <th>Package</th>
                        <th>Message</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['event_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['event_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['guests']); ?></td>
                            <td><?php echo htmlspecialchars($row['package']); ?></td>
                            <td><?php echo htmlspecialchars($row['message']); ?></td>
                            <td>
                                <?php if ($row['edit_request_id']): ?>
                                    <a href="confirm_edit.php?id=<?php echo $row['edit_request_id']; ?>&booking_id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm"><i class="fa fa-check"></i> Confirm Edit</a>
                                <?php else: ?>
                                    <span class="text-success">No edit request</span>
                                <?php endif; ?>
                                <a href="delete_booking.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this booking?');"><i class="fa fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>