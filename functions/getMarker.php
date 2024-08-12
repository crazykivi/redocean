<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "web-player";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$idVideo = $_GET['idVideo']; // Пример

// SQL-запрос для выборки данных
$sql = "SELECT time_marker, image_name, name_marker FROM marker WHERE idVideo = $idVideo"; 

$result = $conn->query($sql);

$timingsAndImages = [];

if ($result->num_rows > 0) {
    // Формируем массив с данными о таймингах и изображениях
    while($row = $result->fetch_assoc()) {
        $timingsAndImages[] = [
            'time' => $row['time_marker'],
            'image' => $row['image_name'],
            'name' => $row['name_marker']
        ];
    }
}

// Отправляем данные в формате JSON
echo json_encode($timingsAndImages);

$conn->close();
?>