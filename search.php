<?php include "header.php"; ?>

<?php
include_once 'category/CategoryManager.php';

$categoryManager = new CategoryManager();

// Get the search term from the query string
$searchTerm = isset($_GET['query']) ? trim($_GET['query']) : '';

// Define how many records to fetch per page
$recordsPerPage = 5; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Get current page from query string
$offset = ($page - 1) * $recordsPerPage; // Calculate offset for pagination

// Get products based on search term
if (!empty($searchTerm)) {
    $products = $categoryManager->searchProducts($searchTerm, $recordsPerPage, $offset);
}

// Get total number of products for pagination
$totalProducts = !empty($searchTerm) ? $categoryManager->getTotalProductsBySearch($searchTerm) : $categoryManager->getTotalProductsByCategoryId($categoryId);
$total_pages = ceil($totalProducts / $recordsPerPage);

/**
 * Highlight all occurrences of $searchTerm inside $text (case-insensitive),
 * safe for usage with HTML entities.
 * Wrap matches in <span class="highlight"> for styling.
 */
function highlightTerms(string $text, string $searchTerm): string {
    if (trim($searchTerm) === '') {
        return htmlspecialchars($text);
    }

    // Escape the search term for use in regex, split by spaces for multiple terms
    $terms = array_filter(preg_split('/\s+/', $searchTerm));
    if (count($terms) === 0) {
        return htmlspecialchars($text);
    }

    $escapedTerms = array_map(function($term){
        return preg_quote($term, '/');
    }, $terms);

    // Build regex to match any of the terms, word-boundaried for better relevance
    $pattern = '/(' . implode('|', $escapedTerms) . ')/i';

    // Escape text to prevent XSS
    $escapedText = htmlspecialchars($text);

    // Callback function for preg_replace_callback
    $result = preg_replace_callback($pattern, function($matches) {
        return '<span class="highlight">' . $matches[0] . '</span>';
    }, $escapedText);

    return $result === null ? $escapedText : $result;
}
?>

<!-- Search Form -->
<div class="container">
    <form method="GET" action="">
        <input type="hidden" name="id" value="<?php echo isset($categoryId) ? htmlspecialchars($categoryId) : ''; ?>">
        <div class="input-group mb-3">
            <input type="text" class="form-control" name="query" placeholder="Search products..." value="<?php echo htmlspecialchars($searchTerm); ?>">
            <button class="btn btn-primary" type="submit"></button>
        </div>
    </form>
</div>

<!-- Search Term Display -->
<div class="row justify-content-center">
  <div class="col-12 text-center">
    <h6 class="section-subtitle">Vides</h6>
    <?php if ($searchTerm): ?>
      <h1 class="section-title">Search results for: <?php echo htmlspecialchars($searchTerm); ?></h1>
    <?php else: ?>
      <h1 class="section-title">All Products</h1>
    <?php endif; ?>
  </div>
</div>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-md-8 m-4 p-2">
            <div class="row g-4">
                <?php if (empty($products)): ?>
                    <p class="text-center fs-5">No products found for your criteria.</p>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <?php
                        // Discounts sorting as before
                        $discounts = [];
                        for ($i = 0; $i < count($product['discount_quantities']); $i++) {
                            $discounts[] = [
                                'quantity' => $product['discount_quantities'][$i],
                                'final_price' => $product['final_prices'][$i],
                            ];
                        }
                        usort($discounts, fn($a, $b) => $a['final_price'] <=> $b['final_price']);

                        $d1 = $discounts[0] ?? null;
                        $d3 = $discounts[2] ?? $d1;

                        $d1PricePerPiece = $d1 ? $d1['final_price'] / max($d1['quantity'], 1) : 0;
                        $d3PricePerPiece = $d3 ? $d3['final_price'] / max($d3['quantity'], 1) : 0;

                        $colors = !empty($product['colors']) ? $product['colors'] : [];
                        $sizes = !empty($product['sizes']) ? $product['sizes'] : [];
                        ?>
                        <div class="col-lg-4 col-md-6 d-flex align-items-stretch">
                            <div class="product-item bg-white rounded shadow-sm h-100 d-flex flex-column">
                                <div class="product-img position-relative overflow-hidden rounded-top" style="height: 250px;">
                                    <img class="img-fluid w-100 h-100" style="object-fit: cover;"
                                        src="dash/<?php echo !empty($product['image_paths']) ? htmlspecialchars($product['image_paths'][0]) : 'img/default-product.jpg'; ?>"
                                        alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                                    <div class="product-action position-absolute top-0 end-0 m-2 d-flex gap-1">
                                        <a href="d.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-outline-dark btn-square"
                                        aria-label="Add to cart"><i class="fa fa-shopping-cart"></i></a>
                                        <a href="d.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-outline-dark btn-square"
                                        aria-label="Add to wishlist"><i class="far fa-heart"></i></a>
                                        <a href="d.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-outline-dark btn-square"
                                        aria-label="Compare"><i class="fa fa-sync-alt"></i></a>
                                        <a href="d.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-outline-dark btn-square"
                                        aria-label="View details"><i class="fa fa-search"></i></a>
                                    </div>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h2 class="product-name">
                                        <a href="product.php?id=<?php echo $product['product_id']; ?>" class="text-decoration-none text-dark">
                                            <?php echo highlightTerms($product['product_name'], $searchTerm); ?>
                                        </a>
                                    </h2>
                                    <p class="product-description" title="<?php echo htmlspecialchars($product['description']); ?>">
                                        <?php 
                                        $desc = strlen($product['description']) > 90 ? substr($product['description'], 0, 87) . '...' : $product['description']; 
                                        echo highlightTerms($desc, $searchTerm);
                                        ?>
                                    </p>

                                    <?php if (count($colors) > 0): ?>
                                        <div class="colors-container" aria-label="Available colors">
                                            <strong>Colors: </strong>
                                            <?php foreach ($colors as $color): ?>
                                                <?php 
                                                $colorLower = strtolower($color);
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
                                                <span class="color-dot" style="background-color: <?php echo htmlspecialchars($colorHex); ?>"
                                                    title="<?php echo htmlspecialchars(ucfirst($color)); ?>">
                                                    <?php echo highlightTerms($color, $searchTerm); ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (count($sizes) > 0): ?>
                                        <div class="sizes-container" aria-label="Available sizes">
                                            <strong>Sizes: </strong>
                                            <?php foreach ($sizes as $size): ?>
                                                <span class="badge"><?php echo highlightTerms($size, $searchTerm); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="price-per-piece-range mt-2" aria-label="Price per piece range">
                                        Price per piece: $<?php echo number_format($d3PricePerPiece, 2); ?> - $<?php echo number_format($d1PricePerPiece, 2); ?>
                                    </div>

                                    <a href="product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-primary mt-3"
                                        aria-label="Buy <?php echo htmlspecialchars($product['product_name']); ?>">
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
    .highlight {
        background-color: yellow;
        font-weight: bold;
        border-radius: 3px;
        padding: 0 2px;
        color: black;
    }
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
        vertical-align: middle;
        line-height: 20px;
        text-align: center;
    }
    .color-dot[style*="background-color: #ffffff"], 
    .color-dot[style*="background-color: #fff"] {
        border: 1.5px solid #999;
        color: black;
    }
</style>

<?php include "footer.php"; ?>
