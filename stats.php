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

include("include/session_user.php");
include("include/check_auth.php");
if (isset($_SESSION['userId'])) {
    switch ($role) {
        case 'Пользователь':
            header('Location: index');
            break;
    }
}

// SQL-запросы для получения данных
$sql_users = "SELECT COUNT(*) AS count FROM users";
$sql_videos = "SELECT COUNT(*) AS count FROM video";
$sql_approved_videos = "SELECT COUNT(*) AS count FROM check_video WHERE result = 'Одобрено'";

try {
    // Выполнение запросов и получение результатов
    $stmt_users = $pdo->query($sql_users);
    $stmt_videos = $pdo->query($sql_videos);
    $stmt_approved_videos = $pdo->query($sql_approved_videos);

    // Извлечение данных
    $count_users = $stmt_users->fetch(PDO::FETCH_ASSOC)['count'];
    $count_videos = $stmt_videos->fetch(PDO::FETCH_ASSOC)['count'];
    $count_approved_videos = $stmt_approved_videos->fetch(PDO::FETCH_ASSOC)['count'];
} catch (PDOException $e) {
    echo "Ошибка выполнения запроса: " . $e->getMessage();
    exit();
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

        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.5s ease;
        }

        @media screen and (max-width: 1023px) {
            .main-content {
                margin-left: 0;
            }
        }

        .main-content h1 {
            color: #333;
        }

        .main-content form {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
        }

        label {
            display: block;
            margin-top: 20px;
        }

        .main-content input[type="text"],
        textarea {
            width: 80%;
            padding: 10px;
            margin-top: 5px;
        }

        .main-content button {
            padding: 10px 15px;
            margin-top: 20px;
        }

        .form-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .text-fields {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .video-preview {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: start;
        }

        .video-preview img {
            width: 200vh;
            max-width: 100%;
            object-fit: cover;
            aspect-ratio: 16/9;
            height: auto;
            border-radius: 20px;
        }

        .main-content button {
            padding: 10px 15px;
            margin-top: 20px;
            /* Расстояние от текстового поля до кнопки */
            align-self: flex-start;
            /* Центрирует кнопку по горизонтали внутри .text-fields */
        }

        #video-description {
            max-width: 80%;
        }

        .image-container img {
            width: 300px;
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

        #markersContainer .marker {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 10px;
            background-color: #f9f9f9;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        #markersContainer img {
            width: 300px;
            height: auto;
            margin-bottom: 10px;
        }

        .marker input,
        .marker button {
            margin: 5px;
            padding: 5px;
            width: 100px;
        }

        .marker button {
            cursor: pointer;
            background-color: crimson;
            color: white;
        }

        #deleteAllMarkers {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: red;
            color: white;
            border: none;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .view-btn {
            display: block;
            width: 220px;
            padding: 10px;
            margin: 20px auto;
            background-color: #ff4d4d;
            color: white;
            text-align: center;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
        }

        .hide {
            display: none;
        }

        .data-view {
            width: 100%;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .container {
            display: flex;
            justify-content: center;
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
        <div class="container">
            <table>
                <thead>
                    <tr>
                        <th>Показатель</th>
                        <th>Количество</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Количество зарегистрированных пользователей</td>
                        <td><?php echo $count_users; ?></td>
                    </tr>
                    <tr>
                        <td>Количество загруженных видео</td>
                        <td><?php echo $count_videos; ?></td>
                    </tr>
                    <tr>
                        <td>Количество одобренных видео</td>
                        <td><?php echo $count_approved_videos; ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>

</body>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    /*
    $(document).ready(function() {
        $('.view-btn').click(function() {
            const type = $(this).data('type');
            const targetDiv = $('#view-by-' + type);
            if (targetDiv.is(':visible')) {
                targetDiv.hide();
            } else {
                // Очистка всех открытых таблиц
                $('.data-view').hide();
                // AJAX запрос данных
                $.ajax({
                    url: 'functions/fetch_data.php',
                    type: 'GET',
                    data: {
                        viewType: type
                    }, // Передача типа запроса
                    success: function(data) {
                        var html = '<table><tr><th>Дата</th><th>Количество просмотров</th></tr>';
                        data.forEach(function(row) {
                            html += '<tr><td>' + row.date + '</td><td>' + row.view_count + '</td></tr>';
                        });
                        html += '</table>';
                        targetDiv.html(html).show();
                    },
                    error: function() {
                        targetDiv.html('<p>Ошибка при загрузке данных.</p>').show();
                    }
                });
            }
        });
    });*/
</script>
<script>
    function toggleMenu() {
        var menu = document.getElementById('menu');
        var videolist = document.getElementById('video-list');
        var table = document.getElementById('table');
        var conten = document.getElementById('main-content');

        // Переключаем класс для меню и .video-list
        if (menu.style.transform === 'translateX(0%)') {
            menu.style.transform = 'translateX(-100%)';
            conten.style.marginLeft = "0"; // Изменяем margin-left вместо transform
        } else {
            menu.style.transform = 'translateX(0%)';
            conten.style.marginLeft = "250px"; // Изменяем margin-left вместо transform
        }
    }
    document.querySelector('.user-logo').addEventListener('click', function() {
        var menu2 = document.getElementById('menu2');
        if (menu2.style.transform === 'translateX(0%)') {
            menu2.style.transform = 'translateX(100%)'; // Скрываем меню
        } else {
            menu2.style.transform = 'translateX(0%)'; // Показываем меню
        }
    });


    // Добавляем обработчик события для кнопки меню
    document.querySelector('.menu-button').addEventListener('click', function(event) {
        // Переключаем меню
        toggleMenu();
        // Предотвращаем всплывание события, чтобы не сработал обработчик на window
        event.stopPropagation();
    });

    function adjustMenuVisibility() {
        var menu = document.getElementById('menu');
        if (window.innerWidth <= 1023) {
            menu.style.transform = "translateX(-100%)";
        } else {
            menu.style.transform = "translateX(0%)";
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
</script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>


</html>