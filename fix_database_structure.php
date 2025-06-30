<?php
include 'db.php';

echo "<h2>Database Structure Fix</h2>";

// Check if boundary_data column is JSON type
$result = $conn->query("SHOW COLUMNS FROM user_boundaries LIKE 'boundary_data'");
$column = $result->fetch_assoc();

echo "Current boundary_data type: " . $column['Type'] . "<br>";

if ($column['Type'] !== 'json') {
    echo "⚠️ boundary_data is not JSON type. Fixing...<br>";
    
    // Try to alter the column to JSON
    $sql = "ALTER TABLE user_boundaries MODIFY COLUMN boundary_data JSON NOT NULL";
    
    if ($conn->query($sql) === TRUE) {
        echo "✅ Successfully changed boundary_data to JSON type<br>";
    } else {
        echo "❌ Error changing column type: " . $conn->error . "<br>";
        echo "This might be because there's existing data. Let's try a different approach...<br>";
        
        // Create a new table with correct structure
        $sql = "CREATE TABLE user_boundaries_new (
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
            echo "✅ Created new table with correct JSON structure<br>";
            echo "⚠️ You may need to drop the old table and rename the new one<br>";
        } else {
            echo "❌ Error creating new table: " . $conn->error . "<br>";
        }
    }
} else {
    echo "✅ boundary_data is already JSON type<br>";
}

// Test JSON functionality
echo "<h3>Testing JSON functionality:</h3>";
$test_data = json_encode([[11.1271, 78.6569], [11.1272, 78.6570], [11.1273, 78.6571]]);
echo "Test JSON data: " . $test_data . "<br>";

// Test if we can insert JSON data
$sql = "INSERT INTO user_boundaries (user_id, boundary_name, boundary_data, total_buildings, total_area_km2, agricultural_area_km2, other_land_area_km2) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $user_id = 10; // Your user ID
    $boundary_name = "Test_Boundary_" . date('Y-m-d_H-i-s');
    $total_buildings = 5;
    $total_area_km2 = 1.5;
    $agricultural_area_km2 = 0.8;
    $other_land_area_km2 = 0.7;
    
    $stmt->bind_param("issdddd", 
        $user_id, 
        $boundary_name, 
        $test_data, 
        $total_buildings, 
        $total_area_km2, 
        $agricultural_area_km2, 
        $other_land_area_km2
    );
    
    if ($stmt->execute()) {
        echo "✅ Test insert successful! Boundary ID: " . $conn->insert_id . "<br>";
        
        // Clean up test data
        $conn->query("DELETE FROM user_boundaries WHERE boundary_name LIKE 'Test_Boundary_%'");
        echo "✅ Test data cleaned up<br>";
    } else {
        echo "❌ Test insert failed: " . $stmt->error . "<br>";
    }
} else {
    echo "❌ Prepare statement failed: " . $conn->error . "<br>";
}

echo "<h3>Database is ready for testing!</h3>";
echo "<p><a href='index.html'>Go to the map</a> and try saving a boundary.</p>";

$conn->close();
?> 