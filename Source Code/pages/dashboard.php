<?php
session_start();
include '../includes/config.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$query = "SELECT profile_pic FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$profile_pic = !empty($user['profile_pic']) ? "../uploads/" . $user['profile_pic'] : "../assets/default-profile.png";
$profile_button_text = !empty($user['profile_pic']) ? "Change Profile" : "Upload Picture";

$query = "SELECT b.*, 
            (SELECT COUNT(*) FROM edit_requests er WHERE er.booking_id = b.id AND er.user_id = ?) AS pending_edit 
          FROM bookings b 
          WHERE b.user_id = ? 
          ORDER BY b.event_date ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
    body {
        background: #121212;
        color: white;
        font-family: Arial, sans-serif;
    }

    .dashboard-container {
        max-width: 1100px;
        margin: auto;
        padding: 40px 20px;
        opacity: 0;
        transform: translateY(20px);
        animation: fadeIn 0.8s forwards ease-in-out;
    }

    @keyframes fadeIn {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .card-custom {
        background: #1e1e1e;
        border-radius: 12px;
        padding: 30px;
        transition: transform 0.3s ease-in-out;
    }

    .card-custom:hover {
        transform: scale(1.05);
    }

    .table th {
        background: linear-gradient(to right, #ff416c, #ff4b2b);
        color: white;
    }

    .table-hover tbody tr {
        transition: background 0.3s ease-in-out;
    }

    .table-hover tbody tr:hover {
        background: #262626;
    }

    .btn-custom {
        background: #ff4b2b;
        color: white;
        transition: background 0.3s ease-in-out;
    }

    .btn-custom:hover {
        background: #ff1a1a;
    }

    .profile-img {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 50%;
        cursor: pointer;
        transition: transform 0.2s;
    }

    .profile-img:hover {
        transform: scale(1.1);
    }

    .loading-indicator {
        display: none;
        margin-top: 10px;
        color: #ff4b2b;
    }

    #profileInput {
        display: none;
    }
</style>
</head>
<body>
    
<div class="dashboard-container">
    <div class="row g-4 fade-in">
        <div class="col-md-4">
            <div class="card-custom text-center">
                <h4>Welcome, <strong><?php echo $_SESSION['user_name']; ?></strong></h4>
                <img src="<?php echo $profile_pic; ?>" class="profile-img mb-3" id="profileImage" onclick="openImageModal()">
                <form id="profileUploadForm" action="upload_profile.php" method="POST" enctype="multipart/form-data">
                    <input type="file" name="profile_pic" id="profileInput" accept="image/*">
                    <button type="button" class="btn btn-custom w-100" id="profileButton"><?php echo $profile_button_text; ?></button>
                    <button type="submit" class="btn btn-custom w-100 mt-2" id="uploadButton" style="display: none;">Upload</button>
                    <div class="loading-indicator">Uploading... Please wait.</div>
                </form>
                <a href="booking.php" class="btn btn-custom w-100 mt-2">Make a Booking</a>
                <a href="logout.php" class="btn btn-danger w-100 mt-2">Logout</a>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card-custom">
                <h4 class="mb-3 text-center fw-bold" style="color: #ff4b2b;">Your Bookings</h4>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Event Type</th>
                                <th>Event Date</th>
                                <th>Guests</th>
                                <th>Package</th>
                                <th>Message</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['event_type']); ?></td>
                                    <td><?php echo htmlspecialchars($row['event_date']); ?></td>
                                    <td><?php echo htmlspecialchars($row['guests']); ?></td>
                                    <td><?php echo htmlspecialchars($row['package']); ?></td>
                                    <td><?php echo htmlspecialchars($row['message']); ?></td>
                                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                    <td>
                                        <a href="edit_booking.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="delete_booking.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="imageModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); justify-content:center; align-items:center;">
    <img id="modalImage" src="" style="max-width:90%; max-height:90%; border-radius:10px;">
</div>

</body>
</html>

<script>
    document.getElementById("profileButton").addEventListener("click", function() {
        document.getElementById("profileInput").click();
    });

    document.getElementById("profileInput").addEventListener("change", function() {
        document.getElementById("uploadButton").style.display = "block";
    });

    document.getElementById("profileUploadForm").addEventListener("submit", function() {
        document.querySelector(".loading-indicator").style.display = "block";
    });

    function openImageModal() {
        document.getElementById("modalImage").src = document.getElementById("profileImage").src;
        document.getElementById("imageModal").style.display = "flex";
    }

    document.getElementById("imageModal").addEventListener("click", function() {
        this.style.display = "none";
    });
</script>