<?php
session_start();
include 'db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set JSON header
header('Content-Type: application/json');

// Log the request
error_log("Save boundary request received");

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    error_log("User not authenticated");
    http_response_code(401);
    echo json_encode(['error' => 'User not authenticated']);
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Invalid method: " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get JSON data from request
$raw_input = file_get_contents('php://input');
error_log("Raw input: " . $raw_input);

$input = json_decode($raw_input, true);

if (!$input) {
    error_log("JSON decode failed: " . json_last_error_msg());
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON data']);
    exit();
}

if (!isset($input['polygon']) || !isset($input['analysis'])) {
    error_log("Missing required fields");
    http_response_code(400);
    echo json_encode(['error' => 'Missing polygon or analysis data']);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];
    error_log("User ID: " . $user_id);
    
    $polygon_data = json_encode($input['polygon']);
    $analysis = $input['analysis'];
    
    error_log("Polygon data: " . $polygon_data);
    error_log("Analysis data: " . json_encode($analysis));
    
    // Extract analysis data
    $total_buildings = $analysis['total_buildings'] ?? 0;
    $total_area_km2 = $analysis['total_area_km2'] ?? 0;
    $agricultural_area_km2 = $analysis['agricultural_area_km2'] ?? 0;
    $other_land_area_km2 = $analysis['other_land_area_km2'] ?? 0;
    
    error_log("Extracted values - Buildings: $total_buildings, Total Area: $total_area_km2, Agri: $agricultural_area_km2, Other: $other_land_area_km2");
    
    // Generate boundary name with timestamp
    $boundary_name = "Boundary_" . date('Y-m-d_H-i-s');
    error_log("Boundary name: " . $boundary_name);
    
    // Check if table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'user_boundaries'");
    if ($table_check->num_rows == 0) {
        throw new Exception("user_boundaries table does not exist");
    }
    
    // Prepare and execute the insert statement
    $sql = "INSERT INTO user_boundaries (user_id, boundary_name, boundary_data, total_buildings, total_area_km2, agricultural_area_km2, other_land_area_km2) VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    error_log("SQL: " . $sql);
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("issdddd", 
        $user_id, 
        $boundary_name, 
        $polygon_data, 
        $total_buildings, 
        $total_area_km2, 
        $agricultural_area_km2, 
        $other_land_area_km2
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $boundary_id = $conn->insert_id;
    error_log("Successfully saved boundary with ID: " . $boundary_id);
    
    echo json_encode([
        'success' => true,
        'message' => 'Boundary data saved successfully!',
        'boundary_id' => $boundary_id,
        'boundary_name' => $boundary_name
    ]);
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?> 