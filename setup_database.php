<?php
// Database setup script
$host = "localhost";
$username = "root";
$password = "";

try {
    // Connect without database first
    $conn = new mysqli($host, $username, $password);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    echo "<h2>Database Setup</h2>";
    
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS map";
    if ($conn->query($sql) === TRUE) {
        echo "✅ Database 'map' created successfully or already exists<br>";
    } else {
        echo "❌ Error creating database: " . $conn->error . "<br>";
    }
    
    // Select the database
    $conn->select_db("map");
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        address TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "✅ Users table created successfully<br>";
    } else {
        echo "❌ Error creating users table: " . $conn->error . "<br>";
    }
    
    // Create user_boundaries table
    $sql = "CREATE TABLE IF NOT EXISTS user_boundaries (
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
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "✅ User_boundaries table created successfully<br>";
    } else {
        echo "❌ Error creating user_boundaries table: " . $conn->error . "<br>";
    }
    
    // Insert test user if not exists
    $check_user = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $test_email = "test@example.com";
    $check_user->bind_param("s", $test_email);
    $check_user->execute();
    $result = $check_user->get_result();
    
    if ($result->num_rows == 0) {
        $insert_user = $conn->prepare("INSERT INTO users (full_name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)");
        $full_name = "Test User";
        $password = password_hash("test123", PASSWORD_DEFAULT);
        $phone = "1234567890";
        $address = "Test Address";
        
        $insert_user->bind_param("sssss", $full_name, $test_email, $password, $phone, $address);
        
        if ($insert_user->execute()) {
            echo "✅ Test user created successfully<br>";
            echo "Email: test@example.com<br>";
            echo "Password: test123<br>";
        } else {
            echo "❌ Error creating test user: " . $insert_user->error . "<br>";
        }
    } else {
        echo "✅ Test user already exists<br>";
    }
    
    // Check tables
    echo "<h3>Database Tables:</h3>";
    $tables = $conn->query("SHOW TABLES");
    while ($table = $tables->fetch_array()) {
        echo "📋 " . $table[0] . "<br>";
    }
    
    echo "<h3>Setup Complete!</h3>";
    echo "<p>You can now <a href='login.html'>login</a> with the test account or <a href='register.html'>register</a> a new account.</p>";
    
} catch (Exception $e) {
    echo "❌ Setup failed: " . $e->getMessage();
}

$conn->close();
?> 