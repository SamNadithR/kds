<?php
require_once('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;
    if (!$user_id) {
        sendError('User ID is required');
    }

    try {
        $stmt = $conn->prepare("SELECT c.*, p.name, p.price, p.image FROM cart c 
                               JOIN products p ON c.product_id = p.id 
                               WHERE c.user_id = ?");
        $stmt->execute([$user_id]);
        $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($cart_items as &$item) {
            if ($item['image']) {
                $item['image_url'] = 'http://' . $_SERVER['HTTP_HOST'] . '/KDS/uploads/products/' . $item['image'];
            }
        }

        sendResponse(['cart_items' => $cart_items]);
    } catch (PDOException $e) {
        sendError('Failed to fetch cart: ' . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['user_id']) || !isset($data['product_id']) || !isset($data['quantity'])) {
        sendError('User ID, product ID and quantity are required');
    }

    try {
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$data['user_id'], $data['product_id'], $data['quantity']]);
        sendResponse(['message' => 'Added to cart successfully']);
    } catch (PDOException $e) {
        sendError('Failed to add to cart: ' . $e->getMessage());
    }
}
?>
