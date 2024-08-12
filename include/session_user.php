<?php
session_start();
/*$currentUserId = 1;
$currentUserId = NULL;*/


/*$currentUserId = $_SESSION['userId'];
$idUsers = $currentUserId;
*/


//$_SESSION['userId'] = $currentUserId;

/*
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$key = 'KEYREDOCEAN23451341'; // Сильный ключ. Сохраните его в безопасном месте!
$method = 'AES-256-CBC';
$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
$querySubscriptions = "SELECT users.nameUsers, users.idUsers
                       FROM subscriptions
                       JOIN users ON subscriptions.subscribedId = users.idUsers
                       WHERE subscriptions.idUsers = :userId";
$stmt1 = $pdo->prepare($querySubscriptions);
$stmt1->bindParam(':userId', $_SESSION['userId'], PDO::PARAM_INT);
$stmt1->execute();
*/
if (isset($_SESSION['userId'])) {
    $currentUserId = $_SESSION['userId'];
    $idUsers = $currentUserId;
    $queryRole = "SELECT roleUsers FROM users WHERE idUsers = :userId";
    $stmt = $pdo->prepare($queryRole);
    $stmt->bindParam(':userId', $currentUserId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $role = $result['roleUsers'];

    // Генерация CSRF токена, если его нет
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $key = 'KEYREDOCEAN23451341'; // Сильный ключ. Сохраните его в безопасном месте!
    $method = 'AES-256-CBC';
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
}

$querySubscriptions = "SELECT users.nameUsers, users.idUsers
FROM subscriptions
JOIN users ON subscriptions.subscribedId = users.idUsers
WHERE subscriptions.idUsers = :userId";
$stmt1 = $pdo->prepare($querySubscriptions);
$stmt1->bindParam(':userId', $_SESSION['userId'], PDO::PARAM_INT);
$stmt1->execute();
