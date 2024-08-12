<?php
header('Content-Type: application/json');
$host = 'localhost';
$dbname = 'web-player';
$username = 'root';
$password = '';
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT id_themes_video, name_themes FROM themes_video");
    $themes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['themes' => $themes]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Ошибка подключения: ' . $e->getMessage()]);
    exit();
}
