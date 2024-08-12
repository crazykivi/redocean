<?php
header('Content-Type: application/json'); // Установите заголовок ответа JSON

$dbHost = 'localhost';
$dbName = 'web-player';
$dbUser = 'root';
$dbPass = '';

$response = ['success' => false, 'error' => 'Unknown error'];

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    $response['error'] = "Сервис временно недоступен, просим прощения за доставленные неудобства: " . $e->getMessage();
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $urlVideo = $_POST['urlVideo'];

    if (filter_var($urlVideo, FILTER_VALIDATE_URL)) {
        try {
            $stmt = $pdo->prepare("SELECT idVideo FROM video WHERE urlVideo = ?");
            $stmt->execute([$urlVideo]);
            $idVideo = $stmt->fetchColumn();

            if ($idVideo) {
                $response = ['success' => true, 'idVideo' => $idVideo];
            } else {
                $response['error'] = 'Видео не найдено.';
            }
        } catch (PDOException $e) {
            $response['error'] = 'Ошибка при выполнении запроса к базе данных: ' . $e->getMessage();
        }
    } else {
        $response['error'] = 'Некорректный URL.';
    }
} else {
    $response['error'] = 'Некорректный метод запроса.';
}

echo json_encode($response);
?>
