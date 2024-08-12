<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "web-player";

// Создание подключения
$conn = new mysqli($servername, $username, $password, $dbname);

// Проверка подключения
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Получение idVideo и lastCommentId
$idVideo = isset($_POST['idVideo']) ? $_POST['idVideo'] : null;
$lastCommentId = isset($_POST['lastCommentId']) ? $_POST['lastCommentId'] : null;

// SQL-запрос для выборки последних 10 комментариев и имен пользователей
$sql = "SELECT comments.*, users.nameUsers AS username 
        FROM comments 
        INNER JOIN users ON comments.idUsers = users.idUsers 
        WHERE comments.idVideo = ? ";
if ($lastCommentId) {
    $sql .= "AND comments.idComments < ? ";
}
$sql .= "ORDER BY comments.datecomments DESC 
         LIMIT 10";
$stmt = $conn->prepare($sql);

// Если используются параметры, связать их с заполнителями в запросе
if ($lastCommentId) {
    $stmt->bind_param("ii", $idVideo, $lastCommentId);
} else {
    $stmt->bind_param("i", $idVideo);
}

$stmt->execute();
$result = $stmt->get_result();
$comments = $result->fetch_all(MYSQLI_ASSOC);

// Возвращаем результат в формате JSON
echo json_encode($comments);
?>