<?php
session_start();
include 'category/CategoryManager.php';

header('Content-Type: application/json');

if (!isset($_COOKIE['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$userId = $_COOKIE['user_id'] ?? null;
$categoryManager = new CategoryManager();

// Get JSON payload from request
$postData = json_decode(file_get_contents('php://input'), true);
if (!isset($postData['updates']) || !is_array($postData['updates'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit;
}

$updates = $postData['updates'];
$updatedCart = [];
$subtotal = 0;

foreach ($updates as $update) {
    if (!isset($update['cartId'], $update['quantity'])) {
        continue;
    }
    $cartId = intval($update['cartId']);
    $quantity = intval($update['quantity']);
    if ($quantity < 1) {
        $quantity = 1;
    }

    // Fetch cart item info to validate ownership and get product and price
    $cartItem = $categoryManager->getCartItemById($cartId);
    if (!$cartItem || $cartItem['user_id'] != $userId) {
        // invalid cart or user mismatch, skip
        continue;
    }

    $discounts = $categoryManager->getDiscountsByProductId($cartItem['product_id']);

    // Calculate unit price with discount if applicable
    $unitPrice = $cartItem['price'];
    foreach ($discounts as $discount) {
        if ($quantity >= $discount['quantity']) {
            $unitPrice = $discount['final_price'] / $discount['quantity'];
        }
    }

    $totalPrice = $unitPrice * $quantity;

    // Update cart item quantity and price
    $categoryManager->updateCartItemQuantityAndPrice($cartId, $quantity, $totalPrice);

    $updatedCart[] = [
        'cartId' => $cartId,
        'quantity' => $quantity,
        'unitPrice' => number_format($unitPrice, 2),
        'totalPrice' => number_format($totalPrice, 2)
    ];

    $subtotal += $totalPrice;
}

$shippingCost = 10.00; // example fixed shipping cost
$total = $subtotal + $shippingCost;

echo json_encode([
    'success' => true,
    'updatedCart' => $updatedCart,
    'subtotal' => number_format($subtotal, 2),
    'shippingCost' => number_format($shippingCost, 2),
    'total' => number_format($total, 2)
]);
