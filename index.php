
<?php include 'category/CategoryManager.php';

$categoryManager = new CategoryManager();
$allCategories = $categoryManager->getCategories();
$message = '';



$reviews = $categoryManager->getHighRatedReviews();


?>

<?php
// Example usage: Make sure $conn is your valid MySQLi connection
$search_param = isset($_GET['search']) ? $_GET['search'] : '';
$records_per_page = 16;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $records_per_page;
$products = $categoryManager->getProducts( $search_param, $records_per_page, $offset);
?>


<!DOCTYPE html>
<html lang="en">

<?php include "header.php" ?>

 
    <!-- Carousel Start -->
    <div class="container-fluid mb-3">
        <div class="row px-xl-5">
            <div class="col-lg-8">
                <div id="header-carousel" class="carousel slide carousel-fade mb-30 mb-lg-0" data-ride="carousel">
                    <ol class="carousel-indicators">
                        <li data-target="#header-carousel" data-slide-to="0" class="active"></li>
                        <li data-target="#header-carousel" data-slide-to="1"></li>
                        <li data-target="#header-carousel" data-slide-to="2"></li>
                    </ol>
                    <div class="carousel-inner">
                        <div class="carousel-item position-relative active" style="height: 430px;">
                            <img class=" w-100 h-100" src="img/carousel-1.jpg" style="object-fit: cover;">
                            <div class="carousel-caption d-flex flex-column align-items-center justify-content-center">
                                <div class="p-3" style="max-width: 700px;">
                                    <h1 class="display-4 mb-3 animated-heading animate__fadeInDown"  style="color:#F8D244;;" id="typingText">
                                        Learn from RazaBaba
                                    </h1>
                                    
                                    <script>
                                        document.addEventListener("DOMContentLoaded", function () {
                                            let textElement = document.getElementById("typingText");
                                            let originalText = textElement.innerText;
                                            let index = originalText.length;
                                            let isDeleting = true;
                                    
                                            function typeEffect() {
                                                if (isDeleting) {
                                                    if (index >= 0) {
                                                        textElement.innerText = originalText.substring(0, index);
                                                        index--;
                                                        setTimeout(typeEffect, 150); // Speed of deletion
                                                    } else {
                                                        isDeleting = false;
                                                        setTimeout(typeEffect, 750); // Pause before retyping
                                                    }
                                                } else {
                                                    if (index < originalText.length) {
                                                        textElement.innerText = originalText.substring(0, index + 1);
                                                        index++;
                                                        setTimeout(typeEffect, 150); // Speed of typing
                                                    } else {
                                                        isDeleting = true;
                                                        setTimeout(typeEffect, 750); // Pause before deleting
                                                    }
                                                }
                                            }
                                    
                                            typeEffect(); // Start animation
                                        });
                                    </script>
                                    
                                   
                                    <p class="mx-md-5 px-5 animate__animated animate__bounceIn">Unlock The leading B2B ecommerce platform for global trade</p>
                                    <a class="btn btn btn-primary animate__animated animate__fadeInUp" class="btn btn-primary" href="#">Quotation Now</a>
                                </div>
                            </div>
                        </div>
                        <div class="carousel-item position-relative" style="height: 430px;">
                            <img class=" w-100 h-100" src="img/carousel-2.jpg" style="object-fit: cover;">
                            <div class="carousel-caption d-flex flex-column align-items-center justify-content-center">
                                <div class="p-3" style="max-width: 700px;">
                                    <h1 class="display-4 text- mb-3 animate__animated animate__fadeInDown   " style="color:#F8D244;;">Partner</h1>
                                    <p class="mx-md-5 px-5 animate__animated animate__bounceIn">Your Trusted Partner for B2B Wholesale Solutions</p>
                                    <a class=" btn btn-primary py-2 px-4 mt-3 animate__animated animate__fadeInUp" href="#">Quotation Now</a>
                                </div>
                            </div>
                        </div>
                        <div class="carousel-item position-relative" style="height: 430px;">
                            <img class="w-100 h-100" src="img/carousel-3.jpg" style="object-fit: cover;">
                            <div class="carousel-caption d-flex flex-column align-items-center justify-content-center">
                                <div class="p-3" style="max-width: 700px;">
                                    <h1 class="display-4 text-yellow mb-3 animate__animated animate__fadeInDown" style="color:#F8D244;;">Streamline Your Orders</h1>
                                    <p class="mx-md-5 px-5 animate__animated animate__bounceIn">Future-Proof Your Business with Exclusive Bulk Pricing</p>
                                    <a class=" btn btn-primary py-2 px-4 mt-3 animate__animated animate__fadeInUp" href="#">Quotation Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="product-offer mb-30" style="height: 200px;">
                    <img class="img-fluid" src="img/offer-1.jpg" alt="">
                    <div class="offer-text">
                        <!-- Updated h6 Heading Here -->
                        <h6 class="text-white text-uppercase">Exclusive Offer: Limited Time Only!</h6>
                        <h3 class="text-white mb-3">Stock up and save more!</h3>
                        <a href="" class="btn btn-primary">Shop Now</a>
                    </div> 
                </div>
                <div class="product-offer mb-30" style="height: 200px;">
                    <img class="img-fluid" src="img/offer-2.jpg" alt="">
                    <div class="offer-text">
                        <!-- Updated h6 Heading Here -->
                        <h6 class="text-white text-uppercase">Hurry! Grab the Best Deals on Bulk Orders!</h6>
                        <h3 class="text-white mb-3">Stock up and save more!</h3>
                        <a href="" class="btn btn-primary">Shop Now</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Carousel End -->


    <!-- Featured Start -->
    <div class="container-fluid pt-5">
        <div class="row px-xl-5 pb-3">
            <div class="col-lg-3 col-md-6 col-sm-12 pb-1">
                <div class="d-flex align-items-center bg-light mb-4 service-box" style="padding: 30px;">
                    <h1 class="fa fa-check text-primary m-0 mr-3"></h1>
                    <h5 class="font-weight-semi-bold m-0">Quality Product</h5>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12 pb-1">
                <div class="d-flex align-items-center bg-light mb-4 service-box" style="padding: 30px;">
                    <h1 class="fa fa-shipping-fast text-primary m-0 mr-2"></h1>
                    <h5 class="font-weight-semi-bold m-0">Fast Shipping</h5>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12 pb-1">
                <div class="d-flex align-items-center bg-light mb-4 service-box" style="padding: 30px;">
                    <h1 class="fas fa-exchange-alt text-primary m-0 mr-3"></h1>
                    <h5 class="font-weight-semi-bold m-0">4-7 Delivery Days </h5>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12 pb-1">
                <div class="d-flex align-items-center bg-light mb-4 service-box" style="padding: 30px;">
                    <h1 class="fa fa-phone-volume text-primary m-0 mr-3"></h1>
                    <h5 class="font-weight-semi-bold m-0">24/6 Support</h5>
                </div>
            </div>
        </div>
    </div>
    <!-- Featured End -->


    <!-- Categories Start -->
  <div class="container-fluid pt-5">
    <h2 class="section-title position-relative text-uppercase mx-xl-5 mb-4">
        <span class="bg-secondary pr-3">Categories</span>
    </h2>
    
    <div class="row px-xl-5 pb-3">
        <?php foreach ($allCategories as $category): ?>
            <div class="col-lg-3 col-md-4 col-sm-6 pb-1">
                <a class="text-decoration-none" href="category.php?id=<?php echo $category['Category_ID']; ?>">
                    <div class="cat-item d-flex align-items-center mb-4 border rounded shadow-sm overflow-hidden transition-transform transform-hover">
                        <div class="overflow-hidden" style="width: 100px; height: 100px;">
                            <img class="img-fluid" src="category/<?php echo $category['Image']; ?>" alt="" style="transition: transform 0.3s;">
                        </div>
                        <div class="flex-fill pl-3">
                            <h6 class="font-weight-bold text-dark"><?php echo $category['Category_Name']; ?></h6>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
    .cat-item {
        background-color: #ffffff; /* White background for each category item */
        transition: transform 0.3s, box-shadow 0.3s; /* Smooth transition for hover effects */
        padding: 15px; /* Padding for better spacing */
    }

    .cat-item:hover {
        transform: translateY(-5px); /* Lift effect on hover */
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); /* Shadow effect on hover */
    }

    .cat-item img {
        transition: transform 0.3s; /* Smooth image scaling */
    }

    .cat-item:hover img {
        transform: scale(1.1); /* Scale image on hover */
    }

    .section-title {
        font-size: 2rem; /* Larger title font size */
        color: #333; /* Darker color for better contrast */
    }

    h6 {
        font-size: 1.1rem; /* Slightly larger font size for category names */
        color: #555; /* Dark gray color for text */
    }
