<?php
session_start();

if (!isset($_SESSION['userId'])) {
    header('Location: login.php');
    exit();
}

$host = 'localhost';
$dbname = 'web-player';
$username = 'root';
$password = '';
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $videoTitle = $_POST['video-title'] ?? '';
        $videoDescription = $_POST['video-description'] ?? '';
        $urlVideo = $_POST['urlVideo'] ?? '';
        $currentUserId = $_SESSION['userId'];

        if (strlen($videoTitle) > 150) {
            echo "Название видео не должно превышать 150 символов.";
            exit();
        }
        $pdo->beginTransaction();
        $sql = "UPDATE `video`
                SET nameVideo = ?, description = ?
                WHERE urlVideo = ? AND authorsID = ?";
        $stmt = $pdo->prepare($sql);
        $pdo->commit();
        if ($stmt->execute([$videoTitle, $videoDescription, $urlVideo, $currentUserId])) {
            if ($stmt->rowCount() > 0) {
                echo "Видео успешно обновлено.";
            } else {
                echo "Данные не были изменены. Проверьте условия.";
            }
        } else {
            echo "Ошибка выполнения запроса.";
        }
    }
} catch (PDOException $e) {
    echo "Ошибка подключения: " . $e->getMessage();
    exit();
}
