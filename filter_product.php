<?php
// dat connection class
class dat {
    private $conn;
    public function __construct() {
        $this->conn = new mysqli('localhost', 'root', '', 'razababa');
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        $this->conn->set_charset('utf8mb4');
    }
    public function getConnection() {
        return $this->conn;
    }
}

// CategoryManager class for product filtering and pagination
class CategoryManagers {
    private $conn;

    public function __construct() {
        $dat = new dat();
        $this->conn = $dat->getConnection();
    }

    private function bindParams(&$stmt, $params) {
        if (empty($params)) return;
        $types = '';
        $bind_params = [];
        foreach ($params as $param) {
            if (is_int($param)) $types .= 'i';
            elseif (is_float($param)) $types .= 'd';
            else $types .= 's';
            $bind_params[] = $param;
        }
        $refs = [];
        foreach ($bind_params as $key => $value) {
            $refs[$key] = &$bind_params[$key];
        }
        array_unshift($refs, $types);
        call_user_func_array([$stmt, 'bind_param'], $refs);
    }

    public function getFilteredProducts($search_param, $price_filters, $color_filters, $size_filters, $records_per_page, $offset) {
        $query = "SELECT 
            p.product_id, 
            p.product_name, 
            p.description, 
            p.quantity, 
            p.unit, 
            p.category_id,
            p.price,
            GROUP_CONCAT(DISTINCT i.image_path) AS image_paths,
            GROUP_CONCAT(DISTINCT c.color_name) AS colors,
            GROUP_CONCAT(DISTINCT s.size_name) AS sizes,
            GROUP_CONCAT(DISTINCT t.tag_name) AS tags,
            GROUP_CONCAT(DISTINCT d.quantity) AS discount_quantities,
            GROUP_CONCAT(DISTINCT d.discount) AS discounts,
            GROUP_CONCAT(DISTINCT d.total_original_price) AS total_original_prices,
            GROUP_CONCAT(DISTINCT d.total_discount_amount) AS total_discount_amounts,
            GROUP_CONCAT(DISTINCT d.final_price) AS final_prices
        FROM 
            products p
        LEFT JOIN images i ON p.product_id = i.product_id
        LEFT JOIN colors c ON p.product_id = c.product_id
        LEFT JOIN sizes s ON p.product_id = s.product_id
        LEFT JOIN tags t ON p.product_id = t.product_id
        LEFT JOIN discounts d ON p.product_id = d.product_id
        WHERE 1=1";

        $params = [];

        if (!empty($search_param)) {
            $query .= " AND p.product_name LIKE ?";
            $params[] = '%' . $search_param . '%';
        }

        if (!empty($price_filters) && !in_array('all', $price_filters)) {
            $price_conns = [];
            foreach ($price_filters as $range) {
                if (strpos($range, '-') !== false) {
                    list($min, $max) = explode('-', $range);
                    if (is_numeric($min) && is_numeric($max)) {
                        $price_conns[] = "(p.price BETWEEN ? AND ?)";
                        $params[] = (float)$min;
                        $params[] = (float)$max;
                    }
                }
            }
            if (!empty($price_conns)) {
                $query .= " AND (" . implode(" OR ", $price_conns) . ")";
            }
        }

        if (!empty($color_filters) && !in_array('all', $color_filters)) {
            $placeholders = implode(',', array_fill(0, count($color_filters), '?'));
            $query .= " AND c.color_name IN ($placeholders)";
            $params = array_merge($params, $color_filters);
        }

        if (!empty($size_filters) && !in_array('all', $size_filters)) {
            $placeholders = implode(',', array_fill(0, count($size_filters), '?'));
            $query .= " AND s.size_name IN ($placeholders)";
            $params = array_merge($params, $size_filters);
        }

        $query .= " GROUP BY p.product_id ORDER BY p.product_id DESC LIMIT ? OFFSET ?";
        $params[] = (int)$records_per_page;
        $params[] = (int)$offset;

        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            die('Prepare failed: ' . $this->conn->error);
        }

