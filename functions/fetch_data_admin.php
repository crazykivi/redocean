<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Параметры подключения к базе данных
$host = 'localhost';
$dbname = 'web-player';
$username = 'root';
$password = '';

// Попытка подключения к базе данных с использованием PDO
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    include("../include/session_user.php");

    // Получение параметра viewType из POST запроса
    $viewType = isset($_POST['viewType']) ? $_POST['viewType'] : 'months';
    $idVideo=1;

    if ($idVideo) {
        switch ($viewType) {
            case 'days':
                $sql = "SELECT DATE_FORMAT(viewed_at, '%Y-%m-%d') AS date, COUNT(*) AS view_count 
                        FROM video_views
                        WHERE idVideo = :idVideo
                        GROUP BY DATE_FORMAT(viewed_at, '%Y-%m-%d')
                        ORDER BY date DESC";
                break;
            case 'months':
                $sql = "SELECT DATE_FORMAT(viewed_at, '%Y-%m') AS date, COUNT(*) AS view_count 
                        FROM video_views
                        WHERE idVideo = :idVideo
                        GROUP BY DATE_FORMAT(viewed_at, '%Y-%m')
                        ORDER BY date DESC";
                break;
            case 'years':
                $sql = "SELECT DATE_FORMAT(viewed_at, '%Y') AS date, COUNT(*) AS view_count 
                        FROM video_views
                        WHERE idVideo = :idVideo
                        GROUP BY DATE_FORMAT(viewed_at, '%Y')
                        ORDER BY date DESC";
                break;
            default:
                echo json_encode(['error' => 'Неизвестный тип просмотра данных']);
                exit;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idVideo', $idVideo, PDO::PARAM_INT);
    } elseif ($idUsers) {
        // Если нет idVideo, ищем все просмотры для всех видео, принадлежащих пользователю
        switch ($viewType) {
            case 'days':
                $sql = "SELECT DATE_FORMAT(vv.viewed_at, '%Y-%m-%d') AS date, COUNT(*) AS view_count 
                        FROM video_views vv
                        JOIN video v ON vv.idVideo = v.idVideo
                        WHERE v.authorsID = :idUsers
                        GROUP BY DATE_FORMAT(vv.viewed_at, '%Y-%m-%d')
                        ORDER BY date DESC";
                break;
            case 'months':
                $sql = "SELECT DATE_FORMAT(vv.viewed_at, '%Y-%m') AS date, COUNT(*) AS view_count 
                        FROM video_views vv
                        JOIN video v ON vv.idVideo = v.idVideo
                        WHERE v.authorsID = :idUsers
                        GROUP BY DATE_FORMAT(vv.viewed_at, '%Y-%m')
                        ORDER BY date DESC";
                break;
            case 'years':
                $sql = "SELECT DATE_FORMAT(vv.viewed_at, '%Y') AS date, COUNT(*) AS view_count 
                        FROM video_views vv
                        JOIN video v ON vv.idVideo = v.idVideo
                        WHERE v.authorsID = :idUsers
                        GROUP BY DATE_FORMAT(vv.viewed_at, '%Y')
                        ORDER BY date DESC";
                break;
            default:
                // Возвращаем ошибку, если тип не поддерживается
                echo json_encode(['error' => 'Неизвестный тип просмотра данных']);
                exit;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idUsers', $idUsers, PDO::PARAM_INT);
    } else {
        // Возвращаем ошибку, если ни idVideo, ни idUsers не установлены
        echo json_encode(['error' => 'Не задан ни idVideo, ни idUsers']);
        exit;
    }

    // Выполнение запроса
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Возвращаем данные в формате JSON
    echo json_encode($data);
} catch (PDOException $e) {
    echo json_encode(['error' => "Ошибка подключения: " . $e->getMessage()]);
    exit();
}
?>