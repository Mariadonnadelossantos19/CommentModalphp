-- Create users table
CREATE TABLE IF NOT EXISTS `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `email` varchar(100) NOT NULL,
    `username` varchar(50) NOT NULL,
    `password` varchar(255) NOT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `avatar` VARCHAR(255) DEFAULT 'default-avatar.png',
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`