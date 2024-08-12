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

$query = "SELECT * FROM `themes_video`";
$themes = $pdo->query($query);

$currentPage = isset($_GET['page']) ? intval($_GET['page']) : 0;
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
if ($currentPage > 0) {
    $startFrom = ($currentPage - 1) * 20; // Вычисляем начальную позицию для выборки видео
} else {
    $startFrom = 0; // Если страница равна 1, начинаем с первого видео
}
if (!empty($searchQuery)) {
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
              (video.nameVideo LIKE '%$searchQuery%' OR users.nameUsers LIKE '%$searchQuery%')
              AND check_video.result = 'Одобрено'
              AND type_video.nameType = 'Открытый доступ'
            GROUP BY 
              video.idVideo 
            LIMIT 
              $startFrom, 20";
} else {
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
              check_video.result = 'Одобрено'
              AND type_video.nameType = 'Открытый доступ'
            GROUP BY 
              video.idVideo 
            LIMIT 
              $startFrom, 20";
}


$stmt = $pdo->query($query);

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
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="logo-mini.png">
    <title>Главная страница</title>
    <style>
        body,
        html {
            height: 100%;
            margin: 0;
            font-family: Arial, sans-serif;
            /* background: #f1f1f1; */
            padding: 20px;
        }

        .menu {
            display: block;
        }


        .video-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: flex-start;
            margin-top: 30px;
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

        #overlaybackground {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.2);
            /* Изначально прозрачный */
            z-index: 11;
            opacity: 0;
            /* Начальная прозрачность */
            transition: opacity 0.5s ease;
            /* Плавное изменение прозрачности */
            display: none;
            /* Изначально оверлей не отображается */
        }

        /* Класс для светлого затемнения */
        .light-overlay {
            background: rgba(0, 0, 0, 0.2);
        }

        /* Класс для темного затемнения */
        .dark-overlay {
            background: rgba(0, 0, 0, 0.5);
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

<body>
    <div id="overlaybackground" style="display: none;"></div>
    <?php include("include/header.php"); ?>
    <?php include_once "include/menu.php"; ?>
    <section class="video-list">
        <?php
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $formattedViews = formatViews($row['viewCount']);
                // Вывод контейнера видео для каждого извлеченного видео с учетом названия канала
                /* echo "<div class='video'>
                <a href='player.php?watch={$row['urlVideo']}'>
                    <img src='video/{$row['urlVideo']}.PNG' alt='Превью видео'>
                    <div class='video-info'>
                        <div class='video-title'>{$row['nameVideo']}</div>
                        <div class='video-meta'>{$row['nameUsers']}</div>
                        <div class='video-meta'>{$formattedViews}</div>
                    </div>
                </a>
            </div>";*/
                echo "<div class='video'>
                <a href='player?watch={$row['urlVideo']}'>
                <div class='video-thumbnail'>
                <div class='loader'></div> <!-- Если убирать загрузку, то не забыть убрать это -->
                <img src='video/{$row['urlVideo']}.PNG' alt='Превью видео'"; ?> onerror="this.onerror=null; this.src='video/stock-video.png'"> <!-- ПОПРОБОВАТЬ ИСПРАВИТЬ -->
        <?php
                echo "</div>
                <div class='video-info'>
                <div class='video-title'>{$row['nameVideo']}</div>
                <div class='video-meta'>{$row['nameUsers']}</div>
                <div class='video-meta'>{$formattedViews}</div>
                </div>
                </a>
                </div>";
            }
        }
        ?>
        <!--
        <div class="video">
            <img src="video/b35.png" alt="Превью видео">
            <div class="video-info">
                <div class="video-title">Название видео</div>
                <div class="video-meta">Автор видео</div>
                <div class="video-meta">10 просмотров</div>
            </div>
        </div>-->
    </section>
</body>
<script>
    /*
    function adjustVideoHeight() {
        var videos = document.querySelectorAll('.video img');
        videos.forEach(function(video) {
            var width = video.offsetWidth; // Получаем текущую ширину элемента
            var height = width * (9 / 16); // Вычисляем высоту для соотношения сторон 16:9
            video.style.height = height + 'px'; // Устанавливаем высоту
        });
    }

    // Вызов функции при загрузке страницы
    window.addEventListener('load', adjustVideoHeight);

    // Вызов функции при изменении размера окна, если требуется адаптивность
    window.addEventListener('resize', adjustVideoHeight);
    */
    function adjustVideoHeight() {
        var videos = document.querySelectorAll('.video img');
        videos.forEach(function(video) {
            var width = video.offsetWidth; // Получаем текущую ширину элемента
            var height = width * (9 / 16); // Вычисляем высоту для соотношения сторон 16:9
            video.style.height = height + 'px'; // Устанавливаем высоту

            // Добавляем проверку на существование элемента
            var loader = video.parentNode.querySelector('.loader');
            if (loader) {
                loader.style.display = 'none'; // Скрываем анимацию загрузки
            }
        });
    }

    // Вызов функции при загрузке страницы и при изменении размера окна
    window.addEventListener('load', function() {
        adjustVideoHeight();
        document.querySelectorAll('.loader').forEach(loader => {
            if (loader) loader.style.display = 'none'; // Скрываем все загрузчики после загрузки страницы
        });
    });
    window.addEventListener('resize', adjustVideoHeight);
</script>
<script src="scripts/loadmorevideo.js"></script>
<script src="scripts/menu.js"></script>

</html>