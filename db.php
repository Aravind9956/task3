<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'apexplanet_task3');

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, 3307);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    die('Database connection failed. Import database/schema.sql and check config/db.php.');
}