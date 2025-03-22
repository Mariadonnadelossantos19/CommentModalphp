<?php
require_once 'config/database.php';

try {
    // Disable foreign key checks temporarily
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Delete all comments and replies
    $db->exec("DELETE FROM tblcomments");
    
    // Reset auto-increment
    $db->exec("ALTER TABLE tblcomments AUTO_INCREMENT = 1");
    
    // Re-enable foreign key checks
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    // Clear PHP session cache
    session_start();
    session_write_close();
    
    echo "All comments have been cleared successfully!";
    echo "<br><a href='comments.php'>Go back to comments</a>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
} 