<?php /*
session_start();
$host = 'localhost';
$dbname = 'web-player';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Ошибка подключения: " . $e->getMessage(); // Обработка ошибки подключения
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['csrf_token'], $_POST['authorId'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token mismatch.');
    }

    $key = 'KEYREDOCEAN23451341';
    $method = 'AES-256-CBC';
    $data = base64_decode($_POST['authorId']);
    list($encrypted_data, $iv) = explode('::', $data, 2);

    $authorId = openssl_decrypt($encrypted_data, $method, $key, 0, $iv);

    if (!$authorId) {
        die('Decryption error.');
    }

    // Логика отписки
    $userId = $_SESSION['userId'];  // Используем ID из сессии
    $query = "DELETE FROM subscriptions WHERE idUsers = ? AND subscribedId = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$userId, $authorId]);

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
*/
session_start();
$host = 'localhost';
$dbname = 'web-player';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Ошибка подключения: " . $e->getMessage(); // Обработка ошибки подключения
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['csrf_token'], $_POST['authorId'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token mismatch.');
    }

    $key = 'KEYREDOCEAN23451341';
    $method = 'AES-256-CBC';
    $data = base64_decode($_POST['authorId']);
    list($encrypted_data, $iv) = explode('::', $data, 2);

    $authorId = openssl_decrypt($encrypted_data, $method, $key, 0, $iv);

    if (!$authorId) {
        die('Decryption error.');
    }

    $userId = $_SESSION['userId'];
    $query = "DELETE FROM subscriptions WHERE idUsers = ? AND subscribedId = ?";
    $stmt = $pdo->prepare($query);
    if ($stmt->execute([$userId, $authorId])) {
        echo json_encode(['success' => true]);

        // Сброс AUTO_INCREMENT после удаления данных
        $resetAutoIncrement = "ALTER TABLE subscriptions AUTO_INCREMENT = 1;";
        $pdo->exec($resetAutoIncrement);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка при удалении подписки']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}