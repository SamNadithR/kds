-- Delete existing data to start fresh
DELETE FROM qa;

-- Migrate questions
INSERT INTO qa (
    product_id,
    user_id,
    question,
    question_date
)
SELECT 
    q.product_id,
    q.user_id,
    q.question,
    q.created_at
FROM questions q;

-- Migrate answers
UPDATE qa q
JOIN answers a ON q.id = a.question_id
SET 
    q.answer = a.answer,
    q.answer_date = a.created_at,
    q.answerer_id = a.user_id;
