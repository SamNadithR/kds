<?php
require_once('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $conn->query("SELECT * FROM products");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add image URLs
        foreach ($products as &$product) {
            if ($product['image']) {
                $product['image_url'] = 'http://' . $_SERVER['HTTP_HOST'] . '/KDS/uploads/products/' . $product['image'];
            }
        }
        
        sendResponse(['products' => $products]);
    } catch (PDOException $e) {
        sendError('Failed to fetch products: ' . $e->getMessage());
    }
}
?>
