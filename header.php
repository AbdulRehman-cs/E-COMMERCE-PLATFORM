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


<?php
// Get the current script name (e.g., "index.php", "about.php")
$current_page = basename($_SERVER['PHP_SELF']);

// Define titles for different pages
$page_titles = [
    'index.php' => 'Home',
    'products.php' => 'Our Products',
    'abouts.php' => 'About Us',
    'contact.php' => 'Contact Us',
     'filter_product.php' => 'Filteration Page ',
    'Privacy Policy.php' => 'Policy ',
    'search.php' => 'Search',
    'signin.php' => 'Login Your Account',
];

// Set default title if page not found
$page_title = $page_titles[$current_page] ?? 'Raza Baba';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raza Baba - <?php echo htmlspecialchars($page_title); ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Free HTML Templates" name="keywords">
    <meta content="Free HTML Templates" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
    <style>
        /* Customer Reviews Carousel */
        
        .carousel-item {
            font-size: 18px;
            color: #555;
            text-align: center;
        }
        
        .carousel-item footer {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            text-align: center;
        }
        
        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            background-color: #17359a;
        }
        /* Star Animation */
        
        @keyframes starGlow {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.2);
                opacity: 0.7;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        /* Hover effect for the service boxes */
        
        .service-box {
            transition: background-color 0.3s ease, transform 0.3s ease;
        }
        
        .service-box:hover {
            background-color: rgb(255, 251, 0);
            /* Smoke white */
            transform: translateY(-5px);
            /* Slight lift effect */
        }
        
        .star-animation {
            animation: starGlow 1.5s infinite ease-in-out;
        }
        
        .star-animation:hover {
            animation: none;
            /* Stop animation on hover */
            transform: scale(1.2);
            opacity: 1;
        }
        
        .animated-heading {
            display: inline-block;
            font-size: 2rem;
            font-weight: bold;
            white-space: nowrap;
            overflow: hidden;
            border-right: 2px solid black;
            /* Cursor effect */
            animation: blinkCursor 0.75s infinite;
        }
        
        @keyframes blinkCursor {
            0%,
            100% {
                border-right: 2px solid black;
            }
            50% {
                border-right: 2px solid transparent;
            }
        }
        
        .user-icon {
            font-size: 50px;
            color: #007bff;
            /* Primary Blue */
            margin-bottom: 10px;
        }
        
        .stars {
            font-size: 20px;
            color: #FFD700;
            /* Gold color for stars */
        }
    </style>
</head>

<body>
   
    <!-- Topbar Start -->
    <!-- Topbar Start -->
    <div class="container-fluid">
        <div class="row bg-secondary py-1 px-xl-5">
            <div class="col-lg-6 d-none d-lg-block">
                <div class="d-inline-flex align-items-center h-100">
                    <a class="text-body mr-3" href="./abouts.php">About</a>
                    <a class="text-body mr-3" href="contact.php">Contact</a>
                    <a class="text-body mr-3" href="./abouts.php">Help</a>
                </div>
            </div>
            <div class="col-lg-6 text-center text-lg-right">
                <div class="d-inline-flex align-items-center">
             <div class="btn-group">
                <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    My Account
                </button>
                <div class="bg-danger " style="color: #007bff;" aria-labelledby="">
                    <?php if (!isset($_COOKIE['user_id'])) { ?>
                        <a class="dropdown-item bg-primary text-white" href="signup.php">Sign up</a>
                        <a class="dropdown-item bg-primary text-white" href="signin.php">Sign in</a>
                    <?php } else { ?>
                        <a class="dropdown-item bg-primary text-white" style="color: #007bff;" href="whole/logout.php">Logout</a>
                    <?php } ?>
                </div>
            </div>

                    </div>
<!-- Sign In Modal -->
<?php
// Check if the user ID cookie is set
if (!isset($_COOKIE['user_id'])) {
    echo "<script>
            let modalShown = false; // Track if the modal has been shown
            let modalTimeout; // Variable to store the timeout

            function showSignInModal() {
                if (!modalShown) {
                    $('#signInModal').modal('show');
                    modalShown = true; // Set the flag to true
                    // Disable further showing for 3 seconds
                    clearTimeout(modalTimeout);
                    modalTimeout = setTimeout(() => {
                        modalShown = false; // Reset the flag after 3 seconds
                    }, 3000);
                }
            }

            // Show the sign-in modal every 15 seconds
            setInterval(showSignInModal, 15000);
          </script>";
}
?>

