<?php
session_start();

// Check if user is logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $response = [
        'logged_in' => true,
        'user_name' => $_SESSION['user_name'],
        'user_email' => $_SESSION['user_email'],
        'user_id' => $_SESSION['user_id']
    ];
} else {
    $response = [
        'logged_in' => false
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
?> 