<?php
//ini_set('session.gc_maxlifetime', 3600); //Задать время жизни session на 1 час

include 'functions/connectdb.php';
//$idUsers = $_SESSION['idUsers'];
$watch = $_GET['watch'];
$query = "SELECT `idVideo`,`nameVideo` FROM `video` WHERE `urlVideo` = :watch";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':watch', $watch);
$stmt->execute();
if ($stmt) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $nameVideo = $row['nameVideo'];
    $idVideo = $row['idVideo'];
}


/* $query = "SELECT video.idVideo, video.nameVideo, video.urlVideo, users.nameUsers 
          FROM video 
          JOIN users ON video.authorsID = users.idUsers 
          WHERE urlVideo = :watch LIMIT 1"; */

/* $query = "SELECT video.idVideo, video.nameVideo, video.urlVideo, users.nameUsers, themes_video.name_themes
          FROM video 
          JOIN users ON video.authorsID = users.idUsers 
          LEFT JOIN themes_video ON video.id_themes_video = themes_video.id_themes_video
          WHERE urlVideo = :watch LIMIT 1"; */
include("include/session_user.php");

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$queryAuthorId = "SELECT users.idUsers FROM video JOIN users ON video.authorsID = users.idUsers WHERE video.urlVideo = :watch LIMIT 1";
$stmt = $pdo->prepare($queryAuthorId);
$stmt->bindParam(':watch', $watch);

