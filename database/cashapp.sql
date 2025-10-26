-- Table for storing OAuth state parameters
CREATE TABLE IF NOT EXISTS oauth_states (
    id INT AUTO_INCREMENT PRIMARY KEY,
    state VARCHAR(32) NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_state (state),
    INDEX idx_created_at (created_at)
);

-- Table for storing Cash App credentials
CREATE TABLE IF NOT EXISTS cashapp_credentials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    cashtag VARCHAR(50) NOT NULL,
    access_token TEXT NOT NULL,
    refresh_token TEXT NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY unique_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_expires_at (expires_at)
);

-- Table for storing payment history
CREATE TABLE IF NOT EXISTS payment_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(20) NOT NULL, -- 'pending', 'completed', 'failed'
    payment_date DATETIME NOT NULL,
    transaction_id VARCHAR(100),
    error_message TEXT,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_payment_date (payment_date),
    INDEX idx_status (status)
);