</style>

    <!-- Categories End -->


<!-- Products Start -->


<!-- Products End -->
<div class="container-fluid p-1">
     <h2 class="section-title position-relative text-uppercase mx-xl-5 mb-4">
        <span class="bg-secondary pr-3">Products</span>
    </h2>
    
    <div class="row justify-content-center">
        <div class="col-lg-10 col-md-8 m-4 p-2">
            <div class="row  m-6 p-3"> <br>
                <?php if (empty($products)): ?>
                    <p class="text-center fs-5">No products found for your criteria.</p>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <?php
                        // Gather and sort discount data ascending by final price
                        $discounts = [];
                        for ($i = 0; $i < count($product['discount_quantities']); $i++) {
                            $discounts[] = [
                                'quantity' => $product['discount_quantities'][$i],
                                'final_price' => $product['final_prices'][$i],
                            ];
                        }
                        usort($discounts, fn($a, $b) => $a['final_price'] <=> $b['final_price']);
                        
                        // Assign D1 and D3 discounts
                        $d1 = $discounts[0] ?? null;
                        $d3 = $discounts[2] ?? $d1;

                        // Calculate price per piece safely
                        $d1PricePerPiece = $d1 ? $d1['final_price'] / max($d1['quantity'], 1) : 0;
                        $d3PricePerPiece = $d3 ? $d3['final_price'] / max($d3['quantity'], 1) : 0;

                        // Prepare colors and sizes arrays safely
                        $colors = !empty($product['colors']) ? $product['colors'] : [];
                        $sizes = !empty($product['sizes']) ? $product['sizes'] : [];
                        ?>
                        <div class="col-lg-3 col-md-6 d-flex align-items-stretch p-1">
                            <div class="product-item bg-white rounded shadow-sm h-100 d-flex flex-column mb-5">
                                <div class="product-img position-relative overflow-hidden rounded-top" style="height: 250px;">
                                    <img class="img-fluid w-100 h-100" style="object-fit: cover;" src="dash/<?php echo !empty($product['image_paths']) ? htmlspecialchars($product['image_paths'][0]) : 'img/default-product.jpg'; ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                                    <div class="product-action position-absolute top-0 end-0 m-2 d-flex gap-1">
                                        <a href="product_detail.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-outline-dark btn-square" aria-label="Add to cart"><i class="fa fa-shopping-cart"></i></a>
                                        <a href="product_detail.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-outline-dark btn-square" aria-label="Add to wishlist"><i class="far fa-heart"></i></a>
                                        <a href="product_detail.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-outline-dark btn-square" aria-label="Compare"><i class="fa fa-sync-alt"></i></a>
                                        <a href="product_detail.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-outline-dark btn-square" aria-label="View details"><i class="fa fa-search"></i></a>
                                    </div>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h2 class="product-name">
                                        <a href="product.php?id=<?php echo $product['product_id']; ?>" class="text-decoration-none text-dark">
                                            <?php echo htmlspecialchars($product['product_name']); ?>
                                        </a>
                                    </h2>
                                    <p class="product-description" title="<?php echo htmlspecialchars($product['description']); ?>">
                                        <?php echo htmlspecialchars(strlen($product['description']) > 90 ? substr($product['description'], 0, 87) . '...' : $product['description']); ?>
                                    </p>

                                    <?php if (count($colors) > 0): ?>
                                        <div class="colors-container" aria-label="Available colors">
                                            <strong>Colors: </strong>
                                            <?php foreach ($colors as $color): ?>
                                                <?php 
                                                // Normalize color name to hex code or fallback to gray
                                                $colorLower = strtolower($color);
                                                // Basic common colors, add more as needed
                                                $colorMap = [
                                                    'red' => '#ff4d4f',
                                                    'blue' => '#1890ff',
                                                    'green' => '#52c41a',
                                                    'yellow' => '#fadb14',
                                                    'black' => '#000000',
                                                    'white' => '#ffffff',
                                                    'orange' => '#fa8c16',
                                                    'purple' => '#722ed1',
                                                    'gray' => '#8c8c8c',
                                                ];
                                                $colorHex = $colorMap[$colorLower] ?? '#6c757d';
                                                ?>
                                                <span class="color-dot" style="background-color: <?php echo htmlspecialchars($colorHex); ?>" title="<?php echo htmlspecialchars(ucfirst($color)); ?>"></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (count($sizes) > 0): ?>
                                        <div class="sizes-container" aria-label="Available sizes">
                                            <strong>Sizes: </strong>
                                            <?php foreach ($sizes as $size): ?>
                                                <span class="badge"><?php echo htmlspecialchars($size); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="price-per-piece-range mt-2" aria-label="Price per piece range">
                                        Price per piece: PKR <?php echo number_format($d3PricePerPiece, 2); ?> - PKR <?php echo number_format($d1PricePerPiece, 2); ?>
                                    </div>

                                    <a href="product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-primary mt-3" aria-label="Buy <?php echo htmlspecialchars($product['product_name']); ?>">
                                        Buy Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

           
        </div>
    </div>
    
