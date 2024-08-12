<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Проверяем, залогинен ли пользователь
if (!isset($_SESSION['userId'])) {
    header('Location: login.php');
    exit();
}

$host = "localhost";
$username = "root";
$password = "";
$dbname = "web-player";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['urlVideo'])) {
            echo json_encode(['success' => false, 'error' => 'URL видео не указан.']);
            exit();
        }

        $urlVideo = $data['urlVideo'];
        $userId = $_SESSION['userId'];

        // Получаем текущий статус доступа и проверяем автора
        $stmt = $pdo->prepare("SELECT v.idVideo, v.authorsID, t.nameType as accessType 
                               FROM video v 
                               JOIN type_video t ON v.idVideo = t.idVideo 
                               WHERE v.urlVideo = :urlVideo");
        $stmt->bindParam(':urlVideo', $urlVideo, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            echo json_encode(['success' => false, 'error' => 'Видео не найдено.']);
            exit();
        }

        /*
        if ($row['authorsID'] !== $userId) {
            echo json_encode(['success' => false, 'error' => 'Вы не являетесь автором этого видео.']);
            exit();
        }
        */
        if ($row['authorsID'] != $userId) { // Сравниваем как строку и число
            echo json_encode(['success' => false, 'error' => 'Вы не являетесь автором этого видео']);
            exit();
        }

        $currentAccessType = $row['accessType'];
        $newAccessType = ($currentAccessType === 'Открытый доступ') ? 'Закрыто' : 'Открытый доступ';

        // Обновляем статус доступа
        $updateStmt = $pdo->prepare("UPDATE type_video SET nameType = :newAccessType WHERE idVideo = :idVideo");
        $updateStmt->bindParam(':newAccessType', $newAccessType, PDO::PARAM_STR);
        $updateStmt->bindParam(':idVideo', $row['idVideo'], PDO::PARAM_INT);
        $updateStmt->execute();

        echo json_encode(['success' => true, 'newAccessType' => $newAccessType]);
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