$stmt->execute();
$authorId = $stmt->fetch(PDO::FETCH_ASSOC);
if (isset($_SESSION['userId'])) {
    $checkSubscription = $pdo->prepare("SELECT COUNT(*) FROM subscriptions WHERE idUsers = :userId AND subscribedId = :authorId");
    $checkSubscription->execute(['userId' => $idUsers, 'authorId' => $authorId['idUsers']]);
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
$authorId = $authorId['idUsers'];

if (isset($_SESSION['userId'])) {
    $encryptedAuthorId = openssl_encrypt($authorId, $method, $key, 0, $iv);
    $encryptedAuthorId = base64_encode($encryptedAuthorId . '::' . $iv);
    $idUsers = NULL;
}
/*
$query = "SELECT video.idVideo, video.nameVideo, video.urlVideo, users.nameUsers, themes_video.name_themes
          FROM video 
          JOIN users ON video.authorsID = users.idUsers 
          LEFT JOIN themes_video ON video.id_themes_video = themes_video.id_themes_video
          INNER JOIN check_video ON video.idVideo = check_video.idVideo
          WHERE urlVideo = :watch AND check_video.result = 'Одобрено' LIMIT 1";
          */
$query = "SELECT video.idVideo, video.nameVideo, video.urlVideo, video.description, users.nameUsers, themes_video.name_themes
          FROM video 
          JOIN users ON video.authorsID = users.idUsers 
          LEFT JOIN themes_video ON video.id_themes_video = themes_video.id_themes_video
          INNER JOIN check_video ON video.idVideo = check_video.idVideo
          INNER JOIN type_video ON video.idVideo = type_video.idVideo AND type_video.nameType = 'Открытый доступ'
          WHERE video.urlVideo = :watch AND check_video.result = 'Одобрено' LIMIT 1";

$about = $pdo->prepare($query);
$about->bindParam(':watch', $watch);
$about->execute();
$ipAddress = get_ip();
/*
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}*/
//$idUsers = isset($_COOKIE['idUsers']) ? $_COOKIE['idUsers'] : null;
/*
function recordVideoView($idVideo, $idUsers, $ipAddress)
{
    global $pdo;
    $timeInterval = 1800;

    if ($idUsers) {
        $query = "SELECT * FROM video_views WHERE idVideo = :idVideo AND idUsers = :idUsers ORDER BY viewed_at DESC LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':idVideo', $idVideo);
        $stmt->bindParam(':idUsers', $idUsers);
        $stmt->execute();
        $existingView = $stmt->fetch(PDO::FETCH_ASSOC);
        $currentTime = time();
        if ($existingView) {
            $lastViewTime = strtotime($existingView['viewed_at']);

            if (($currentTime - $lastViewTime) > $timeInterval) {
                $query = "INSERT INTO video_views (idVideo, idUsers, ip_address, viewed_at) VALUES (:idVideo, :idUsers, :ipAddress, NOW())";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':idVideo', $idVideo);
                $stmt->bindParam(':idUsers', $idUsers);
                $stmt->bindParam(':ipAddress', $ipAddress);
                $stmt->execute();
            }
        } else {
            $query = "INSERT INTO video_views (idVideo, idUsers, ip_address, viewed_at) VALUES (:idVideo, :idUsers, :ipAddress, NOW())";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':idVideo', $idVideo);
            $stmt->bindParam(':idUsers', $idUsers);
            $stmt->bindParam(':ipAddress', $ipAddress);
            $stmt->execute();
        }
    } else {
        $query = "SELECT * FROM video_views WHERE idVideo = :idVideo AND idUsers IS NULL AND ip_address = :ipAddress ORDER BY viewed_at DESC LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':idVideo', $idVideo);
        $stmt->bindParam(':ipAddress', $ipAddress);
        $stmt->execute();
        $existingView = $stmt->fetch(PDO::FETCH_ASSOC);

        $currentTime = time();
        if ($existingView) {
            $lastViewTime = strtotime($existingView['viewed_at']);

            if (($currentTime - $lastViewTime) > $timeInterval) {
                $query = "UPDATE video_views SET viewed_at = NOW() WHERE idVideo = :idVideo AND idUsers IS NULL AND ip_address = :ipAddress";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':idVideo', $idVideo);
                $stmt->bindParam(':ipAddress', $ipAddress);
                $stmt->execute();
            }
        } else {
            $query = "INSERT INTO video_views (idVideo, idUsers, ip_address, viewed_at) VALUES (:idVideo, NULL, :ipAddress, NOW())";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':idVideo', $idVideo);
            $stmt->bindParam(':ipAddress', $ipAddress);
            $stmt->execute();
        }
    }
}*/
function recordVideoView($idVideo, $ipAddress)
{
    date_default_timezone_set('Asia/Krasnoyarsk');
    global $pdo;
    $timeInterval = 1200;

    if (isset($_SESSION['userId'])) {
        $idUsers = $_SESSION['userId'];
    } else {
        $idUsers = NULL;
    }
    if ($idUsers) {
        $query = "SELECT * FROM history_views WHERE idVideo = :idVideo AND idUsers = :idUsers";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':idVideo', $idVideo);
        $stmt->bindParam(':idUsers', $idUsers);
        $stmt->execute();
        $existingView = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingView) {
            // Если запись существует, обновляем время viewed_at
            $query = "UPDATE history_views SET viewed_at = NOW() WHERE idVideo = :idVideo AND idUsers = :idUsers";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':idVideo', $idVideo);
            $stmt->bindParam(':idUsers', $idUsers);
            $stmt->execute();
        } else {
            // Если запись не найдена, добавляем новую запись
            $query = "INSERT INTO history_views (idVideo, idUsers, viewed_at) VALUES (:idVideo, :idUsers, NOW())";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':idVideo', $idVideo);
            $stmt->bindParam(':idUsers', $idUsers);
            $stmt->execute();
        }
        $query = "SELECT * FROM video_views WHERE idVideo = :idVideo AND idUsers = :idUsers ORDER BY viewed_at DESC LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':idVideo', $idVideo);
        $stmt->bindParam(':idUsers', $idUsers);
        $stmt->execute();
        $existingView = $stmt->fetch(PDO::FETCH_ASSOC);
        $currentTime = time();
        if (!$existingView || ($currentTime - strtotime($existingView['viewed_at'])) > $timeInterval) {
            $query = "INSERT INTO video_views (idVideo, idUsers, ip_address, viewed_at) VALUES (:idVideo, :idUsers, :ipAddress, NOW())";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':idVideo', $idVideo);
            $stmt->bindParam(':idUsers', $idUsers);
            $stmt->bindParam(':ipAddress', $ipAddress);
            $stmt->execute();
        }
    } else {
        $query = "SELECT * FROM video_views WHERE idVideo = :idVideo AND idUsers IS NULL AND ip_address = :ipAddress ORDER BY viewed_at DESC LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':idVideo', $idVideo);
        $stmt->bindParam(':ipAddress', $ipAddress);
        $stmt->execute();
        $existingView = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$existingView) {
            $query = "INSERT INTO video_views (idVideo, idUsers, ip_address, viewed_at) VALUES (:idVideo, NULL, :ipAddress, NOW())";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':idVideo', $idVideo);
            $stmt->bindParam(':ipAddress', $ipAddress);
            $stmt->execute();
        } else {
            $currentTime = time();
            if (($currentTime - strtotime($existingView['viewed_at'])) > $timeInterval) {
                $query = "INSERT INTO video_views (idVideo, idUsers, ip_address, viewed_at) VALUES (:idVideo, NULL, :ipAddress, NOW())";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':idVideo', $idVideo);
                $stmt->bindParam(':ipAddress', $ipAddress);
                $stmt->execute();
            }
        }
    }
}
function countVideoViews($idVideo)
{
    global $pdo;

    $query = "SELECT COUNT(*) AS viewCount FROM video_views WHERE idVideo = :idVideo";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':idVideo', $idVideo);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result['viewCount'];
}

