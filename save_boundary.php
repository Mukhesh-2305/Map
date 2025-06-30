<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'User not authenticated']);
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get JSON data from request
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['polygon']) || !isset($input['analysis'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid data provided']);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['user_name'];
    $user_email = $_SESSION['user_email'];
    $polygon_data = json_encode($input['polygon']);
    $analysis = $input['analysis'];
    
    // Extract analysis data
    $total_buildings = $analysis['total_buildings'] ?? 0;
    $total_area_km2 = $analysis['total_area_km2'] ?? 0;
    $agricultural_area_km2 = $analysis['agricultural_area_km2'] ?? 0;
    $other_land_area_km2 = $analysis['other_land_area_km2'] ?? 0;
    
    // Generate boundary name with timestamp
    $boundary_name = "Boundary_" . date('Y-m-d_H-i-s');
    
    // Prepare and execute the insert statement
    $sql = "INSERT INTO user_boundaries (
                user_id, user_name, user_email, boundary_name, boundary_data, 
                total_buildings, total_area_km2, agricultural_area_km2, other_land_area_km2
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssdddd", 
        $user_id, 
        $user_name,
        $user_email,
        $boundary_name, 
        $polygon_data, 
        $total_buildings, 
        $total_area_km2, 
        $agricultural_area_km2, 
        $other_land_area_km2
    );
    
    if ($stmt->execute()) {
        $boundary_id = $conn->insert_id;
        echo json_encode([
            'success' => true,
            'message' => 'Boundary data saved successfully!',
            'boundary_id' => $boundary_id,
            'boundary_name' => $boundary_name
        ]);
    } else {
        throw new Exception("Failed to save boundary data: " . $stmt->error);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>