<?php
// Start the session to manage user login state
include 'category/CategoryManager.php';
$categoryManager = new CategoryManager();
$user_id = $_COOKIE['user_id'];

$cartItems = $categoryManager->getCartItems($user_id);
$totalPrice = 0; // Initialize total price

// Handle item removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item_id'])) {
    $itemId = $_POST['remove_item_id'];
    $categoryManager->removeItemFromCart($user_id, $itemId); // Call the method to remove the item
    header("Location: " . $_SERVER['PHP_SELF']); // Refresh the page
    exit();
}

// Calculate total price
foreach ($cartItems as $item) {
    $totalPrice += $item['total_price']; // Calculate total price
}
include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <!-- Bootstrap CSS -->
    <!-- Font Awesome CSS for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            background-color: #f8f9fa;
        }

        .cart-container {
            margin-top: 3rem;
            margin-bottom: 3rem;
        }

        .cart-item {
            background-color: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
            overflow: hidden;
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        }

        .cart-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .product-image {
            max-height: 200px; /* Increased image size */
            object-fit: cover;
            width: 100%;
            border-bottom: 1px solid #dee2e6;
            transition: opacity 0.3s ease-in-out; /* Added opacity transition */
        }

        .product-image:hover {
            opacity: 0.8; /* Slight dim on hover */
        }

        .cart-item-body {
            padding: 1.5rem; /* Increased padding */
        }

        .product-name {
            font-size: 1.5rem; /* Increased font size */
            font-weight: 600;
            color: #343a40;
            margin-bottom: 0.75rem; /* Increased margin */
        }

        .product-details {
            font-size: 1.1rem; /* Increased font size */
            color: #6c757d;
            margin-bottom: 1rem; /* Increased margin */
        }

        .product-price {
            font-size: 1.3rem; /* Increased font size */
            font-weight: 500;
            color: #28a745;
        }

        .remove-button,
        .checkout-button {
            border-radius: 0.375rem;
            padding: 0.6rem 1.2rem; /* Increased padding */
            font-size: 1rem; /* Increased font size */
            font-weight: 500;
            transition: background-color 0.3s ease-in-out, color 0.3s ease-in-out;
        }

        .remove-button {
            background-color: #dc3545;
            color: #fff;
            border: none;
        }

        .remove-button:hover {
            background-color: #c82333;
        }

        .checkout-button {
            background-color: #007bff;
            color: #fff;
            border: none;
        }

        .checkout-button:hover {
            background-color: #0069d9;
        }

        .total-price {
            font-size: 1.7rem; /* Increased font size */
            font-weight: bold;
            color: #28a745;
            padding: 1.2rem; /* Increased padding */
            background-color: #f0f8ff;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
        }

        .checkout-all-button {
            border-radius: 0.5rem;
            padding: 0.8rem 1.6rem; /* Increased padding */
            font-size: 1.2rem; /* Increased font size */
            font-weight: 600;
            background-color: #28a745;
            color: #fff;
            border: none;
            transition: background-color 0.3s ease-in-out;
        }

        .checkout-all-button:hover {
            background-color: #218838;
        }

        .empty-cart-message {
            font-size: 1.3rem; /* Increased font size */
            color: #6c757d;
            padding: 1.2rem; /* Increased padding */
            background-color: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        /* Animation for total price */
        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
            }
        }

        .total-price {
            animation: pulse 2s infinite;
        }



        body, html {
            margin: 0;
            height: 100%;
            background: #eef2ff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .cart-header-fullwidth {
            position: relative;
            width: 100%;
            height: 30vh;
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(59, 130, 246, 0.5);
            user-select: none;
        }
        .cart-header-fullwidth h2 {
            position: relative;
            font-size: 3.75rem;
            font-weight: 800;
            text-shadow: 0 4px 15px rgba(0,0,0,0.4);
            z-index: 2;
            letter-spacing: 0.1em;
        }
        .cart-header-icon-large {
            position: absolute;
            top: 50%;
            left: 50%;
            font-size: 15rem;
            color: rgba(255, 255, 255, 0.12);
            transform: translate(-50%, -50%) rotate(-25deg);
            pointer-events: none;
            user-select: none;
            filter: drop-shadow(0 0 8px rgba(59, 130, 246, 0.7));
            z-index: 1;
        }
        /* Decorative floating circles */
        .circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.15);
            animation: float 6s ease-in-out infinite;
            filter: blur(12px);
            z-index: 0;
        }
        .circle1 {
            width: 120px;
            height: 120px;
            bottom: 10%;
            left: 15%;
            animation-delay: 0s;
        }
        .circle2 {
            width: 180px;
            height: 180px;
            top: 15%;
            right: 20%;
            animation-delay: 2s;
        }
        .circle3 {
            width: 100px;
            height: 100px;
            top: 50%;
            left: 5%;
            animation-delay: 4s;
        }
        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
        }
        @media(max-width: 768px) {
            .cart-header-fullwidth {
                height: 20vh;
            }
            .cart-header-fullwidth h2 {
                font-size: 2.5rem;
                letter-spacing: 0.05em;
            }
            .cart-header-icon-large {
                font-size: 10rem;
                color: rgba(255, 255, 255, 0.08);
            }
            .circle1 {
                width: 80px; height: 80px; bottom: 8%; left: 10%;
            }
            .circle2 {
                width: 120px; height: 120px; top: 12%; right: 10%;
            }
            .circle3 {
                width: 70px; height: 70px; top: 45%; left: 4%;
            }
        }
    </style>

    <header class="cart-header-fullwidth" role="banner" aria-label="Shopping cart header">
        <span class="material-icons cart-header-icon-large" aria-hidden="true">shopping_cart</span>
        <h2>Your Cart</h2>
        <!-- Decorative circles -->
        <div class="circle circle1"></div>
        <div class="circle circle2"></div>
        <div class="circle circle3"></div>
    </header>


    <div class="container cart-container">
        <div class="row">
            <?php if (count($cartItems) > 0): ?>
                <?php foreach ($cartItems as $item): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="cart-item">
                            <img src="dash/<?php echo htmlspecialchars($item['image_paths'][0]); ?>" class="img-fluid product-image" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                            <div class="cart-item-body">
                                <h5 class="product-name"><?php echo htmlspecialchars($item['product_name']); ?></h5>
                                <p class="product-details">
                                    Quantity: <?php echo htmlspecialchars($item['quantity']); ?><br>
                                    Color: <?php echo htmlspecialchars($item['pc']); ?><br>
                                    Size: <?php echo htmlspecialchars($item['size']); ?>
                                </p>
                                <p class="product-price">Price: PKR <?php echo htmlspecialchars($item['total_price']); ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <form method="POST" action="">
                                        <input type="hidden" name="remove_item_id" value="<?php echo htmlspecialchars($item['id']); ?>">
                                        <button type="submit" class="btn remove-button"><i class="fas fa-trash"></i> Remove</button>
                                    </form>
                                    <form method="POST" action="payment.php">
                                        <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item['id']); ?>">
                                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
                                        <input type="hidden" name="item_price" value="<?php echo htmlspecialchars($item['total_price']); ?>">
                                        <input type="hidden" name="item_price_20_percent" value="<?php echo htmlspecialchars($item['total_price'] * 0.20); ?>">
                                        <button type="submit" class="btn checkout-button"><i class="fas fa-shopping-cart"></i> Checkout</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert empty-cart-message" role="alert">
                        Your cart is empty.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</>
</html>
