<?php
require_once '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    if (!isLoggedIn()) {
        throw new Exception('You must be logged in to use this feature');
    }

    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    switch ($action) {
        case 'ask_question':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['product_id']) || !isset($data['question'])) {
                throw new Exception('Product ID and question are required');
            }

            $stmt = $conn->prepare("
                INSERT INTO qa (product_id, user_id, question, question_date)
                VALUES (?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $data['product_id'],
                $_SESSION['user_id'],
                $data['question']
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Question submitted successfully'
            ]);
            break;

        case 'get_questions':
            $product_id = $_GET['product_id'] ?? '';
            
            if (!$product_id) {
                throw new Exception('Product ID is required');
            }

            $stmt = $conn->prepare("
                SELECT 
                    q.id,
                    q.product_id,
                    q.question,
                    q.question_date,
                    q.answer,
                    q.answer_date,
                    u1.username as asker_name,
                    u2.username as answerer_name
                FROM qa q
                LEFT JOIN users u1 ON q.user_id = u1.id
                LEFT JOIN users u2 ON q.answerer_id = u2.id
                WHERE q.product_id = ?
                ORDER BY q.question_date DESC
            ");
            
            $stmt->execute([$product_id]);
            $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'questions' => $questions
            ]);
            break;

        case 'reply_to_question':
            if (!isAdmin()) {
                throw new Exception('Only admins can reply to questions');
            }

            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['qa_id']) || !isset($data['answer'])) {
                throw new Exception('Question ID and answer are required');
            }

            $stmt = $conn->prepare("
                UPDATE qa 
                SET answer = ?, answer_date = NOW(), answerer_id = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $data['answer'],
                $_SESSION['user_id'],
                $data['qa_id']
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Answer submitted successfully'
            ]);
            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    error_log('Error in qa.php: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
