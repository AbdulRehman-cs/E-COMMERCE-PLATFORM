<?php
// process_payment.php

// Database connection parameters
$host = 'localhost'; // or your database host
$db = 'razababa'; // your database name
$user = 'root'; // your database username
$pass = ''; // your database password

// Create a connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the payment data from the request
    $data = json_decode(file_get_contents('php://input'), true);
    $method = $data['method'];
    $account_or_card = $data['account_or_card'];
    $amount = $data['amount'];
    $card_number = isset($data['card_number']) ? $data['card_number'] : null;
    $cvv = isset($data['cvv']) ? $data['cvv'] : null;
    $expiry_date = isset($data['expiry_date']) ? $data['expiry_date'] : null;

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO payments (method, account_or_card, amount, card_number, cvv, expiry_date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdsss", $method, $account_or_card, $amount, $card_number, $cvv, $expiry_date); // "ssdsss" means string, string, double, string, string, string

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Payment recorded successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error recording payment: ' . $stmt->error]);
    }

    // Close the statement
    $stmt->close();
}

// Close the connection
$conn->close();
?>
