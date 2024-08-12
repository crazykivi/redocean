<?php 
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
}?>