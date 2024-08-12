<?php
$host = 'localhost';
$dbname = 'web-player';
$username = 'root';
$password = '';

$data = json_decode(file_get_contents("php://input"), true);
$markerId = $data['markerId'];

if (!defined('SITE_ROOT')) {
    define('SITE_ROOT', realpath(dirname(__FILE__, 2))); // 2 levels up
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Получение данных о маркере и соответствующем видео
    $sql = "SELECT marker.image_name, video.urlVideo 
            FROM marker 
            JOIN video ON marker.idVideo = video.idVideo 
            WHERE marker.id_marker = :markerId";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':markerId', $markerId, PDO::PARAM_INT);
    $stmt->execute();
    $marker = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($marker) {
        // Путь к файлу маркера
        //$filePath = SITE_ROOT . "\\video\\{$marker['urlVideo']}\\{$marker['image_name']}";
        $filePath = SITE_ROOT . "/video/{$marker['urlVideo']}/{$marker['image_name']}";

        if (file_exists($filePath)) {
            if (unlink($filePath)) { 
            } else {
                echo "Ошибка удаления файла"; // Отладочное сообщение о неудаче удаления файла
                exit();
            }
        } else {
            echo "Файл не найден"; // Отладочное сообщение о том, что файл не существует
            exit();
        }

        // Удаление записи о маркере из базы данных
        $sqlDelete = "DELETE FROM marker WHERE id_marker = :markerId";
        $stmtDelete = $pdo->prepare($sqlDelete);
        $stmtDelete->bindParam(':markerId', $markerId, PDO::PARAM_INT);
        $stmtDelete->execute();

        echo "Маркер и файл успешно удалены.";
    } else {
        echo "Маркер не найден.";
    }
} catch (PDOException $e) {
    echo "Ошибка при удалении маркера: " . $e->getMessage();
}
