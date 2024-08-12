<?php
session_start();
$dbHost = 'localhost';
$dbName = 'web-player';
$dbUser = 'root';
$dbPass = '';


if (!isset($_SESSION['userId']) || !isset($_POST['idComment'])) {
    echo json_encode(['success' => false, 'error' => 'Неверный запрос']);
    exit();
}

$userId = $_SESSION['userId'];
$commentId = $_POST['idComment'];
$role = $_SESSION['role']; // Роль пользователя

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Получаем информацию о комментарии
    $stmt = $pdo->prepare("SELECT * FROM comments WHERE idComments = :commentId");
    $stmt->bindParam(':commentId', $commentId, PDO::PARAM_INT);
    $stmt->execute();
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$comment) {
        echo json_encode(['success' => false, 'error' => 'Комментарий не найден']);
        exit();
    }

    // Проверяем права пользователя на удаление комментария
    if ($role === 'Администратор' || ($role === 'Модератор' && $comment['role'] === 'Пользователь') || $userId === $comment['userId']) {
        // Удаляем комментарий
        $stmt = $pdo->prepare("DELETE FROM comments WHERE idComments = :commentId");
        $stmt->bindParam(':commentId', $commentId, PDO::PARAM_INT);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Ошибка при удалении комментария']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'У вас нет прав на удаление этого комментария']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Ошибка базы данных: ' . $e->getMessage()]);
}
