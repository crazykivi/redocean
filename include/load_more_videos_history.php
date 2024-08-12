<?php
$host = 'localhost';
$dbname = 'web-player';
$username = 'root';
$password = '';

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  echo "Ошибка подключения: " . $e->getMessage();
  exit();
}

$startFrom = isset($_GET['startFrom']) ? (int)$_GET['startFrom'] : 0;
include ("session_user.php");
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
$query = "SELECT 
video.idVideo, 
video.nameVideo, 
video.urlVideo, 
users.nameUsers, 
COUNT(DISTINCT video_views.idVideoViews) AS viewCount, 
themes_video.name_themes,
'Одобрено' AS result,
history_views.viewed_at,
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
JOIN
history_views ON video.idVideo = history_views.idVideo
WHERE check_video.result = 'Одобрено'
AND type_video.nameType = 'Открытый доступ'
AND history_views.idUsers = $idUsers
GROUP BY 
video.idVideo
ORDER BY 
history_views.viewed_at DESC 
LIMIT 
$startFrom, 10";



// Prepare statement and bind variables
$stmt = $pdo->prepare($query);
$stmt->bindParam(':idUsers', $idUsers, PDO::PARAM_INT);
$stmt->bindParam(':startFrom', $startFrom, PDO::PARAM_INT);

$stmt->execute();

if ($stmt->rowCount() > 0) {
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $formattedViews = formatViews($row['viewCount']);
    echo "<div class='video'>
    <a href='player?watch={$row['urlVideo']}'>
    <div class='video-thumbnail'>
    <div class='loader'></div> <!-- Если убирать загрузку, то не забыть убрать это -->
    <img src='video/{$row['urlVideo']}.PNG' alt='Превью видео'"; ?> onerror="this.onerror=null; this.src='video/stock-video.png'"> <!-- ПОПРОБОВАТЬ ИСПРАВИТЬ -->
<?php
    echo "</div>
    <div class='video-info'>
    <div class='video-title'>{$row['nameVideo']}</div>
    <div class='video-meta'>Дата и время просмотра: {$row['viewed_at']}</div>
    <div class='video-meta'>{$row['nameUsers']}</div>
    <div class='video-meta'>{$formattedViews}</div>
    </div>
    </a>
    </div>";
  }
}
