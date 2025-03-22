<?php
require_once 'config/database.php';

try {
    // Create users table
    $db->exec("
        CREATE TABLE IF NOT EXISTS `users` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `email` varchar(100) NOT NULL,
            `username` varchar(50) NOT NULL,
            `password` varchar(255) NOT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `username` (`username`),
            UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Insert test user
    $password_hash = password_hash('password123', PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (name, email, username, password) VALUES (?, ?, ?, ?)");
    $stmt->execute(['Test User', 'test@example.com', 'testuser', $password_hash]);

    echo "Setup completed successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
} 