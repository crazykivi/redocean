<?php
if (!defined('SITE_ROOT')) {
    define('SITE_ROOT', realpath(dirname(__FILE__, 2))); // 2 уровня вверх от текущего расположения файла
}

session_start();

// Проверяем, залогинен ли пользователь
if (!isset($_SESSION['userId'])) {
    header('Location: login');
    exit();
}

header('Content-Type: application/json; charset=utf-8');

$response = [];

try {
    if (isset($_POST['videoId'], $_POST['videoTitle'], $_POST['videoTheme']) && !empty($_POST['videoId'])) {
        $host = "localhost";
        $username = "root";
        $password = "";
        $dbname = "web-player";

        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $videoId = $_POST['videoId'];
        $videoTitle = $_POST['videoTitle'];
        $videoTheme = $_POST['videoTheme'];
        $authorID = $_SESSION['userId'];

        $sql = "UPDATE video SET nameVideo = :nameVideo, id_themes_video = :videoTheme WHERE idVideo = :videoId AND authorsID = :authorsID";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nameVideo', $videoTitle);
        $stmt->bindParam(':videoTheme', $videoTheme);
        $stmt->bindParam(':videoId', $videoId);
        $stmt->bindParam(':authorsID', $authorID);
        $stmt->execute();

        $stmt = $pdo->prepare("SELECT `urlVideo`,`nameVideo` FROM `video` WHERE `idVideo` = :videoId");
        $stmt->bindParam(':videoId', $videoId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $urlVideo = $result['urlVideo'];
            $previewFileName = $urlVideo . '.png';
            $uploadDir = SITE_ROOT . '/video';
            $uploadPath = $uploadDir . '/' . $previewFileName;

            if (isset($_FILES['videoPreview']) && $_FILES['videoPreview']['error'] === UPLOAD_ERR_OK) {
                if (move_uploaded_file($_FILES['videoPreview']['tmp_name'], $uploadPath)) {
                    $response['message'] = "Новое превью успешно загружено.";
                } else {
                    $response['message'] = "Ошибка при загрузке превью.";
                }
            } else {
                // Копирование стандартного изображения stock-video.png если превью не загружено
                $defaultPreviewPath = $uploadDir . '/stock-video.png';
                if (copy($defaultPreviewPath, $uploadPath)) {
                    $response['message'] = "Стандартное изображение превью было использовано.";
                } else {
                    $response['message'] = "Ошибка при копировании стандартного изображения превью.";
                }
            }
        } else {
            $response['error'] = "Видео не найдено.";
        }
    } else {
        $response['error'] = "Не все параметры были переданы.";
    }
} catch (Exception $e) {
    $response['error'] = "Произошла ошибка: " . $e->getMessage();
}

echo json_encode($response);
?>