<!-- Sign In Modal -->
<div class="modal fade" id="signInModal" tabindex="-1" role="dialog" aria-labelledby="signInModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="signInModalLabel">Sign In Required</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                You need to sign in to access this feature. Please click the button below to sign in.
            </div>
            <div class="modal-footer">
                <a href="signin.php" class="btn btn-primary">Sign In</a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<!-- Bootstrap JS -->

                </div>
                <div class="d-inline-flex align-items-center d-block d-lg-none">
                    <a href="" class="btn px-0 ml-2">
                        <i class="fas fa-heart text-dark"></i>
                        <span class="badge text-dark border border-dark rounded-circle" style="padding-bottom: 2px;">0</span>
                    </a>
                    <a href="" class="btn px-0 ml-2">
                        <i class="fas fa-shopping-cart text-dark"></i>
                        <span class="badge text-dark border border-dark rounded-circle" style="padding-bottom: 2px;">0</span>
                    </a>
                </div>
            </div>
        </div>
        <div class="row align-items-center  py-3 px-xl-5 d-none d-lg-flex">
            <div class="col-lg-4">   <p class="m-0">Customer Service</p>
                <h5 class="m-0">+92-32133242</h5>
              
            </div>
            <div class="col-lg-4 col-6 text-left">
            <form id="searchForm" action="search.php" method="GET">
           <div class="input-group">
          <input type="text" name="query" class="form-control" placeholder="Search for products">
          <div class="input-group-append">
            <span class="input-group-text bg-transparent text-primary" style="cursor: pointer;" onclick="document.getElementById('searchForm').submit();">
         <i class="fa fa-search"></i>
            </span>
           </div>
         </div>
         </form>
            </div>
            <div class="col-lg-4 col-6 text-right">
  <a href="" class="text-decoration-none">
                    <span class="h1 text-uppercase text-primary bg-dark px-2">Raza  </span>
                    <span class="h1 text-uppercase text-dark bg-primary px-2 ml-n1">Baba</span>
                </a>
            </div>
        </div>
    </div>
    <!-- Topbar End -->

    <!-- Navbar Start -->
    <div class="container-fluid bg-dark mb-30">
        <div class="row px-xl-5">
          <div class="col-lg-3 d-none d-lg-block">
        <a class="btn d-flex align-items-center justify-content-between bg-primary w-100 text-decoration-none" data-toggle="collapse" href="#navbar-vertical" role="button" aria-expanded="false" aria-controls="navbar-vertical" style="height: 65px; padding: 0 30px;">
          <h6 class="text-dark m-0"><i class="fa fa-bars mr-2"></i>Categories</h6>
          <i class="fa fa-angle-down text-dark"></i>
        </a>

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
        transition: background-color 0.2s;
    }
    
    .nav-link:hover, .dropdown-link:hover {
        background-color: #e9ecef;
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
        z-index: 1000;
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
        width: 12px;
        height: 12px;
        display: inline-block;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23212529'%3E%3Cpath d='M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: center;
    }
    
    .dropdown-icon.active {
        transform: rotate(90deg);
    }
</style>

