<?php
// config.php --
// Database Connection Settings --

define('DB_HOST', 'localhost');
define('DB_PORT', '3322'); // Your database port -- port your 3306
define('DB_USER', 'root'); // Database username --
define('DB_PASS', ''); // Database password --
define('DB_NAME', 'settingspanel'); // Database name --

// Function to connect to database --
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    
    if ($conn->connect_error) {
        die("Database connection error: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}
?>