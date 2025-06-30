<?php
include "db.php";

$data = json_decode(file_get_contents("php://input"));

$full_name = $data->full_name ?? '';
$email = $data->email ?? '';
$password = $data->password ?? '';

if ($full_name && $email && $password) {
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $full_name, $email, $hashed);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "User registered successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Email already exists"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "All fields are required"]);
}
?>
