<?php
require_once '../config.php';

if (!isAdmin()) {
    header('Location: ../login.php');
    exit();
}

$stats = [
    'products' => $conn->query("SELECT COUNT(*) FROM products")->fetchColumn(),
    'orders' => $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'users' => $conn->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'revenue' => $conn->query("SELECT SUM(total_amount) FROM orders WHERE status != 'cancelled'")->fetchColumn()
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - KDS Computer Store</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <h1>KDS Admin</h1>
            </div>
            <div class="nav-links">
                <a href="index.php">Dashboard</a>
                <a href="products.php">Products</a>
                <a href="orders.php">Orders</a>
                <a href="questions.php">Questions</a>
                <a href="users.php">Users</a>
                <a href="../">View Site</a>
                <a href="../logout.php">Logout</a>
            </div>
        </nav>
    </header>

    <main class="admin-container">
        <h2>Admin Dashboard</h2>
        
        <div class="admin-grid">
            <div class="admin-card">
                <h3><i class="fas fa-box"></i> Products</h3>
                <p class="stat"><?php echo $stats['products']; ?></p>
                <a href="products.php" class="btn btn-primary">Manage Products</a>
            </div>
            
            <div class="admin-card">
                <h3><i class="fas fa-shopping-cart"></i> Orders</h3>
                <p class="stat"><?php echo $stats['orders']; ?></p>
                <a href="orders.php" class="btn btn-primary">View Orders</a>
            </div>
            
            <div class="admin-card">
                <h3><i class="fas fa-users"></i> Users</h3>
                <p class="stat"><?php echo $stats['users']; ?></p>
                <a href="users.php" class="btn btn-primary">Manage Users</a>
            </div>
            
            <div class="admin-card">
                <h3><i class="fas fa-dollar-sign"></i> Revenue</h3>
                <p class="stat">LKR <?php echo number_format($stats['revenue'], 2); ?></p>
                <a href="orders.php" class="btn btn-primary">View Details</a>
            </div>
            
            <div class="admin-card">
                <h3><i class="fas fa-question-circle"></i> Customer Questions</h3>
                <p class="stat"><?php 
                    $stmt = $conn->query("SELECT COUNT(*) FROM questions WHERE id NOT IN (SELECT question_id FROM answers)");
                    echo $stmt->fetchColumn();
                ?></p>
                <a href="questions.php" class="btn btn-primary">Manage Questions</a>
            </div>
        </div>

        <div class="admin-card" style="margin-top: 2rem;">
            <h3>Recent Orders</h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $conn->query("
                        SELECT o.*, u.username 
                        FROM orders o 
                        JOIN users u ON o.user_id = u.id 
                        ORDER BY o.created_at DESC 
                        LIMIT 5
                    ");
                    while ($order = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo '<tr>';
                        echo '<td>#' . $order['id'] . '</td>';
                        echo '<td>' . htmlspecialchars($order['username']) . '</td>';
                        echo '<td>LKR ' . number_format($order['total_amount'], 2) . '</td>';
                        echo '<td>' . ucfirst($order['status']) . '</td>';
                        echo '<td>' . date('M d, Y', strtotime($order['created_at'])) . '</td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>

    <script src="../js/main.js"></script>
</body>
</html>
