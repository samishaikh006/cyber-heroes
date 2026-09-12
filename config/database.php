<?php
declare(strict_types=1);

require_once __DIR__ . '/app.php';

$dbHost = env_value('DB_HOST', '127.0.0.1');
$dbPort = env_value('DB_PORT', '3306');
$dbName = env_value('DB_NAME', 'cyber_comic');
$dbUser = env_value('DB_USER', 'root');
$dbPass = env_value('DB_PASSWORD', '');

$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $exception) {
    http_response_code(500);
    exit('Database connection failed. Check your database settings.');
}
