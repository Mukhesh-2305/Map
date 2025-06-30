<?php
include 'db.php';

echo "<h2>Database Status Check</h2>";

// Check database connection
if ($conn->ping()) {
    echo "✅ Database connection successful<br>";
} else {
    echo "❌ Database connection failed<br>";
    exit();
}

// Check if users table exists and has data
$result = $conn->query("SHOW TABLES LIKE 'users'");
if ($result->num_rows > 0) {
    echo "✅ Users table exists<br>";
    
    // Check user count
    $user_count = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
    echo "📊 Users in database: $user_count<br>";
    
    if ($user_count == 0) {
        echo "⚠️ No users found. Creating test user...<br>";
        
        // Create test user
        $sql = "INSERT INTO users (full_name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $full_name = "Test User";
        $email = "test@example.com";
        $password = password_hash("test123", PASSWORD_DEFAULT);
        $phone = "1234567890";
        $address = "Test Address";
        
        $stmt->bind_param("sssss", $full_name, $email, $password, $phone, $address);
        
        if ($stmt->execute()) {
            echo "✅ Test user created successfully<br>";
            echo "Email: test@example.com<br>";
            echo "Password: test123<br>";
        } else {
            echo "❌ Error creating test user: " . $stmt->error . "<br>";
        }
    }
} else {
    echo "❌ Users table does not exist<br>";
}

// Check if user_boundaries table exists
$result = $conn->query("SHOW TABLES LIKE 'user_boundaries'");
if ($result->num_rows > 0) {
    echo "✅ User_boundaries table exists<br>";
    
    // Check table structure
    $structure = $conn->query("DESCRIBE user_boundaries");
    echo "<h4>User_boundaries table structure:</h4>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $structure->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check boundary count
    $boundary_count = $conn->query("SELECT COUNT(*) as count FROM user_boundaries")->fetch_assoc()['count'];
    echo "📊 Boundaries in database: $boundary_count<br>";
    
} else {
    echo "❌ User_boundaries table does not exist. Creating it...<br>";
    
    // Create user_boundaries table
    $sql = "CREATE TABLE user_boundaries (
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
}

// Test session functionality
session_start();
echo "<h3>Session Test:</h3>";
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    echo "✅ User is logged in<br>";
    echo "User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "<br>";
    echo "User Name: " . ($_SESSION['user_name'] ?? 'Not set') . "<br>";
} else {
    echo "⚠️ No user logged in<br>";
    echo "<p>Please <a href='login.html'>login</a> first to test the save functionality.</p>";
}

echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li><a href='login.html'>Login</a> with test@example.com / test123</li>";
echo "<li><a href='index.html'>Go to the map</a> and create a boundary</li>";
echo "<li>Try to save the boundary and check for errors</li>";
echo "</ol>";

$conn->close();
?> 