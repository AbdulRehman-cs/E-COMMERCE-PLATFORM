<?php include "header.php"; ?>

<?php
include_once 'category/CategoryManager.php';

$categoryManager = new CategoryManager();
$categoryId = $_GET['id']; // Assuming the category ID is passed via a GET request
$category = $categoryManager->getCategoryById($categoryId);
// Get the category ID from the URL
$recordsPerPage = 5; // Define how many records to fetch per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Get current page from query string
$offset = ($page - 1) * $recordsPerPage; // Calculate offset for pagination
$products = $categoryManager->getProductsByCategoryId($categoryId, $recordsPerPage, $offset);
// Get total number of products for pagination
$totalProducts = $categoryManager->getTotalProductsByCategoryId($categoryId);
$total_pages = ceil($totalProducts / $recordsPerPage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Category Details</title>
  <link rel="stylesheet" href="styles.css"> <!-- Link to your custom CSS -->
</head><style>
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
<body>

<div hidden id="container">
  <!-- Message will appear here -->
</div>
<div id="popup" class="popup">
  <span class="popup-content"></span>
</div>

<?php
 $imagePath = 'category/' . urlencode($category['Image']) . '.jpg';

$defaultImagePath = 'category/default.jpg'; // Path to a default image

// Check if the image exists
if (!file_exists($imagePath)) {
    $imagePath = $defaultImagePath; // Use default image if specific image does not exist
}
?>
<div class="container-fluid bg-primary py-5 mb-5 page-header" style="background-image: url('category/<?php echo $category['Image']; ?>'); background-size: cover; background-position: center; position: relative;">
  <div class="overlay"></div> <!-- Overlay for better text visibility -->
  <div class="container py-5 position-relative z-index-1">
    <div class="row justify-content-center">
      <div class="col-lg-10 text-center">
        <h1 class="display-3 text-white animated slideInDown"><?=$category ? htmlspecialchars($category['Category_Name']) : 'Subcategory Not Found'; ?></h1>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb justify-content-center">
            <li class="breadcrumb-item"><a class="text-white" href="#">Home</a></li>
            <li class="breadcrumb-item"><a class="text-white" href="#">category</a></li>
            <li class="breadcrumb-item text-white active" aria-current="page"><?=$category ? htmlspecialchars($category['Category_Name']) : 'Subcategory Not Found'; ?></li>
          </ol>
        </nav>
      </div>
    </div>
  </div>
</div>

<style>
  .page-header {
    position: relative;
    overflow: hidden;
  }

  .overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5); /* Dark overlay for better text contrast */
    z-index: 0; /* Behind the text */
  }

  .display-3 {
    font-size: 3rem; /* Adjust font size for better visibility */
    font-weight: 700; /* Bold font weight */
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7); /* Text shadow for depth */
  }

  .breadcrumb {
    background: transparent; /* Make breadcrumb background transparent */
    padding: 0; /* Remove padding */
  }

  .breadcrumb-item a {
    color: #ffffff; /* White color for breadcrumb links */
    transition: color 0.3s; /* Smooth transition for hover effect */
  }

  .breadcrumb-item a:hover {
    color: #ffc107; /* Change color on hover */
  }

  .breadcrumb-item.active {
    color: #ffc107; /* Highlight active breadcrumb */
  }
</style>
<div class="row justify-content-center">
  <div class="col-12 text-center">
    <h6 class="section-subtitle">Vides</h6>
    <h1 class="section-title">Available Products</h1>
  </div>
</div>

<style>
  .section-subtitle {
    display: inline-block;
    background-color: #ffffff;
    color: #0d6efd; /* Primary blue */
    padding: 0.35rem 1.2rem;
    border-radius: 30px;
    font-weight: 700;
    font-size: 1rem;
    letter-spacing: 0.08em;
    box-shadow:
      0 4px 10px rgba(13, 110, 253, 0.25);
    text-transform: uppercase;
    margin-bottom: 0.8rem;
    transition: box-shadow 0.3s ease;
  }
  .section-subtitle:hover {
    box-shadow:
      0 6px 15px rgba(13, 110, 253, 0.45);
  }
  .section-title {
    font-size: 3.5rem;
    font-weight: 900;
    color: #222;
    letter-spacing: -0.02em;
    text-shadow: 2px 2px 8px rgba(13, 110, 253, 0.15);
    margin-bottom: 2.5rem;
    font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }
  /* Responsive adjustments */
  @media (max-width: 768px) {
    .section-title {
      font-size: 2.5rem;
      margin-bottom: 1.8rem;
    }
    .section-subtitle {
      font-size: 0.9rem;
      padding: 0.3rem 1rem;
    }
  }
</style>

     <div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-md-8 m-4 p-2">
            <div class="row g-4">
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
                        <div class="col-lg-4 col-md-6 d-flex align-items-stretch">
                            <div class="product-item bg-white rounded shadow-sm h-100 d-flex flex-column">
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
                                        Price per piece: $<?php echo number_format($d3PricePerPiece, 2); ?> - $<?php echo number_format($d1PricePerPiece, 2); ?>
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

            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a>
                        </li>
                        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                            <li class="page-item <?= $p == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $p])); ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" aria-label="Next"><span aria-hidden="true">&raquo;</span></a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .product-item {
        transition: transform 0.3s, box-shadow 0.3s;
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


<?include 'footer.php'?>