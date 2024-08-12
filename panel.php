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
include("include/check_auth.php");
$authorID = $_SESSION['userId'];
$query = "SELECT * FROM `themes_video`";
$themes = $pdo->query($query);

$currentPage = isset($_GET['page']) ? intval($_GET['page']) : 0;
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
$videosPerPage = 10;
if ($currentPage > 0) {
    $startFrom = ($currentPage - 1) * 10; // Вычисляем начальную позицию для выборки видео
} else {
    $startFrom = 0; // Если страница равна 1, начинаем с первого видео
}
$queryTotal = "
SELECT 
    COUNT(DISTINCT video.idVideo) AS videoCount, 
    users.nameUsers AS channelName 
FROM 
    video 
JOIN 
    users ON video.authorsID = users.idUsers 
WHERE 
    video.authorsID = :authorID";
$stmtTotal = $pdo->prepare($queryTotal);
$stmtTotal->bindParam(':authorID', $authorID, PDO::PARAM_INT);
$stmtTotal->execute();

$row = $stmtTotal->fetch(PDO::FETCH_ASSOC);

$channelName = $row['channelName'];
$totalVideos = $row ? $row['videoCount'] : 0;
$totalPages = ceil($totalVideos / $videosPerPage);
/* $query = "SELECT 
          video.idVideo, 
          video.nameVideo, 
          video.urlVideo, 
          video.uploadDate,
          users.nameUsers, 
          COUNT(DISTINCT video_views.idVideoViews) AS viewCount, 
          themes_video.name_themes,
          check_video.result
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
          (video.nameVideo LIKE '%$searchQuery%' OR users.nameUsers LIKE '%$searchQuery%')
          AND video.authorsID = $authorID
        GROUP BY 
          video.idVideo 
        LIMIT 
          $startFrom, 20"; */
