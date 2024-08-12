<?php
/*
$host = 'localhost';
$dbname = 'web-player';
$username = 'root';
$password = '';

// Получение данных из запроса
$urlVideo = $_GET['urlVideo'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Получение idVideo для urlVideo
    $stmt = $pdo->prepare("SELECT idVideo FROM video WHERE urlVideo = :urlVideo");
    $stmt->bindParam(':urlVideo', $urlVideo, PDO::PARAM_STR);
    $stmt->execute();
    $idVideo = $stmt->fetchColumn();

    // Удаление всех маркеров для данного idVideo
    $sql = "DELETE FROM marker WHERE idVideo = :idVideo";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':idVideo', $idVideo, PDO::PARAM_INT);
    $stmt->execute();

    echo "Все маркеры для видео успешно удалены.";
} catch (PDOException $e) {
    echo "Ошибка удаления маркеров: " . $e->getMessage();
}*/

if (!defined('SITE_ROOT')) {
    define('SITE_ROOT', realpath(dirname(__FILE__, 2))); // 2 levels up
}

$host = 'localhost';
$dbname = 'web-player';
$username = 'root';
$password = '';

// Получение данных из запроса
$urlVideo = $_GET['urlVideo'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Получение idVideo для urlVideo
    $stmt = $pdo->prepare("SELECT idVideo FROM video WHERE urlVideo = :urlVideo");
    $stmt->bindParam(':urlVideo', $urlVideo, PDO::PARAM_STR);
    $stmt->execute();
    $idVideo = $stmt->fetchColumn();

    // Удаление всех маркеров для данного idVideo
    $sql = "DELETE FROM marker WHERE idVideo = :idVideo";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':idVideo', $idVideo, PDO::PARAM_INT);
    $stmt->execute();

    // Удаление папки с файлами видео
    $videoDir = SITE_ROOT . "/video/$urlVideo";
    if (is_dir($videoDir)) {
        // Удаление всех файлов в папке
        $files = glob($videoDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        rmdir($videoDir);
        echo "Все маркеры для видео и папка с файлами видео успешно удалены.";
    } else {
        echo "Папка с файлами видео не найдена.";
    }
} catch (PDOException $e) {
    echo "Ошибка удаления маркеров: " . $e->getMessage();
}