<nav class="collapse position-absolute navbar navbar-vertical navbar-light align-items-start p-0 bg-light border" id="navbar-vertical">
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
                    <?php if (!empty($subcategoriesByCategory[$category['Category_ID']])): ?>
                        <i class="dropdown-icon"></i>
                    <?php endif; ?>
                </a>
                
                <!-- Subcategories Dropdown -->
                <?php if (!empty($subcategoriesByCategory[$category['Category_ID']])): ?>
                    <div class="dropdown-menu" id="subcategories-<?php echo $category['Category_ID']; ?>">
                        <?php foreach ($subcategoriesByCategory[$category['Category_ID']] as $subcategory): ?>
                            <div class="subcategory-item" data-subcategory-id="<?php echo $subcategory['Subcategory_ID']; ?>">
                                
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
                                    <div class="synonyms-menu table-dark" id="synonyms-<?php echo $subcategory['Subcategory_ID']; ?>">
                                        <?php foreach ($synonymsBySubcategory[$subcategory['Subcategory_ID']] as $synonym): ?>
                                            <a href="synonym.php?id=<?php echo $synonym['Synonym_ID']; ?>" 
                                               class="synonym-link ">
                                                <?php echo htmlspecialchars($synonym['Synonym_Name']); ?><br>
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
    // Track the state of each category and subcategory
    const categoryStates = {};
    
    // Handle category clicks
    function handleCategoryClick(event, categoryId) {
        event.preventDefault();
        const dropdown = document.getElementById(`subcategories-${categoryId}`);
        const icon = event.currentTarget.querySelector('.dropdown-icon');
        
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
            if (icon) icon.classList.remove('active');
        } else {
            // Hide all other dropdowns
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.style.display = 'none';
            });
            document.querySelectorAll('.dropdown-icon').forEach(i => {
                i.classList.remove('active');
            });
            
            // Show current dropdown
            dropdown.style.display = 'block';
            if (icon) icon.classList.add('active');
        }
    }
    
    // Handle subcategory clicks
    function handleSubcategoryClick(event, subcategoryId) {
        event.preventDefault();
        const synonyms = document.getElementById(`synonyms-${subcategoryId}`);
        const icon = event.currentTarget.querySelector('.dropdown-icon');
        
        // Initialize state for subcategory
        if (!categoryStates[subcategoryId]) {
            categoryStates[subcategoryId] = {
                clickCount: 0,
                lastClickTime: 0
            };
        }
        
        const now = Date.now();
        const state = categoryStates[subcategoryId];
        
        // Check for double click (within 500ms)
        if (now - state.lastClickTime < 500) {
            // Double click - redirect
            window.location.href = event.currentTarget.href;
            return;
        }
        
        // Single click - toggle synonyms
        state.lastClickTime = now;
        
        if (synonyms.style.display === 'block') {
            synonyms.style.display = 'none';
            if (icon) icon.classList.remove('active');
        } else {
            // Hide all other synonym menus
            document.querySelectorAll('.synonyms-menu').forEach(menu => {
                menu.style.display = 'none';
            });
            document.querySelectorAll('.dropdown-icon').forEach(i => {
                i.classList.remove('active');
            });
            
            // Show current synonyms
            synonyms.style.display = 'block';
            if (icon) icon.classList.add('active');
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



      </div>
      
           <?php
// Get the current script name
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="col-lg-9">
    <nav class="navbar navbar-expand-lg bg-dark navbar-dark py-3 py-lg-0 px-0">
        <a href="" class="text-decoration-none d-block d-lg-none">
            <span class="h1 text-uppercase text-dark bg-light px-2">Raza</span>
            <span class="h1 text-uppercase text-light bg-primary px-2 ml-n1">Baba</span>
        </a>
        <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-between" id="navbarCollapse">
            <div class="navbar-nav mr-auto py-0">
                <a href="index.php" class="nav-item nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Home</a>
                <a href="filter_product.php" class="nav-item nav-link <?php echo ($current_page == 'filter_product.php') ? 'active' : ''; ?>">Shop</a>
                <?php if (!isset($_COOKIE['user_id'])) { ?>
                    <!-- If user is not logged in, you can add links for login or registration here -->
                <?php } else { ?>
                    <a href="whole/profile.php" class="nav-item nav-link <?php echo ($current_page == 'whole/profile.php') ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($retailerName); ?> b
                    </a>
                    <a href="cart.php" class="nav-item nav-link <?php echo ($current_page == 'cart.php') ? 'active' : ''; ?>">Cart</a>
                    <a href="payment.php" class="nav-item nav-link <?php echo ($current_page == 'payment.php') ? 'active' : ''; ?>">Payment</a>
                <?php } ?>

                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">Pages <i class="fa fa-angle-down mt-1"></i></a>
                    <div class="dropdown-menu bg-primary rounded-0 border-0 m-0">
                        <a href="./abouts.php" class="dropdown-item <?php echo ($current_page == 'abouts.php') ? 'active' : ''; ?>">About</a>
                        <a href="./Privacy Policy.php" class="dropdown-item <?php echo ($current_page == 'Privacy Policy.php') ? 'active' : ''; ?>">Privacy Policy</a>
                        <a href="cart.php" class="dropdown-item <?php echo ($current_page == 'cart.php') ? 'active' : ''; ?>">Shopping Cart</a>
                        <a href="checkout.php" class="dropdown-item <?php echo ($current_page == 'checkout.php') ? 'active' : ''; ?>">Checkout</a>
                    </div>
                </div>
                <a href="contact.php" class="nav-item nav-link <?php echo ($current_page == 'contact.php') ? 'active' : ''; ?>">Contact</a>
            </div>
            <div class="navbar-nav ml-auto py-0 d-none d-lg-block">
                <a href="whole/" class="btn px-0">
                    <i class="fas fa-shopping-cart text-primary"></i>
                    <span class="badge text-secondary border border-secondary rounded-circle" style="padding-bottom: 2px;"><?php echo $totalProductsFromOrders; ?></span>
                </a>
                <a href="cart.php" class="btn px-0 ml-3">
                    <i class="fas fa-heart text-primary"></i>
                    <span class="badge text-secondary border border-secondary rounded-circle" style="padding-bottom: 2px;"><?php echo $totalProductsFromCart; ?></span>
                </a>
            </div>
        </div>
    </nav>
</div>

        </div>
    </div>
    <!-- Navbar End -->