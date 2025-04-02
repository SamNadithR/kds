-- Create qa table
CREATE TABLE IF NOT EXISTS qa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    question TEXT NOT NULL,
    question_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    answer TEXT,
    answer_date DATETIME,
    answerer_id INT,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (answerer_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create indexes for better performance
CREATE INDEX idx_product_id ON qa(product_id);
CREATE INDEX idx_user_id ON qa(user_id);
CREATE INDEX idx_answerer_id ON qa(answerer_id);
CREATE INDEX idx_question_date ON qa(question_date);
CREATE INDEX idx_answer_date ON qa(answer_date);
