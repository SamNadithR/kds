<?php
require_once 'config.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Simple contact form processing
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $message_text = $_POST['message'] ?? '';
    
    if ($name && $email && $message_text) {
        $message = 'Thank you for your message. We will get back to you soon!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - KDS Computer Store</title>
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
        <h1>Contact Us</h1>
        
        <?php if ($message): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="contact-content">
            <div class="contact-info">
                <h2>Our Information</h2>
                <p><strong>Address:</strong> 123 Computer Street, Tech City</p>
                <p><strong>Email:</strong> info@kds.com</p>
                <p><strong>Phone:</strong> (123) 456-7890</p>
                <p><strong>Business Hours:</strong> Monday - Saturday: 9:00 AM - 6:00 PM</p>
            </div>

            <div class="contact-form">
                <h2>Send us a Message</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" rows="5" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
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
</body>
</html>
