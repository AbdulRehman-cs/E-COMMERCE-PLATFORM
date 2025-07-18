<?php
// Start the session to manage user login state


if (!isset($_COOKIE['user_id'])) {
    $type='button';
}else{
    $userId = $_COOKIE['user_id'];
    $type='submit';}
include 'category/CategoryManager.php';
$categoryManager = new CategoryManager();
$allCategories = $categoryManager->getCategories();
$message = '';

// Get the product ID from GET parameter "id"
$product_id = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : null;

if ($product_id === null) {
    die('Error: Product ID is required and must be a valid number.');
}

// Fetch single product by ID
$product = $categoryManager->getProductById($product_id);
if (!$product) {
    echo 'Product not found.';
    exit;
}

// Fetch discounts for the product
$discounts = $categoryManager->getDiscountsByProductId($product_id);

// Initialize variables
$selectedQuantity = 1; // Default quantity
$finalPrice = $product['price']; // Default price

// Determine the applicable price based on quantity
foreach ($discounts as $discount) {
    if ($selectedQuantity >= $discount['quantity']) {
        $finalPrice = $discount['final_price'] / $discount['quantity'];
    }
}

// Calculate total price
$totalPrice = $finalPrice * $selectedQuantity;

// Handle adding to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $color = $_POST['color'] ?? '';
    $size = $_POST['size'] ?? '';
    $selectedQuantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    // Determine applicable discount
    foreach ($discounts as $discount) {
        if ($selectedQuantity >= $discount['quantity']) {
            $finalPrice = $discount['final_price'];
        }
    }

if (!isset($_COOKIE['user_id'])) {
    $type='button';
}else{
    $userId = $_COOKIE['user_id'];
    $type='submit';
}// Assuming user is logged in

$addToCartResult = $categoryManager->addToCart($userId, $product_id, $color, $size, $selectedQuantity, $finalPrice);
if ($addToCartResult) {
    // Success! Redirect to cart.php
    header('Location: cart.php');
    exit();
} else {
    // Error!  Display an error message to the user.
    echo "Error adding item to cart. Please try again."; // Or a more user-friendly message
    // Optionally, log the error for debugging:
    error_log("Error adding to cart: userId=$userId, productId=$product_id");
    }
}

// Fetch reviews
$reviews = $categoryManager->getReviewsByProductId($product_id);



// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['hlo'])) {
            // Existing add to cart logic...
        } elseif (isset($_POST['delete_review'])) {
            $reviewId = intval($_POST['review_id']);
            if ($categoryManager->deleteReview($reviewId)) {
                $message = "Review deleted successfully!";
            } else {
                throw new Exception("Failed to delete review.");
            }
        } elseif (isset($_POST['review'])) {
            $productId = isset($_GET['id']) ? intval($_GET['id']) : null;
            $email = trim($_POST['email']);
            $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
            $comment = trim($_POST['review']);
            $imagePaths = []; // Initialize image paths array

            // Handle file uploads
            if (isset($_FILES['images'])) {
                foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                    if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                        $fileName = basename($_FILES['images']['name'][$key]);
                        $targetPath = "uploads/" . uniqid() . "_" . $fileName; // Use unique filename
                        if (!move_uploaded_file($tmpName, $targetPath)) {
                            throw new Exception("Failed to move uploaded file: " . $fileName);
                        }
                        $imagePaths[] = $targetPath; // Store the path of the uploaded image
                    } else {
                        throw new Exception("Error uploading file: " . $_FILES['images']['error'][$key]);
                    }
                }
            }

            // Validate input
            if ($productId && filter_var($email, FILTER_VALIDATE_EMAIL) && $rating > 0 && !empty($comment)) {
                // Submit the review and get the review ID
                $reviewId = $categoryManager->submitReview($productId, $userId, $email, $rating, $comment, $imagePaths);
                
                if ($reviewId) {
                    // Call the function to upload review images
                    $categoryManager->uploadReviewImages($reviewId, $imagePaths);
                    $message = "Thank you for your review!";
                } else {
                    throw new Exception("There was an error submitting your review. Please try again.");
                }
            } else {
                throw new Exception("Please fill in all fields correctly.");
            }
        }
    } catch (Exception $e) {
        $message = $e->getMessage(); // Capture the error message
    }

    // Refresh the page after processing
    header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $product_id); // Redirect to the same page with the product ID
    exit(); // Ensure no further code is executed after the redirect
}


