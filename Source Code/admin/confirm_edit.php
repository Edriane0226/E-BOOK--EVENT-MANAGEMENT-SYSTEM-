<?php
session_start();
include '../includes/config.php';

if (!isset($_SESSION['admin_id']) || !isset($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$edit_id = $_GET['id'];

// Fetch edit request details
$stmt = $conn->prepare("SELECT * FROM edit_requests WHERE id = ?");
$stmt->bind_param("i", $edit_id);
$stmt->execute();
$result = $stmt->get_result();
$edit = $result->fetch_assoc();

if (!$edit) {
    echo "<script>alert('Edit request not found.'); window.location='admin_dashboard.php';</script>";
    exit();
}

$booking_id = $edit['booking_id'];

// Fetch package name
$package_stmt = $conn->prepare("SELECT name FROM packages WHERE id = ?");
$package_stmt->bind_param("i", $edit['package_id']);
$package_stmt->execute();
$package_result = $package_stmt->get_result();
$package = $package_result->fetch_assoc();
$package_name = $package ? $package['name'] : 'Unknown Package';

// Handle approval
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn->begin_transaction();

    try {
        // Update booking with new values
        $update_stmt = $conn->prepare("UPDATE bookings SET event_type = ?, event_date = ?, guests = ?, package_id = ?, message = ? WHERE id = ?");
        $update_stmt->bind_param(
            "ssissi",
            $edit['event_type'],
            $edit['event_date'],
            $edit['guests'],
            $edit['package_id'],
            $edit['message'],
            $booking_id
        );
        $update_stmt->execute();

        // Mark the edit request as approved
        $status_stmt = $conn->prepare("UPDATE edit_requests SET status = 'approved' WHERE id = ?");
        $status_stmt->bind_param("i", $edit_id);
        $status_stmt->execute();

        $conn->commit();
        echo "<script>alert('Edit request approved and booking updated.'); window.location='admin_dashboard.php';</script>";
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('Transaction failed. Try again.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Edit Request - E-Book Event Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
        }
        
        .main-container {
            max-width: 900px;
            margin: auto;
            background: #1c1c1c;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            margin-top: 2rem;
            margin-bottom: 2rem;
            transition: all 0.3s ease-in-out;
        }
        
        .page-header {
            background: linear-gradient(to right, #ff416c, #ff4b2b);
            color: white;
            border-radius: 12px 12px 0 0;
            padding: 60px 0;
            text-align: center;
        }
        
        .page-header h1 {
            margin: 0;
            font-size: 3rem;
            font-weight: bold;
        }
        
        .page-header .subtitle {
            margin: 0.5rem 0 0 0;
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .content-section {
            padding: 40px;
        }
        
        .comparison-table {
            background: #222;
            border: 1px solid #ff4b2b;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 2rem;
            transition: 0.3s;
        }
        .comparison-table table {
            margin-bottom: 0;
        }
        
        .comparison-table thead th {
            background: linear-gradient(45deg, #ff416c, #ff4b2b);
            color: black;
            font-weight: 600;
            text-align: center;
            padding: 1rem;
            border: none;
        }
        
        .comparison-table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-color: #333;
            color: black;
        }
        .field-label {
            font-weight: 600;
            color:black;
        }
        .action-buttons {
            text-align: center;
            padding-top: 1rem;
        }
        
        .btn-approve {
            background: linear-gradient(45deg, #ff416c, #ff4b2b);
            border: none;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 8px;
            color: white;
            transition: 0.3s;
        }
        
        .btn-approve:hover {
            background: linear-gradient(45deg, #2a5298, #1e3c72);
            transform: scale(1.05);
            color: white;
        }
        
        .btn-cancel {
            background: #6c757d;
            border: none;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 8px;
            color: white;
            transition: 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .request-id-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            display: inline-block;
            margin-top: 1rem;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-container fade-in">
            <div class="page-header">
                <h1><i class="fas fa-edit me-3"></i>Confirm Edit Request</h1>
                <p class="subtitle">Review and approve booking modification</p>
                <div class="request-id-badge">
                    Request ID: #<?= $edit_id ?>
                </div>
            </div>
            
            <div class="content-section">
                <form method="post">
                    <div class="comparison-table">
                        <table class="table table-borderless">
                            <thead>
                                <tr>
                                    <th style="width: 25%;">Field</th>
                                    <th style="width: 37.5%;">Original Value</th>
                                    <th style="width: 37.5%;">Requested Change</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="field-label">
                                        <i class="fas fa-calendar-alt me-2"></i>Event Type
                                    </td>
                                    <td class="original-value"><?= htmlspecialchars($edit['original_event_type']) ?></td>
                                    <td class="new-value"><?= htmlspecialchars($edit['event_type']) ?></td>
                                </tr>
                                <tr>
                                    <td class="field-label">
                                        <i class="fas fa-clock me-2"></i>Event Date
                                    </td>
                                    <td class="original-value"><?= htmlspecialchars($edit['original_event_date']) ?></td>
                                    <td class="new-value"><?= htmlspecialchars($edit['event_date']) ?></td>
                                </tr>
                                <tr>
                                    <td class="field-label">
                                        <i class="fas fa-users me-2"></i>Number of Guests
                                    </td>
                                    <td class="original-value"><?= htmlspecialchars($edit['original_guests']) ?></td>
                                    <td class="new-value"><?= htmlspecialchars($edit['guests']) ?></td>
                                </tr>
                                <tr>
                                    <td class="field-label">
                                        <i class="fas fa-box me-2"></i>Package
                                    </td>
                                    <td class="original-value">Package ID: <?= htmlspecialchars($edit['original_package_id']) ?></td>
                                    <td class="new-value"><?= htmlspecialchars($package_name) ?></td>
                                </tr>
                                <tr>
                                    <td class="field-label">
                                        <i class="fas fa-comment me-2"></i>Message
                                    </td>
                                    <td class="original-value"><?= htmlspecialchars($edit['original_message']) ?></td>
                                    <td class="new-value"><?= htmlspecialchars($edit['message']) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="action-buttons">
                        <button type="submit" class="btn btn-approve me-3">
                            <i class="fas fa-check me-2"></i>Approve Changes
                        </button>
                        <a href="admin_dashboard.php" class="btn btn-cancel">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
            
            document.querySelector('form').addEventListener('submit', function(e) {
                if (!confirm("Are you sure you want to approve and apply these changes?")) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>