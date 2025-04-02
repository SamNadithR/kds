<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: my_orders.php');
    exit;
}

$orderId = $_GET['id'];

// Check if database connection file exists
if (file_exists('config/database.php')) {
    require_once 'config/database.php';
} else {
    // Direct database connection if config file doesn't exist
    $host = 'localhost';
    $dbname = 'kds_store';
    $username = 'root';
    $password = '';
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Get order details
$stmt = $conn->prepare("
    SELECT * FROM orders 
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

// Redirect if order doesn't exist or doesn't belong to user
if (!$order) {
    header('Location: my_orders.php');
    exit;
}

// First, check the structure of the products table
$stmt = $conn->query("DESCRIBE products");
$productColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Check if image column exists
$hasImageColumn = in_array('image', $productColumns);
$hasImageUrl = in_array('image_url', $productColumns);
$imageColumn = $hasImageColumn ? 'image' : ($hasImageUrl ? 'image_url' : null);

// Get order items with appropriate columns
if ($imageColumn) {
    $stmt = $conn->prepare("
        SELECT oi.*, p.name, p.$imageColumn 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
} else {
    $stmt = $conn->prepare("
        SELECT oi.*, p.name 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
}
$stmt->execute([$orderId]);
$orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

// Include header if it exists
$headerFile = 'includes/header.php';
$footerFile = 'includes/footer.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - KDS Store</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .order-details {
            margin-top: 30px;
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
        .order-meta {
            margin-bottom: 30px;
        }
        .order-meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        .meta-box {
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .meta-box h3 {
            margin-top: 0;
            font-size: 16px;
            color: #6c757d;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        .items-table th, .items-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .items-table th {
            background-color: #121212
;
            font-weight: bold;
        }
        .product-cell {
            display: flex;
            align-items: center;
        }
        .product-image {
            width: 60px;
            height: 60px;
            margin-right: 15px;
            object-fit: cover;
            border-radius: 4px;
        }
        .product-image-placeholder {
            width: 60px;
            height: 60px;
            margin-right: 15px;
            background-color: #f1f1f1;
            border-radius: 4px;
        }
        .order-summary {
            margin-top: 30px;
            background-color: #121212   ;
            padding: 20px;
            border-radius: 5px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }
        .summary-row.total {
            font-weight: bold;
            font-size: 18px;
            border-top: 1px solid #ddd;
            padding-top: 15px;
            margin-top: 10px;
        }
        .btn {
            display: inline-block;
            padding: 8px 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .secondary-btn {
            background-color: #6c757d;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-pending {
            background-color: #ffc107;
            color: #212529;
        }
        .status-processing {
            background-color: #17a2b8;
            color: white;
        }
        .status-shipped {
            background-color: #007bff;
            color: white;
        }
        .status-delivered {
            background-color: #28a745;
            color: white;
        }
        .status-cancelled {
            background-color: #dc3545;
            color: white;
        }
        .dark-table {
            background-color: #000;
            color: #fff;
        }
        .white-text {
            color: #fff;
        }
    </style>
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
                <?php if (isLoggedIn()): ?>
                    <a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    <?php if (file_exists($headerFile)) include $headerFile; ?>
    
    <main class="container">
        <div class="order-details">
            <div class="order-header">
                <div>
                    <h1>Order #<?php echo $order['id']; ?></h1>
                    <?php 
                    // Check if order has a date field
                    $dateField = null;
                    foreach ($order as $field => $value) {
                        if (strpos(strtolower($field), 'date') !== false || $field == 'created_at') {
                            $dateField = $field;
                            break;
                        }
                    }
                    
                    if ($dateField && !empty($order[$dateField])): 
                    ?>
                        <p>Placed on <?php echo date('F d, Y', strtotime($order[$dateField])); ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <?php 
                    $status = isset($order['status']) ? $order['status'] : 'pending';
                    $statusClass = 'status-' . strtolower($status);
                    echo '<span class="status-badge ' . $statusClass . '">' . ucfirst($status) . '</span>';
                    ?>
                </div>
            </div>
            
            <div class="order-meta">
                <div class="order-meta-grid dark-table">
                    <?php if (isset($order['shipping_address']) && !empty($order['shipping_address'])): ?>
                    <div class="meta-box white-text">
                        <h3>Shipping Address</h3>
                        <p>
                            <?php echo $order['shipping_address']; ?><br>
                            <?php if (isset($order['shipping_city'])) echo $order['shipping_city']; ?> 
                            <?php if (isset($order['shipping_state'])) echo ', ' . $order['shipping_state']; ?><br>
                            <?php if (isset($order['shipping_zip'])) echo $order['shipping_zip']; ?><br>
                            <?php if (isset($order['shipping_country'])) echo $order['shipping_country']; ?>
                        </p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($order['payment_method']) && !empty($order['payment_method'])): ?>
                    <div class="meta-box white-text">
                        <h3>Payment Method</h3>
                        <p><?php echo ucfirst($order['payment_method']); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($order['customer_name']) || isset($order['customer_email']) || isset($order['customer_phone'])): ?>
                    <div class="meta-box white-text">
                        <h3>Contact Information</h3>
                        <p>
                            <?php if (isset($order['customer_name'])) echo $order['customer_name'] . '<br>'; ?>
                            <?php if (isset($order['customer_email'])) echo $order['customer_email'] . '<br>'; ?>
                            <?php if (isset($order['customer_phone'])) echo $order['customer_phone']; ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <h2>Order Items</h2>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderItems as $item): ?>
                        <tr>
                            <td>
                                <div class="product-cell">
                                    <?php if ($imageColumn && isset($item[$imageColumn]) && !empty($item[$imageColumn])): ?>
                                        <img src="<?php echo $item[$imageColumn]; ?>" alt="<?php echo $item['name']; ?>" class="product-image">
                                    <?php else: ?>
                                        <div class="product-image-placeholder"></div>
                                    <?php endif; ?>
                                    <div>
                                        <div><?php echo $item['name']; ?></div>
                                        <?php if (isset($item['options']) && !empty($item['options'])): ?>
                                            <small><?php echo $item['options']; ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>LKR<?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>LKR<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="order-summary">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>LKR<?php echo number_format(isset($order['subtotal']) ? $order['subtotal'] : $order['total_amount'], 2); ?></span>
                </div>
                <?php if (isset($order['shipping_cost']) && $order['shipping_cost'] > 0): ?>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span>LKR<?php echo number_format($order['shipping_cost'], 2); ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($order['tax']) && $order['tax'] > 0): ?>
                <div class="summary-row">
                    <span>Tax</span>
                    <span>LKR<?php echo number_format($order['tax'], 2); ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($order['discount']) && $order['discount'] > 0): ?>
                <div class="summary-row">
                    <span>Discount</span>
                    <span>-LKR<?php echo number_format($order['discount'], 2); ?></span>
                </div>
                <?php endif; ?>
                <div class="summary-row total">
                    <span>Total</span>
                    <span>LKR<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>
            
            <div style="margin-top: 30px; text-align: center;">
                <a href="my_orders.php" class="btn secondary-btn">Back to My Orders</a>
            </div>
        </div>
    </main>
    
    <?php if (file_exists($footerFile)) include $footerFile; ?>
</body>
</html>