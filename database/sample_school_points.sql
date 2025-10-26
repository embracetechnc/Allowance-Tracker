-- Get user IDs
SET @hannah_id = (SELECT id FROM users WHERE email = 'hannahastokes@icloud.com');
SET @haven_id = (SELECT id FROM users WHERE email = 'havenastokes@icloud.com');

-- Clear existing school points
DELETE FROM school_points;

-- Add school points for Hannah (last 12 weeks)
INSERT INTO school_points (user_id, points, week_start, created_at) VALUES
-- October 2025
(@hannah_id, 2, '2025-10-21', '2025-10-21'), -- Current week (3 points taken = $5 deduction)
(@hannah_id, 1, '2025-10-14', '2025-10-14'), -- Last week (4 points taken = $5 deduction)
(@hannah_id, 0, '2025-10-07', '2025-10-07'), -- Two weeks ago (5 points taken = $5 deduction)

-- September 2025
(@hannah_id, 4, '2025-09-30', '2025-09-30'),
(@hannah_id, 3, '2025-09-23', '2025-09-23'),
(@hannah_id, 2, '2025-09-16', '2025-09-16'),
(@hannah_id, 1, '2025-09-09', '2025-09-09'),
(@hannah_id, 0, '2025-09-02', '2025-09-02'),

-- August 2025
(@hannah_id, 3, '2025-08-26', '2025-08-26'),
(@hannah_id, 4, '2025-08-19', '2025-08-19'),
(@hannah_id, 2, '2025-08-12', '2025-08-12'),
(@hannah_id, 1, '2025-08-05', '2025-08-05'),

-- Add school points for Haven (last 12 weeks)
-- October 2025
(@haven_id, 3, '2025-10-21', '2025-10-21'), -- Current week (2 points taken)
(@haven_id, 2, '2025-10-14', '2025-10-14'), -- Last week (3 points taken)
(@haven_id, 4, '2025-10-07', '2025-10-07'), -- Two weeks ago (1 point taken)

-- September 2025
(@haven_id, 3, '2025-09-30', '2025-09-30'),
(@haven_id, 2, '2025-09-23', '2025-09-23'),
(@haven_id, 1, '2025-09-16', '2025-09-16'),
(@haven_id, 0, '2025-09-09', '2025-09-09'),
(@haven_id, 2, '2025-09-02', '2025-09-02'),

-- August 2025
(@haven_id, 4, '2025-08-26', '2025-08-26'),
(@haven_id, 3, '2025-08-19', '2025-08-19'),
(@haven_id, 2, '2025-08-12', '2025-08-12'),
(@haven_id, 1, '2025-08-05', '2025-08-05');

-- Add point deduction transactions
INSERT INTO transactions (user_id, amount, type, reason, created_at) VALUES
-- Hannah's point deductions
(@hannah_id, -5.00, 'deduction', 'School points deduction (5 points taken)', '2025-10-07'),
(@hannah_id, -5.00, 'deduction', 'School points deduction (4 points taken)', '2025-10-14'),
(@hannah_id, -5.00, 'deduction', 'School points deduction (3 points taken)', '2025-10-21'),
(@hannah_id, -5.00, 'deduction', 'School points deduction (5 points taken)', '2025-09-02'),
(@hannah_id, -5.00, 'deduction', 'School points deduction (4 points taken)', '2025-09-09'),
(@hannah_id, -5.00, 'deduction', 'School points deduction (3 points taken)', '2025-09-16'),
(@hannah_id, -5.00, 'deduction', 'School points deduction (4 points taken)', '2025-08-05'),
(@hannah_id, -5.00, 'deduction', 'School points deduction (3 points taken)', '2025-08-12'),

-- Haven's point deductions
(@haven_id, -5.00, 'deduction', 'School points deduction (4 points taken)', '2025-10-14'),
(@haven_id, -5.00, 'deduction', 'School points deduction (2 points taken)', '2025-10-21'),
(@haven_id, -5.00, 'deduction', 'School points deduction (5 points taken)', '2025-09-09'),
(@haven_id, -5.00, 'deduction', 'School points deduction (4 points taken)', '2025-09-16'),
(@haven_id, -5.00, 'deduction', 'School points deduction (3 points taken)', '2025-09-23'),
(@haven_id, -5.00, 'deduction', 'School points deduction (4 points taken)', '2025-08-05'),
(@haven_id, -5.00, 'deduction', 'School points deduction (3 points taken)', '2025-08-12');