?>

<!-- Include this message in your HTML to show feedback -->
<?php if (isset($message)): ?>
    <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>



<!DOCTYPE html>
<html lang="en">
<?php include "header.php"; ?>

<!-- Breadcrumb Start -->
<div class="container-fluid">
    <div class="row px-xl-5">
        <div class="col-12">
            <nav class="breadcrumb bg-light mb-30">
                <a class="breadcrumb-item text-dark" href="#">Home</a>
                <a class="breadcrumb-item text-dark" href="#">Shop</a>
                <span class="breadcrumb-item active">Shop Detail</span>
            </nav>
        </div>
    </div>
</div>
<!-- Breadcrumb End -->

<!-- Shop Detail Start -->
<div class="container-fluid pb-5">
    <div class="row px-xl-5">
        <div class="col-lg-5 mb-30">

            <!-- Main Carousel -->
            <div id="product-carousel" class="carousel slide" data-ride="carousel">
    <div class="carousel-inner bg-light">
        <?php foreach ($product['image_paths'] as $index => $image): ?>
            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                <img class="d-block w-100" src="dash/<?php echo htmlspecialchars($image); ?>" alt="Image <?php echo $index + 1; ?>" style="height: 400px; object-fit: cover;">
            </div>
        <?php endforeach; ?>
    </div>
    <a class="carousel-control-prev" href="#product-carousel" role="button" data-slide="prev">
        <i class="fa fa-2x fa-angle-left text-dark"></i>
    </a>
    <a class="carousel-control-next" href="#product-carousel" role="button" data-slide="next">
        <i class="fa fa-2x fa-angle-right text-dark"></i>
    </a>
</div>

<!-- Thumbnail Carousel -->
<div class="carousel-thumbnails mt-3 d-flex justify-content-center">
    <?php foreach ($product['image_paths'] as $index => $image): ?>
        <button type="button" class="btn p-0 mx-1" data-target="#product-carousel" data-slide-to="<?php echo $index; ?>">
            <img src="dash/<?php echo htmlspecialchars($image); ?>" alt="Thumbnail <?php echo $index + 1; ?>" class="img-thumbnail" style="width: 100px; height: auto;">
        </button>
    <?php endforeach; ?>
</div>

<script>
    // Optional: Add functionality to update the main carousel when a thumbnail is clicked
    document.querySelectorAll('.carousel-thumbnails button').forEach((button, index) => {
        button.addEventListener('click', () => {
            $('#product-carousel').carousel(index);
        });
    });
</script>


        </div>

        <div class="col-lg-7 h-auto mb-30">
            <div class="row">
    <div class="col-lg-7 h-auto mb-30">
        <div class="h-100 bg-light p-30">
            <h3 class="bg-warning p-2 align-center"><?php echo htmlspecialchars($product['product_name']); ?></h3>
            <h3 class="font-weight-semi-bold mb-4">
                <?php if ($finalPrice < $product['price']): ?>
                    <span class="text-muted" style="text-decoration: line-through;">$<?php echo number_format($product['price'], 2); ?></span>
                <?php endif; ?>
                <span id="final-price"><?php echo number_format($finalPrice, 2); ?></span>
            </h3>
            <p class="mb-4">                                        <?php echo htmlspecialchars(strlen($product['description']) > 90 ? substr($product['description'], 0, 87) . '...' : $product['description']); ?>