/*
$query = "SELECT 
          video.idVideo, 
          video.nameVideo, 
          video.urlVideo, 
          video.uploadDate,
          users.nameUsers, 
          COUNT(DISTINCT video_views.idVideoViews) AS viewCount, 
          themes_video.name_themes,
          check_video.result,
          COUNT(DISTINCT comments.idComments) AS commentCount
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
        WHERE 
          (video.nameVideo LIKE '%$searchQuery%' OR users.nameUsers LIKE '%$searchQuery%')
          AND video.authorsID = $authorID
        GROUP BY 
          video.idVideo 
        LIMIT 
          $startFrom, 20";
          */
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
        LEFT JOIN 
          type_video ON video.idVideo = type_video.idVideo
        WHERE video.authorsID = $authorID
        GROUP BY 
          video.idVideo 
        ORDER BY 
          video.uploadDate DESC
        LIMIT 
          $startFrom, 10";
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
    <title>Панель мультимедии</title>
    <style>
        body,
        html {
            height: 70%;
            margin: 0;
            font-family: Arial, sans-serif;
            /* background: #f1f1f1; */
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .tabs button {
            /* Стили для кнопок табов */
        }

        #create-button {
            /* Стили для кнопки "Создать" */
        }

        .video-list {
            /* Расчет ширины с учетом боковой панели */
            /* width: 100%; Для закрытого меню*/
            width: 86.8%;
            padding-left: 250px;
            /* Убрать, если не нужно, чтобы меню было изначально открыто */
            transition: padding-left 0.4s ease, width 0.4s ease;
            z-index: 2;
            overflow: visible;
        }

        .video-list img {
            width: 130px;
            aspect-ratio: 16/9;
        }

        .video-list table {
            right: 0;
            width: 100%;
            border-collapse: collapse;
            transition: transform 0.4s ease;
        }

        thead {
            position: sticky;
            top: 80px;
            background: white;
            z-index: 2;
            box-shadow: 0 1px 1px -1px rgba(0, 0, 0, 0.4);
        }

        .video-list th,
        .video-list td {
            border: 1px solid #ddd;
            border-top: 0px;
            border-left: 0px;
            padding: 8px;
            text-align: left;
        }


        .video-list th {
            /* background-color: #f2f2f2; */
        }

        .video-list a {
            text-decoration: none;
            color: inherit;
            outline: none;
        }

        .video-list a:hover {
            text-decoration: none;
            color: inherit;
        }

        main {
            margin-top: 150px;
            /* Если есть необходимость в отступе сверху */
            /* padding-left: 250px; */
            /* Отступ слева, равный ширине боковой панели */
            transition: transform 0.4s ease;
        }

        .sidebar {
            margin-top: 70px;
            width: 250px;
            /* Ширина боковой панели */
            height: 100%;
            /* Высота на всю доступную высоту */
            position: fixed;
            /* Фиксированное позиционирование */
            left: 0;
            /* Прижато к левой стороне */
            top: 0;
            /* Прижато к верхней стороне */
            background-color: #fff;
            /* Белый фон */
            /* Тень справа от меню */
            z-index: 3;
            /* Выше других элементов */
            transition: transform 0.4s ease;
            transform: translateX(0%);
        }

        .sidebar-header {
            border: 1px solid #ddd;
            padding: 20px;
            /* Отступ внутри заголовка */
            /* background-color: #f9f9f9; */
            /* Светлый фон заголовка */
            /* border-bottom: 1px solid #ddd; */
            /* Граница снизу */
            transition: transform 0.5s ease;
        }

        .user-info p,
        .user-info h2 {
            margin: 0;
            /* Убираем отступы */
        }

        .sidebar-nav .nav-list {
            /* border-bottom: 1px solid #ddd; */
            border: 1px solid #ddd;
            list-style: none;
            /* Убираем маркеры списка */
            padding: 0;
            /* Убираем отступы */
            margin: 0;

            /* Убираем отступы */
        }

        .sidebar-nav .nav-list li a {
            display: block;
            /* Ссылка на всю ширину элемента списка */
            padding: 10px 20px;
            /* Отступы внутри ссылки */
            text-decoration: none;
            /* Убираем подчеркивание текста */
            color: #333;
            /* Цвет текста */
            transition: background-color 0.3s;
            /* Анимация фона при наведении */
            transition: transform 0.5s ease;
        }

        .sidebar-nav .nav-list li a:hover {
            background-color: #f4f4f4;
            /* Фон при наведении */
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

        @keyframes spin {
            0% {
                transform: translate(-50%, -50%) rotate(0deg);
            }

            100% {
                transform: translate(-50%, -50%) rotate(360deg);
            }
        }

        @media screen and (max-width: 1023px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .video-list {
                padding-left: 0px;
                width: 100%;
            }
        }
    </style>
    <link rel="stylesheet" href="css/header.css">
</head>

<body>
    <?php include("include/header.php"); ?>
    <?php //include("include/menu.php"); 
    include("include/menu-panel.php");
    ?>


    <main>
        <aside class="sidebar" id="menu">
            <div class="sidebar-header">
                <div class="user-info">
                    <p>Ваш канал</p>
                    <h2><?php echo "$channelName"; ?></h2>
                </div>
            </div>
            <nav class="sidebar-nav">
                <ul class="nav-list">
                    <li><a href="index">Главная</a></li>
                    <li><a href="panel">Контент</a></li>
                    <li><a href="global_analytics">Аналитика</a></li>
                    <li><a href="comments-list">Комментарии</a></li>
                    <li><a href="channel-settings">Настройка канала</a></li>
                    <li><a href="settings">Настройки</a></li>
                </ul>
            </nav>
        </aside>
        <section class="video-list" id="video-list">
            <table id="table">
                <?php if ($stmt->rowCount() > 0) {
                    echo '
            <thead>
                <tr>
                    <th>Превью видео</th>
                    <th>Название видео</th>
                    <th>Параметры доступа</th>
                    <th>Дата</th>
                    <th>Просмотры</th>
                    <th>Комментарии</th>
                    <th>Результат одобрения видео</th>
                </tr>
            </thead>
            <tbody>';
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>
                            <td><a href='changepanel?urlVideo={$row['urlVideo']}'><div class='loader'></div><img src='video/{$row['urlVideo']}.PNG' alt='Превью видео'></a></td>
                            <td><a href='changepanel?urlVideo={$row['urlVideo']}'>{$row['nameVideo']}</a></td>
                            <td><span onclick=\"toggleAccess('{$row['urlVideo']}', this)\" style='cursor: pointer;'>{$row['accessType']}</span></td>
                            <td class='date-cell'>{$row['uploadDate']}</td>
                            <td>{$row['viewCount']}</td>
                            <td>{$row['commentCount']}</td>
                            <td>{$row['result']}</td>
                        </tr>";
                    }
                } else {
                    echo "<tr>
                <td>Ни одно видео ещё не загружено</td>
            </tr>";
                } ?>
                </tbody>
            </table>
        </section>
        <?php
        if ($totalPages > 1) {
            // Вывод кнопки "Первая страница", если не первая страница
            if ($currentPage > 1) {
                echo '<a href="?page=1&search=' . urlencode($searchQuery) . '">Первая страница</a> ';
            }

            // Вывод кнопки "Предыдущая страница", если это не первая страница
            if ($currentPage > 1) {
                echo '<a href="?page=' . ($currentPage - 1) . '&search=' . urlencode($searchQuery) . '">Предыдущая страница</a> ';
            }

            // Вывод кнопок для трех следующих страниц
            for ($i = $currentPage; $i < $currentPage + 3 && $i <= $totalPages; $i++) {
                if ($i != $currentPage) {
                    echo '<a href="?page=' . $i . '&search=' . urlencode($searchQuery) . '">' . $i . '</a> ';
                } else {
                    echo $i . ' '; // текущая страница без ссылки
                }
            }

            // Вывод кнопки "Последняя страница", если это не последняя страница
            if ($currentPage < $totalPages) {
                echo '<a href="?page=' . $totalPages . '&search=' . urlencode($searchQuery) . '">Последняя страница</a> ';
            }
        }
        ?>
    </main>

