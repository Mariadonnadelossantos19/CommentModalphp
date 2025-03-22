<?php
require_once 'config/database.php';

try {
    // Add avatar column to users table
    $db->exec("ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT 'default-avatar.png'");
    
    // Create avatars directory
    $upload_dir = 'uploads/avatars/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Use a placeholder image for default avatar
    $default_avatar = $upload_dir . 'default-avatar.png';
    if (!file_exists($default_avatar)) {
        // Download a placeholder image
        file_put_contents($default_avatar, file_get_contents('https://ui-avatars.com/api/?name=User&background=random'));
    }
    
    echo "Avatar column added successfully!";
    echo "<br><a href='comments.php'>Go back to comments</a>";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Duplicate column name 'avatar'") !== false) {
        echo "Avatar column already exists.";
    } else {
        echo "Error: " . $e->getMessage();
    }
} 