        $this->bindParams($stmt, $params);

        if (!$stmt->execute()) {
            die('Execute failed: ' . $stmt->error);
        }

        $result = $stmt->get_result();
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $row['image_paths'] = $row['image_paths'] ? explode(',', $row['image_paths']) : [];
            $row['colors'] = $row['colors'] ? explode(',', $row['colors']) : [];
            $row['sizes'] = $row['sizes'] ? explode(',', $row['sizes']) : [];
            $row['tags'] = $row['tags'] ? explode(',', $row['tags']) : [];

            $row['discount_quantities'] = $row['discount_quantities'] ? explode(',', $row['discount_quantities']) : [];
            $row['discounts'] = $row['discounts'] ? explode(',', $row['discounts']) : [];
            $row['total_original_prices'] = $row['total_original_prices'] ? explode(',', $row['total_original_prices']) : [];
            $row['total_discount_amounts'] = $row['total_discount_amounts'] ? explode(',', $row['total_discount_amounts']) : [];
            $row['final_prices'] = $row['final_prices'] ? explode(',', $row['final_prices']) : [];

            $products[] = $row;
        }
        $stmt->close();

        return $products;
    }

    public function getTotalFilteredProducts($search_param, $price_filters, $color_filters, $size_filters) {
        $query = "SELECT COUNT(DISTINCT p.product_id) as total
                  FROM products p
                  LEFT JOIN colors c ON p.product_id = c.product_id
                  LEFT JOIN sizes s ON p.product_id = s.product_id
                  WHERE 1=1";

        $params = [];

        if (!empty($search_param)) {
            $query .= " AND p.product_name LIKE ?";
            $params[] = '%' . $search_param . '%';
        }

        if (!empty($price_filters) && !in_array('all', $price_filters)) {
            $price_conns = [];
            foreach ($price_filters as $range) {
                if (strpos($range, '-') !== false) {
                    list($min, $max) = explode('-', $range);
                    if (is_numeric($min) && is_numeric($max)) {
                        $price_conns[] = "(p.price BETWEEN ? AND ?)";
                        $params[] = (float)$min;
                        $params[] = (float)$max;
                    }
                }
            }
            if (!empty($price_conns)) {
                $query .= " AND (" . implode(" OR ", $price_conns) . ")";
            }
        }

        if (!empty($color_filters) && !in_array('all', $color_filters)) {
            $placeholders = implode(',', array_fill(0, count($color_filters), '?'));
            $query .= " AND c.color_name IN ($placeholders)";
            $params = array_merge($params, $color_filters);
        }

        if (!empty($size_filters) && !in_array('all', $size_filters)) {
            $placeholders = implode(',', array_fill(0, count($size_filters), '?'));
            $query .= " AND s.size_name IN ($placeholders)";
            $params = array_merge($params, $size_filters);
        }

        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            die('Prepare failed: ' . $this->conn->error);
        }

        $this->bindParams($stmt, $params);

        if (!$stmt->execute()) {
            die('Execute failed: ' . $stmt->error);
        }

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row['total'] ?? 0;
    }
}
include "header.php";
// Main processing
$CategoryManagers = new CategoryManagers();

$search_param = $_GET['search'] ?? '';
$price_filters = $_GET['price'] ?? [];
$color_filters = $_GET['color'] ?? [];
$size_filters = $_GET['size'] ?? [];

$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$records_per_page = 8;
$offset = ($page - 1) * $records_per_page;