</body>
<script>
    function toggleAccess(urlVideo, element) {
        fetch('functions/toggleAccess.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    urlVideo: urlVideo
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    element.textContent = data.newAccessType;
                } else {
                    alert('Ошибка при изменении доступа: ' + data.error);
                }
            })
            .catch(error => {
                console.error('There was a problem with the fetch operation:', error);
                alert('Ошибка: ' + error.message);
            });
    }
</script>
<script>
    function toggleMenu() {
        var menu = document.getElementById('menu');
        var videolist = document.getElementById('video-list');
        var table = document.getElementById('table');

        // Переключаем класс для меню и .video-list
        if (menu.style.transform === 'translateX(0%)') {
            menu.style.transform = 'translateX(-100%)';
            // Удаляем padding-left у .video-list
            videolist.style.paddingLeft = '0';
            videolist.style.width = "100%";
            table.style.width = "100%";
        } else {
            menu.style.transform = 'translateX(0%)';
            // Добавляем padding-left у .video-list
            videolist.style.paddingLeft = '250px';
            //videolist.style.width = "86%";
            videolist.style.width = "86.8%";
            table.style.width = "100%";
        }
    }

    /*
    window.addEventListener('resize', function() {
        if (window.innerWidth <= 1023) {
            menu.style.transform = "translateX(-100%)"; 
        } else {
            menu.style.transform = "translateX(0%)"; 
        }
    });*/



    // Добавляем обработчик события для кнопки меню
    document.querySelector('.menu-button').addEventListener('click', function(event) {
        // Переключаем меню
        toggleMenu();
        // Предотвращаем всплывание события, чтобы не сработал обработчик на window
        event.stopPropagation();
    });

    function adjustMenuVisibility() {
        var menu = document.getElementById('menu');
        var menu2 = document.getElementById('menu2');
        if (window.innerWidth <= 1023) {
            menu.style.transform = "translateX(-100%)";
        } else {
            menu.style.transform = "translateX(0%)";
            menu2.style.transform = 'translateX(100%)'; // Скрываем меню
        }
    }

    document.addEventListener('DOMContentLoaded', adjustMenuVisibility);

    // второй вариант, при изменении больше, чем на 15px, но не очень 
    let lastWidth = window.innerWidth;

    function handleResize() {
        const currentWidth = window.innerWidth;
        if (Math.abs(currentWidth - lastWidth) > 15) {
            var menu = document.getElementById('menu');
            if (currentWidth <= 1023) {
                menu.style.transform = "translateX(-100%)";
            } else {
                menu.style.transform = "translateX(0%)";
            }
            lastWidth = currentWidth;
        }
    }
    window.addEventListener('resize', handleResize);

    document.getElementById('menu').style.transform = 'translateX(-100%)';

    document.querySelector('.user-logo').addEventListener('click', function() {
        var menu2 = document.getElementById('menu2');
        if (menu2.style.transform === 'translateX(0%)') {
            menu2.style.transform = 'translateX(100%)'; // Скрываем меню
        } else {
            menu2.style.transform = 'translateX(0%)'; // Показываем меню
        }
    });
    // Добавляем обработчик события на window, чтобы закрыть меню при клике вне его
    /* window.addEventListener('click', function() {
        var menu = document.getElementById('menu');
        var videolist = document.getElementById('video-list');
        var table = document.getElementById('table');
        // Закрываем меню, если оно открыто
        if (menu.style.transform === 'translateX(0%)') {
            menu.style.transform = 'translateX(-100%)';
            videolist.style.paddingLeft = '0';
            videolist.style.width = "100%";
            table.style.width = "100%";
        }
    }); */

    // Для закрытия меню при загрузке страницы (если оно должно быть изначально закрыто)
    //document.getElementById('menu').style.transform = 'translateX(-100%)';
