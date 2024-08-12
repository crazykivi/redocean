<?php
/*
$mysqli = new mysqli("localhost", "root", "", "web-player");

// Проверка соединения
if ($mysqli->connect_errno) {
    echo "Не удалось подключиться к MySQL: " . $mysqli->connect_error;
    exit();
}

$urlVideo = $_GET['urlVideo'];

$query = "SELECT nameVideo, description FROM `web-player`.video WHERE urlVideo = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("s", $urlVideo);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

echo json_encode($data);

$stmt->close();
$mysqli->close();
*/
$mysqli = new mysqli("localhost", "root", "", "web-player");

// Проверка соединения
if ($mysqli->connect_errno) {
    echo "Не удалось подключиться к MySQL: " . $mysqli->connect_error;
    exit();
}

$urlVideo = $_GET['urlVideo'];

$query = "SELECT nameVideo, description, id_themes_video FROM `web-player`.video WHERE urlVideo = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("s", $urlVideo);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

echo json_encode($data);

$stmt->close();
$mysqli->close();
?>