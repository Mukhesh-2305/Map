<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';

    // Validate input
    if (empty($full_name) || empty($email) || empty($password) || empty($phone) || empty($address)) {
        die("❌ All fields are required.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("❌ Invalid email format.");
    }

    if (strlen($password) < 6) {
        die("❌ Password must be at least 6 characters long.");
    }

    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();
    if ($result->num_rows > 0) {
        die("❌ Email already registered. Please use a different one.");
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $full_name, $email, $hashed_password, $phone, $address);
    
    if ($stmt->execute()) {
        header("Location: login.html?success=1"); // Redirect to login with success message
        exit();
    } else {
        echo "❌ Registration failed: " . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>
