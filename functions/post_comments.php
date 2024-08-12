<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "web-player";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Устанавливаем режим ошибок PDO в исключения
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

include("../include/session_user.php");

// Получение данных из POST запроса
$idVideo = $_POST['idVideo'];
$comment = $_POST['comment'];

try {
    // SQL-запрос для вставки комментария с использованием подготовленного выражения
    $sql = "INSERT INTO comments (idVideo, idUsers, comment) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idVideo, $idUsers, $comment]);

    if ($stmt->rowCount() > 0) {
        echo "Комментарий добавлен успешно";
    } else {
        echo "Ошибка при добавлении комментария";
    }
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}

// Закрытие соединения с базой данных
$pdo = null;
