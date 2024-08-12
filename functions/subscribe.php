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

// Проверка CSRF токена и наличия данных
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['csrf_token'], $_POST['authorId'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(['success' => false, 'message' => 'CSRF token mismatch.']);
        exit();
    }

    $key = 'KEYREDOCEAN23451341'; // Убедитесь, что ключ защищен
    $method = 'AES-256-CBC';
    $data = base64_decode($_POST['authorId']);
    list($encrypted_data, $iv) = explode('::', $data, 2);

    $authorId = openssl_decrypt($encrypted_data, $method, $key, 0, $iv);

    if (!$authorId) {
        echo json_encode(['success' => false, 'message' => 'Decryption error.']);
        exit();
    }

    $userId = $_SESSION['userId']; // ID пользователя из сессии

    // Проверка, что пользователь не пытается подписаться сам на себя
    if ($userId == $authorId) {
        echo json_encode(['success' => false, 'message' => 'Вы не можете подписаться на самого себя']);
        exit();
    }

    // Проверка, не подписан ли уже пользователь на автора
    $checkSubscription = $pdo->prepare("SELECT COUNT(*) FROM subscriptions WHERE idUsers = :userId AND subscribedId = :authorId");
    $checkSubscription->execute(['userId' => $userId, 'authorId' => $authorId]);
    if ($checkSubscription->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'Вы уже подписаны']);
        exit();
    }

    // Вставка подписки в базу данных, если все проверки пройдены
    $query = "INSERT INTO subscriptions (idUsers, subscribedId, subscriptionDate) VALUES (?, ?, CURDATE())";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$userId, $authorId]);

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>