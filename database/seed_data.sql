-- Get user IDs
SET @hannah_id = (SELECT id FROM users WHERE email = 'hannahastokes@icloud.com');
SET @haven_id = (SELECT id FROM users WHERE email = 'havenastokes@icloud.com');
SET @william_id = (SELECT id FROM users WHERE email = 'william_stokes@hotmail.com');
SET @tonya_id = (SELECT id FROM users WHERE email = 'tonyastokes@yahoo.com');

-- Add tasks for Hannah (last week)
INSERT INTO tasks (user_id, task_type, status, completed_at, verified_by, verified_at, created_at) VALUES
(@hannah_id, 'room_cleaning', 'verified', DATE_SUB(NOW(), INTERVAL 6 DAY), @william_id, DATE_SUB(NOW(), INTERVAL 6 DAY), DATE_SUB(NOW(), INTERVAL 6 DAY)),
(@hannah_id, 'bathroom_cleaning', 'verified', DATE_SUB(NOW(), INTERVAL 5 DAY), @tonya_id, DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY)),
(@hannah_id, 'kitchen_cleaning', 'verified', DATE_SUB(NOW(), INTERVAL 4 DAY), @william_id, DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 4 DAY)),
(@hannah_id, 'laundry', 'verified', DATE_SUB(NOW(), INTERVAL 3 DAY), @tonya_id, DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY));

-- Add tasks for Haven (last week)
INSERT INTO tasks (user_id, task_type, status, completed_at, verified_by, verified_at, created_at) VALUES
(@haven_id, 'room_cleaning', 'verified', DATE_SUB(NOW(), INTERVAL 6 DAY), @tonya_id, DATE_SUB(NOW(), INTERVAL 6 DAY), DATE_SUB(NOW(), INTERVAL 6 DAY)),
(@haven_id, 'bathroom_cleaning', 'verified', DATE_SUB(NOW(), INTERVAL 5 DAY), @william_id, DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY)),
(@haven_id, 'kitchen_cleaning', 'rejected', DATE_SUB(NOW(), INTERVAL 4 DAY), @tonya_id, DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 4 DAY)),
(@haven_id, 'laundry', 'verified', DATE_SUB(NOW(), INTERVAL 3 DAY), @william_id, DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY));

-- Add tasks for current week
INSERT INTO tasks (user_id, task_type, status, completed_at, verified_by, verified_at, created_at) VALUES
(@hannah_id, 'room_cleaning', 'completed', NOW(), NULL, NULL, NOW()),
(@hannah_id, 'bathroom_cleaning', 'pending', NULL, NULL, NULL, NOW()),
(@haven_id, 'room_cleaning', 'pending', NULL, NULL, NULL, NOW()),
(@haven_id, 'bathroom_cleaning', 'completed', NOW(), NULL, NULL, NOW());

-- Add school points for both children
INSERT INTO school_points (user_id, points, week_start, created_at) VALUES
(@hannah_id, 2, DATE_SUB(NOW(), INTERVAL 2 WEEK), DATE_SUB(NOW(), INTERVAL 2 WEEK)),
(@hannah_id, 1, DATE_SUB(NOW(), INTERVAL 1 WEEK), DATE_SUB(NOW(), INTERVAL 1 WEEK)),
(@hannah_id, 0, DATE_FORMAT(NOW(), '%Y-%m-%d'), NOW()),
(@haven_id, 3, DATE_SUB(NOW(), INTERVAL 2 WEEK), DATE_SUB(NOW(), INTERVAL 2 WEEK)),
(@haven_id, 2, DATE_SUB(NOW(), INTERVAL 1 WEEK), DATE_SUB(NOW(), INTERVAL 1 WEEK)),
(@haven_id, 1, DATE_FORMAT(NOW(), '%Y-%m-%d'), NOW());

-- Add transactions (allowance payments and deductions)
INSERT INTO transactions (user_id, amount, type, reason, created_at) VALUES
-- Hannah's transactions
(@hannah_id, 20.00, 'credit', 'Weekly allowance - Week 1', DATE_SUB(NOW(), INTERVAL 2 WEEK)),
(@hannah_id, -5.00, 'deduction', 'School points deduction - Week 1', DATE_SUB(NOW(), INTERVAL 2 WEEK)),
(@hannah_id, 20.00, 'credit', 'Weekly allowance - Week 2', DATE_SUB(NOW(), INTERVAL 1 WEEK)),
(@hannah_id, -5.00, 'deduction', 'School points deduction - Week 2', DATE_SUB(NOW(), INTERVAL 1 WEEK)),

-- Haven's transactions
(@haven_id, 20.00, 'credit', 'Weekly allowance - Week 1', DATE_SUB(NOW(), INTERVAL 2 WEEK)),
(@haven_id, -5.00, 'deduction', 'Incomplete kitchen cleaning', DATE_SUB(NOW(), INTERVAL 2 WEEK)),
(@haven_id, 20.00, 'credit', 'Weekly allowance - Week 2', DATE_SUB(NOW(), INTERVAL 1 WEEK)),
(@haven_id, -5.00, 'deduction', 'School points deduction - Week 2', DATE_SUB(NOW(), INTERVAL 1 WEEK));

-- Add notifications
INSERT INTO notifications (user_id, type, message, read_at, created_at) VALUES
(@hannah_id, 'task_verified', 'Your room cleaning task has been verified', NOW(), DATE_SUB(NOW(), INTERVAL 6 DAY)),
(@hannah_id, 'payout_processed', 'Weekly allowance of $15 has been processed', NULL, DATE_SUB(NOW(), INTERVAL 1 WEEK)),
(@haven_id, 'task_verified', 'Your room cleaning task has been verified', NOW(), DATE_SUB(NOW(), INTERVAL 6 DAY)),
(@haven_id, 'task_verified', 'Your kitchen cleaning task has been rejected - please try again', NULL, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(@william_id, 'task_completed', 'Hannah has completed room cleaning task', NOW(), NOW()),
(@tonya_id, 'task_completed', 'Haven has completed bathroom cleaning task', NULL, NOW());
