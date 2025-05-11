<?php
require_once 'config.php';

// Define admin credentials - CHANGE THESE VALUES!
$admin_username = "ADMIN";
$admin_email = "admin@gmail.com";
$admin_password = "admin1234";  // You should use a strong password in production

// Check if admin already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $admin_username, $admin_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "Admin account already exists!";
} else {
    // Create admin account
    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, is_admin, registration_date) VALUES (?, ?, ?, 1, NOW())");
    $stmt->bind_param("sss", $admin_username, $admin_email, $hashed_password);
    
    if ($stmt->execute()) {
        echo "Admin account created successfully! Username: $admin_username";
    } else {
        echo "Error creating admin account: " . $conn->error;
    }
}

?>
