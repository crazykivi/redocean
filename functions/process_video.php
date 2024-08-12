<?php
$host = 'localhost';
$db = 'web-player';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_checkVideo = $_POST['id_checkVideo'];
    $result = $_POST['result'];
    $reason = isset($_POST['reason']) ? $_POST['reason'] : null;

    $query = "UPDATE `web-player`.check_video 
              SET result = :result, reason = :reason 
              WHERE id_checkVideo = :id_checkVideo";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':result', $result);
    $stmt->bindParam(':reason', $reason);
    $stmt->bindParam(':id_checkVideo', $id_checkVideo);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка обновления данных']);
    }
    exit();
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный метод запроса']);
    exit();
}
