<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KDS Computer Store</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="js/main.js" defer></script>
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

    <main>
        <section class="hero">
            <div class="hero-content">
                <h2>Welcome to KDS Computers</h2>
                <p>Your One-Stop Shop for All Computer Needs</p>
                <a href="products.php" class="cta-button">Shop Now</a>
            </div>
            <div class="slider-nav">
                <span class="dot active"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </div>
        </section>

        <section class="featured-categories">
            <h2>Product Categories</h2>
            <div class="category-grid">
                <?php
                $stmt = $conn->query("SELECT * FROM categories");
                while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '<div class="category-card">';
                    echo '<h3>' . htmlspecialchars($category['name']) . '</h3>';
                    echo '<p>' . htmlspecialchars($category['description']) . '</p>';
                    echo '<a href="products.php?category=' . $category['id'] . '" class="category-link">View Products</a>';
                    echo '</div>';
                }
                ?>
            </div>
        </section>

        <div class="container">
            <!-- PC Build Recommender -->
            <section class="recommender-section">
                <div class="recommender-card">
                    <h2>AI PC Build Recommender</h2>
                    <p>Let us help you find the perfect PC build based on your needs and budget!</p>
                    
                    <form id="buildRecommenderForm" class="recommender-form">
                        <div class="form-group">
                            <label for="purpose">What will you use your PC for?</label>
                            <select name="purpose" id="purpose" required>
                                <option value="">Select Purpose</option>
                                <option value="gaming">Gaming</option>
                                <option value="office">Office Work</option>
                                <option value="content_creation">Content Creation</option>
                                <option value="programming">Programming</option>
                                <option value="student">Student</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="budget">Your Budget Range (LKR)</label>
                            <select name="budget" id="budget" required>
                                <option value="">Select Budget</option>
                                <option value="budget">Budget (Under 100,000 LKR)</option>
                                <option value="mid_range">Mid Range (100,000 - 250,000 LKR)</option>
                                <option value="high_end">High End (Above 250,000 LKR)</option>
                            </select>
                        </div>

                        <button type="submit" class="btn cta2-button">Get Recommendation</button>
                    </form>

                    <div id="recommendationResult" style="display: none;">
                        <h3>Recommended Build</h3>
                        <div class="recommended-build">
                            <!-- Results will be populated here -->
                        </div>
                    </div>
                </div>
            </section>

            <!-- Featured Products -->
            <section class="featured-products">
                <h2>Featured Products</h2>
                <div class="product-grid">
                    <?php
                    $stmt = $conn->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 4");
                    while ($product = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo '<div class="product-card">';
                        echo '<img src="' . htmlspecialchars($product['image_url']) . '" alt="' . htmlspecialchars($product['name']) . '">';
                        echo '<h3>' . htmlspecialchars($product['name']) . '</h3>';
                        echo '<p class="price">LKR ' . number_format($product['price'], 2) . '</p>';
                        echo '<a href="products.php?id=' . $product['id'] . '" class="product-link">View Details</a>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </section>
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
            <div class="footer-section">
                <h3>Follow Us</h3>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
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
</body>
</html>