<?php
$host = 'localhost';
$dbname = 'web-player';
$username = 'root';
$password = '';

// Получение JSON данных из запроса
$data = json_decode(file_get_contents("php://input"), true);
$markerId = $data['markerId'];
$newName = $data['newName'];
$newTime = $data['newTime'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "UPDATE marker SET name_marker = :newName, time_marker = :newTime WHERE id_marker = :markerId";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':newName', $newName, PDO::PARAM_STR);
    $stmt->bindParam(':newTime', $newTime, PDO::PARAM_STR);
    $stmt->bindParam(':markerId', $markerId, PDO::PARAM_INT);
    $stmt->execute();

    echo "Маркер успешно обновлён.";
} catch (PDOException $e) {
    echo "Ошибка обновления маркера: " . $e->getMessage();
}
?>