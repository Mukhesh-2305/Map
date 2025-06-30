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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Viewer - Geo Analysis</title>
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
            max-width: 1400px;
            margin: 20px auto;
            padding: 0 20px;
        }

        .section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .section h2 {
            color: #333;
            margin-bottom: 15px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        .data-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .data-table tr:nth-child(even) {
            background: #f9f9f9;
        }

        .data-table tr:hover {
            background: #f0f0f0;
        }

        .json-data {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            font-family: monospace;
            font-size: 12px;
            max-height: 200px;
            overflow-y: auto;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .stat-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #667eea;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }

        .stat-label {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
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

        .refresh-btn {
            background: #28a745;
            border: none;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 15px;
        }

        .refresh-btn:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Database Viewer</h1>
        <div class="nav-links">
            <a href="index.html">🗺️ Map</a>
            <a href="my_boundaries.php">📊 My Boundaries</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="section">
            <h2>👤 User Information</h2>
            <p><strong>Logged in as:</strong> <?php echo htmlspecialchars($user_name); ?> (ID: <?php echo $user_id; ?>)</p>
            <button class="refresh-btn" onclick="location.reload()">🔄 Refresh Data</button>
        </div>

        <?php
        // Get all users
        $users_result = $conn->query("SELECT id, full_name, email, phone, address, created_at FROM users ORDER BY created_at DESC");
        ?>

        <div class="section">
            <h2>👥 All Users in Database</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $users_result->num_rows; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
            </div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone']); ?></td>
                        <td><?php echo htmlspecialchars($user['address']); ?></td>
                        <td><?php echo date('M j, Y g:i A', strtotime($user['created_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <?php
        // Get all boundaries
        $boundaries_result = $conn->query("
            SELECT ub.*, u.full_name 
            FROM user_boundaries ub 
            JOIN users u ON ub.user_id = u.id 
            ORDER BY ub.created_at DESC
        ");
        ?>

        <div class="section">
            <h2>🗺️ All Boundaries in Database</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $boundaries_result->num_rows; ?></div>
                    <div class="stat-label">Total Boundaries</div>
                </div>
                <?php
                $total_buildings = 0;
                $total_area = 0;
                $total_agri = 0;
                $total_other = 0;
                
                $boundaries_result->data_seek(0); // Reset pointer
                while ($boundary = $boundaries_result->fetch_assoc()) {
                    $total_buildings += $boundary['total_buildings'];
                    $total_area += $boundary['total_area_km2'];
                    $total_agri += $boundary['agricultural_area_km2'];
                    $total_other += $boundary['other_land_area_km2'];
                }
                ?>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $total_buildings; ?></div>
                    <div class="stat-label">Total Buildings</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($total_area, 2); ?> km²</div>
                    <div class="stat-label">Total Area</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($total_agri, 2); ?> km²</div>
                    <div class="stat-label">Agricultural Land</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($total_other, 2); ?> km²</div>
                    <div class="stat-label">Other Land</div>
                </div>
            </div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Boundary Name</th>
                        <th>Buildings</th>
                        <th>Total Area (km²)</th>
                        <th>Agricultural (km²)</th>
                        <th>Other Land (km²)</th>
                        <th>Created At</th>
                        <th>Boundary Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $boundaries_result->data_seek(0); // Reset pointer
                    while ($boundary = $boundaries_result->fetch_assoc()): 
                    ?>
                    <tr>
                        <td><?php echo $boundary['id']; ?></td>
                        <td><?php echo htmlspecialchars($boundary['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($boundary['boundary_name']); ?></td>
                        <td><?php echo $boundary['total_buildings']; ?></td>
                        <td><?php echo number_format($boundary['total_area_km2'], 4); ?></td>
                        <td><?php echo number_format($boundary['agricultural_area_km2'], 4); ?></td>
                        <td><?php echo number_format($boundary['other_land_area_km2'], 4); ?></td>
                        <td><?php echo date('M j, Y g:i A', strtotime($boundary['created_at'])); ?></td>
                        <td>
                            <div class="json-data">
                                <?php 
                                $boundary_data = json_decode($boundary['boundary_data'], true);
                                echo "Points: " . count($boundary_data) . "\n";
                                echo "Coordinates: " . json_encode($boundary_data, JSON_PRETTY_PRINT);
                                ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2>📋 Database Tables Structure</h2>
            
            <h3>Users Table</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Type</th>
                        <th>Null</th>
                        <th>Key</th>
                        <th>Default</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $users_structure = $conn->query("DESCRIBE users");
                    while ($field = $users_structure->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?php echo $field['Field']; ?></td>
                        <td><?php echo $field['Type']; ?></td>
                        <td><?php echo $field['Null']; ?></td>
                        <td><?php echo $field['Key']; ?></td>
                        <td><?php echo $field['Default']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <h3>User_Boundaries Table</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Type</th>
                        <th>Null</th>
                        <th>Key</th>
                        <th>Default</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $boundaries_structure = $conn->query("DESCRIBE user_boundaries");
                    while ($field = $boundaries_structure->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?php echo $field['Field']; ?></td>
                        <td><?php echo $field['Type']; ?></td>
                        <td><?php echo $field['Null']; ?></td>
                        <td><?php echo $field['Key']; ?></td>
                        <td><?php echo $field['Default']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2>🔗 Quick Actions</h2>
            <a href="index.html" class="btn">🗺️ Create New Boundary</a>
            <a href="my_boundaries.php" class="btn btn-secondary">📊 View My Boundaries</a>
            <a href="phpmyadmin" target="_blank" class="btn btn-secondary">🗄️ Open phpMyAdmin</a>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?> 