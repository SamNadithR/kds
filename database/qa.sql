-- Create Q&A table
CREATE TABLE IF NOT EXISTS qa (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    question TEXT NOT NULL,
    question_date DATETIME NOT NULL,
    answer TEXT,
    answer_date DATETIME,
    answerer_id INT,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (answerer_id) REFERENCES users(id)
);

-- Add indexes for better performance
CREATE INDEX idx_qa_product ON qa(product_id);
CREATE INDEX idx_qa_user ON qa(user_id);
CREATE INDEX idx_qa_date ON qa(question_date);
