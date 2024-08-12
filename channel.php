<?php
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

include("include/session_user.php");

$currentPage = isset($_GET['page']) ? intval($_GET['page']) : 0;
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
if ($currentPage > 0) {
    $startFrom = ($currentPage - 1) * 20; // Вычисляем начальную позицию для выборки видео
} else {
    $startFrom = 0; // Если страница равна 1, начинаем с первого видео
}

$nameUsers = $_GET['user'];

$stmt = $pdo->prepare("
SELECT 
  u.idUsers,
  (SELECT COUNT(*)
   FROM video v
   JOIN check_video cv ON v.idVideo = cv.idVideo AND cv.result = 'Одобрено'
   JOIN type_video tv ON v.idVideo = tv.idVideo
   WHERE v.authorsID = u.idUsers) AS videoCount,
  (SELECT COUNT(*)
   FROM subscriptions s
   WHERE s.subscribedId = u.idUsers) AS subscriberCount
FROM 
  users u
WHERE 
  u.nameUsers = :nameUsers;
");

// Привязка параметра :nameUsers
$stmt->bindParam(':nameUsers', $nameUsers, PDO::PARAM_STR);

// Выполнение запроса
$stmt->execute();

// Получение результата
$result = $stmt->fetch(PDO::FETCH_ASSOC);

// Проверка наличия результата и вывод информации
if ($result) {
    $authorID = $result['idUsers']; // ID пользователя
    $videoCount = $result['videoCount']; // Количество видео
    $subscriberCount = $result['subscriberCount']; // Количество подписчиков
    echo "<script>var authorID = $authorID;</script>";
}/*
$query = "SELECT
    video.idVideo,
    video.nameVideo,
    video.urlVideo,
    video.uploadDate,
    users.nameUsers,
    COUNT(DISTINCT video_views.idVideoViews) AS viewCount,
    themes_video.name_themes,
    check_video.result,
    COUNT(DISTINCT comments.idComments) AS commentCount,
    type_video.nameType AS accessType
FROM
    video
INNER JOIN
    users ON video.authorsID = users.idUsers
LEFT JOIN
    video_views ON video.idVideo = video_views.idVideo
LEFT JOIN
    themes_video ON video.id_themes_video = themes_video.id_themes_video
INNER JOIN
    check_video ON video.idVideo = check_video.idVideo AND check_video.result = 'Одобрено'
LEFT JOIN
    comments ON video.idVideo = comments.idVideo
INNER JOIN
    type_video ON video.idVideo = type_video.idVideo AND type_video.nameType = 'Открытый доступ'
WHERE video.authorsID = $authorID
GROUP BY
    video.idVideo
ORDER BY
    video.uploadDate DESC
LIMIT
    $startFrom, 10";
$stmt = $pdo->query($query);*/
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
if (isset($_SESSION['userId'])) {
    $checkSubscription = $pdo->prepare("SELECT COUNT(*) FROM subscriptions WHERE idUsers = :userId AND subscribedId = :authorId");
    $checkSubscription->execute(['userId' => $idUsers, 'authorId' => $authorID]);
    $isSubscribed = $checkSubscription->fetchColumn() > 0;

    $buttonText = $isSubscribed ? "Отписаться" : "Подписаться";
    $dataSubscribed = $isSubscribed ? "true" : "false";
    $buttonClass = $isSubscribed ? "subscribe-btn subscribed" : "subscribe-btn";
    $key = 'KEYREDOCEAN23451341';
    $method = 'AES-256-CBC';

    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
} else {
    $buttonText = "Подписаться";
    $dataSubscribed = "false";
    $buttonClass = "subscribe-btn";
}
$querySubscriptions = "SELECT users.nameUsers, users.idUsers
                       FROM subscriptions
                       JOIN users ON subscriptions.subscribedId = users.idUsers
                       WHERE subscriptions.idUsers = :userId";
$stmt1 = $pdo->prepare($querySubscriptions);
$stmt1->bindParam(':userId', $_SESSION['userId'], PDO::PARAM_INT);
$stmt1->execute();

if (isset($_SESSION['userId'])) {
    $encryptedAuthorId = openssl_encrypt($authorID, $method, $key, 0, $iv);
    $encryptedAuthorId = base64_encode($encryptedAuthorId . '::' . $iv);
    $idUsers = NULL;
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="logo-mini.png">
    <title><?php echo $nameUsers; ?> канал</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        .channel-info-header {
            width: 100%;
            display: flex;
            align-items: center;
            background-color: white;
            padding: 10px;
            /* box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); */
        }

        .profile-image {
            border-radius: 50%;
            margin-right: 20px;
        }

        .channel-info-header img {
            width: 150px;
            height: auto;
        }

        .channel-info {
            flex-grow: 1;
        }

        .channel-name {
            margin: 0;
            color: #333;
            font-size: 24px;
        }

        .channel-subscribe {
            margin: 0;
            color: #666;
            font-size: 16px;
        }

        .channel-links {
            margin: 0;
            padding: 0;
            list-style-type: none;
            display: flex;
            margin-top: 10px;
        }

        .channel-links li {
            margin-right: 20px;
        }

        .channel-links a {
            text-decoration: none;
            color: #4A90E2;
        }

        .channel-navigation {
            display: flex;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 10px;
        }

        .channel-navigation a {
            text-decoration: none;
            color: #333;
            padding: 10px;
            margin-right: 10px;
            border-bottom: 3px solid transparent;
        }

        .channel-navigation a:hover {
            border-bottom-color: #4A90E2;
        }

        .video-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: flex-start;
            margin: auto;
            margin-top: 30px;
            width: 98%;
        }

        .video {
            background: #fff;
            /* width: 440px; */
            width: 24%;
            /* width: 430px; */
            overflow: hidden;
            /* box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); */
        }

        .video img {
            width: 100%;
            /* height: auto; */
            height: auto;
            /* width: 430px;
            height: 242px; */
            display: block;
            border-radius: 25px;
            aspect-ratio: 16/9;
        }

        .video .video-info {
            padding: 15px;
        }

        .video .video-title {
            /* font-size: 18px; */
            font-size: 1.0vw;
            color: #333;
            margin-bottom: 5px;
            font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
            font: 'Roboto';
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .video .video-meta {
            font-size: 15px;
            color: #666;
        }

        .video-list a {
            text-decoration: none;
            color: #333;
            display: block;
        }

        .loader {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 40px;
            /* фиксированный размер для видимости */
            height: 40px;
            /* фиксированный размер для видимости */
            background: rgba(255, 255, 255, 0.8);
            /* Фон делаем прозрачным */
            border: 6px solid rgba(0, 0, 0, 0.2);
            /* серый цвет для остальной части круга */
            border-top-color: #000;
            /* черный цвет для анимации вращения */
            border-radius: 50%;
            /* круглая форма */
            animation: spin 1s linear infinite;
            /* бесконечная анимация вращения */
        }


        .subscribe-btn {
            padding: 10px;
            background-color: #ff0000;
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 50px;
            transition: transform 0.3s ease;
        }

        .subscribe-btn:hover {
            outline: 1px solid rgba(156, 156, 156, 0.15);
        }

        .subscribe-btn.subscribed {
            background-color: grey;
            /* Серый фон для подписанных пользователей */
            /* cursor: not-allowed; */
            /* Меняем курсор, указывая на невозможность действия */
        }

        @keyframes spin {
            0% {
                transform: translate(-50%, -50%) rotate(0deg);
            }

            100% {
                transform: translate(-50%, -50%) rotate(360deg);
            }
        }

        @media screen and (max-width:1595px) {
            .video {
                width: 32%;
                /* width: 300px; */
            }

            .video .video-title {
                font-size: 2vw;
            }
        }

        @media screen and (max-width:1079px) and (min-width: 1024px) {
            .video {
                width: 31%;
            }
        }

        @media screen and (max-width: 1023px) {
            .video {
                width: 100%;
            }

            .video .video-title {
                font-size: 4vw;
            }
        }
    </style>
    <link rel="stylesheet" href="css/header.css">
</head>
<?php include("include/header.php"); ?>
<?php include_once "include/menu.php"; ?>
<div class="channel-info-header">
    <img src="default.jpg" alt="Профиль" class="profile-image" width="80" height="80">
    <div class="channel-info">
        <h1 class="channel-name"><?php echo $nameUsers; ?></h1>
        <p class="channel-subscribe"><?php echo $subscriberCount; ?> подписчик • <?php echo $videoCount; ?> видео</p><br>
        <?php
        if (isset($_SESSION['userId'])) {
            echo "<button class='{$buttonClass}' id='subscribe-btn' data-subscribed='{$dataSubscribed}' data-author-id='" . htmlspecialchars($encryptedAuthorId) . "'>{$buttonText}</button>";
        } ?>
        <ul class="channel-links"><!--
            <li><a href="#">vk.com/nikitaredko</a></li>
    -->
        </ul>
    </div>
</div>

<!--
<nav class="channel-navigation">
    <a href="#">Видео</a>
    <a href="#">Трансляции (не работает)</a>
    <a href="#">Плейлисты (не работает)</a>
</nav>
    -->
<section class="video-list"></section>
</body>

<!-- Остальное содержимое страницы -->
<script>
    document.getElementById('subscribe-btn').addEventListener('click', function() {
        const button = this;
        const isSubscribed = button.getAttribute('data-subscribed') === 'true';
        const authorId = button.getAttribute('data-author-id');

        // Определяем URL для подписки или отписки
        const url = isSubscribed ? 'functions/unsubscribe.php' : 'functions/subscribe.php';

        // Формируем данные для отправки
        const formData = new FormData();
        formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');
        formData.append('authorId', authorId);

        // Отправка запроса
        fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Обновляем атрибуты и текст кнопки
                    const newSubscribedState = !isSubscribed;
                    button.setAttribute('data-subscribed', newSubscribedState);
                    button.textContent = newSubscribedState ? 'Отписаться' : 'Подписаться';
                    button.classList.toggle('subscribed', newSubscribedState);
                    alert(newSubscribedState ? 'Вы успешно подписались!' : 'Вы успешно отписались!');
                } else {
                    //console.error('Ошибка подписки:', data.message);
                    alert(data.message); // Вывод сообщения об ошибке
                }
            })
            .catch(error => {
                //console.error('Ошибка запроса:', error);
                alert('Ошибка запроса: ' + error); // Вывод сообщения при ошибке запроса
            });
    });
</script>
<script src="scripts/loadmorevideochannel.js"></script>
<script src="scripts/menuchannel.js"></script>
</body>

</html>