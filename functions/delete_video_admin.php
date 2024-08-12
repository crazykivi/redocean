<?php

if (!defined('SITE_ROOT')) {
    define('SITE_ROOT', realpath(dirname(__FILE__, 2))); // 2 уровня вверх от текущего расположения файла
}

$host = "localhost";
$username = "root";
$password = "";
$dbname = "web-player";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    include("../include/session_user.php");

    if (!isset($_SESSION['userId'])) {
        header('Location: login.php');
        exit();
    } else {
        switch ($role) {
            case 'Пользователь':
                header('Location: index');
                break;
            case 'Модератор':
                header('Location: index');
                break;
        }
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Прочитаем JSON данные из тела запроса
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['urlVideo'])) {
            echo json_encode(['success' => false, 'error' => 'URL видео не указан.']);
            exit();
        }

        $urlVideo = $data['urlVideo'];

        // Удаляем запись из базы данных
        $stmt = $pdo->prepare("DELETE FROM video WHERE urlVideo = :urlVideo");
        $stmt->bindParam(':urlVideo', $urlVideo, PDO::PARAM_STR);
        if ($stmt->execute()) {
            // Удаляем файлы с сервера
            $videoFilePath = SITE_ROOT . '/video/' . $urlVideo . '.mp4';
            $previewFilePath = SITE_ROOT . '/video/' . $urlVideo . '.png';

            if (file_exists($videoFilePath)) {
                unlink($videoFilePath);
            }

            if (file_exists($previewFilePath)) {
                unlink($previewFilePath);
            }

            // Удаляем папку с маркерами, если существует
            $markerDir = SITE_ROOT . '/video/' . $urlVideo . '/';
            if (is_dir($markerDir)) {
                array_map('unlink', glob("$markerDir/*.*"));
                rmdir($markerDir);
            }

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Ошибка удаления видео из базы данных.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Неверный запрос.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Ошибка базы данных: ' . $e->getMessage()]);
    exit();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Ошибка: ' . $e->getMessage()]);
    exit();
}
