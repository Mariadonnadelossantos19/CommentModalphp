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
$db_host = 'localhost'; // Host ng database server
$db_name = 'commentmodal_db'; // Pangalan ng database
$db_user = 'root'; // Username para sa database access
$db_pass = ''; // Password para sa database access

try {
    $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass); // Gumagawa ng PDO connection sa database
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Nagse-set ng error mode para sa debugging
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage(); // Nagdi-display ng error message kung hindi makakonekta
} 