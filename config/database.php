<?php
// config/database.php
define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', '/StudentManagementPHP/');
function getDBConnection() {
    $host = "localhost"; 
    $db_name = "qlsv";
    $username = "thanh";
    $password = "612435";
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db_name;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    return $pdo;
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
}
?>