<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.html");
    exit();
}

// Check if boundary ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: my_boundaries.php");
    exit();
}

$boundary_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Get boundary data
$sql = "SELECT * FROM user_boundaries WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $boundary_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: my_boundaries.php");
    exit();
}

$boundary = $result->fetch_assoc();
$boundary_data = json_decode($boundary['boundary_data'], true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boundary Details - Geo Analysis</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 24px;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background 0.3s ease;
        }

        .nav-links a:hover {
            background: rgba(255,255,255,0.2);
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }

        .boundary-info {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .boundary-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .boundary-name {
            font-size: 24px;
            font-weight: 600;
            color: #333;
        }

        .boundary-date {
            font-size: 14px;
            color: #666;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            border-left: 4px solid #667eea;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            color: #666;
        }

        .map-container {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        #map {
            height: 500px;
            width: 100%;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: transform 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: #6c757d;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Boundary Details</h1>
        <div class="nav-links">
            <a href="index.html">🗺️ Map</a>
            <a href="my_boundaries.php">📊 My Boundaries</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="boundary-info">
            <div class="boundary-header">
                <div class="boundary-name"><?php echo htmlspecialchars($boundary['boundary_name']); ?></div>
                <div class="boundary-date">Created on <?php echo date('F j, Y \a\t g:i A', strtotime($boundary['created_at'])); ?></div>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $boundary['total_buildings']; ?></div>
                    <div class="stat-label">Buildings Found</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($boundary['total_area_km2'], 2); ?> km²</div>
                    <div class="stat-label">Total Area</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($boundary['agricultural_area_km2'], 2); ?> km²</div>
                    <div class="stat-label">Agricultural Land</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($boundary['other_land_area_km2'], 2); ?> km²</div>
                    <div class="stat-label">Other Land</div>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="my_boundaries.php" class="btn btn-secondary">← Back to My Boundaries</a>
                <a href="index.html" class="btn">Create New Boundary</a>
            </div>
        </div>

        <div class="map-container">
            <div id="map"></div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        // Initialize map
        const map = L.map('map').setView([11.1271, 78.6569], 10);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }).addTo(map);

        // Get boundary data from PHP
        const boundaryData = <?php echo json_encode($boundary_data); ?>;
        
        if (boundaryData && boundaryData.length >= 3) {
            // Create polygon from boundary data
            const polygon = L.polygon(boundaryData, {
                color: 'blue',
                fillOpacity: 0.3,
                weight: 2
            }).addTo(map);
            
            // Fit map to polygon bounds
            map.fitBounds(polygon.getBounds());
            
            // Add markers for each point
            boundaryData.forEach((point, index) => {
                L.marker(point).addTo(map)
                    .bindPopup(`Point ${index + 1}<br>Lat: ${point[0].toFixed(6)}<br>Lng: ${point[1].toFixed(6)}`);
            });
        }
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?> 