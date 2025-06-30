<?php
include 'db.php';

echo "<h2>Database Connection Test</h2>";

// Test database connection
if ($conn->ping()) {
    echo "✅ Database connection successful<br>";
} else {
    echo "❌ Database connection failed<br>";
    exit();
}

// Test if tables exist
$tables = ['users', 'user_boundaries'];

foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "✅ Table '$table' exists<br>";
        
        // Show table structure
        $structure = $conn->query("DESCRIBE $table");
        echo "<h4>Structure of $table:</h4>";
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
    } else {
        echo "❌ Table '$table' does not exist<br>";
    }
}

// Test if users exist
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$count = $result->fetch_assoc()['count'];
echo "📊 Number of users in database: $count<br>";

// Test if boundaries exist
$result = $conn->query("SELECT COUNT(*) as count FROM user_boundaries");
$count = $result->fetch_assoc()['count'];
echo "📊 Number of boundaries in database: $count<br>";

$conn->close();
?> 