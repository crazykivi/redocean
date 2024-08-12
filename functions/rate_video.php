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
/*
if (!isset($_SESSION['userId'], $_POST['type'], $_POST['idVideo'])) {
    echo json_encode(['message' => 'Недостаточно данных']);
    exit;
}

$idUsers = $_SESSION['userId'];
$idVideo = $_POST['idVideo'];
$type = $_POST['type'];

$sql = "INSERT INTO video_likes (idVideo, idUsers, likeType) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE likeType=?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$idVideo, $idUsers, $type, $type]);

echo json_encode(['message' => 'Ваш голос учтён']);
*/
if (!isset($_SESSION['userId'], $_POST['type'], $_POST['idVideo'])) {
    echo json_encode(['message' => 'Недостаточно данных', 'status' => 'error']);
    exit;
}

$idUsers = $_SESSION['userId'];
$idVideo = $_POST['idVideo'];
$type = $_POST['type'];

// Проверяем, есть ли уже лайк или дизлайк от этого пользователя для данного видео
$sql = "SELECT likeType FROM video_likes WHERE idVideo = ? AND idUsers = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$idVideo, $idUsers]);
$currentLikeType = $stmt->fetchColumn();

if ($currentLikeType) {
    if ($currentLikeType === $type) {
        // Если текущий тип совпадает с новым, удаляем запись
        $sql = "DELETE FROM video_likes WHERE idVideo = ? AND idUsers = ?";
        $message = "Ваш голос был удален.";
    } else {
        // Если тип не совпадает, обновляем запись
        $sql = "UPDATE video_likes SET likeType = ? WHERE idVideo = ? AND idUsers = ?";
        $message = "Ваш голос изменен на " . ($type === 'like' ? 'лайк' : 'дизлайк') . ".";
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idVideo, $idUsers]);
} else {
    // Если записи не было, добавляем новую
    $sql = "INSERT INTO video_likes (idVideo, idUsers, likeType) VALUES (?, ?, ?)";
    $message = "Ваш голос учтен как " . ($type === 'like' ? 'лайк' : 'дизлайк') . ".";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idVideo, $idUsers, $type]);
}

echo json_encode(['message' => $message, 'status' => 'success']);
?>