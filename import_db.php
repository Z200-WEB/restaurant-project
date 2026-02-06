<?php
// Protected import script - only accessible with secret key
// Usage: import_db.php?key=smartorder2026
if (!isset($_GET['key']) || $_GET['key'] !== 'smartorder2026') {
    http_response_code(403);
    die("Access denied.");
}

$dbHost = getenv('MYSQLHOST') ?: "localhost";
$dbName = getenv('MYSQLDATABASE') ?: "practice";
$user = getenv('MYSQLUSER') ?: "root";
$password = getenv('MYSQLPASSWORD') ?: "";
$dbPort = getenv('MYSQLPORT') ?: "3306";

try {
    $pdo = new PDO(
        'mysql:host='.$dbHost.';port='.$dbPort.';dbname='.$dbName,
        $user, $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "Connected to database successfully.<br>";

    $sql = file_get_contents(__DIR__ . '/database_utf8.sql');
    if (!$sql) {
        die("Could not read database_utf8.sql");
    }

    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
    $pdo->exec($sql);
    echo "Database imported successfully!<br>";
    echo "Tables created. You can now DELETE this file (import_db.php) and database_utf8.sql from your repo.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
