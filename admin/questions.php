<?php
require_once '../config.php';
require_once '../qa.php';

if (!isAdmin()) {
    header('Location: ../login.php');
    exit();
}

// Get unanswered questions
$unanswered_questions = getUnansweredQuestions();

// Get all questions for stats
$stmt = $conn->query("SELECT COUNT(*) FROM qa");
$total_questions = $stmt->fetchColumn();

// Get answered questions count
$stmt = $conn->query("SELECT COUNT(*) FROM qa WHERE answer IS NOT NULL");
$answered_questions = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Questions - KDS Admin</title>
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
        <h2>Manage Questions</h2>

        <!-- Stats Section -->
        <div class="admin-card stats-card">
            <div class="stats-grid">
                <div class="stat-item">
                    <h3>Total Questions</h3>
                    <p class="stat-number"><?php echo $total_questions; ?></p>
                </div>
                <div class="stat-item">
                    <h3>Unanswered</h3>
                    <p class="stat-number"><?php echo count($unanswered_questions); ?></p>
                </div>
                <div class="stat-item">
                    <h3>Answered</h3>
                    <p class="stat-number"><?php echo $answered_questions; ?></p>
                </div>
            </div>
        </div>

        <!-- Unanswered Questions -->
        <div class="admin-card">
            <h3>Unanswered Questions</h3>
            <?php if (empty($unanswered_questions)): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <span>All questions have been answered!</span>
                </div>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Customer Name</th>
                            <th>Question</th>
                            <th>Asked On</th>
                            <th>Product</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($unanswered_questions as $question): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($question['asker_name']); ?></td>
                            <td><?php echo htmlspecialchars($question['question']); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($question['question_date'])); ?></td>
                            <td><?php echo htmlspecialchars($question['product_name']); ?></td>
                            <td>
                                <form method="POST" action="../qa.php" class="answer-form">
                                    <input type="hidden" name="action" value="answer_question">
                                    <input type="hidden" name="qa_id" value="<?php echo $question['qa_id']; ?>">
                                    <div class="answer-input">
                                        <textarea name="answer" required placeholder="Write your answer here..." rows="3"></textarea>
                                        <button type="submit" class="btn btn-primary btn-small">
                                            <i class="fas fa-reply"></i> Answer
                                        </button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Recent Questions -->
        <div class="admin-card">
            <h3>Recent Questions</h3>
            <?php
            // Get recent questions
            $stmt = $conn->query("
                SELECT 
                    q.id as qa_id,
                    q.question,
                    q.question_date,
                    q.answer,
                    q.answer_date,
                    u.username as asker_name,
                    p.name as product_name,
                    a.username as answerer_name
                FROM qa q
                JOIN users u ON q.user_id = u.id
                JOIN products p ON q.product_id = p.id
                LEFT JOIN users a ON q.answerer_id = a.id
                ORDER BY q.question_date DESC
                LIMIT 10
            ");
            $recent_questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($recent_questions)): ?>
                <div class="info-message">
                    <i class="fas fa-info-circle"></i>
                    <span>No questions have been asked yet.</span>
                </div>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Customer Name</th>
                            <th>Question</th>
                            <th>Product</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_questions as $question): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($question['asker_name']); ?></td>
                            <td><?php echo htmlspecialchars($question['question']); ?></td>
                            <td><?php echo htmlspecialchars($question['product_name']); ?></td>
                            <td>
                                <?php if ($question['answer']): ?>
                                    <span class="status answered">Answered</span>
                                    <br>
                                    <small>by <?php echo htmlspecialchars($question['answerer_name']); ?></small>
                                    <br>
                                    <small><?php echo date('M d, Y H:i', strtotime($question['answer_date'])); ?></small>
                                <?php else: ?>
                                    <span class="status unanswered">Unanswered</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$question['answer']): ?>
                                    <form method="POST" action="../qa.php" class="answer-form">
                                        <input type="hidden" name="action" value="answer_question">
                                        <input type="hidden" name="qa_id" value="<?php echo $question['qa_id']; ?>">
                                        <div class="answer-input">
                                            <textarea name="answer" required placeholder="Write your answer here..." rows="3"></textarea>
                                            <button type="submit" class="btn btn-primary btn-small">
                                                <i class="fas fa-reply"></i> Answer
                                            </button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // Auto-scroll to success/error messages
        document.addEventListener('DOMContentLoaded', function() {
            const message = document.querySelector('.success-message, .error-message');
            if (message) {
                message.scrollIntoView({ behavior: 'smooth' });
            }
        });

        // Handle form submissions
        document.querySelectorAll('.answer-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const textarea = this.querySelector('textarea');
                if (textarea.value.trim() === '') {
                    e.preventDefault();
                    textarea.style.borderColor = '#dc3545';
                    setTimeout(() => {
                        textarea.style.borderColor = '';
                    }, 2000);
                }
            });
        });
    </script>
</body>
</html>
