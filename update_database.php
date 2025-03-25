<?php
/*
* update_database.php
*
* Ito ang utility script para sa database updates
* - Para sa mga database schema changes
* - Nag-add ng new columns kung kailangan
* - Para sa system upgrades
*
* Konektado sa:
* - config/database.php (para sa database connection)
* - database.sql (source ng schema updates)
*/
require_once 'config/database.php'; // Nag-lo-load ng database connection

try {
    // Add avatar column to users table
    $db->exec("ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT 'default-avatar.png'"); // Nagdadagdag ng avatar column sa users table
    
    // Create avatars directory
    $upload_dir = 'uploads/avatars/'; // Folder para sa avatar images
    if (!file_exists($upload_dir)) { // Checking kung umiiral na ang folder
        mkdir($upload_dir, 0777, true); // Gumagawa ng folder kung wala pa
    }
    
    // Use a placeholder image for default avatar
    $default_avatar = $upload_dir . 'default-avatar.png'; // Path para sa default avatar
    if (!file_exists($default_avatar)) { // Checking kung umiiral na ang default avatar
        // Download a placeholder image
        file_put_contents($default_avatar, file_get_contents('https://ui-avatars.com/api/?name=User&background=random')); // Nagda-download ng placeholder image
    }
    
    echo "Avatar column added successfully!"; // Nagdi-display ng success message
    echo "<br><a href='comments.php'>Go back to comments</a>"; // Naglalagay ng link pabalik sa comments
} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Duplicate column name 'avatar'") !== false) { // Checking kung duplicate column error
        echo "Avatar column already exists."; // Nagdi-display ng message kung umiiral na ang column
    } else {
        echo "Error: " . $e->getMessage(); // Nagdi-display ng error message kung may ibang problema
    }
} 