<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        
        switch ($_POST['action']) {
            case 'update':
                $quantity = max(1, (int)$_POST['quantity']);
                $_SESSION['cart'][$product_id] = $quantity;
                break;
                
            case 'remove':
                unset($_SESSION['cart'][$product_id]);
                break;
                
            case 'add':
                $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
                $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
                
                // Validate input
                if ($product_id <= 0 || $quantity <= 0) {
                    $_SESSION['error_message'] = "Invalid product or quantity";
                    header('Location: product-detail.php?id=' . $product_id);
                    exit();
                }
                
                // Check if product exists and has enough stock
                $stmt = $conn->prepare("SELECT id, stock, price FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$product) {
                    $_SESSION['error_message'] = "Product not found";
                    header('Location: products.php');
                    exit();
                }
                
                if ($product['stock'] < $quantity) {
                    $_SESSION['error_message'] = "Not enough stock available. Maximum available: " . $product['stock'];
                    header('Location: product-detail.php?id=' . $product_id);
                    exit();
                }
                
                // Add to cart or update existing quantity
                if (isset($_SESSION['cart'][$product_id])) {
                    $_SESSION['cart'][$product_id] += $quantity;
                } else {
                    $_SESSION['cart'][$product_id] = $quantity;
                }
                
                // Update product stock
                $new_stock = $product['stock'] - $quantity;
                $stmt = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
                $stmt->execute([$new_stock, $product_id]);
                
                // Set success message
                $_SESSION['success_message'] = "Product added to cart successfully!";
                
                // Redirect to cart page
                header('Location: cart.php');
                exit();
                break;
                
            case 'checkout':
                if (!empty($_SESSION['cart'])) {
                    $total_amount = 0;
                    
                    // Calculate total amount
                    foreach ($_SESSION['cart'] as $pid => $qty) {
                        $stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
                        $stmt->execute([$pid]);
                        $price = $stmt->fetchColumn();
                        $total_amount += $price * $qty;
                    }
                    
                    // Get user's address and contact information
                    $stmt = $conn->prepare("SELECT address, contact_number FROM users WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $user_info = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Create order with customer information
                    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, address, contact_number) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$_SESSION['user_id'], $total_amount, $user_info['address'], $user_info['contact_number']]);
                    $order_id = $conn->lastInsertId();
                    
                    // Create order items
                    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                    foreach ($_SESSION['cart'] as $pid => $qty) {
                        $product_stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
                        $product_stmt->execute([$pid]);
                        $price = $product_stmt->fetchColumn();
                        
                        $stmt->execute([$order_id, $pid, $qty, $price]);
                        
                        // Update product stock
                        $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?")->execute([$qty, $pid]);
                    }
                    
                    // Clear cart
                    $_SESSION['cart'] = [];
                    
                    header('Location: order_confirmation.php?id=' . $order_id);
                    exit();
                }
                break;
        }
        
        if (isset($_POST['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit();
        }
        
        header('Location: cart.php');
        exit();
    }
}

// Get cart items
$cart_items = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
    $placeholders = str_repeat('?,', count(array_keys($_SESSION['cart'])) - 1) . '?';
    $stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute(array_keys($_SESSION['cart']));
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($products as $product) {
        $quantity = $_SESSION['cart'][$product['id']];
        $subtotal = $product['price'] * $quantity;
        $total += $subtotal;
        
        $cart_items[] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => $quantity,
            'subtotal' => $subtotal,
            'image_url' => $product['image_url']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - KDS Computer Store</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                <a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a>
                <a href="logout.php">Logout</a>
            </div>
        </nav>
    </header>

    <main class="admin-container">
        <h2>Shopping Cart</h2>
        
        <?php if (empty($cart_items)): ?>
            <div class="admin-card">
                <p>Your cart is empty. <a href="products.php">Continue shopping</a></p>
            </div>
        <?php else: ?>
            <div class="admin-card">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 1rem;">
                                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>"
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                        <span><?php echo htmlspecialchars($item['name']); ?></span>
                                    </div>
                                </td>
                                <td>LKR <?php echo number_format($item['price'], 2); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                               min="1" style="width: 60px;" onchange="this.form.submit()">
                                    </form>
                                </td>
                                <td>LKR <?php echo number_format($item['subtotal'], 2); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3"><strong>Total:</strong></td>
                            <td colspan="2"><strong>LKR <?php echo number_format($total, 2); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>

                <div style="text-align: right; margin-top: 1rem;">
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="checkout">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-shopping-cart"></i> Checkout
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
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

    <script src="js/main.js"></script>
</body>
</html>
