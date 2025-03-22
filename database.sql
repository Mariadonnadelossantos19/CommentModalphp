/*
* database.sql
*
* Ito ang SQL dump file ng database structure
* - Backup ng database schema
* - Pwedeng gamitin para i-recreate ang database
* - May CREATE TABLE statements para sa users at comments
*
* Konektado sa:
* - setup.php (pareho silang may database structure)
* - config/database.php (para sa actual database)
*/

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