$products = $CategoryManagers->getFilteredProducts($search_param, $price_filters, $color_filters, $size_filters, $records_per_page, $offset);
$total_products = $CategoryManagers->getTotalFilteredProducts($search_param, $price_filters, $color_filters, $size_filters);
$total_pages = ceil($total_products / $records_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Shop List</title>

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

</head>
<body>
<div class="container-fluid">
  <div class="row px-xl-5 my-4">
    <div class="col-lg-3 col-md-4">
      <form method="get" id="filterForm">
        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_param); ?>">
        <!-- Price Filters -->
        <h5 class="section-title bg-secondary text-white py-2 px-3 mb-3 rounded">Filter by price</h5>
        <?php
        $priceOptions = [
          'all' => 'All Price',
          '0-25' => '$0 - $25',
          '30-100' => '$30 - $100',
          '200-300' => '$200 - $300',
          '300-400' => '$300 - $400',
          '400-500' => '$400 - $500',
        ];
        foreach ($priceOptions as $key => $label):
          $checked = in_array($key, $price_filters) || ($key === 'all' && empty($price_filters)) ? 'checked' : '';
        ?>
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="price[]" id="price-<?php echo $key; ?>" value="<?php echo $key; ?>" <?php echo $checked; ?> onchange="document.getElementById('filterForm').submit();">
            <label class="form-check-label" for="price-<?php echo $key; ?>"><?php echo $label; ?></label>
          </div>
        <?php endforeach; ?>

        <!-- Color Filters -->
        <h5 class="section-title bg-secondary text-white py-2 px-3 mb-3 rounded mt-4">Filter by color</h5>
        <?php
        $colorOptions = ['all' => 'All Color', 'Black', 'White', 'Red', 'Blue', 'Green'];
        foreach ($colorOptions as $key => $label):
          $value = is_int($key) ? $label : $key;
          $checked = in_array($value, $color_filters) || ($value === 'all' && empty($color_filters)) ? 'checked' : '';
        ?>
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="color[]" id="color-<?php echo $value; ?>" value="<?php echo $value; ?>" <?php echo $checked; ?> onchange="document.getElementById('filterForm').submit();">
            <label class="form-check-label" for="color-<?php echo $value; ?>"><?php echo $label; ?></label>
          </div>
        <?php endforeach; ?>

        <!-- Size Filters -->
        <h5 class="section-title bg-secondary text-white py-2 px-3 mb-3 rounded mt-4">Filter by size</h5>
        <?php
        $sizeOptions = ['all' => 'All Size', 'XS', 'S', 'M', 'L', 'XL'];
        foreach ($sizeOptions as $key => $label):
          $value = is_int($key) ? $label : $key;
          $checked = in_array($value, $size_filters) || ($value === 'all' && empty($size_filters)) ? 'checked' : '';
        ?>
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="size[]" id="size-<?php echo $value; ?>" value="<?php echo $value; ?>" <?php echo $checked; ?> onchange="document.getElementById('filterForm').submit();">
            <label class="form-check-label" for="size-<?php echo $value; ?>"><?php echo $label; ?></label>
          </div>
        <?php endforeach; ?>
      </form>
    </div>

    <div class="col-lg-9 col-md-8">
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
                <div class="col-lg-4 col-md-6">
                    <div class="product-item bg-white rounded shadow-sm h-100 d-flex flex-column">
                        <div class="product-img position-relative overflow-hidden rounded-top" style="height: 250px;">
                            <img class="img-fluid w-100 h-100" style="object-fit: cover;" src="dash/<?php echo !empty($product['image_paths']) ? htmlspecialchars($product['image_paths'][0]) : 'img/default-product.jpg'; ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                            <div class="product-action position-absolute top-0 end-0 m-2 d-flex gap-1">
                                <a href="product_detail.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-outline-dark btn-square" aria-label="Add to cart"><i class="fa fa-shopping-cart"></i></a>
                                <a href="product_detail.php?id=<?php echo $product['product_id']; ?> "class="btn btn-sm btn-outline-dark btn-square" aria-label="Add to wishlist"><i class="far fa-heart"></i></a>
                                <a href="product_detail.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-outline-dark btn-square" aria-label="Compare"><i class="fa fa-sync-alt"></i></a>
                                <a href="product_detail.php?id=<?php echo $product['product_id']; ?> "class="btn btn-sm btn-outline-dark btn-square" aria-label="View details"><i class="fa fa-search"></i></a>
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
<?php include 'footer.php'?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>