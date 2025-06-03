<?php
include '../includes/config.php';

// Authenticate user using JWT
$user_data = authenticateUser();

if (!$user_data) {
    header("Location: login.php");
    exit();
}

$user_id = $user_data['id'];
$user_name = $user_data['name'];

include '../includes/header.php';

// Get profile pic
$stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$profile_pic = !empty($user['profile_pic']) ? "../uploads/" . $user['profile_pic'] : "../assets/default-profile.png";

// Separate queries for better readability and maintainability

// 1. Get latest edit request status per booking
$latest_edit_status = "
    SELECT er.booking_id, er.status
    FROM edit_requests er
    INNER JOIN (
        SELECT booking_id, MAX(requested_at) AS latest_request
        FROM edit_requests
        GROUP BY booking_id
    ) latest_er ON er.booking_id = latest_er.booking_id 
                AND er.requested_at = latest_er.latest_request
";

// 2. Get refunded bookings to exclude
$refunded_bookings = "
    SELECT DISTINCT py.booking_id
    FROM payments py
    WHERE py.is_refunded = 1
";

// 3. Main bookings query combining all subqueries
$bookings_query = "
    SELECT 
        b.id, 
        b.event_type, 
        b.event_date, 
        b.guests, 
        b.message, 
        b.status, 
        b.created_at,
        b.payment_type, 
        b.payment_amount, 
        p.name AS package_name, 
        p.price,
        les.status AS latest_edit_status
    FROM bookings b
    LEFT JOIN packages p ON b.package_id = p.id
    LEFT JOIN ($latest_edit_status) les ON b.id = les.booking_id
    WHERE b.user_id = ?
      AND b.id NOT IN (
          SELECT booking_id FROM ($refunded_bookings) rb
      )
    ORDER BY b.event_date ASC
";

$stmt = $conn->prepare($bookings_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>User Dashboard</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
<style>
  body {
    background: #121212;
    color: #fff;
  }

  .sidebar {
    height: 100vh;
    background-color: #121212;
    padding: 20px;
    position: fixed;
    width: 250px;
    transition: all 0.3s ease-in-out;
    z-index: 1000;
  }

  .sidebar.collapsed {
    width: 80px;
    overflow: hidden;
  }

  .sidebar .btn-toggle {
    background: none;
    border: none;
    color: #fff;
    font-size: 1.5rem;
    float: right;
    margin-bottom: 20px;
  }

  .sidebar.collapsed .nav-text {
    display: none;
  }

  .sidebar img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 50%;
    margin-bottom: 10px;
  }
  .sidebar-content {
  transition: opacity 0.3s ease-in-out;
}

.sidebar.collapsed .sidebar-content {
  display: none;
}

  .main-content {
    margin-left: 250px;
    padding: 40px 20px;
    transition: margin-left 0.3s ease-in-out;
  }

  .main-content.collapsed {
    margin-left: 80px;
  }

  .card-custom {
    background: #1e1e1e;
    border-radius: 12px;
    padding: 30px;
  }

  .table th {
    background: linear-gradient(to right, #ff416c, #ff4b2b);
    color: white;
  }

  .btn-custom {
    background-color: #ff4b2b;
    color: white;
  }
</style>
</head>

<body>
  <div class="sidebar" id="sidebar">
  <button class="btn-toggle" onclick="toggleSidebar()">
    <i class="bi bi-list"></i>
  </button>

  <div class="sidebar-content">
    <div class="text-center">
        <br><br><br>
      <img src="<?php echo $profile_pic; ?>" onclick="openImageModal()" />
      <p class="mt-2 fw-bold"><?php echo htmlspecialchars($user_name); ?></p>
    </div>
    <a href="booking.php" class="btn btn-custom w-100 mt-2">Make a Booking</a>
    <a href="edit_center.php" class="btn btn-custom w-100 mt-2">Edit & Payment Center</a>
    <a href="payment.php" class="btn btn-custom w-100 mt-2">Payment History</a>
    <a href="refund_history.php" class="btn btn-custom w-100 mt-2">Refunds</a>
    <a href="settings.php" class="btn btn-custom w-100 mt-2">Settings</a>
    <a href="logout.php" class="btn btn-danger w-100 mt-2">Logout</a>
  </div>
</div>
  <div class="main-content" id="mainContent">
    <div class="card-custom">
      <h4 class="text-center fw-bold mb-3" style="color: #ff4b2b;">Your Bookings</h4>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>Event Type</th>
              <th>Date</th>
              <th>Guests</th>
              <th>Package</th>
              <th>Created at</th>
              <th>Message</th>
            </tr>
          </thead>
          <tbody>
          <?php while ($row = $bookings->fetch_assoc()): ?>
            <tr>
              <td><?php echo htmlspecialchars($row['event_type']); ?></td>
              <td><?php echo htmlspecialchars($row['event_date']); ?></td>
              <td><?php echo htmlspecialchars($row['guests']); ?></td>
              <td><?php echo htmlspecialchars($row['package_name']); ?></td>
              <td><?php echo htmlspecialchars($row['created_at']); ?></td>
              <td><?php echo htmlspecialchars($row['message']); ?></td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Modal -->
  <div id="imageModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); justify-content:center; align-items:center;">
    <img id="modalImage" src="" style="max-width:90%; max-height:90%; border-radius:10px;" />
  </div>

  <script>
    function toggleSidebar() {
      document.getElementById("sidebar").classList.toggle("collapsed");
      document.getElementById("mainContent").classList.toggle("collapsed");
    }

    document.getElementById("profileButton").addEventListener("click", () => {
      document.getElementById("profileInput").click();
    });

    document.getElementById("profileInput").addEventListener("change", () => {
      document.getElementById("uploadButton").style.display = "block";
    });

    document.getElementById("profileUploadForm").addEventListener("submit", () => {
      if (document.querySelector(".loading-indicator")) {
        document.querySelector(".loading-indicator").style.display = "block";
      }
    });

    function openImageModal() {
      const profileImg = document.querySelector(".sidebar img");
      document.getElementById("modalImage").src = profileImg.src;
      document.getElementById("imageModal").style.display = "flex";
    }

    document.getElementById("imageModal").addEventListener("click", function () {
      this.style.display = "none";
    });
  </script>
</body>
</html>