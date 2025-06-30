<?php
session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$lat = $data['lat'];
$lng = $data['lng'];

$user_id = $_SESSION['user_id'] ?? 1; // fallback to 1 for testing

$conn = new mysqli("localhost", "root", "", "geo_tracking");

if ($conn->connect_error) {
    echo json_encode(["status" => "DB connection failed"]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO user_points (user_id, latitude, longitude) VALUES (?, ?, ?)");
$stmt->bind_param("idd", $user_id, $lat, $lng);
$stmt->execute();

echo json_encode(["status" => "Point saved"]);