$views = countVideoViews($idVideo);
function get_ip()
{
    $value = '';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $value = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $value = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $value = $_SERVER['REMOTE_ADDR'];
    }

    return $value;
}

$path = 'video/' . $watch . '.mp4';
// Получаем длительность видео с помощью FFmpeg
/*ДОДЕЛАТЬ ОБЯЗАТЕЛЬНО
$command = "ffmpeg -i " . $path . " 2>&1 | grep Duration";
exec($command, $output);

if (!empty($output)) {
    preg_match('/Duration: (.*?),/', $output[0], $matches);
    if (isset($matches[1])) {
        $duration = $matches[1];
        // Преобразуйте формат времени, если необходимо
        // Например, $duration = yourCustomTimeFormatFunction($duration);
        
        // Теперь можно внести длительность видео в базу данных или выполнить другие операции
    }
} */

recordVideoView($idVideo, $ipAddress);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $nameVideo; ?></title>
    <link rel="icon" type="image/png" href="logo-mini.png">
    <!-- <link rel="stylesheet" href="styles.css"> -->
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        .body {
            margin-top: 300px;
        }

        .video-container {
            display: flex;
            flex-wrap: wrap;
        }

        .video-details {
            margin-left: 20px;
            width: 40%;
            -ms-hyphens: auto;
            -webkit-hyphens: auto;
            hyphens: auto;
        }

        .video-details.vertical {
            margin-left: 20px;
            width: 70%;
            -ms-hyphens: auto;
            -webkit-hyphens: auto;
            hyphens: auto;
        }

        .video-details.horizontal {
            margin-left: 20px;
            width: 40%;
            -ms-hyphens: auto;
            -webkit-hyphens: auto;
            hyphens: auto;
        }

        .overlay.video-container video {
            width: 55%;
            border-radius: 0px 0px 20px 0px;
        }

        .video-container video.horizontal {
            width: 55%;
            /* Ширина для горизонтальных видео */
            /* Остальные стили */
        }

        /* Стиль для вертикального видео */
        .video-container video.vertical {
            width: 25%;
        }

        .video-loading {
            position: relative;
            width: 100%;
            padding-top: 56.25%;
            /* 16:9 Aspect Ratio */
            background: #f3f3f3;
            /* Цвет фона анимации загрузки */
        }

        .video-loading::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 50px;
            height: 50px;
            margin: -25px 0 0 -25px;
            /* Центрирование */
            border: 5px solid #e63022;
            /* Цвет анимации загрузки */
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: translate(-50%, -50%) rotate(0deg);
            }

            100% {
                transform: translate(-50%, -50%) rotate(360deg);
            }
        }

        a {
            text-decoration: none;
            color: inherit;
            outline: none;
        }

        a:hover {
            text-decoration: none;
            color: inherit;
        }

        .video-container video {
            /* Изначально скрываем видео */
            display: none;
        }

        .video-container video.loaded {
            /* Когда видео загружено, показываем его */
            display: block;
        }


        .video-details h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .author,
        .views {
            font-weight: bold;
        }

        .subscribe-btn {
            padding: 10px;
            background-color: #ff0000;
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 10px;
            transition: transform 0.3s ease;
        }

        .subscribe-btn:hover {
            outline: 1px solid rgba(156, 156, 156, 0.15);
        }

        .description {
            max-width: 400px;
            background-color: #d4d4d4;
            margin-top: 10px;
            border-radius: 10px;
            padding: 10px;
        }

        .comments-section {
            margin-top: 20px;
            margin-left: 20px;
        }

        .comment {
            display: flex;
            margin-bottom: 20px;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            margin-right: 10px;
        }

        .video-message {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 20px;
            color: #fff;
            background-color: rgba(0, 0, 0, 0.7);
            padding: 10px 20px;
            border-radius: 5px;
            text-align: center;
        }

        .overlay #overlayImage {
            /* position: absolute;
            top: 40%;
            left: 40%; 
            transform: translate(-50%, -50%);
            width: 400px;
            height: 400px; */
            z-index: 1;
            position: absolute;
            width: 300px;
            height: 300px;
        }

        .overlay {
            transition: opacity 0.5s;
        }

        .overlay.hidden {
            opacity: 0;
            pointer-events: none;
        }

        .subscribe-btn.subscribed {
            background-color: grey;
            /* Серый фон для подписанных пользователей */
            /* cursor: not-allowed; */
            /* Меняем курсор, указывая на невозможность действия */
        }


        /* @media screen and (max-width: 777px) { */
        @media screen and (max-width: 1079px) {
            body {
                margin: 0;
                margin-top: 90px;
            }

            .search {
                width: 60%;
            }

            .search input {
                width: 100%;
            }

            .video-container {
                width: 100%;
                overflow: hidden;
            }

            .video-container video {
                width: 100%;
                border-radius: 0;
                display: block;
            }

            .video-container video.horizontal {
                width: 100%;
            }

            .video-container video.vertical {
                /* width: 100%; */
                width: 300px;
            }

            .video-details.vertical {
                margin-left: 20px;
                width: 60%;
                -ms-hyphens: auto;
                -webkit-hyphens: auto;
                hyphens: auto;
            }

            .video-details.horizontal {
                margin-left: 20px;
                width: 100%;
                -ms-hyphens: auto;
                -webkit-hyphens: auto;
                hyphens: auto;
            }

            .video-details {
                margin-left: 20px;
                max-width: calc(100% + 100px);
                width: 100%;
            }

            .video-details h1 {
                font-size: 24px;
                margin-bottom: 5px;
                line-height: 0.9;
            }

            .description {
                width: 90%;
            }

            .logo img {
                display: none;
            }

            .search input {
                width: 60px;
            }

            .upload button {
                display: none;
            }

            @media screen and (max-width: 300px) {
                .search {
                    left: -1px;
                }
            }
        }

        @media screen and (max-width: 800px) {
            .video-details.vertical {
                margin-left: 20px;
                width: 36%;
                -ms-hyphens: auto;
                -webkit-hyphens: auto;
                hyphens: auto;
            }
        }

        @media screen and (max-width: 500px) {
            .video-container video.vertical {
                /* width: 100%; */
                width: 100%;
            }

            .video-details.vertical {
                margin-left: 20px;
                width: 100%;
                -ms-hyphens: auto;
                -webkit-hyphens: auto;
                hyphens: auto;
            }
        }

        .comment-form {
            display: flex;
            align-items: flex-start;
            margin-top: 20px;
            padding: 10px;
        }

        .comment-avatar img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .comment-box {
            flex-grow: 1;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 5px;
        }

        textarea {
            width: 100%;
            border: none;
            resize: none;
            /* Запрет на изменение размера */
            height: 50px;
            padding: 8px;
            box-sizing: border-box;
            /* Учитывать padding в ширине и высоте */
            border-radius: 5px;
        }

        .comment-actions {
            display: flex;
            justify-content: flex-end;
            padding-top: 5px;
        }

        .cancel-btn,
        .submit-btn {
            padding: 6px 12px;
            margin-left: 5px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .submit-btn {
            background-color: #065fd4;
            color: white;
        }

        .submit-btn:hover {
            background-color: #044cab;
        }
    </style>

</head>

<body>

    <?php include("include/header.php"); ?>

    <main>
        <?php include_once "include/menu.php"; ?>
        <?php if (file_exists($path)) {
            if ($about) {
                while ($row = $about->fetch(PDO::FETCH_ASSOC)) { ?>
                    <div class="video-container" style="overflow-y:visible;">
                        <!-- Video Player -->
                        <!-- <iframe width="560" height="315" src="https://www.youtube.com/watch?v=GNP5ZJ2RHv4&ab_channel=Dota2Stream" frameborder="0" allowfullscreen></iframe> -->
                        <video id="myVideo" controlslist="nodownload" controls width="100%" autoplay preload>
                            <source src="<?php echo $path; ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                        <!-- <p id="overlayText" style="display: none;">Текст наложенный на видео</p> -->
                        <!-- Video Details -->
                        <div class="video-details">
                            <?php
                            echo "<h1>{$row['nameVideo']}</h1>
                            <p>Загрузил: <a href='channel?user={$row['nameUsers']}'><span class='author'>{$row['nameUsers']}</span></a></p>
                            <p>Просмотры: <span class='views'>$views</span></p>";
                            if (isset($_SESSION['userId'])) {
                                echo "<button class='{$buttonClass}' id='subscribe-btn' data-subscribed='{$dataSubscribed}' data-author-id='" . htmlspecialchars($encryptedAuthorId) . "'>{$buttonText}</button>"; ?>
                                <button class='like-btn' onclick="rateVideo('like', <?= $row['idVideo'] ?>)"><i class="fas fa-thumbs-up"></i></button>
                                <button class='dislike-btn' onclick="rateVideo('dislike', <?= $row['idVideo'] ?>)"><i class="fas fa-thumbs-down"></i></button>
                            <?php } ?>
                            <?php echo "<p class='description'>Тема видео: {$row['name_themes']}
                            <br><br>{$row['description']}</p>
                            <div class='overlay'>
                            <div id='overlayText' style='display: none;'>Поле для текста маркера</div>
                            <img id='overlayImage' src='logo.png' style='display: none;'>
                        </div></div>"; ?>
                        </div>
                        <!-- Comments Section -->
                        <div class="comments-section">
                            <h2>Комментарии</h2>
                            <form id="commentForm" class="comment-form">
                                <div class="comment-avatar">
                                    <!-- Здесь может быть аватар пользователя, если есть система аутентификации -->
                                    <img src="default.jpg" alt="avatar" />
                                </div>
                                <div class="comment-box">
                                    <textarea id="commentText" placeholder="Добавьте комментарий..."></textarea>
                                    <div class="comment-actions">
                                        <button type="submit" class="submit-btn">Комментировать</button>
                                    </div>
                                </div>
                            </form>
                            <div class="your_comment"></div>
                            <!--
                            <div class="comment">
                                <div class="user-avatar"><img src="default.jpg"></div>
                                <div class="comment-details">
                                    <p class="comment-author">crazykivi</p>
                                    <p class="comment-text">Тест комментариев</p>
                                </div>
                            </div>
                            <div class="comment">
                                <div class="user-avatar"><img src="default.jpg"></div>
                                <div class="comment-details">
                                    <p class="comment-author">crazykivi</p>
                                    <p class="comment-text">Тест комментариев</p>
                                </div>
                            </div>
                            <div class="comment">
                                <div class="user-avatar"><img src="default.jpg"></div>
                                <div class="comment-details">
                                    <p class="comment-author">crazykivi</p>
                                    <p class="comment-text">Тест комментариев</p>
                                </div>
                            </div>-->
                            <!-- Комментарии закончились -->
                        </div>
            <?php                 }
            } else {
                echo 'Видео не обнаружено';
            }
        } ?>
    </main>
    <script>
        /*document.addEventListener("DOMContentLoaded", function() {
            var videos = document.querySelectorAll('.video-container video');

            videos.forEach(function(video) {
                video.addEventListener('loadedmetadata', function() {
                    // Получаем ближайший элемент с классом video-details
                    var videodetail = video.closest('.video-container').querySelector('.video-details');

                    if (video.videoHeight > video.videoWidth) {
                        // Видео вертикальное
                        video.classList.add('vertical');
                        video.classList.remove('horizontal');
                        videodetail.classList.add('vertical');
                        videodetail.classList.remove('horizontal');
                    } else {
                        // Видео горизонтальное
                        video.classList.add('horizontal');
                        video.classList.remove('vertical');
                        videodetail.classList.add('horizontal');
                        videodetail.classList.remove('vertical');
                    }
                });
            });
        });*/
    </script>
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
        /*
         document.getElementById('subscribe-btn').addEventListener('click', function() {
             const button = this;
             const isSubscribed = button.getAttribute('data-subscribed') === 'true';
             const authorId = button.getAttribute('data-author-id');
             const authorName = button.getAttribute('data-author-name'); // имя автора для добавления в список

             const url = isSubscribed ? 'functions/unsubscribe.php' : 'functions/subscribe.php';

             const formData = new FormData();
             formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');
             formData.append('authorId', authorId);

             fetch(url, {
                     method: 'POST',
                     body: formData
                 })
                 .then(response => response.json())
                 .then(data => {
                     if (data.success) {
                         const newSubscribedState = !isSubscribed;
                         button.setAttribute('data-subscribed', newSubscribedState);
                         button.textContent = newSubscribedState ? 'Отписаться' : 'Подписаться';
                         button.classList.toggle('subscribed', newSubscribedState);

                         if (newSubscribedState) {
                             // Добавляем новый канал в список подписок
                             const ul = document.getElementById('subscriptions-list');
                             const li = document.createElement('li');
                             const a = document.createElement('a');
                             a.href = 'channel.php?user=' + encodeURIComponent(authorId); // используем зашифрованный ID
                             a.textContent = authorName;
                             li.appendChild(a);
                             ul.appendChild(li);
                         } else {
                             // Находим и удаляем элемент из списка подписок
                             const links = document.querySelectorAll('#subscriptions-list a');
                             links.forEach(link => {
                                 if (link.textContent === authorName) {
                                     link.parentNode.remove();
                                 }
                             });
                         }

                         alert(newSubscribedState ? 'Вы успешно подписались!' : 'Вы успешно отписались!');
                     } else {
                         alert(data.message);
                     }
                 })
                 .catch(error => {
                     alert('Ошибка: ' + error.message);
                 });
         });
         */
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var videos = document.querySelectorAll('.video-container video');

            videos.forEach(function(video) {
                // Сначала добавляем класс для анимации загрузки
                video.closest('.video-container').classList.add('video-loading');

                video.addEventListener('loadedmetadata', function() {
                    var videodetail = video.closest('.video-container').querySelector('.video-details');

                    // Определение ориентации видео и добавление соответствующих классов
                    if (video.videoHeight > video.videoWidth) {
                        video.classList.add('vertical');
                        videodetail.classList.add('vertical');
                    } else {
                        //if (video.videoWidth < 1080) {}
                        video.classList.add('horizontal');
                        videodetail.classList.add('horizontal');
                    }

                    // Удаление класса загрузки и отображение видео
                    video.closest('.video-container').classList.remove('video-loading');
                    video.classList.add('loaded');
                });
            });
        });
    </script>
    <script>
        function updateInputSize() { // Динамическое разрешение поля поиска
            const searchInput = document.querySelector('.search-input');

            //if (window.innerWidth < 768 && window.innerWidth > 399) {
            if (window.innerWidth < 777 && window.innerWidth > 475) {
                const viewportWidth = window.innerWidth;
                const containerWidth = viewportWidth * 0.8;

                searchInput.style.width = containerWidth * (6 / 10) + 'px';
            } else if (window.innerWidth < 476 && window.innerWidth > 300) {
                const viewportWidth = window.innerWidth;
                const containerWidth = viewportWidth * 0.7;

                searchInput.style.width = containerWidth * (6 / 10) + 'px';
            } else if (window.innerWidth < 300) {
                const viewportWidth = window.innerWidth;
                const containerWidth = viewportWidth * 0.6;

                searchInput.style.width = containerWidth * (6 / 10) + 'px';
            } else {
                // searchInput.style.width = '300px';
                const viewportWidth = window.innerWidth;
                const containerWidth = viewportWidth * 0.9;

                searchInput.style.width = containerWidth * (9 / 10) + 'px';
            }
        }

        updateInputSize(); // Вызываем функцию при загрузке страницы
        window.addEventListener('resize', updateInputSize); // Вызываем функцию при изменении размеров окна
    </script>
    <script>
        /*
        const video = document.getElementById('myVideo');
        const overlayImage = document.getElementById('overlayImage');
        const overlayText = document.getElementById('overlayText');

        video.addEventListener('pause', function() {
            if (video.currentTime >= 0 && video.currentTime <= 30) {
                overlayText.style.display = 'block';
                overlayImage.style.display = 'block';
            } else {
                overlayText.style.display = 'none';
                overlayImage.style.display = 'none';
            }
        });*/
    </script>
    <script>
        const videoElement = document.getElementById('myVideo');
        const overlayImage = document.getElementById('overlayImage');
        const overlayText = document.getElementById('overlayText');
        const overlayDiv = document.querySelector('.overlay');

        let idVideo = <?php echo json_encode($idVideo); ?>;
        const queryParams = new URLSearchParams(window.location.search);
        const watchParam = queryParams.get('watch');

        let markerFound = false;
        videoElement.addEventListener('timeupdate', function() {
            const currentTime = formatTime(videoElement.currentTime);

            fetch(`functions/getMarker.php?idVideo=${idVideo}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(marker => {
                        if (currentTime >= marker.time && currentTime <= addSeconds(marker.time, 15)) {
                            const imagePath = `video/${watchParam}/`;
                            overlayImage.src = `${imagePath}${marker.image}`;
                            overlayText.textContent = marker.name;
                            overlayText.style.display = 'block';
                            overlayImage.style.display = 'block';
                            overlayDiv.style.display = 'block';
                            markerFound = true;
                        }
                    });

                    if (!markerFound) {
                        overlayText.style.display = 'none';
                        overlayImage.style.display = 'none';
                        overlayDiv.style.display = 'none';
                        overlayDiv.classList.add('hidden');
                    } else {
                        overlayDiv.classList.remove('hidden');
                    }

                    markerFound = false;
                })
                .catch(error => console.error('Error:', error));
        });

        function formatTime(time) {
            const hours = Math.floor(time / 3600);
            const minutes = Math.floor((time % 3600) / 60);
            const seconds = Math.floor(time % 60);

            return `${padZero(hours)}:${padZero(minutes)}:${padZero(seconds)}`;
        }

        function padZero(num) {
            return num.toString().padStart(2, '0');
        }

        function addSeconds(time, seconds) {
            const [hours, minutes, currentSeconds] = time.split(':');
            let totalSeconds = parseInt(hours) * 3600 + parseInt(minutes) * 60 + parseInt(currentSeconds);
            totalSeconds += seconds;
            const newHours = Math.floor(totalSeconds / 3600);
            const newMinutes = Math.floor((totalSeconds % 3600) / 60);
            const newSeconds = Math.floor(totalSeconds % 60);

            return `${padZero(newHours)}:${padZero(newMinutes)}:${padZero(newSeconds)}`;
        }
    </script>
    <script>
        var video = document.getElementById("myVideo");

        function setSavedVolume() {
            if (localStorage.getItem("savedVolume")) {
                video.volume = parseFloat(localStorage.getItem("savedVolume"));
            }
            video.addEventListener("volumechange", function() {
                localStorage.setItem("savedVolume", video.volume);
            });
        }

        setSavedVolume();

        function playVideo() {
            video.play();
        }
        /*
        document.addEventListener("click", function() {
            playVideo();
        }); */
    </script>
    <script>
        /*
        function rateVideo(type, idVideo) {
            fetch('functions/rate_video.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `type=${type}&idVideo=${idVideo}`
                })
                .then(response => response.json())
                .then(data => {
                    console.log(data.message); // Обработка ответа от сервера
                })
                .catch(error => console.error('Error:', error));
        }*/
        function rateVideo(type, idVideo) {
            fetch('/rate_video.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `type=${type}&idVideo=${idVideo}`
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message); // Вывод уведомления пользователю
                    if (data.status === 'success') {
                        // Можно добавить логику для изменения интерфейса, например, обновление счетчика лайков/дизлайков
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    </script>
    <script>
        /*
        document.addEventListener('DOMContentLoaded', function() {
            var videoId = '<?= $idVideo; ?>'; // ID видео

            function updateViews() {
                fetch('update_video_views.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'videoId=' + videoId + '&csrf_token=' + '<?= $_SESSION['csrf_token']; ?>'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.viewCount) {
                            document.querySelector('.views').textContent = data.viewCount + ' просмотров';
                        }
                    })
                    .catch(error => console.error('Ошибка:', error));
            }

            updateViews(); // Обновление просмотров при загрузке страницы или при соответствующем событии
        });
        */
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        /*
        var videoId = <?php echo $idVideo; ?>;
        var userRole = "<?php echo $role; ?>";
        var loading = false;
        var lastCommentId = null;

        function loadComments() {
            if (!loading) {
                loading = true;
                $.ajax({
                    url: 'functions/get_comments.php',
                    type: 'POST',
                    data: {
                        idVideo: videoId,
                        lastCommentId: lastCommentId
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (data.length > 0) {
                            for (var i = 0; i < data.length; i++) {
                                var deleteButton = '';
                                if ((userRole === 'Модератор' && data[i].role === 'Пользователь') || userRole === 'Администратор') {
                                    deleteButton = '<button class="delete-comment" data-id="' + data[i].idComments + '">Удалить</button>';
                                }
                                var commentHtml = '<div class="comment">' +
                                    '<div class="user-avatar"><img src="default.jpg"></div>' +
                                    '<div class="comment-details">' +
                                    '<p class="comment-author"><a href="/redocean/channel?user=' + data[i].username + '">' + data[i].username + '</a></p>' +
                                    '<p class="comment-text">' + data[i].comment + '</p>' +
                                    deleteButton +
                                    '</div>' +
                                    '</div>';
                                $('.comments-section').append(commentHtml);
                            }
                            lastCommentId = data[data.length - 1].idComments;
                        } else {
                            $('.comments-section').append('<p class="no-more-comments">Комментариев больше нету</p>');
                            $(window).off('scroll');
                        }
                        loading = false;
                    }
                });
            }
        }

        $(document).ready(function() {
            loadComments();

            $(window).scroll(function() {
                if ($(window).scrollTop() + $(window).height() >= $(document).height() - 100) {
                    loadComments();
                }
            });

            $(document).on('click', '.delete-comment', function() {
                var commentId = $(this).data('id');
                $.ajax({
                    url: 'functions/delete_comment.php',
                    type: 'POST',
                    data: {
                        idComment: commentId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('button[data-id="' + commentId + '"]').closest('.comment').remove();
                        } else {
                            alert('Ошибка: ' + response.error);
                        }
                    },
                    error: function() {
                        alert('Произошла ошибка при удалении комментария.');
                    }
                });
            });
        });
        */
        var videoId = <?php echo $idVideo; ?>;
        var userRole = "<?php echo $role; ?>";
        var loading = false;
        var lastCommentId = null;

        function loadComments() {
            if (!loading) {
                loading = true;
                $.ajax({
                    url: 'functions/get_comments.php',
                    type: 'POST',
                    data: {
                        idVideo: videoId,
                        lastCommentId: lastCommentId
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (data.length > 0) {
                            for (var i = 0; i < data.length; i++) {
                                var deleteButton = '';
                                if ((userRole === 'Модератор' && data[i].role === 'Пользователь') || userRole === 'Администратор') {
                                    deleteButton = '<button class="delete-comment" data-id="' + data[i].idComments + '">Удалить</button>';
                                }
                                var commentHtml = '<div class="comment">' +
                                    '<div class="user-avatar"><img src="default.jpg"></div>' +
                                    '<div class="comment-details">' +
                                    '<p class="comment-author"><a href="/redocean/channel?user=' + data[i].username + '">' + data[i].username + '</a></p>' +
                                    '<p class="comment-text">' + data[i].comment + '</p>' +
                                    deleteButton +
                                    '</div>' +
                                    '</div>';
                                $('.comments-section').append(commentHtml);
                            }
                            lastCommentId = data[data.length - 1].idComments;
                        } else {
                            $('.comments-section').append('<p class="no-more-comments">Комментариев больше нету</p>');
                            $(window).off('scroll');
                        }
                        loading = false;
                    }
                });
            }
        }

        $(document).ready(function() {
            loadComments();

            $(window).scroll(function() {
                if ($(window).scrollTop() + $(window).height() >= $(document).height() - 100) {
                    loadComments();
                }
            });

            $(document).on('click', '.delete-comment', function() {
                var commentId = $(this).data('id');
                $('button[data-id="' + commentId + '"]').closest('.comment').remove();
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#commentForm').submit(function(event) {
                event.preventDefault(); // предотвратить стандартное поведение формы
                var comment = $('#commentText').val();
                $.ajax({
                    url: 'functions/post_comments.php',
                    type: 'POST',
                    data: {
                        comment: comment,
                        idVideo: videoId
                    },
                    success: function(data) {
                        $('#commentText').val(''); // очистить поле ввода
                        $('.your_comment').prepend('<div class="comment"><div class="user-avatar"><img src="default.jpg"></div>' +
                            '<div class="comment-details">' +
                            '<p class="comment-author">You</p>' +
                            '<p class="comment-text">' + comment + '</p>' +
                            '</div>' +
                            '</div>');

                    },
                    error: function() {
                        alert('Ошибка при добавлении комментария.');
                    }
                });
            });
        });
    </script>
    <script src="scripts/menu.js"></script>
</body>

</html>