</p>

            <form method="POST">
                <div class="d-flex mb-3">
                    <strong class="text-dark mr-3">Sizes:</strong>
                    <?php foreach ($product['sizes'] as $size): ?>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" class="custom-control-input" id="size-<?php echo htmlspecialchars($size); ?>" name="size" value="<?php echo htmlspecialchars($size); ?>">
                            <label class="custom-control-label" for="size-<?php echo htmlspecialchars($size); ?>"><?php echo htmlspecialchars($size); ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="d-flex mb-4">
                    <strong class="text-dark mr-3">Colors:</strong>
                    <?php foreach ($product['colors'] as $color): ?>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" class="custom-control-input" id="color-<?php echo htmlspecialchars($color); ?>" name="color" value="<?php echo htmlspecialchars($color); ?>">
                            <label class="custom-control-label" for="color-<?php echo htmlspecialchars($color); ?>"><?php echo htmlspecialchars($color); ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="d-flex mb-3">
                    <strong class="text-dark mr-3">Quantity:</strong>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" class="form-control" style="width: 60px;" onchange="updateTotalPrice()">
                </div>
                <div class="d-flex mb-3">
                    <strong class="text-dark mr-3">Total Price:</strong>
                    <span id="total-price" class="font-weight-bold">PKR<?php echo number_format($totalPrice, 2); ?></span>
                </div>

                <button type="<?php echo $type?>" name="add_to_cart" class="btn btn-primary px-3" onclick="checkLogin()" ><i class="fa fa-shopping-cart mr-1"></i> Add To Cart</button>
            </form>

            <?php if ($message): ?>
                <div class="alert alert-success mt-3"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-5">
