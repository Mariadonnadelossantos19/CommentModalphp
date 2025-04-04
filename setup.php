<?php
/*
* setup.php
* 
* Ito ang file na ginagamit para sa initial setup ng database.
* - Gumagawa ng users table
* - Naglalagay ng test user para sa testing
* - Kailangan lang patakbuhin ito one time para ma-setup ang database
* - Naka-comment out na ito para hindi na magamit ulit
*
* Konektado sa:
* - config/database.php (para sa database connection)
* - register.php (pareho silang gumagawa ng users)
*/

require_once 'config/database.php'; // Nag-lo-load ng database connection

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
    "); // Gumagawa ng users table kung wala pa

    // Insert test user
    $password_hash = password_hash('password123', PASSWORD_DEFAULT); // Nage-encrypt ng password
    $stmt = $db->prepare("INSERT INTO users (name, email, username, password) VALUES (?, ?, ?, ?)"); // Query para magdagdag ng test user
    $stmt->execute(['Test User', 'test@example.com', 'testuser', $password_hash]); // Nag-e-execute ng query

    echo "Setup completed successfully!"; // Nagdi-display ng success message
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage(); // Nagdi-display ng error message kung may problema
}