<?php
$host = 'localhost';
$dbname = 'web-player';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Ошибка подключения: " . $e->getMessage(); // Обработка ошибки подключения
    exit();
}
$urlVideo = $_GET['urlVideo'];

// Подготавливаем запрос для получения idVideo для указанного urlVideo
$sql = "SELECT idVideo FROM video WHERE urlVideo = :urlVideo";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':urlVideo', $urlVideo, PDO::PARAM_STR);
$stmt->execute();

// Получаем результат запроса
$idVideo = $stmt->fetchColumn();

// Подготавливаем запрос для получения маркеров для указанного idVideo
$sqlMarkers = "SELECT * FROM marker WHERE idVideo = :idVideo";
$stmtMarkers = $pdo->prepare($sqlMarkers);
$stmtMarkers->bindParam(':idVideo', $idVideo, PDO::PARAM_INT);
$stmtMarkers->execute();

// Получаем результат запроса
$markers = $stmtMarkers->fetchAll(PDO::FETCH_ASSOC);
header('Content-Type: application/json');
echo json_encode($markers);
