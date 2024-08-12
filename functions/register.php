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

    // Шифрование пароля
    $hashedPassword = password_hash($passUsers, PASSWORD_BCRYPT);

    // Вставка пользователя в базу данных
    $query = "INSERT INTO `users` (nameUsers, passUsers) VALUES (:nameUsers, :passUsers)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nameUsers', $nameUsers);
    $stmt->bindParam(':passUsers', $hashedPassword);
    if ($stmt->execute()) {
        echo json_encode(["message" => "Регистрация успешна!"]);
    } else {
        echo json_encode(["message" => "Ошибка регистрации!"]);
    }
}