<div class="row">
            <?php if (!empty($discounts)): ?>
                <?php foreach ($discounts as $discount): ?>
                    <div class="col-md-9 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Buy <?php echo htmlspecialchars($discount['quantity']); ?> or more</h5>
                                <p class="card-text">Price per unit: PKR<?php echo number_format($discount['final_price'] / $discount['quantity'], 2); ?></p>
                                <p class="card-text">Total Price: PKR<?php echo number_format($discount['final_price'], 2); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-warning">No discounts available for this product.</div>
                </div>
            <?php endif; ?>

            </div></div>
    
    </div></div></div>
    <div class="row px-xl-5">
        <div class="col">
            <div class="bg-light p-30">
                <div class="nav nav-tabs mb-4">
                    <a class="nav-item nav-link text-dark active" data-toggle="tab" href="#tab-pane-1">Description</a>
                    <a class="nav-item nav-link text-dark" data-toggle="tab" href="#tab-pane-2">Information</a>
                    <a class="nav-item nav-link text-dark" data-toggle="tab" href="#tab-pane-3">Reviews (<?php echo count($reviews); ?>)</a>
                </div>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="tab-pane-1">
                        <h4 class="mb-3">Product Description</h4>
                        <p><?php echo htmlspecialchars($product['description']); ?></p>
                    </div>
                    <div class="tab-pane fade" id="tab-pane-2">
                        <h4 class="mb-3">Additional Information</h4>
                        <p>Additional details about the product can be added here.</p>

                        <h5 class="mt-4">Discount Levels</h5>
                        <?php if (!empty($discounts)): ?>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Quantity</th>
                                        <th>Final Price</th>
                                        <th>Price per Unit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($discounts as $discount): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($discount['quantity']); ?></td>
                                            <td>PKR <?php echo number_format($discount['final_price'], 2); ?></td>
                                            <td>PKR <?php echo number_format($discount['final_price'] / $discount['quantity'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>No discounts available for this product.</p>
                        <?php endif; ?>
                    </div>

                    <div class="tab-pane fade" id="tab-pane-3">
                       
                    
        <h4 class="mb-4">Reviews</h4>
        <div class="row">
            <div class="col-md-6">
                <h5>Existing Reviews</h5>
                <?php foreach ($reviews as $review): ?>
                    <div class="media mb-4">
                        <div class="media-body">
                            <h6><?php echo htmlspecialchars($review['retailer']['full_name']); ?><small> - <i><?php echo htmlspecialchars($review['date']); ?></i></small></h6>
                            <div class="text-primary mb-2">
                                <?php for ($i = 0; $i < $review['rating']; $i++): ?>
                                    <i class="fas fa-star"></i>
                                <?php endfor; ?>
                            </div>
                            <p><?php echo htmlspecialchars($review['comment']); ?></p>
                            <?php if (!empty($review['images'])): ?>
                                <div class="review-images mt-2">
                                    <?php foreach ($review['images'] as $image): ?>
                                        <img src="<?php echo htmlspecialchars($image); ?>" alt="Review Image" class="img-fluid" style="max-width: 100px; margin-right: 5px;">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($review['user_name'] == $userId): ?>
                                <form method="POST" action="">
                                    <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                    <button type="submit" name="delete_review" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="col-md-6">
                <h5>Leave a Review</h5>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="review">Your Review *</label>
                        <textarea id="review" name="review" cols="30" rows="5" class="form-control" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="email">Your Email *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="rating">Your Rating *</label>
                        <select id="rating" name="rating" class="form-control" required>
                            <option value="">Select Rating</option>
                            <option value="1">1 Star</option>
                            <option value="2">2 Stars</option>
                            <option value="3">3 Stars</option>
                            <option value="4">4 Stars</option>
                            <option value="5">5 Stars</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="images">Upload Images</label>
                        <input type="file" class="form-control" id="images" name="images[]" multiple>
                    </div>
                    <div class="form-group mb-0">
                        <input type="<?php echo $type?>" value="Leave Your Review" onclick="checkLogin()" class="btn btn-primary px-3">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function populateUpdateForm(review) {
        document.getElementById('review').value = review.comment;
        document.getElementById('rating').value = review.rating;
        // Optionally, you can handle image previews if needed
    }
</script>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Shop Detail End -->

<div class="container-fluid">
    <div class="row px-xl-5">
        <div class="col-lg-12">
            <h4 class="section-title position-relative text-uppercase mb-3">
                <span class="bg-secondary pr-1">Customer Reviews</span>
            </h4>
            <div class="bg-light p-30 mb-5">
                <div id="reviewCarousel" class="carousel slide" data-ride="carousel">
                    <div class="carousel-inner">
                        <?php if (!empty($reviews)): ?>
                            <?php foreach ($reviews as $index => $review): ?>
                                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?> text-center">
                                    <?php if (!empty($review['images'])): ?>
                                        <img src="<?php echo htmlspecialchars($review['images'][0]); ?>" alt="Review Image" class="img-fluid user-icon" style="max-width: 100px; border-radius: 50%;">
                                    <?php else: ?>
                                        <i class="fas fa-user-circle user-icon"></i>
                                    <?php endif; ?>
                                    <p><?php echo htmlspecialchars($review['comment']); ?></p>
                                    <div class="stars">
                                        <?php for ($i = 0; $i < $review['rating']; $i++): ?>
                                            <span>⭐</span>
                                        <?php endfor; ?>
                                    </div>
                                    <footer>- <?php echo htmlspecialchars($review['retailer']['full_name']); ?></footer>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="carousel-item active text-center">
                                <p>No reviews available.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <!-- Controls -->
                    <a class="carousel-control-prev" href="#reviewCarousel" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="sr-only">Previous</span>
                    </a>
                    <a class="carousel-control-next" href="#reviewCarousel" role="button" data-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="sr-only">Next</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Add this modal HTML somewhere in your page -->


<script>
    // Sample discounts array (this should be populated dynamically from PHP)
    const discounts = <?php echo json_encode($discounts); ?>;

    function updateTotalPrice() {
        const quantity = parseInt(document.getElementById('quantity').value); // Corrected 'parse int' to 'parseInt'
        const basePrice = <?php echo $product['price']; ?>;
        let finalPrice = basePrice;

        // Check for applicable discount
        for (let discount of discounts) {
            if (quantity >= discount.quantity) {
                finalPrice = discount.final_price / discount.quantity; // Price per unit after discount
            }
        }

        // Calculate total price
        const totalPrice = finalPrice * quantity;

        // Update the total price display
        document.getElementById('total-price').innerText = 'PKR ' + totalPrice.toFixed(2);
        document.getElementById('final-price').innerText = 'PKR  ' + finalPrice.toFixed(2); // Update final price display
    }

    // Initial call to set the total price on page load
    updateTotalPrice();
</script>


<!-- Products End -->

<?php include "footer.php"; ?>