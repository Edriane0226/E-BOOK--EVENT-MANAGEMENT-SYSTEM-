<?php
session_start();
include 'C:\xampp\htdocs\E-BOOK--EVENT-MANAGEMENT-SYSTEM-\Source Code\API\bookingConfig.php';
require_once 'jwt_utilsBooking.php';

header("Content-Type: application/json");

// Get JWT from Authorization Header
$headers = getallheaders();
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(["error" => "Missing Authorization header"]);
    exit;
}

$authHeader = $headers['Authorization'];
$token = str_replace('Bearer ', '', $authHeader);

// Decode JWT
$user = JWTUtils::verifyToken($token);

if (!$user || !isset($user['id']) || !isset($user['is_admin'])) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid token"]);
    exit;
}

$user_id = $user['id'];
$is_admin = $user['is_admin'];

// Handle request using switch case based on HTTP method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getBookings($conn, $user_id, $is_admin);
        break;
    case 'POST':
        addBooking($conn, $user_id, $is_admin);
        break;
    case 'PUT':
        updateBooking($conn, $user_id, $is_admin);
        break;
    case 'DELETE':
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
    try {
        $data = json_decode(file_get_contents("php://input"), true);

        // Check for required fields - note: using 'package_id' not 'package'
        if (!isset($data['event_type'], $data['event_date'], $data['guests'], $data['package_id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing required fields: event_type, event_date, guests, package_id"]);
            return;
        }

        // Validate event type against your database values
        $valid_event_types = ['Wedding', 'Birthday', 'Conference', 'Debut'];
        if (!in_array($data['event_type'], $valid_event_types)) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid event type. Must be one of: " . implode(', ', $valid_event_types)]);
            return;
        }

        // Validate event date
        $event_date = $data['event_date'];
        if (!strtotime($event_date)) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid event date format"]);
            return;
        }

        if (strtotime($event_date) < strtotime('today')) {
            http_response_code(400);
            echo json_encode(["error" => "Event date cannot be in the past"]);
            return;
        }

        // Validate guests
        $guests = filter_var($data['guests'], FILTER_VALIDATE_INT);
        if ($guests === false || $guests < 1) {
            http_response_code(400);
            echo json_encode(["error" => "Number of guests must be a positive integer"]);
            return;
        }

        // Validate and get package information
        $package_id = filter_var($data['package_id'], FILTER_VALIDATE_INT);
        if ($package_id === false) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid package ID"]);
            return;
        }

        // Get package price from database
        $package_query = $conn->prepare("SELECT id, name, price FROM packages WHERE id = ?");
        $package_query->bind_param("i", $package_id);
        $package_query->execute();
        $package_result = $package_query->get_result();
        $package_data = $package_result->fetch_assoc();

        if (!$package_data) {
            http_response_code(404);
            echo json_encode(["error" => "Package not found"]);
            return;
        }

        $payment_amount = $package_data['price'];

        // Fix message handling - properly handle empty messages
        $message = null;
        if (isset($data['message']) && !empty(trim($data['message']))) {
            $message = trim(htmlspecialchars($data['message'], ENT_QUOTES, 'UTF-8'));
            if (strlen($message) > 1000) {
                http_response_code(400);
                echo json_encode(["error" => "Message cannot exceed 1000 characters"]);
                return;
            }
        }

        // Handle status
        $status = isset($data['status']) ? $data['status'] : 'Pending';
        $valid_statuses = ['Pending', 'Approved', 'Rejected'];
        if (!in_array($status, $valid_statuses)) {
            $status = 'Pending';
        }

        // Admin-specific logic
        if ($is_admin) {
            if (!isset($data['user_id'])) {
                http_response_code(400);
                echo json_encode(["error" => "Admin must specify the user_id"]);
                return;
            }
            
            $target_user_id = filter_var($data['user_id'], FILTER_VALIDATE_INT);
            if ($target_user_id === false) {
                http_response_code(400);
                echo json_encode(["error" => "Invalid user_id"]);
                return;
            }
            
            // Verify user exists
            $check_user = $conn->prepare("SELECT id, name FROM users WHERE id = ?");
            $check_user->bind_param("i", $target_user_id);
            $check_user->execute();
            $user_result = $check_user->get_result();
            
            if ($user_result->num_rows === 0) {
                http_response_code(404);
                echo json_encode(["error" => "User not found"]);
                return;
            }
            
            $user_id = $target_user_id;
        } else {
            // Regular user validation
            if (isset($data['user_id']) && $data['user_id'] != $user_id) {
                http_response_code(403);
                echo json_encode(["error" => "You can only create bookings for yourself"]);
                return;
            }
            
            // Check for duplicate bookings on same date (optional business rule)
            $check_duplicate = $conn->prepare("SELECT id FROM bookings WHERE user_id = ? AND event_date = ? AND status != 'Rejected'");
            $check_duplicate->bind_param("is", $user_id, $event_date);
            $check_duplicate->execute();
            if ($check_duplicate->get_result()->num_rows > 0) {
                http_response_code(409);
                echo json_encode(["error" => "You already have a booking on this date"]);
                return;
            }
        }

        // Set refundable status (default to true)
        $refundable = isset($data['refundable']) ? (bool)$data['refundable'] : 1;

        // Insert booking with proper message handling
        $stmt = $conn->prepare("INSERT INTO bookings (user_id, event_type, event_date, guests, package_id, message, status, payment_amount, refundable, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("isssiisdi", $user_id, $data['event_type'], $event_date, $guests, $package_id, $message, $status, $payment_amount, $refundable);

        if ($stmt->execute()) {
            $booking_id = $conn->insert_id;
            
            http_response_code(201);
            echo json_encode([
                "success" => "Booking added successfully",
                "booking_id" => $booking_id,
                "status" => $status,
                "payment_amount" => $payment_amount,
                "package_name" => $package_data['name'],
                "message" => $message ? $message : "No additional message"
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Database error: Failed to add booking - " . $conn->error]);
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => "Server error: " . $e->getMessage()]);
    }
}

