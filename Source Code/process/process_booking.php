<?php
include '../includes/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $phone = $_POST["phone"]; // i ready lng nakooo
    $event_type = $_POST["event_type"];
    $event_date = $_POST["event_date"];
    $guests = $_POST["guests"];
    $package = $_POST["package"];
    $message = $_POST["message"];

    $name = $conn->real_escape_string($name);
    $email = $conn->real_escape_string($email);
    $phone = $conn->real_escape_string($phone);
    $event_type = $conn->real_escape_string($event_type);
    $event_date = $conn->real_escape_string($event_date);
    $guests = (int)$guests;
    $package = $conn->real_escape_string($package);
    $message = $conn->real_escape_string($message);

    $sql = "INSERT INTO bookings (name, email, phone, event_type, event_date, guests, package, message)
            VALUES ('$name', '$email', '$phone', '$event_type', '$event_date', '$guests', '$package', '$message')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Booking successful!'); window.location.href='index.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }

    $conn->close();
} else {
    echo "Invalid request.";
}
?>
