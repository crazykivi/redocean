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

if (isset($_SESSION['userId'])) {
    switch ($role) {
        case 'Пользователь':
            header('Location: index.php');
            break;
    }
}
$authorID = $_SESSION['userId'];
$query = "SELECT * FROM `themes_video`";
$themes = $pdo->query($query);
$urlVideo = isset($_GET['urlVideo']) ? $_GET['urlVideo'] : '';

$query = "
    SELECT
        v.nameVideo,
        u.nameUsers AS authorName,
        v.uploadDate,
        t.name_themes AS themeName,
        v.urlVideo,
        c.id_checkVideo,
        c.result,
        c.reason
    FROM
        `web-player`.video v
    JOIN
        `web-player`.users u ON v.authorsID = u.idUsers
    LEFT JOIN
        `web-player`.themes_video t ON v.id_themes_video = t.id_themes_video
    JOIN
        `web-player`.check_video c ON v.idVideo = c.idVideo
    WHERE
        v.urlVideo = :urlVideo;
";

$stmt = $pdo->prepare($query);
$stmt->bindParam(':urlVideo', $urlVideo, PDO::PARAM_STR);
$stmt->execute();
if ($stmt === false) {
    die("Ошибка выполнения запроса: " . print_r($pdo->errorInfo(), true));
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
                    <p>Панель</p>
                    <h2>
                        <?php
                        switch ($role) {
                            case 'Администратор':
                                echo 'Администратора';
                                break;
                            case 'Модератор':
                                echo 'Модератора';
                                break;
                        } ?>
                    </h2>
                </div>
            </div>
            <nav class="sidebar-nav">
                <ul class="nav-list">
                    <li><a href="index">Главная</a></li>
                    <li><a href="management">Просмотр видео</a></li>
                    <li><a href="check_comments">Просмотр комментариев</a></li>
                    <?php
                    switch ($role) {
                        case 'Администратор':
                            echo '<li><a href="stats">Просмотр статистики</a></li>';
                            break;
                    } ?>
                </ul>
            </nav>
        </aside>
        <section class="video-list" id="video-list">
            <!-- Таблица с видео -->
            <table id="table">
                <thead>
                    <tr>
                        <th>Превью</th>
                        <th>Название</th>
                        <th>Автор</th>
                        <th>Тема</th>
                        <th>Дата публикации</th>
                        <th></th>
                        <?php
                        switch ($role) {
                            case 'Администратор':
                                echo '<th>Кнопка удаления</th>';
                                break;
                        } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($stmt->rowCount() > 0) : ?>
                        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
                            <tr id="video-<?php echo $row['id_checkVideo']; ?>">
                                <td>
                                    <a href='changepanel?urlVideo=<?php echo $row['urlVideo']; ?>'>
                                        <div class='loader'></div>
                                        <video id="myVideo" width="200" controls>
                                            <source src='video/<?php echo $row['urlVideo']; ?>.mp4' type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                    </a>
                                </td>
                                <td><?php echo $row['nameVideo']; ?></td>
                                <td><?php echo $row['authorName']; ?></td>
                                <td><?php echo $row['themeName']; ?></td>
                                <td class='date-cell'><?php echo $row['uploadDate']; ?></td>
                                <td>
                                    <form onsubmit="submitForm(event, <?php echo $row['id_checkVideo']; ?>)">
                                        <input type="hidden" name="id_checkVideo" value="<?php echo $row['id_checkVideo']; ?>">
                                        <input type="hidden" name="urlVideo" value="<?php echo $row['urlVideo']; ?>">
                                        <select name="result" id="result-<?php echo $row['id_checkVideo']; ?>">
                                            <option value="Одобрено">Одобрено</option>
                                            <option value="Не одобрено">Не одобрено</option>
                                        </select>
                                        <textarea name="reason" id="reason-<?php echo $row['id_checkVideo']; ?>" placeholder="Причина отказа (если есть)"></textarea>
                                        <button type="submit">Применить</button>
                                    </form>
                                </td>
                                <?php
                                switch ($role) {
                                    case 'Администратор': ?>
                                        <td><button onclick="deleteVideo('<?php echo $row['urlVideo']; ?>', <?php echo $row['id_checkVideo']; ?>)">Удалить видео</button></td> <?php break;
                                                                                                                                                                        } ?>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="6">Нет данных для отображения.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
        <?php
        /*
        if ($totalPages > 1) {
            if ($currentPage > 1) {
                echo '<a href="?page=1&search=' . urlencode($searchQuery) . '">Первая страница</a> ';
            }

            if ($currentPage > 1) {
                echo '<a href="?page=' . ($currentPage - 1) . '&search=' . urlencode($searchQuery) . '">Предыдущая страница</a> ';
            }

            for ($i = $currentPage; $i < $currentPage + 3 && $i <= $totalPages; $i++) {
                if ($i != $currentPage) {
                    echo '<a href="?page=' . $i . '&search=' . urlencode($searchQuery) . '">' . $i . '</a> ';
                } else {
                    echo $i . ' ';
                }
            }

            if ($currentPage < $totalPages) {
                echo '<a href="?page=' . $totalPages . '&search=' . urlencode($searchQuery) . '">Последняя страница</a> ';
            }
        }
        */
        ?>
    </main>

</body>
<script>
    function deleteVideo(urlVideo, videoId) {
        if (confirm('Вы уверены, что хотите удалить это видео?')) {
            fetch('functions/delete_video_admin.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        urlVideo: urlVideo
                    })
                })
                .then(response => {
                    // Добавьте дополнительную проверку для обработки не-JSON ответов
                    if (!response.ok) {
                        throw new Error('Network response was not ok ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Удаляем строку из таблицы
                        const row = document.getElementById('video-' + videoId);
                        row.parentNode.removeChild(row);
                    } else {
                        alert('Ошибка при удалении видео: ' + data.error);
                    }
                })
                .catch(error => {
                    alert('Ошибка: ' + error.message);
                });
        }
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
</script>
<script>
    function submitForm(event, id_checkVideo) {
        event.preventDefault();

        // Получение значений из формы
        const result = document.getElementById(`result-${id_checkVideo}`).value;
        const reason = document.getElementById(`reason-${id_checkVideo}`).value;

        // Создание объекта FormData для отправки данных
        const formData = new FormData();
        formData.append('id_checkVideo', id_checkVideo);
        formData.append('result', result);
        formData.append('reason', reason);

        // Отправка AJAX-запроса
        fetch('functions/process_video.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Удаление строки после успешного обновления
                    const row = document.getElementById(`video-${id_checkVideo}`);
                    row.remove();
                } else {
                    // Обработка ошибок
                    alert('Ошибка обновления данных: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
                alert('Произошла ошибка при отправке запроса.');
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