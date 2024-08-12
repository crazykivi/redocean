<?php
if (!defined('SITE_ROOT')) {
    define('SITE_ROOT', realpath(dirname(__FILE__, 2))); // 2 уровня вверх от текущего расположения файла
}
session_start();

if (!isset($_SESSION['userId'])) {
    error_log("User not logged in.");
    header('Location: login');
    exit();
}

if (!empty($_FILES['video'])) {
    error_log("File upload started.");
    $video = $_FILES['video'];
    $targetDir = SITE_ROOT . "/video/"; // Использование абсолютного пути
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $host = "localhost";
    $username = "root";
    $password = "";
    $dbname = "web-player";

    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $videoTitle = basename($video["name"], "." . pathinfo($video["name"], PATHINFO_EXTENSION));
    //$uploadDate = date('Y.m.d'); //Под DATE
    $uploadDate = date('Y-m-d H:i:s');

    $query = "SELECT MAX(idVideo) AS last_id FROM video";
    $statement = $pdo->query($query);
    $result = $statement->fetch(PDO::FETCH_ASSOC);
    $urlVideo = md5(uniqid($result['last_id'], true));
    $targetFile = $targetDir . $urlVideo . '.mp4'; // Присваиваем расширение .mp4

    if (move_uploaded_file($video["tmp_name"], $targetFile)) {
        error_log("File moved to target directory.");
        $sql = "INSERT INTO video (nameVideo, urlVideo, authorsID, uploadDate) 
        VALUES (:nameVideo, :urlVideo, :authorsID, :uploadDate)";
        $stmt = $pdo->prepare($sql);
        $insertSuccess = $stmt->execute([
            ':nameVideo' => $videoTitle,
            ':urlVideo' => $urlVideo,
            ':authorsID' => $_SESSION['userId'], // Убедитесь, что автор ID установлен правильно
            ':uploadDate' => $uploadDate
        ]);
        if ($insertSuccess) {
            $response = [
                'success' => true,
                'fileName' => $urlVideo . '.mp4',
                'idVideo' => $pdo->lastInsertId() // Получение последнего вставленного ID
            ];
            echo json_encode($response);
        } else {
            $response = ['success' => false, 'error' => 'Ошибка сохранения файла.'];
            echo json_encode($response);
        }
    } else {
        $response = ['success' => false, 'error' => 'Ошибка загрузки файла.'];
        echo json_encode($response);
    }
} else {
    $response = ['success' => false, 'error' => 'Файл не был загружен.'];
    echo json_encode($response);
}
