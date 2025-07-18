<?php
session_start();
include 'category/CategoryManager.php'; // Include your CategoryManager
$categoryManager = new CategoryManager();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_COOKIE['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'User  not logged in.']);
        exit();
    }

    $userId = $_COOKIE['user_id'];
    $selectedItems = json_decode($_POST['selectedItems'], true);

    foreach ($selectedItems as $item) {
        // Extracting all necessary fields from the item
        $productId = $item['product_id'];
        $quantity = $item['quantity'];
        $color = $item['color'];
        $size = $item['size'];
        $unitPrice = $item['unit_price'];
        $discountPercent = $item['discount_percent'];
        $shipping = $item['shipping'];
        $commission = $item['commission'];
        $totalPrice = $item['total_price'];
        


        // Insert the order into the database
        $categoryManager->insertOrder($userId, $productId, $quantity, $color, $size, $unitPrice, $discountPercent, $shipping, $commission, $totalPrice);
    }
    echo json_encode(['status' => 'success', 'message' => 'Checkout successful.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
?>
