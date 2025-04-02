<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - KDS Computer Store</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <h1>KDS Computers</h1>
            </div>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="products.php">Products</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="cart.php">Cart</a>
                    <a href="my_orders.php">My Orders</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main class="container">
        <h1>About Us</h1>
        <div class="about-content">
            <p>Welcome to KDS Computer Store, your trusted destination for all your computing needs. Established with a vision to provide quality computer hardware and accessories, we take pride in offering our customers the latest technology at competitive prices.</p>
            
            <h2>Our Mission</h2>
            <p>To provide high-quality computer products and exceptional customer service, making technology accessible to everyone.</p>
            
            <h2>Why Choose Us?</h2>
            <ul>
                <li>Wide selection of products</li>
                <li>Competitive prices</li>
                <li>Expert technical support</li>
                <li>Fast and reliable shipping</li>
                <li>Secure shopping experience</li>
            </ul>
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
</body>
</html>