</div>

<style>
    .product-item {
        transition: transform 0.3s, box-shadow 0.3s;
        height: 100%; /* Ensure all cards have the same height */
    }
    .product-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }
    .color-dot {
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: inline-block;
        margin: 0 5px 5px 0;
        border: 1.5px solid #ddd;
    }
    .color-dot[style*="background-color: #ffffff"], 
    .color-dot[style*="background-color: #fff"] {
        border: 1.5px solid #999;
    }
</style>
<style>
  body { background: #f8f9fa; }
  .product-item { background: #fff; border-radius: 10px; transition: box-shadow 0.3s; }
  .product-item:hover { box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
  .product-img { position: relative; overflow: hidden; border-radius: 10px 10px 0 0; height: 250px; }
  .product-img img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease; }
  .product-item:hover .product-img img { transform: scale(1.05); }
  .product-action a { margin: 0 6px; }
  .price-range { font-weight: 700; color: #0d6efd; }
  .badge-filter-selected { background: #0d6efd !important; color: white !important; }

  .product-item {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgb(0 0 0 / 0.1);
    transition: box-shadow 0.3s ease;
    display: flex;
    flex-direction: column;
    height: 100%;
    text-align: center;
  }
  .product-item:hover {
    box-shadow: 0 12px 30px rgb(0 0 0 / 0.15);
  }
  .product-img {
    position: relative;
    overflow: hidden;
    border-radius: 10px 10px 0 0;
    height: 250px;
  }
  .product-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
  }
  .product-item:hover .product-img img {
    transform: scale(1.05);
  }
  .product-action a {
    margin: 0 4px;
  }
  .card-body {
    flex-grow: 1;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }
  .product-name {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
  }
  .product-description {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 1rem;
    min-height: 48px; /* to keep consistent height for descriptions */
  }
  .colors-container, .sizes-container {
    margin-bottom: 0.75rem;
  }
  .colors-container strong, .sizes-container strong {
    display: block;
    margin-bottom: 0.3rem;
    font-weight: 600;
    color: #333;
  }
  .color-dot {
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: inline-block;
    margin: 0 5px 5px 0;
    border: 1.5px solid #ddd;
  }
  /* Add white border for white color to be visible */
  .color-dot[style*="background-color: #ffffff"], 
  .color-dot[style*="background-color: #fff"] {
    border: 1.5px solid #999;
  }
  .sizes-container .badge {
    margin: 0 5px 5px 0;
    padding: 0.35em 0.7em;
    font-size: 0.8rem;
    font-weight: 600;
    background: #e9ecef;
    color: #495057;
    border-radius: 0.375rem;
    display: inline-block;
  }
  .price-per-piece-range {
    font-size: 1.1rem;
    font-weight: 700;
    color: #0d6efd;
    margin-top: auto;
  }
  .btn-buy {
    margin-top: 1rem;
    background-color: #0d6efd;
    border: none;
    color: white;
    padding: 0.45rem 1.1rem;
    border-radius: 0.375rem;
    font-weight: 700;
    transition: background-color 0.3s ease;
  }
  .btn-buy:hover {
    background-color: #084ddb;
    color: white;
  }
</style>

    <!-- Offer Start -->
    <div class="container-fluid pt-5 pb-3">
        <div class="row px-xl-5">
            <div class="col-md-6">
                <div class="product-offer mb-30" style="height: 300px;">
                    <img class="img-fluid" src="img/offer-1.jpg" alt="">
                    <div class="offer-text">
                        <h6 class="text-white text-uppercase">Buy in bulk and enjoy  </h6>
                        <h3 class="text-white mb-3"> Bigger orders, Bigger savingsr</h3>
                        <a href="" class="btn btn-primary">Shop Now</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="product-offer mb-30" style="height: 300px;">
                    <img class="img-fluid" src="img/offer-2.jpg" alt="">
                    <div class="offer-text">
                        <h6 class="text-white text-uppercase">Buy in bulk and enjoy</h6>
                        <h3 class="text-white mb-3">Stock up and save more!</h3>
                        <a href="" class="btn btn-primary">Shop Now</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Offer End -->
<!-- Customer Reviews Section Start -->
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


<!-- Customer Reviews Section End -->


<!-- Font Awesome for Icons -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<!-- Products Start -->
    </div>
    <!-- Vendor Start -->
 

    <!-- Vendor End -->
    <?php include "footer.php" ?>