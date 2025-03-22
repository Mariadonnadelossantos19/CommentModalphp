<?php
/*
* config/database.php
*
* Ito ang database configuration file
* - May database credentials
* - Nag-establish ng PDO connection
* - Central point ng database access
*
* Konektado sa:
* - Lahat ng files na need ng database access
* - .env (para sa database credentials)
*/
$db_host = 'localhost';
$db_name = 'commentmodal_db';
$db_user = 'root';
$db_pass = '';

try {
    $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
} 