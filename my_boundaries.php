<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Get user's boundaries
$sql = "SELECT * FROM user_boundaries WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$boundaries = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Boundaries - Geo Analysis</title>
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

        .welcome {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .boundaries-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }

        .boundary-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }

        .boundary-card:hover {
            transform: translateY(-2px);
        }

        .boundary-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .boundary-name {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }

        .boundary-date {
            font-size: 12px;
            color: #666;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
        }

        .stat-item {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }

        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #667eea;
        }

        .stat-label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .no-boundaries {
            text-align: center;
            padding: 50px;
            color: #666;
        }

        .no-boundaries h3 {
            margin-bottom: 10px;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>My Saved Boundaries</h1>
        <div class="nav-links">
            <a href="index.html">🗺️ Map</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="welcome">
            <h2>Welcome back, <?php echo htmlspecialchars($user_name); ?>!</h2>
            <p>Here are your saved boundary analyses. Click on any boundary to view details.</p>
        </div>

        <?php if (empty($boundaries)): ?>
            <div class="no-boundaries">
                <h3>No boundaries saved yet</h3>
                <p>Create your first boundary analysis on the map!</p>
                <a href="index.html" class="btn">Go to Map</a>
            </div>
        <?php else: ?>
            <div class="boundaries-grid">
                <?php foreach ($boundaries as $boundary): ?>
                    <div class="boundary-card">
                        <div class="boundary-header">
                            <div class="boundary-name"><?php echo htmlspecialchars($boundary['boundary_name']); ?></div>
                            <div class="boundary-date"><?php echo date('M j, Y', strtotime($boundary['created_at'])); ?></div>
                        </div>
                        
                        <div class="stats-grid">
                            <div class="stat-item">
                                <div class="stat-value"><?php echo $boundary['total_buildings']; ?></div>
                                <div class="stat-label">Buildings</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value"><?php echo number_format($boundary['total_area_km2'], 2); ?> km²</div>
                                <div class="stat-label">Total Area</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value"><?php echo number_format($boundary['agricultural_area_km2'], 2); ?> km²</div>
                                <div class="stat-label">Agricultural</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value"><?php echo number_format($boundary['other_land_area_km2'], 2); ?> km²</div>
                                <div class="stat-label">Other Land</div>
                            </div>
                        </div>
                        
                        <a href="view_boundary.php?id=<?php echo $boundary['id']; ?>" class="btn">View Details</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?> 