function updateBooking($conn, $user_id, $is_admin) {
    try {
        $data = json_decode(file_get_contents("php://input"), true);

        // Validate required fields
        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing booking ID"]);
            return;
        }

        $booking_id = filter_var($data['id'], FILTER_VALIDATE_INT);
        if ($booking_id === false) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid booking ID"]);
            return;
        }

        // Check if booking exists and get current data
        $check_booking = $conn->prepare("SELECT * FROM bookings WHERE id = ?");
        $check_booking->bind_param("i", $booking_id);
        $check_booking->execute();
        $existing_booking = $check_booking->get_result()->fetch_assoc();

        if (!$existing_booking) {
            http_response_code(404);
            echo json_encode(["error" => "Booking not found"]);
            return;
        }

        if ($is_admin) {
            // Admin can update any booking directly
            $fields_to_update = [];
            $values = [];
            $types = "";

            // Build dynamic update query based on provided fields
            if (isset($data['event_type'])) {
                $valid_event_types = ['Wedding', 'Birthday', 'Conference', 'Debut'];
                if (!in_array($data['event_type'], $valid_event_types)) {
                    http_response_code(400);
                    echo json_encode(["error" => "Invalid event type. Must be one of: " . implode(', ', $valid_event_types)]);
                    return;
                }
                $fields_to_update[] = "event_type = ?";
                $values[] = $data['event_type'];
                $types .= "s";
            }

            if (isset($data['event_date'])) {
                if (!strtotime($data['event_date'])) {
                    http_response_code(400);
                    echo json_encode(["error" => "Invalid event date format"]);
                    return;
                }
                $fields_to_update[] = "event_date = ?";
                $values[] = $data['event_date'];
                $types .= "s";
            }

            if (isset($data['guests'])) {
                $guests = filter_var($data['guests'], FILTER_VALIDATE_INT);
                if ($guests === false || $guests < 1) {
                    http_response_code(400);
                    echo json_encode(["error" => "Invalid number of guests"]);
                    return;
                }
                $fields_to_update[] = "guests = ?";
                $values[] = $guests;
                $types .= "i";
            }

            if (isset($data['package_id'])) {
                $package_id = filter_var($data['package_id'], FILTER_VALIDATE_INT);
                if ($package_id === false) {
                    http_response_code(400);
                    echo json_encode(["error" => "Invalid package ID"]);
                    return;
                }

                // Verify package exists and get new price
                $package_query = $conn->prepare("SELECT id, name, price FROM packages WHERE id = ?");
                $package_query->bind_param("i", $package_id);
                $package_query->execute();
                $package_result = $package_query->get_result();
                $package_data = $package_result->fetch_assoc();

                if (!$package_data) {
                    http_response_code(404);
                    echo json_encode(["error" => "Package not found"]);
                    return;
                }

                $fields_to_update[] = "package_id = ?";
                $values[] = $package_id;
                $types .= "i";

                // Update payment amount when package changes
                $fields_to_update[] = "payment_amount = ?";
                $values[] = $package_data['price'];
                $types .= "d";
            }

            if (isset($data['message'])) {
                $message = null;
                if (!empty(trim($data['message']))) {
                    $message = trim(htmlspecialchars($data['message'], ENT_QUOTES, 'UTF-8'));
                    if (strlen($message) > 1000) {
                        http_response_code(400);
                        echo json_encode(["error" => "Message cannot exceed 1000 characters"]);
                        return;
                    }
                }
                $fields_to_update[] = "message = ?";
                $values[] = $message;
                $types .= "s";
            }

            // In the user section of updateBooking function:
            $message = null;
            if (isset($data['message']) && !empty(trim($data['message']))) {
                $message = trim(htmlspecialchars($data['message'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($data['status'])) {
                $valid_statuses = ['Pending', 'Approved', 'Rejected'];
                if (!in_array($data['status'], $valid_statuses)) {
                    http_response_code(400);
                    echo json_encode(["error" => "Invalid status. Must be one of: " . implode(', ', $valid_statuses)]);
                    return;
                }
                $fields_to_update[] = "status = ?";
                $values[] = $data['status'];
                $types .= "s";
            }

            if (isset($data['refundable'])) {
                $refundable = (bool)$data['refundable'] ? 1 : 0;
                $fields_to_update[] = "refundable = ?";
                $values[] = $refundable;
                $types .= "i";
            }

            if (empty($fields_to_update)) {
                http_response_code(400);
                echo json_encode(["error" => "No valid fields to update"]);
                return;
            }

            // Remove the updated_at field since it doesn't exist in your schema
            $values[] = $booking_id;
            $types .= "i";

            $sql = "UPDATE bookings SET " . implode(", ", $fields_to_update) . " WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$values);

            if ($stmt->execute()) {
                http_response_code(200);
                echo json_encode([
                    "success" => "Booking updated successfully by admin",
                    "booking_id" => $booking_id
                ]);
            } else {
                http_response_code(500);
                echo json_encode(["error" => "Database error: Failed to update booking - " . $conn->error]);
            }

        } else {
            // Regular user - check ownership and create edit request
            if ($existing_booking['user_id'] != $user_id) {
                http_response_code(403);
                echo json_encode(["error" => "You can only update your own bookings"]);
                return;
            }

            // Check if booking is still editable
            if (in_array($existing_booking['status'], ['Approved', 'Rejected'])) {
                http_response_code(403);
                echo json_encode(["error" => "Cannot edit booking with status: " . $existing_booking['status']]);
                return;
            }

            // For users, all fields are required for edit request
            if (!isset($data['event_type'], $data['event_date'], $data['guests'], $data['package_id'])) {
                http_response_code(400);
                echo json_encode(["error" => "Missing required fields: event_type, event_date, guests, package_id"]);
                return;
            }

            // Validate all fields
            $valid_event_types = ['Wedding', 'Birthday', 'Conference', 'Debut'];
            if (!in_array($data['event_type'], $valid_event_types)) {
                http_response_code(400);
                echo json_encode(["error" => "Invalid event type"]);
                return;
            }

            if (!strtotime($data['event_date'])) {
                http_response_code(400);
                echo json_encode(["error" => "Invalid event date format"]);
                return;
            }

            if (strtotime($data['event_date']) < strtotime('today')) {
                http_response_code(400);
                echo json_encode(["error" => "Event date cannot be in the past"]);
                return;
            }

            $guests = filter_var($data['guests'], FILTER_VALIDATE_INT);
            if ($guests === false || $guests < 1) {
                http_response_code(400);
                echo json_encode(["error" => "Invalid number of guests"]);
                return;
            }

            $package_id = filter_var($data['package_id'], FILTER_VALIDATE_INT);
            if ($package_id === false) {
                http_response_code(400);
                echo json_encode(["error" => "Invalid package ID"]);
                return;
            }

            // Verify package exists
            $package_query = $conn->prepare("SELECT id FROM packages WHERE id = ?");
            $package_query->bind_param("i", $package_id);
            $package_query->execute();
            if ($package_query->get_result()->num_rows === 0) {
                http_response_code(404);
                echo json_encode(["error" => "Package not found"]);
                return;
            }

            $message = isset($data['message']) ? trim(htmlspecialchars($data['message'], ENT_QUOTES, 'UTF-8')) : '';

            // Store original values
            $original_event_type = $existing_booking['event_type'];
            $original_event_date = $existing_booking['event_date'];
            $original_guests = $existing_booking['guests'];
            $original_message = isset($existing_booking['message']) ? $existing_booking['message'] : '';
            $original_package_id = $existing_booking['package_id'];

            // Create edit request - Fixed to use 'requested_at' instead of 'created_at'
            $stmt = $conn->prepare("
                INSERT INTO edit_requests 
                (booking_id, user_id, event_type, event_date, guests, package_id, message,
                 original_event_type, original_event_date, original_guests, original_message, original_package_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "iississssssi",
                $booking_id,
                $user_id,
                $data['event_type'],
                $data['event_date'],
                $guests,
                $package_id,
                $message,
                $original_event_type,
                $original_event_date,
                $original_guests,
                $original_message,
                $original_package_id
            );

            if ($stmt->execute()) {
                $edit_request_id = $conn->insert_id;
                http_response_code(201);
                echo json_encode([
                    "success" => "Edit request submitted for admin approval",
                    "edit_request_id" => $edit_request_id,
                    "booking_id" => $booking_id
                ]);
            } else {
                http_response_code(500);
                echo json_encode(["error" => "Failed to create edit request: " . $conn->error]);
            }
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => "Server error: " . $e->getMessage()]);
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

        if ($booking['user_id'] != $user_id) {
            echo json_encode(["error" => "You are not allowed to delete this booking"]);
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