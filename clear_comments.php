<?php
/*
* clear_comments.php
*
* Ito ang utility script para ma-clear lahat ng comments
* - Para sa admin lang dapat
* - Delikado gamitin kasi mabubura lahat
* - Dapat may confirmation muna
*
* Konektado sa:
* - config/database.php (para sa database)
*/
require_once 'config/database.php'; // Nag-lo-load ng database connection

try {
    // Disable foreign key checks temporarily
    $db->exec("SET FOREIGN_KEY_CHECKS = 0"); // Pansamantalang dini-disable ang foreign key checks para makapag-delete ng data
    
    // Delete all comments and replies
    $db->exec("DELETE FROM tblcomments"); // Binubura lahat ng comments sa database
    
    // Reset auto-increment
    $db->exec("ALTER TABLE tblcomments AUTO_INCREMENT = 1"); // Nire-reset ang auto increment counter
    
    // Re-enable foreign key checks
    $db->exec("SET FOREIGN_KEY_CHECKS = 1"); // Bina-bawi ang foreign key checks
    
    // Clear PHP session cache
    session_start(); // Nagsisimula ng session
    session_write_close(); // Nagsasara ng session
    
    echo "All comments have been cleared successfully!"; // Nagdi-display ng success message
    echo "<br><a href='comments.php'>Go back to comments</a>"; // Naglalagay ng link pabalik sa comments page
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage(); // Nagdi-display ng error message kung may problema
} 