<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: register.html");
    exit();
}

$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');

if (empty($full_name) || empty($email) || empty($password) || empty($phone) || empty($address)) {
    die("❌ All fields are required.");
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("❌ Invalid email format.");
}
if (strlen($password) < 6) {
    die("❌ Password must be at least 6 characters long.");
}

$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    die("❌ Email already registered. Please use a different one.");
}
$check->close();

$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (full_name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $full_name, $email, $hashed_password, $phone, $address);

if ($stmt->execute()) {
    header("Location: login.html?success=1");
    exit();
} else {
    echo "❌ Registration failed: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
