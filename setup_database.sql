-- Create database
CREATE DATABASE IF NOT EXISTS geo_tracking;
USE geo_tracking;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create user_points table
CREATE TABLE IF NOT EXISTS user_points (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create user_boundaries table with spatial support
CREATE TABLE IF NOT EXISTS user_boundaries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    boundary GEOMETRY NOT NULL,
    area_km2 DECIMAL(10, 4),
    perimeter_km DECIMAL(10, 4),
    building_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    SPATIAL INDEX(boundary)
);

-- Create boundary_analysis table for detailed results
CREATE TABLE IF NOT EXISTS boundary_analysis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    boundary_id INT NOT NULL,
    total_buildings INT DEFAULT 0,
    total_area_km2 DECIMAL(10, 4),
    building_area_km2 DECIMAL(10, 4),
    empty_area_km2 DECIMAL(10, 4),
    analysis_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (boundary_id) REFERENCES user_boundaries(id) ON DELETE CASCADE
);

-- Insert sample user for testing (password: test123)
INSERT INTO users (username, email, password_hash) VALUES 
('testuser', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY UPDATE username=username; 