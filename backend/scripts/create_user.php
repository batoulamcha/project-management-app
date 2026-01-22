<?php

require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

$username = 'admin';
$password = 'admin123';
$email = 'admin@example.com';

try {
    $checkQuery = "SELECT id FROM users WHERE username = :username";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bindParam(':username', $username);
    $checkStmt->execute();

    if ($checkStmt->fetch()) {
        echo "User '{$username}' already exists.\n";
        exit(0);
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $insertQuery = "INSERT INTO users (username, password_hash, email) VALUES (:username, :password_hash, :email)";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bindParam(':username', $username);
    $insertStmt->bindParam(':password_hash', $passwordHash);
    $insertStmt->bindParam(':email', $email);
    $insertStmt->execute();

    echo "User created successfully!\n";
    echo "Username: {$username}\n";
    echo "Password: {$password}\n";
    echo "\nYou can now use these credentials to login via /api/auth.php\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
