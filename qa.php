<?php
require_once 'config.php';

// Handle question submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isLoggedIn()) {
        $_SESSION['error'] = "Please login to ask a question.";
        header('Location: login.php');
        exit();
    }
    
    if ($_POST['action'] === 'ask_question') {
        $product_id = (int)$_POST['product_id'];
        $question = trim($_POST['question']);
        
        if (empty($question)) {
            $_SESSION['error'] = "Please enter your question.";
        } else {
            $stmt = $conn->prepare("INSERT INTO qa (product_id, user_id, question, question_date) VALUES (?, ?, ?, NOW())");
            if ($stmt->execute([$product_id, $_SESSION['user_id'], $question])) {
                $_SESSION['success'] = "Your question has been submitted successfully!";
            } else {
                $_SESSION['error'] = "Failed to submit question. Please try again.";
            }
        }
    }
    
    // Handle answer submission
    elseif ($_POST['action'] === 'answer_question') {
        if (!isAdmin()) {
            $_SESSION['error'] = "Only administrators can answer questions.";
        } else {
            $qa_id = (int)$_POST['qa_id'];
            $answer = trim($_POST['answer']);
            
            if (empty($answer)) {
                $_SESSION['error'] = "Please enter your answer.";
            } else {
                $stmt = $conn->prepare("UPDATE qa SET answer = ?, answer_date = NOW(), answerer_id = ? WHERE id = ?");
                if ($stmt->execute([$answer, $_SESSION['user_id'], $qa_id])) {
                    $_SESSION['success'] = "Your answer has been submitted successfully!";
                } else {
                    $_SESSION['error'] = "Failed to submit answer. Please try again.";
                }
            }
        }
    }
    
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}

// Get questions and answers for a product
function getQAForProduct($product_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT 
            q.id as qa_id,
            q.question,
            q.question_date,
            q.answer,
            q.answer_date,
            u1.username as asker_name,
            u2.username as answerer_name
        FROM qa q
        JOIN users u1 ON q.user_id = u1.id
        LEFT JOIN users u2 ON q.answerer_id = u2.id
        WHERE q.product_id = ?
        ORDER BY q.question_date DESC
    ");
    $stmt->execute([$product_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get unanswered questions
function getUnansweredQuestions() {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT 
            q.id as qa_id,
            q.question,
            q.question_date,
            u.username as asker_name,
            p.name as product_name
        FROM qa q
        JOIN users u ON q.user_id = u.id
        JOIN products p ON q.product_id = p.id
        WHERE q.answer IS NULL
        ORDER BY q.question_date DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
