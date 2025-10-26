-- Table for storing school points
CREATE TABLE IF NOT EXISTS school_points (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    points INT NOT NULL DEFAULT 1,
    reason TEXT NOT NULL,
    assigned_by INT NOT NULL,
    week_number INT NOT NULL,
    year INT NOT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_week (user_id, week_number, year),
    INDEX idx_created_at (created_at)
);

-- Table for storing point categories
CREATE TABLE IF NOT EXISTS point_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    default_points INT NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY unique_name (name)
);

-- Insert default point categories
INSERT INTO point_categories (name, description, default_points, created_at, updated_at)
VALUES 
    ('Behavior', 'Points for behavioral issues in school', 1, NOW(), NOW()),
    ('Homework', 'Points for missing or incomplete homework', 1, NOW(), NOW()),
    ('Tardiness', 'Points for being late to class', 1, NOW(), NOW()),
    ('Disruption', 'Points for disrupting class', 1, NOW(), NOW()),
    ('Dress Code', 'Points for dress code violations', 1, NOW(), NOW());

-- Table for storing weekly point summaries
CREATE TABLE IF NOT EXISTS point_summaries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    week_number INT NOT NULL,
    year INT NOT NULL,
    total_points INT NOT NULL DEFAULT 0,
    allowance_deduction DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    processed BOOLEAN NOT NULL DEFAULT FALSE,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_week (user_id, week_number, year),
    INDEX idx_processed (processed)
);

-- Table for storing point history
CREATE TABLE IF NOT EXISTS point_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL, -- 'added', 'removed', 'reset'
    points INT NOT NULL,
    reason TEXT,
    performed_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_action (user_id, action),
    INDEX idx_created_at (created_at)
);
