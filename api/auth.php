<?php
require_once('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['email']) || !isset($data['password'])) {
        sendError('Email and password are required');
    }

    $email = $data['email'];
    $password = $data['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Create JWT token
        $token = [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'exp' => time() + (7 * 24 * 60 * 60) // Token expires in 7 days
        ];
        
        $jwt = base64_encode(json_encode($token));
        
        sendResponse([
            'token' => $jwt,
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['name']
            ]
        ]);
    } else {
        sendError('Invalid credentials', 401);
    }
}
?>
