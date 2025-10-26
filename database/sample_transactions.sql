-- Get user IDs
SET @hannah_id = (SELECT id FROM users WHERE email = 'hannahastokes@icloud.com');
SET @haven_id = (SELECT id FROM users WHERE email = 'havenastokes@icloud.com');

-- Clear existing transactions
DELETE FROM transactions;

-- Add transactions for Hannah (last 6 months)
INSERT INTO transactions (user_id, amount, type, reason, created_at) VALUES
-- Current month (October 2025)
(@hannah_id, 20.00, 'credit', 'Weekly allowance - Week 1', '2025-10-07'),
(@hannah_id, 20.00, 'credit', 'Weekly allowance - Week 2', '2025-10-14'),
(@hannah_id, -5.00, 'deduction', 'School points deduction', '2025-10-14'),
(@hannah_id, 20.00, 'credit', 'Weekly allowance - Week 3', '2025-10-21'),

-- September 2025
(@hannah_id, 20.00, 'credit', 'Weekly allowance - Week 1', '2025-09-02'),
(@hannah_id, 20.00, 'credit', 'Weekly allowance - Week 2', '2025-09-09'),
(@hannah_id, 20.00, 'credit', 'Weekly allowance - Week 3', '2025-09-16'),
(@hannah_id, 20.00, 'credit', 'Weekly allowance - Week 4', '2025-09-23'),
(@hannah_id, -5.00, 'deduction', 'Incomplete kitchen cleaning', '2025-09-23'),
(@hannah_id, 20.00, 'credit', 'Weekly allowance - Week 5', '2025-09-30'),

-- August 2025
(@hannah_id, 20.00, 'credit', 'Weekly allowance - Week 1', '2025-08-05'),
(@hannah_id, 20.00, 'credit', 'Weekly allowance - Week 2', '2025-08-12'),
(@hannah_id, -5.00, 'deduction', 'School points deduction', '2025-08-12'),
(@hannah_id, 20.00, 'credit', 'Weekly allowance - Week 3', '2025-08-19'),
(@hannah_id, 20.00, 'credit', 'Weekly allowance - Week 4', '2025-08-26'),

-- July 2025
(@hannah_id, 20.00, 'credit', 'Weekly allowance - Week 1', '2025-07-01'),
(@hannah_id, 20.00, 'credit', 'Weekly allowance - Week 2', '2025-07-08'),
(@hannah_id, 20.00, 'credit', 'Weekly allowance - Week 3', '2025-07-15'),
(@hannah_id, -5.00, 'deduction', 'Incomplete bathroom cleaning', '2025-07-15'),
(@hannah_id, 20.00, 'credit', 'Weekly allowance - Week 4', '2025-07-22'),
(@hannah_id, 20.00, 'credit', 'Weekly allowance - Week 5', '2025-07-29'),

-- June 2025
(@hannah_id, 20.00, 'credit', 'Weekly allowance - Week 1', '2025-06-03'),
(@hannah_id, 20.00, 'credit', 'Weekly allowance - Week 2', '2025-06-10'),
(@hannah_id, 20.00, 'credit', 'Weekly allowance - Week 3', '2025-06-17'),
(@hannah_id, 20.00, 'credit', 'Weekly allowance - Week 4', '2025-06-24'),

-- May 2025
(@hannah_id, 20.00, 'credit', 'Weekly allowance - Week 1', '2025-05-06'),
(@hannah_id, 20.00, 'credit', 'Weekly allowance - Week 2', '2025-05-13'),
(@hannah_id, -5.00, 'deduction', 'School points deduction', '2025-05-13'),
(@hannah_id, 20.00, 'credit', 'Weekly allowance - Week 3', '2025-05-20'),
(@hannah_id, 20.00, 'credit', 'Weekly allowance - Week 4', '2025-05-27'),

-- Add transactions for Haven (last 6 months)
-- Current month (October 2025)
(@haven_id, 20.00, 'credit', 'Weekly allowance - Week 1', '2025-10-07'),
(@haven_id, 20.00, 'credit', 'Weekly allowance - Week 2', '2025-10-14'),
(@haven_id, 20.00, 'credit', 'Weekly allowance - Week 3', '2025-10-21'),

-- September 2025
(@haven_id, 20.00, 'credit', 'Weekly allowance - Week 1', '2025-09-02'),
(@haven_id, -5.00, 'deduction', 'School points deduction', '2025-09-02'),
(@haven_id, 20.00, 'credit', 'Weekly allowance - Week 2', '2025-09-09'),
(@haven_id, 20.00, 'credit', 'Weekly allowance - Week 3', '2025-09-16'),
(@haven_id, 20.00, 'credit', 'Weekly allowance - Week 4', '2025-09-23'),
(@haven_id, 20.00, 'credit', 'Weekly allowance - Week 5', '2025-09-30'),

-- August 2025
(@haven_id, 20.00, 'credit', 'Weekly allowance - Week 1', '2025-08-05'),
(@haven_id, 20.00, 'credit', 'Weekly allowance - Week 2', '2025-08-12'),
(@haven_id, 20.00, 'credit', 'Weekly allowance - Week 3', '2025-08-19'),
(@haven_id, -5.00, 'deduction', 'Incomplete kitchen cleaning', '2025-08-19'),
(@haven_id, 20.00, 'credit', 'Weekly allowance - Week 4', '2025-08-26'),

-- July 2025
(@haven_id, 20.00, 'credit', 'Weekly allowance - Week 1', '2025-07-01'),
(@haven_id, 20.00, 'credit', 'Weekly allowance - Week 2', '2025-07-08'),
(@haven_id, -5.00, 'deduction', 'School points deduction', '2025-07-08'),
(@haven_id, 20.00, 'credit', 'Weekly allowance - Week 3', '2025-07-15'),
(@haven_id, 20.00, 'credit', 'Weekly allowance - Week 4', '2025-07-22'),
(@haven_id, 20.00, 'credit', 'Weekly allowance - Week 5', '2025-07-29'),

-- June 2025
(@haven_id, 20.00, 'credit', 'Weekly allowance - Week 1', '2025-06-03'),
(@haven_id, 20.00, 'credit', 'Weekly allowance - Week 2', '2025-06-10'),
(@haven_id, 20.00, 'credit', 'Weekly allowance - Week 3', '2025-06-17'),
(@haven_id, -5.00, 'deduction', 'Incomplete bathroom cleaning', '2025-06-17'),
(@haven_id, 20.00, 'credit', 'Weekly allowance - Week 4', '2025-06-24'),

-- May 2025
(@haven_id, 20.00, 'credit', 'Weekly allowance - Week 1', '2025-05-06'),
(@haven_id, 20.00, 'credit', 'Weekly allowance - Week 2', '2025-05-13'),
(@haven_id, 20.00, 'credit', 'Weekly allowance - Week 3', '2025-05-20'),
(@haven_id, 20.00, 'credit', 'Weekly allowance - Week 4', '2025-05-27');
