<?php
/*
* config/database.php
*
* Ito ang database configuration file
* - May database credentials
* - Nag-establish ng connection
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

// Establish mysqli connection
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to utf8
mysqli_set_charset($conn, "utf8");

// For backwards compatibility with existing PDO code
try {
    $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "PDO Connection failed: " . $e->getMessage();
}
?> 