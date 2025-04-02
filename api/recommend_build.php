<?php
require_once '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method is allowed');
    }

    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['purpose']) || !isset($data['budget'])) {
        throw new Exception('Purpose and budget are required');
    }
    
    $purpose = $data['purpose'];
    $budget = $data['budget'];

    // Query the database for matching builds
    $stmt = $conn->prepare("
        SELECT * FROM pc_builds 
        WHERE purpose = ? AND budget_range = ?
        LIMIT 1
    ");
    $stmt->execute([$purpose, $budget]);
    $build = $stmt->fetch(PDO::FETCH_ASSOC);

    // If no exact match, try to find a build with the same purpose
    if (!$build) {
        $stmt = $conn->prepare("
            SELECT * FROM pc_builds 
            WHERE purpose = ?
            ORDER BY FIELD(budget_range, 'budget', 'mid_range', 'high_end')
            LIMIT 1
        ");
        $stmt->execute([$purpose]);
        $build = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // If still no match, return any build
    if (!$build) {
        $stmt = $conn->prepare("
            SELECT * FROM pc_builds 
            LIMIT 1
        ");
        $stmt->execute();
        $build = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if (!$build) {
        throw new Exception('No builds found in the database');
    }

    // Prepare the response
    $components = [
        'cpu' => [
            'name' => $build['processor'],
            'price' => 0 // We'll calculate these prices based on the total price and typical ratios
        ],
        'gpu' => [
            'name' => $build['gpu'],
            'price' => 0
        ],
        'motherboard' => [
            'name' => $build['motherboard'],
            'price' => 0
        ],
        'ram' => [
            'name' => $build['ram'],
            'price' => 0
        ],
        'storage' => [
            'name' => $build['storage'],
            'price' => 0
        ],
        'power_supply' => [
            'name' => $build['psu'],
            'price' => 0
        ],
        'case' => [
            'name' => $build['case_type'],
            'price' => 0
        ]
    ];

    // Calculate component prices based on typical ratios (these are approximate)
    $total_price = floatval($build['total_price']);
    $components['cpu']['price'] = $total_price * 0.25;  // CPU: 25% of total
    $components['gpu']['price'] = $total_price * 0.25;  // GPU: 25% of total
    $components['motherboard']['price'] = $total_price * 0.10;  // Motherboard: 10% of total
    $components['ram']['price'] = $total_price * 0.05;  // RAM: 5% of total
    $components['storage']['price'] = $total_price * 0.05;  // Storage: 5% of total
    $components['power_supply']['price'] = $total_price * 0.05;  // PSU: 5% of total
    $components['case']['price'] = $total_price * 0.05;  // Case: 5% of total

    $response = [
        'success' => true,
        'build' => [
            'name' => $build['name'],
            'description' => $build['description'],
            'components' => $components,
            'total_price' => $total_price
        ]
    ];
    
    echo json_encode($response);

} catch (Exception $e) {
    error_log('Error in recommend_build.php: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>