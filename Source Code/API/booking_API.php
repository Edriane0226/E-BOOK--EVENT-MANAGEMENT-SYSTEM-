<?php
session_start();
include 'C:\xampp\htdocs\E-BOOK--EVENT-MANAGEMENT-SYSTEM-\Source Code\includes\config.php';
header("Content-Type: application/json");

require_once 'jwt_utils.php';

$headers = getallheaders();
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(["error" => "Missing Authorization header"]);
    exit;
}

$authHeader = $headers['Authorization'];
$token = str_replace('Bearer ', '', $authHeader);
$decoded = verifyJWT($token);

if (!$decoded || !isset($decoded['id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid token"]);
    exit;
}

$user_id = $decoded['id'];
$is_admin = isset($decoded['role']) && $decoded['role'] === 'admin'; // Check if admin

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":
        getBookings($conn, $user_id, $is_admin);
        break;
    case "POST":
        addBooking($conn, $user_id, $is_admin);
        break;
    case "PUT":
        updateBooking($conn, $user_id, $is_admin);
        break;
    case "DELETE":
        deleteBooking($conn, $user_id, $is_admin);
        break;
    default:
        echo json_encode(["error" => "Invalid request method"]);
        break;
}

function getBookings($conn, $user_id, $is_admin) {
    if ($is_admin) {
        $query = "SELECT * FROM bookings ORDER BY event_date ASC";
        $stmt = $conn->prepare($query);
    } else {
        $query = "SELECT * FROM bookings WHERE user_id = ? ORDER BY event_date ASC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
    }

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }

    echo json_encode($bookings);
}

function addBooking($conn, $user_id, $is_admin) {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['event_type'], $data['event_date'], $data['guests'], $data['package'])) {
        echo json_encode(["error" => "Missing required fields"]);
        return;
    }

    $message = isset($data['message']) ? $data['message'] : null;
    $status = isset($data['status']) ? $data['status'] : 'Pending';

    if ($is_admin) {
        if (!isset($data['user_id'])) {
            echo json_encode(["error" => "Admin must specify the user_id"]);
            return;
        }
        $user_id = $data['user_id'];
    } else {
        if (isset($data['user_id']) && $data['user_id'] != $user_id) {
            echo json_encode(["error" => "You can only create bookings for yourself"]);
            return;
        }
        $user_id = $user_id;
    }

    $stmt = $conn->prepare("INSERT INTO bookings (user_id, event_type, event_date, guests, package, message, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ississs", $user_id, $data['event_type'], $data['event_date'], $data['guests'], $data['package'], $message, $status);

    if ($stmt->execute()) {
        echo json_encode(["success" => "Booking added successfully"]);
    } else {
        echo json_encode(["error" => "Failed to add booking"]);
    }
}


function updateBooking($conn, $user_id, $is_admin) {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['id'], $data['event_type'], $data['event_date'], $data['guests'], $data['package'])) {
        echo json_encode(["error" => "Missing required fields"]);
        return;
    }

    if ($is_admin) {
        $stmt = $conn->prepare("UPDATE bookings SET event_type=?, event_date=?, guests=?, package=?, message=?, status=? WHERE id=?");
        $message = isset($data['message']) ? $data['message'] : null;
        $status = isset($data['status']) ? $data['status'] : 'Pending';

        $stmt->bind_param("ssisssi", $data['event_type'], $data['event_date'], $data['guests'], $data['package'], $message, $status, $data['id']);

        if ($stmt->execute()) {
            echo json_encode(["success" => "Booking updated by admin"]);
        } else {
            echo json_encode(["error" => "Failed to update booking"]);
        }
    } else {
        $stmt = $conn->prepare("SELECT user_id FROM bookings WHERE id = ?");
        $stmt->bind_param("i", $data['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $booking = $result->fetch_assoc();

        if (!$booking) {
            echo json_encode(["error" => "Booking not found"]);
            return;
        }

        if ($booking['user_id'] !== $user_id) {
            echo json_encode(["error" => "You are not allowed to update this booking"]);
            return;
        }

        $stmt = $conn->prepare("INSERT INTO edit_requests (booking_id, user_id, event_type, event_date, guests, package, message) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $message = isset($data['message']) ? $data['message'] : null;

        $stmt->bind_param("iississ", $data['id'], $user_id, $data['event_type'], $data['event_date'], $data['guests'], $data['package'], $message);

        if ($stmt->execute()) {
            echo json_encode(["success" => "Edit request submitted, awaiting admin approval"]);
        } else {
            echo json_encode(["error" => "Failed to submit edit request"]);
        }
    }
}

function deleteBooking($conn, $user_id, $is_admin) {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['id'])) {
        echo json_encode(["error" => "Missing booking ID"]);
        return;
    }

    if ($is_admin) {
        $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->bind_param("i", $data['id']);

        if ($stmt->execute()) {
            echo json_encode(["success" => "Booking deleted by admin"]);
        } else {
            echo json_encode(["error" => "Failed to delete booking"]);
        }
    } else {
        $stmt = $conn->prepare("SELECT user_id FROM bookings WHERE id = ?");
        $stmt->bind_param("i", $data['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $booking = $result->fetch_assoc();

        if (!$booking) {
            echo json_encode(["error" => "Booking not found"]);
            return;
        }

        if ($booking['user_id'] !== $user_id) {
            echo json_encode(["error" => "You are allowed to delete this booking"]);
            return;
        }

        $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->bind_param("i", $data['id']);

        if ($stmt->execute()) {
            echo json_encode(["success" => "Booking deleted successfully"]);
        } else {
            echo json_encode(["error" => "Failed to delete booking"]);
        }
    }
}

