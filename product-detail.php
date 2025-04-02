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
    <style>
        .qa-section {
            margin-top: 40px;
            padding: 20px;
            background: #121212;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1)
            color:#fff;
        }

        .ask-question {
            margin-bottom: 30px;
            padding: 20px;
            background: #121212;
            border-radius: 8px;
        }

        .qa-form textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            min-height: 100px;
            color: #fff;
            background-color: #121220;
        }

        .qa-list {
            margin-top: 20px;
        }

        .question-item {
            border-bottom: 1px solid #444;
            padding: 20px 0;
        }

        .question-item:last-child {
            border-bottom: none;
        }

        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            color: #b3b3b3;
        }

        .question-author {
            font-weight: 600;
            color: #fff;
        }

        .question-date {
            font-size: 0.9em;
            color: #b3b3b3;
        }

        .question-content {
            margin-bottom: 20px;
            color: #fff;
        }

        .answer {
            background: #121220;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .answer-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            color: #b3b3b3;
        }

        .answer-author {
            font-weight: 600;
            color: #fff;
        }

        .answer-date {
            font-size: 0.9em;
            color: #b3b3b3;
        }

        .answer-content {
            color: #fff;
        }

        .no-questions {
            color: #b3b3b3;
            text-align: center;
            padding: 20px;
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
                <h3 style="color: #fff;">Questions & Answers</h3>
                
                <?php if (isLoggedIn()): ?>
                    <div class="ask-question">
                    
                        <form method="POST" action="qa.php" class="qa-form">
                            <input type="hidden" name="action" value="ask_question">
                            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                            <div class="form-group">
                                <label for="question">Your Question:</label>
                                <textarea id="question" name="question" rows="3" required placeholder="Ask your question about this product..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Question</button>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="qa-list">
                    <h4>Customer Questions</h4>
                    <?php
                    // Get questions and answers for this product
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
                    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (empty($questions)): ?>
                        <p class="no-questions">No questions have been asked yet.</p>
                    <?php else: ?>
                        <?php foreach ($questions as $question): ?>
                            <div class="question-item">
                                <div class="question-header">
                                    <span class="question-author"><?php echo htmlspecialchars($question['asker_name']); ?></span>
                                    <span class="question-date"><?php echo date('M d, Y', strtotime($question['question_date'])); ?></span>
                                </div>
                                <div class="question-content">
                                    <p><?php echo nl2br(htmlspecialchars($question['question'])); ?></p>
                                </div>
                                <?php if ($question['answer']): ?>
                                    <div class="answer">
                                        <div class="answer-header">
                                            <span class="answer-author"><?php echo htmlspecialchars($question['answerer_name']); ?></span>
                                            <span class="answer-date"><?php echo date('M d, Y', strtotime($question['answer_date'])); ?></span>
                                        </div>
                                        <div class="answer-content">
                                            <p><?php echo nl2br(htmlspecialchars($question['answer'])); ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
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
    </script>
</body>
</html>
