<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "map";

$conn = new mysqli($host, $username, $password);
if ($conn->connect_error) {
    die(json_encode(["error" => "DB connection failed: " . $conn->connect_error]));
}
$conn->query("CREATE DATABASE IF NOT EXISTS `map`");
$conn->select_db($dbname);
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");
$conn->query("CREATE TABLE IF NOT EXISTS user_boundaries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_name VARCHAR(100),
    user_email VARCHAR(100),
    boundary_name VARCHAR(100),
    boundary_data JSON NOT NULL,
    total_buildings INT DEFAULT 0,
    total_area_km2 DECIMAL(10,4),
    agricultural_area_km2 DECIMAL(10,4),
    other_land_area_km2 DECIMAL(10,4),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)");
?>
