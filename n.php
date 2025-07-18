<?php
include_once 'category/CategoryManager.php';

$categoryManager = new CategoryManager();
$totalProductsFromCart=0;
$totalProductsFromOrders=0;
// Fetch all categories
$allCategories = $categoryManager->getCategories();

// Fetch all subcategories and synonyms grouped by category
$subcategoriesByCategory = [];
$synonymsBySubcategory = [];
foreach ($allCategories as $category) {
    $subcategories = $categoryManager->getSubcategoriesByCategoryId($category['Category_ID']);
    $subcategoriesByCategory[$category['Category_ID']] = $subcategories;

    // Fetch synonyms for each subcategory
    foreach ($subcategories as $subcategory) {
        $synonymsBySubcategory[$subcategory['Subcategory_ID']] = $categoryManager->getSynonymsBySubcategoryId($subcategory['Subcategory_ID']);
    }
}

if (isset($_COOKIE['user_id'])) {
    // Get the user ID from the cookie
    $userId = intval($_COOKIE['user_id']); // Ensure user ID is an integer

    // Check if the user type is set in the cookie
    if (isset($_COOKIE['user_type']) && $_COOKIE['user_type'] === 'retailer') {
        // Proceed with fetching user data
        require_once 'category/Database.php'; // Safe against re-inclusion

        // Create an instance of the Database class
        $database = new Database();
        $conn = $database->getConnection(); // Get the database connection

        // Prepare the SQL query to fetch data for the logged-in user from the retailers table
        $query = "SELECT id, full_name, email, mobile, password, created_at, profile_pic FROM retailers WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId); // Assuming 'id' is an integer

        // Execute the query
        $stmt->execute();

        // Get the result
        $result = $stmt->get_result();

        // Check if any data was returned
        if ($result->num_rows > 0) {
            // Fetch the data
            $retailerData = $result->fetch_assoc();

            // Store the values in variables
            $retailerId = htmlspecialchars($retailerData['id']);
            $retailerName = htmlspecialchars($retailerData['full_name']);
            $email = htmlspecialchars($retailerData['email']);
            $mobile = htmlspecialchars($retailerData['mobile']);
            $createdAt = htmlspecialchars($retailerData['created_at']);
            $profilePicPath = "../" . htmlspecialchars($retailerData['profile_pic']);

            // Count total products from orders
            $queryOrders = "SELECT COUNT(*) as total_products FROM orders WHERE user_id = ?";
            $stmtOrders = $conn->prepare($queryOrders);
            $stmtOrders->bind_param("i", $userId);
            $stmtOrders->execute();
            $resultOrders = $stmtOrders->get_result();
            $totalProductsFromOrders = $resultOrders->fetch_assoc()['total_products'];
            $stmtOrders->close();

            // Count total products from cart
            $queryCart = "SELECT COUNT(*) as total_products FROM cart WHERE user_id = ?";
            $stmtCart = $conn->prepare($queryCart);
            $stmtCart->bind_param("i", $userId);
            $stmtCart->execute();
            $resultCart = $stmtCart->get_result();
            $totalProductsFromCart = $resultCart->fetch_assoc()['total_products'];
            $stmtCart->close();

            // Total products from both orders and cart
          $totalProducts = $totalProductsFromOrders + $totalProductsFromCart;

            

        } else {
            // Handle case where no retailer data is found
            echo "No retailer data found.";
        }

        // Close the statement and connection
        $stmt->close();
        $conn->close(); 
    } else {
        // Redirect to 404 page if user type is not retailer
        header('location:404.php');
        exit(); // Ensure no further code is executed after the redirect
    }
} else {
    // Handle case where user ID is not set
    $userId = '';
    echo "User  ID is not set.";
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced Navigation</title>
    <style>
        .navbar-vertical {
            position: absolute;
            width: calc(100% - 30px);
            z-index: 999;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        
        .nav-container {
            display: flex;
            flex-direction: column;
        }
        
        .nav-item {
            position: relative;
        }
        
        .nav-link, .dropdown-link {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 1rem;
            color: #212529;
            text-decoration: none;
        }
        
        .dropdown-menu {
            display: none;
            position: absolute;
            left: 100%;
            top: 0;
            min-width: 200px;
            background: white;
            border: 1px solid #dee2e6;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
        }
        
        .synonyms-menu {
            display: none;
            position: absolute;
            left: 100%;
            top: 0;
            min-width: 200px;
            background: white;
            border: 1px solid #dee2e6;
        }
        
        .dropdown-icon {
            margin-left: 10px;
            transition: transform 0.2s;
        }
        
        .dropdown-icon.active {
            transform: rotate(90deg);
        }
    </style>
</head>
<body>
    <nav class="navbar-vertical">
        <div class="nav-container">
            <?php foreach ($allCategories as $category): ?>
                <div class="nav-item" 
                     data-category-id="<?php echo $category['Category_ID']; ?>"
                     onmouseleave="resetCategoryState(<?php echo $category['Category_ID']; ?>)">
                    
                    <!-- Category Link -->
                    <a href="category.php?id=<?php echo $category['Category_ID']; ?>" 
                       class="nav-link"
                       onclick="handleCategoryClick(event, <?php echo $category['Category_ID']; ?>)">
                        <?php echo htmlspecialchars($category['Category_Name']); ?>
                        <i class="dropdown-icon"></i>
                    </a>
                    
                    <!-- Subcategories Dropdown -->
                    <?php if (!empty($subcategoriesByCategory[$category['Category_ID']])): ?>
                        <div class="dropdown-menu" id="subcategories-<?php echo $category['Category_ID']; ?>">
                            <?php foreach ($subcategoriesByCategory[$category['Category_ID']] as $subcategory): ?>
                                <div class="subcategory-item"
                                     data-subcategory-id="<?php echo $subcategory['Subcategory_ID']; ?>">
                                    
                                    <!-- Subcategory Link -->
                                    <a href="subcategory.php?id=<?php echo $subcategory['Subcategory_ID']; ?>" 
                                       class="dropdown-link"
                                       onclick="handleSubcategoryClick(event, <?php echo $subcategory['Subcategory_ID']; ?>)">
                                        <?php echo htmlspecialchars($subcategory['Subcategory_Name']); ?>
                                        <?php if (!empty($synonymsBySubcategory[$subcategory['Subcategory_ID']])): ?>
                                            <i class="dropdown-icon"></i>
                                        <?php endif; ?>
                                    </a>
                                    
                                    <!-- Synonyms Dropdown -->
                                    <?php if (!empty($synonymsBySubcategory[$subcategory['Subcategory_ID']])): ?>
                                        <div class="synonyms-menu" id="synonyms-<?php echo $subcategory['Subcategory_ID']; ?>">
                                            <?php foreach ($synonymsBySubcategory[$subcategory['Subcategory_ID']] as $synonym): ?>
                                                <a href="synonym.php?id=<?php echo $synonym['Synonym_ID']; ?>" 
                                                   class="synonym-link">
                                                    <?php echo htmlspecialchars($synonym['Synonym_Name']); ?>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </nav>

    <script>
        // Track the state of each category
        const categoryStates = {};
        
        // Handle category clicks
        function handleCategoryClick(event, categoryId) {
            event.preventDefault();
            const dropdown = document.getElementById(`subcategories-${categoryId}`);
            
            // Initialize state if not exists
            if (!categoryStates[categoryId]) {
                categoryStates[categoryId] = {
                    clickCount: 0,
                    lastClickTime: 0
                };
            }
            
            const now = Date.now();
            const state = categoryStates[categoryId];
            
            // Check for double click (within 500ms)
            if (now - state.lastClickTime < 500) {
                // Double click - redirect
                window.location.href = event.currentTarget.href;
                return;
            }
            
            // Single click - toggle dropdown
            state.lastClickTime = now;
            
            if (dropdown.style.display === 'block') {
                dropdown.style.display = 'none';
                event.currentTarget.querySelector('.dropdown-icon').classList.remove('active');
            } else {
                // Hide all other dropdowns
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.style.display = 'none';
                });
                document.querySelectorAll('.dropdown-icon').forEach(icon => {
                    icon.classList.remove('active');
                });
                
                // Show current dropdown
                dropdown.style.display = 'block';
                event.currentTarget.querySelector('.dropdown-icon').classList.add('active');
            }
        }
        
        // Handle subcategory clicks
        function handleSubcategoryClick(event, subcategoryId) {
            event.preventDefault();
            const synonyms = document.getElementById(`synonyms-${subcategoryId}`);
            
            if (!synonyms) {
                // No synonyms - redirect immediately
                window.location.href = event.currentTarget.href;
                return;
            }
            
            // Toggle synonyms menu
            if (synonyms.style.display === 'block') {
                synonyms.style.display = 'none';
                event.currentTarget.querySelector('.dropdown-icon').classList.remove('active');
            } else {
                // Hide all other synonym menus
                document.querySelectorAll('.synonyms-menu').forEach(menu => {
                    menu.style.display = 'none';
                });
                document.querySelectorAll('.dropdown-icon').forEach(icon => {
                    icon.classList.remove('active');
                });
                
                // Show current synonyms
                synonyms.style.display = 'block';
                event.currentTarget.querySelector('.dropdown-icon').classList.add('active');
            }
        }
        
        // Reset category state when mouse leaves
        function resetCategoryState(categoryId) {
            if (categoryStates[categoryId]) {
                categoryStates[categoryId].clickCount = 0;
            }
        }
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.nav-item')) {
                document.querySelectorAll('.dropdown-menu, .synonyms-menu').forEach(menu => {
                    menu.style.display = 'none';
                });
                document.querySelectorAll('.dropdown-icon').forEach(icon => {
                    icon.classList.remove('active');
                });
            }
        });
    </script>
</body>
</html>
