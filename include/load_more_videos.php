<?php
// load_more_videos.php
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

function formatViews($viewCount)
{
    if ($viewCount == 1) {
        $formattedCount = $viewCount . " просмотр";
    } elseif ($viewCount > 1 and $viewCount <= 4) {
        $formattedCount = $viewCount . " просмотра";
    } elseif ($viewCount >= 1000000) {
        $formattedCount = number_format($viewCount / 1000000, 1) . ' млн. просмотров';
    } elseif ($viewCount >= 1000) {
        $formattedCount = number_format($viewCount / 1000, 1) . ' тыс. просмотров';
    } else {
        $formattedCount = $viewCount . ' просмотров';
    }
    return $formattedCount;
}

/*
$query = "SELECT 
video.idVideo, 
video.nameVideo, 
video.urlVideo, 
users.nameUsers, 
COUNT(DISTINCT video_views.idVideoViews) AS viewCount, 
themes_video.name_themes,
'Одобрено' AS result
FROM 
video 
JOIN 
users ON video.authorsID = users.idUsers
LEFT JOIN 
video_views ON video.idVideo = video_views.idVideo
LEFT JOIN 
themes_video ON video.id_themes_video = themes_video.id_themes_video
INNER JOIN 
check_video ON video.idVideo = check_video.idVideo
WHERE 
check_video.result = 'Одобрено'
GROUP BY 
video.idVideo LIMIT $startFrom, 20";
*/

$whereClauses = ["check_video.result = 'Одобрено'", "type_video.nameType = 'Открытый доступ'"];
$theme = isset($_GET['themes']) ? $_GET['themes'] : null;
$searchQuery = isset($_GET['search']) ? $_GET['search'] : null;
$startFrom = $_GET['startFrom'];
if (!empty($searchQuery)) {
    $searchQuery = $pdo->quote("%$searchQuery%");
    $whereClauses[] = "(video.nameVideo LIKE $searchQuery OR users.nameUsers LIKE $searchQuery)";
}
if (!empty($theme)) {
    $theme = (int) $theme; // Безопасное приведение к целому числу
    $whereClauses[] = "themes_video.id_themes_video = $theme";
}

$whereSql = implode(' AND ', $whereClauses);

$query = "SELECT 
          video.idVideo, 
          video.nameVideo, 
          video.urlVideo, 
          users.nameUsers, 
          COUNT(DISTINCT video_views.idVideoViews) AS viewCount, 
          themes_video.name_themes,
          'Одобрено' AS result,
          COUNT(DISTINCT comments.idComments) AS commentCount,
          type_video.nameType AS accessType
        FROM 
          video 
        JOIN 
          users ON video.authorsID = users.idUsers
        LEFT JOIN 
          video_views ON video.idVideo = video_views.idVideo
        LEFT JOIN 
          themes_video ON video.id_themes_video = themes_video.id_themes_video
        INNER JOIN 
          check_video ON video.idVideo = check_video.idVideo
        LEFT JOIN 
          comments ON video.idVideo = comments.idVideo
        JOIN 
          type_video ON video.idVideo = type_video.idVideo
        WHERE 
          $whereSql
        GROUP BY 
          video.idVideo 
        LIMIT 
          $startFrom, 20";
$stmt = $pdo->query($query);
if ($stmt->rowCount() > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $formattedViews = formatViews($row['viewCount']);
        echo "<div class='video'>
            <a href='player?watch={$row['urlVideo']}'>
                <img src='video/{$row['urlVideo']}.PNG' alt='Превью видео'>
                <div class='video-info'>
                    <div class='video-title'>{$row['nameVideo']}</div>
                    <div class='video-meta'>{$row['nameUsers']}</div>
                    <div class='video-meta'>{$formattedViews}</div>
                </div>
            </a>
        </div>";
    }
}
