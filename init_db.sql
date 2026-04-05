CREATE DATABASE IF NOT EXISTS llm_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE llm_tracker;

CREATE TABLE IF NOT EXISTS models (
    id INT AUTO_INCREMENT PRIMARY KEY,
    openrouter_id VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    status ENUM('active', 'deleted', 're-added') DEFAULT 'active',
    specs JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS model_prices_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    model_id INT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    input_price_per_m DECIMAL(12, 4) DEFAULT 0.0000,
    output_price_per_m DECIMAL(12, 4) DEFAULT 0.0000,
    context_length INT DEFAULT 0,
    change_type ENUM('created', 'price_update', 'spec_update', 'deleted') NOT NULL,
    FOREIGN KEY (model_id) REFERENCES models(id) ON DELETE CASCADE
);

CREATE INDEX idx_model_timestamp ON model_prices_history(model_id, timestamp);
