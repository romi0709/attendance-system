<?php
require "config/database.php";

// Generate bcrypt hash safely
$password = password_hash("admin123", PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (fullname, email, password, role) 
                        VALUES (?, ?, ?, 'admin')");
$stmt->execute(["Administrator", "admin@example.com", $password]);

echo "ADMIN CREATED SUCCESSFULLY<br>";
echo "Email: admin@example.com<br>";
echo "Password: admin123<br>";
echo "Hash used:<br>" . $password;
?>