<?php
session_start();
$host = 'localhost';
$dbname = 'web-player';
$username = 'root';
$password = '';

// Подключение к базе данных
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Ошибка подключения: " . $e->getMessage(); // Обработка ошибки подключения
    exit();
}
$idUsers = 1;
$idVideo = 1;
//if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['csrf_token'], $_POST['videoId'])) {
if (isset($idVideo, $idUsers)) {
    /*
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        // CSRF проверка не пройдена
        echo json_encode(['success' => false, 'error' => 'CSRF token mismatch.']);
        exit;
    }*/
    date_default_timezone_set('Asia/Krasnoyarsk');
    global $pdo;
    $timeInterval = 3600;

    /*
    $userId = $_SESSION['userId'];
    $idVideo = $_POST['videoId'];
    */
    $value = '';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $value = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $value = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $value = $_SERVER['REMOTE_ADDR'];
    }

    if ($idUsers) {
        $query = "SELECT * FROM history_views WHERE idVideo = :idVideo AND idUsers = :idUsers";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':idVideo', $idVideo);
        $stmt->bindParam(':idUsers', $idUsers);
        $stmt->execute();
        $existingView = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingView) {
            // Если запись существует, обновляем время viewed_at
            $query = "UPDATE history_views SET viewed_at = NOW() WHERE idVideo = :idVideo AND idUsers = :idUsers";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':idVideo', $idVideo);
            $stmt->bindParam(':idUsers', $idUsers);
            $stmt->execute();
        } else {
            // Если запись не найдена, добавляем новую запись
            $query = "INSERT INTO history_views (idVideo, idUsers, viewed_at) VALUES (:idVideo, :idUsers, NOW())";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':idVideo', $idVideo);
            $stmt->bindParam(':idUsers', $idUsers);
            $stmt->execute();
        }
        $query = "SELECT * FROM video_views WHERE idVideo = :idVideo AND idUsers = :idUsers ORDER BY viewed_at DESC LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':idVideo', $idVideo);
        $stmt->bindParam(':idUsers', $idUsers);
        $stmt->execute();
        $existingView = $stmt->fetch(PDO::FETCH_ASSOC);
        $currentTime = time();
        if (!$existingView || ($currentTime - strtotime($existingView['viewed_at'])) > $timeInterval) {
            $query = "INSERT INTO video_views (idVideo, idUsers, ip_address, viewed_at) VALUES (:idVideo, :idUsers, :ipAddress, NOW())";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':idVideo', $idVideo);
            $stmt->bindParam(':idUsers', $idUsers);
            $stmt->bindParam(':ipAddress', $ipAddress);
            $stmt->execute();

            $query = "SELECT COUNT(*) AS viewCount FROM video_views WHERE idVideo = :idVideo";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':idVideo', $idVideo);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $views = $result['viewCount'];
            // Получение обновленного количества просмотров
            echo json_encode(['viewCount' => ++$views]);
        }
    } else {
        $query = "SELECT * FROM video_views WHERE idVideo = :idVideo AND idUsers IS NULL AND ip_address = :ipAddress ORDER BY viewed_at DESC LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':idVideo', $idVideo);
        $stmt->bindParam(':ipAddress', $ipAddress);
        $stmt->execute();
        $existingView = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$existingView) {
            $query = "INSERT INTO video_views (idVideo, idUsers, ip_address, viewed_at) VALUES (:idVideo, NULL, :ipAddress, NOW())";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':idVideo', $idVideo);
            $stmt->bindParam(':ipAddress', $ipAddress);
            $stmt->execute();

            $query = "SELECT COUNT(*) AS viewCount FROM video_views WHERE idVideo = :idVideo";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':idVideo', $idVideo);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $views = $result['viewCount'];
            // Получение обновленного количества просмотров
            echo json_encode(['viewCount' => ++$views]);
        } else {
            $currentTime = time();
            if (($currentTime - strtotime($existingView['viewed_at'])) > $timeInterval) {
                $query = "INSERT INTO video_views (idVideo, idUsers, ip_address, viewed_at) VALUES (:idVideo, NULL, :ipAddress, NOW())";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':idVideo', $idVideo);
                $stmt->bindParam(':ipAddress', $ipAddress);
                $stmt->execute();

                $query = "SELECT COUNT(*) AS viewCount FROM video_views WHERE idVideo = :idVideo";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':idVideo', $idVideo);
                $stmt->execute();

                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                $views = $result['viewCount'];
                // Получение обновленного количества просмотров
                echo json_encode(['viewCount' => ++$views]);
            }
        }
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request.']);
}
