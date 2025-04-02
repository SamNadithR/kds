<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}


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

// First, let's check the structure of the orders table
$stmt = $conn->query("DESCRIBE orders");
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Determine the date column name (could be order_date, created_at, date, etc.)
$dateColumn = 'id'; // Default fallback to order by ID if no date column found
foreach ($columns as $column) {
    if (strpos(strtolower($column), 'date') !== false || $column == 'created_at') {
        $dateColumn = $column;
        break;
    }
}

// Get user's orders with the correct column names
$stmt = $conn->prepare("
    SELECT o.*, COUNT(oi.id) as item_count 
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    WHERE o.user_id = ? 
    GROUP BY o.id 
    ORDER BY o.$dateColumn DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include header if it exists
$headerFile = 'includes/header.php';
$footerFile = 'includes/footer.php';

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper function to check if user is admin
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - KDS Store</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .orders-section {
            margin-top: 30px;
            background-color: #121212;
        }
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #121212;
        }
        .orders-table th, .orders-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .orders-table th {
            background-color: #121212;
            font-weight: bold;
        }
        .orders-table tr:hover {
            background-color: #121220;
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
            font-size: 14px;
        }
        .secondary-btn {
            background-color: #6c757d;
        }
        .no-orders {
            padding: 20px;
            text-align: center;
            background-color: #f8f9fa;
            border-radius: 5px;
            margin-top: 20px;
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
        <h1>My Orders</h1>
        
        <section class="orders-section">
            <?php if (count($orders) > 0): ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td>
                                    <?php 
                                    // Display date if available, otherwise show ID
                                    if (isset($order[$dateColumn]) && !empty($order[$dateColumn])) {
                                        echo date('M d, Y', strtotime($order[$dateColumn]));
                                    } else {
                                        echo "Order #" . $order['id'];
                                    }
                                    ?>
                                </td>
                                <td><?php echo $order['item_count']; ?> item(s)</td>
                                <td>LKR<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <?php 
                                    $status = isset($order['status']) ? $order['status'] : 'pending';
                                    $statusClass = 'status-' . strtolower($status);
                                    echo '<span class="status-badge ' . $statusClass . '">' . ucfirst($status) . '</span>';
                                    ?>
                                </td>
                                <td>
                                    <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn">View Details</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-orders">
                    <p>You haven't placed any orders yet.</p>
                    <a href="products.php" class="btn">Start Shopping</a>
                </div>
            <?php endif; ?>
        </section>
    </main>
    
    <?php if (file_exists($footerFile)) include $footerFile; ?>
</body>
</html>