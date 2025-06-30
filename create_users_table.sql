-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS map;
USE map;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create user_boundaries table to store user's boundary data
CREATE TABLE IF NOT EXISTS user_boundaries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    boundary_name VARCHAR(100),
    boundary_data JSON NOT NULL,
    total_buildings INT DEFAULT 0,
    total_area_km2 DECIMAL(10, 4),
    agricultural_area_km2 DECIMAL(10, 4),
    other_land_area_km2 DECIMAL(10, 4),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert a test user (password: test123)
INSERT INTO users (full_name, email, password, phone, address) VALUES 
('Test User', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1234567890', 'Test Address')
ON DUPLICATE KEY UPDATE full_name=full_name; 