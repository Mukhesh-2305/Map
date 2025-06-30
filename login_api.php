<?php
include "db.php";
session_start();

$data = json_decode(file_get_contents("php://input"));

$email = $data->email ?? '';
$password = $data->password ?? '';

if ($email && $password) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user['full_name'];
            echo json_encode(["status" => "success", "message" => "Login successful"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Incorrect password"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "User not found"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Email and password required"]);
}
?>
