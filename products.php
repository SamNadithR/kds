<?php
require_once 'config.php';

// Get filter parameters
$category_id = isset($_GET['category']) && $_GET['category'] !== '' ? filter_var($_GET['category'], FILTER_VALIDATE_INT) : null;
$search = isset($_GET['search']) ? htmlspecialchars(trim($_GET['search'])) : '';
$sort = isset($_GET['sort']) ? htmlspecialchars(trim($_GET['sort'])) : 'name_asc';
$min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? filter_var($_GET['min_price'], FILTER_VALIDATE_FLOAT) : null;
$max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? filter_var($_GET['max_price'], FILTER_VALIDATE_FLOAT) : null;

// Add validation
if ($category_id !== null && $category_id <= 0) {
    $category_id = null;
}

if ($min_price !== null && $min_price < 0) {
    $min_price = null;
}

if ($max_price !== null && $max_price < 0) {
    $max_price = null;
}

// Build query
$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE 1=1";
$params = [];

if ($category_id) {
    $query .= " AND p.category_id = ?";
    $params[] = $category_id;
}

if ($search) {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($min_price !== null) {
    $query .= " AND p.price >= ?";
    $params[] = $min_price;
}

if ($max_price !== null) {
    $query .= " AND p.price <= ?";
    $params[] = $max_price;
}

// Add sorting
switch ($sort) {
    case 'price_asc':
        $query .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY p.price DESC";
        break;
    case 'name_desc':
        $query .= " ORDER BY p.name DESC";
        break;
    default:
        $query .= " ORDER BY p.name ASC";
}

// Get products
try {
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle query error
    error_log("Query error: " . $e->getMessage());
    $products = [];
}

// Get categories for filter
$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Get price range
$price_range = $conn->query("
    SELECT MIN(price) as min_price, MAX(price) as max_price 
    FROM products
")->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - KDS Computer Store</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Mobile Navigation Styles */
        .hamburger {
            display: none;
            font-size: 24px;
            cursor: pointer;
            padding: 10px;
        }
        
        @media screen and (max-width: 768px) {
            .hamburger {
                display: block;
            }
            
            .nav-links {
                display: none;
                width: 100%;
                flex-direction: column;
                background-color: #121212;
                position: absolute;
                top: 60px;
                left: 0;
                z-index: 100;
                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            }
            
            .nav-links.active {
                display: flex;
            }
            
            .nav-links a {
                width: 100%;
                padding: 15px;
                text-align: center;
                border-bottom: 1px solid #eee;
            }
            
            header nav {
                position: relative;
                justify-content: space-between;
            }
            
            /* Make products page responsive */
            .admin-container > div {
                display: flex !important;
                flex-direction: column !important;
                gap: 2rem !important;
            }
            
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)) !important;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <h1>KDS Computers</h1>
            </div>
            
            <div class="hamburger" id="hamburger-menu">
                <i class="fas fa-bars"></i>
            </div>
            
            <div class="nav-links" id="nav-links">
                <a href="index.php">Home</a>
                <a href="products.php">Products</a>
                <?php if (isLoggedIn()): ?>
                    <a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a>
                    <a href="my_orders.php">My Orders</a>
                    <?php if (isAdmin()): ?>
                        <a href="admin/">Admin Panel</a>
                    <?php endif; ?>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main class="admin-container">
        <div style="display: grid; grid-template-columns: 250px 1fr; gap: 2rem;">
            <!-- Filters Sidebar -->
            <div class="admin-card">
                <h3>Filters</h3>
                <form method="GET" action="products.php" class="admin-form">
                    <!-- Search -->
                    <div class="form-group">
                        <label for="search">Search</label>
                        <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>"
                               placeholder="Search products...">
                    </div>

                    <!-- Categories -->
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"
                                        <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Price Range -->
                    <div class="form-group">
                        <label>Price Range</label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <input type="number" id="min_price" name="min_price" 
                                   value="<?php echo htmlspecialchars($min_price !== null ? $min_price : ''); ?>"
                                   placeholder="Min" step="0.01" min="<?php echo $price_range['min_price']; ?>">
                            <input type="number" id="max_price" name="max_price" 
                                   value="<?php echo htmlspecialchars($max_price !== null ? $max_price : ''); ?>"
                                   placeholder="Max" step="0.01" max="<?php echo $price_range['max_price']; ?>">
                        </div>
                    </div>

                    <!-- Sort -->
                    <div class="form-group">
                        <label for="sort">Sort By</label>
                        <select id="sort" name="sort">
                            <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>
                                Name (A-Z)
                            </option>
                            <option value="name_desc" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>
                                Name (Z-A)
                            </option>
                            <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>
                                Price (Low to High)
                            </option>
                            <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>
                                Price (High to Low)
                            </option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        Apply Filters
                    </button>
                </form>
            </div>

            <!-- Products Grid -->
            <div>
                <div class="product-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card" data-category="<?php echo $product['category_id']; ?>"
                             data-price="<?php echo $product['price']; ?>">
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>">
                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="price">LKR <?php echo number_format($product['price'], 2); ?></p>
                                <p class="stock"><?php echo $product['stock'] > 0 ? 'In Stock' : 'Out of Stock'; ?></p>
                            </a>
                            
                            <?php if (isLoggedIn()): ?>
                            <div class="qa-badge">
                                <a href="product-detail.php?id=<?php echo $product['id']; ?>#qa-section">
                                    <i class="fas fa-comments"></i>
                                    <span>Ask a Question</span>
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (empty($products)): ?>
                    <div class="admin-card" style="text-align: center;">
                        <p>No products found matching your criteria.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>Contact Us</h3>
                <p>Email: info@kds.com</p>
                <p>Phone: (123) 456-7890</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <a href="about.php">About Us</a>
                <a href="contact.php">Contact</a>
                <a href="privacy.php">Privacy Policy</a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 KDS Computer Store. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Hamburger menu functionality
        document.getElementById('hamburger-menu').addEventListener('click', function() {
            document.getElementById('nav-links').classList.toggle('active');
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            const nav = document.getElementById('nav-links');
            const hamburger = document.getElementById('hamburger-menu');
            
            if (!nav.contains(event.target) && !hamburger.contains(event.target) && nav.classList.contains('active')) {
                nav.classList.remove('active');
            }
        });
    </script>
    <script src="js/main.js"></script>
</body>
</html>