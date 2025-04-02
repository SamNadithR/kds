<?php
require_once 'config.php';
require_once 'qa.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header('Location: products.php');
    exit();
}

// Get product details
$stmt = $conn->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: products.php');
    exit();
}

// Get Q&A for this product
$qa = getQAForProduct($product_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - KDS Computer Store</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

    <main class="admin-container">
        <!-- Display messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php 
                    echo htmlspecialchars($_SESSION['error']);
                    unset($_SESSION['error']);
                ?></span>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <span><?php 
                    echo htmlspecialchars($_SESSION['success']);
                    unset($_SESSION['success']);
                ?></span>
            </div>
        <?php endif; ?>

        <div class="product-detail">
            <div class="product-info">
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <div class="product-details">
                    <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                    <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                    <p class="price">LKR <?php echo number_format($product['price'], 2); ?></p>
                    <p class="stock <?php echo $product['stock'] > 0 ? 'in-stock' : ''; ?>">
                        <?php echo $product['stock'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                    </p>
                    <p class="description"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    
                    <?php if ($product['stock'] > 0): ?>
                        <form method="POST" action="cart.php" class="add-to-cart-form">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <div class="quantity-selector">
                                <label for="quantity">Quantity:</label>
                                <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                            </div>
                            <br><br>
                            <button type="submit" class="btn btn-primary btn-large">
                                <i class="fas fa-shopping-cart"></i> Add to Cart
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Q&A Section -->
            <div class="qa-section" id="qa-section">
                <h3>Questions & Answers</h3>
                
                <?php if (isLoggedIn()): ?>
                    <div class="ask-question">
                        <h4>Ask a Question</h4>
                        <form id="askQuestionForm" class="qa-form">
                            <div class="form-group">
                                <label for="question">Your Question:</label>
                                <textarea id="question" name="question" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Question</button>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="qa-list">
                    <h4>Customer Questions</h4>
                    <div id="questionsList">
                        <!-- Questions will be loaded here via JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            <div class="related-products">
                <h3>Related Products</h3>
                <div class="related-products-list">
                    <?php
                    $stmt = $conn->prepare("
                        SELECT p.*, c.name as category_name 
                        FROM products p 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        WHERE p.category_id = ? AND p.id != ?
                    ");
                    $stmt->execute([$product['category_id'], $product_id]);
                    $related_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <?php foreach ($related_products as $related_product): ?>
                        <div class="related-product">
                            <img src="<?php echo htmlspecialchars($related_product['image_url']); ?>" alt="<?php echo htmlspecialchars($related_product['name']); ?>">
                            <div class="related-product-info">
                                <h4><?php echo htmlspecialchars($related_product['name']); ?></h4>
                                <p class="price">LKR <?php echo number_format($related_product['price'], 2); ?></p>
                                <a href="product-detail.php?id=<?php echo $related_product['id']; ?>">View Details</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>About Us</h3>
                <p>KDS Computers is your one-stop shop for all your computer needs. We offer a wide range of products and excellent customer service.</p>
            </div>
            <div class="footer-section">
                <h3>Contact Us</h3>
                <p>Email: info@kdsc.com</p>
                <p>Phone: +1 234 567 890</p>
            </div>
            <div class="footer-section">
                <h3>Follow Us</h3>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> KDS Computers. All rights reserved.</p>
        </div>
    </footer>

    <script>
        const productId = <?php echo $product_id; ?>;

        // Load questions when page loads
        function loadQuestions() {
            fetch(`api/qa.php?action=get_questions&product_id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const questionsList = document.getElementById('questionsList');
                        questionsList.innerHTML = data.questions.map(q => `
                            <div class="question-item">
                                <div class="question-header">
                                    <span class="asker">${q.asker_name}</span>
                                    <span class="date">${new Date(q.question_date).toLocaleDateString()}</span>
                                </div>
                                <div class="question-content">
                                    <p><strong>Question:</strong> ${q.question}</p>
                                    ${q.answer ? `
                                        <p><strong>Answer:</strong> ${q.answer}</p>
                                        <span class="answerer">Answered by: ${q.answerer_name}</span>
                                        <span class="answer-date">${new Date(q.answer_date).toLocaleDateString()}</span>
                                    ` : `
                                        ${isAdmin() ? `
                                            <button onclick="showReplyForm(${q.id})" class="btn btn-primary btn-small">Reply</button>
                                        ` : ''}
                                    `}
                                </div>
                            </div>
                        `).join('');
                    }
                })
                .catch(error => console.error('Error loading questions:', error));
        }

        // Show reply form for admins
        function showReplyForm(qaId) {
            const replyForm = document.createElement('div');
            replyForm.className = 'reply-form';
            replyForm.innerHTML = `
                <form onsubmit="handleReplySubmit(event, ${qaId})">
                    <textarea name="answer" required placeholder="Enter your answer..." rows="3"></textarea>
                    <button type="submit" class="btn btn-primary">Submit Answer</button>
                </form>
            `;
            
            const questionItem = document.querySelector(`.question-item:has(button[onclick*="${qaId}"])`);
            if (questionItem) {
                questionItem.appendChild(replyForm);
                
                // Remove existing reply button
                const replyButton = questionItem.querySelector('button');
                if (replyButton) {
                    replyButton.remove();
                }
            }
        }

        // Handle reply submission
        function handleReplySubmit(event, qaId) {
            event.preventDefault();
            const form = event.target;
            const answer = form.answer.value;

            fetch('api/qa.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'reply_to_question',
                    qa_id: qaId,
                    answer: answer
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    form.parentElement.remove();
                    loadQuestions();
                } else {
                    alert('Error submitting answer: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error submitting answer');
            });
        }

        // Handle question submission
        document.getElementById('askQuestionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const question = this.querySelector('textarea').value;

            fetch('api/qa.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'ask_question',
                    product_id: productId,
                    question: question
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.reset();
                    loadQuestions();
                } else {
                    alert('Error submitting question: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error submitting question');
            });
        });

        // Load questions when page loads
        document.addEventListener('DOMContentLoaded', loadQuestions);

        // Auto-scroll to success/error messages
        document.addEventListener('DOMContentLoaded', function() {
            const message = document.querySelector('.success-message, .error-message');
            if (message) {
                message.scrollIntoView({ behavior: 'smooth' });
            }
        });

        // Handle form submissions
        document.querySelectorAll('.qa-form').forEach(form => {
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

        // Handle quantity input
        document.querySelectorAll('.quantity-selector input').forEach(input => {
            input.addEventListener('input', function() {
                const max = parseInt(this.getAttribute('max'));
                const value = parseInt(this.value);
                if (value > max) {
                    this.value = max;
                } else if (value < 1) {
                    this.value = 1;
                }
            });
        });
    </script>
</body>
</html>
