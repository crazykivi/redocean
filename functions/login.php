<?php
session_start();
$dbHost = 'localhost';
$dbName = 'web-player';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    // Дополнительные настройки для PDO, если необходимо
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    die("Сервис временно недоступен, просим прощение за доставленные неудобства ");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $nameUsers = $data['nameUsers'];
    $passUsers = $data['passUsers'];

    // Поиск пользователя в базе данных
    $query = "SELECT idUsers, passUsers FROM `users` WHERE nameUsers = :nameUsers";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nameUsers', $nameUsers);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($passUsers, $user['passUsers'])) {
        $_SESSION['userId'] = $user['idUsers'];
        echo json_encode(["message" => "Авторизация успешна!"]);
    } else {
        echo json_encode(["message" => "Неверное имя пользователя или пароль!"]);
    }
}