</script>
<script>
    /*
    function convertDateToTextualMonth(dateString) {
        // Создаем массив с названиями месяцев
        const months = [
            "Январь", "Февраль", "Март", "Апрель", "Май", "Июнь",
            "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"
        ];

        // Парсим дату
        const date = new Date(dateString);

        // Возвращаем строку с названием месяца и датой
        return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
    }

    // Пример использования:
    document.addEventListener('DOMContentLoaded', function() {
        const dateElements = document.querySelectorAll('.date-cell'); // Предполагается, что даты находятся в элементах с классом .date-cell

        dateElements.forEach(function(element) {
            element.textContent = convertDateToTextualMonth(element.textContent);
        });
    });*/
    function convertDateToTextualMonth(dateString) {
        if (!dateString) {
            return "Ошибка загрузки даты"; // Возвращаем плейсхолдер, если дата отсутствует
        }

        // Создаем массив с названиями месяцев
        const months = [
            "Январь", "Февраль", "Март", "Апрель", "Май", "Июнь",
            "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"
        ];

        // Парсим дату
        const date = new Date(dateString);

        // Проверяем, является ли дата валидной
        if (isNaN(date.getTime())) {
            return "Ошибка загрузки даты"; // Возвращаем плейсхолдер, если дата некорректна
        }

        // Возвращаем строку с названием месяца и датой
        return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
    }

    // Пример использования:
    document.addEventListener('DOMContentLoaded', function() {
        const dateElements = document.querySelectorAll('.date-cell'); // Предполагается, что даты находятся в элементах с классом .date-cell

        dateElements.forEach(function(element) {
            element.textContent = convertDateToTextualMonth(element.textContent);
        });
    });
</script>
<script>
    function adjustVideoHeight() {
        var videos = document.querySelectorAll('.video-list img');
        videos.forEach(function(video) {
            var width = video.offsetWidth; // Получаем текущую ширину элемента
            var height = width * (9 / 16); // Вычисляем высоту для соотношения сторон 16:9
            video.style.height = height + 'px'; // Устанавливаем высоту
            video.parentNode.querySelector('.loader').style.display = 'none'; // Скрываем анимацию загрузки
        });
    }

    // Вызов функции при загрузке страницы
    window.addEventListener('load', function() {
        adjustVideoHeight();
        document.querySelectorAll('.loader').forEach(loader => loader.style.display = 'none'); // Скрываем все загрузчики после загрузки страницы
    });

    // Вызов функции при изменении размера окна
    window.addEventListener('resize', adjustVideoHeight);
</script>

</html>