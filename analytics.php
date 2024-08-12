<?php
$urlVideo = $_GET['urlVideo'] ?? '';

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

$authorId = NULL;

$sql = "SELECT authorsID,idVideo FROM `video` WHERE urlVideo = :urlVideo";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':urlVideo', $urlVideo, PDO::PARAM_STR);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $authorId = $row['authorsID'];
    $idVideo = $row['idVideo'];
    if ($authorId != $currentUserId) {
        header('Location: redocean/index.php');
        exit();
    }
} else {
    echo "No video found with the provided URL.";
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

        .data-view{
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
                    <p>Ваш канал</p>
                    <h2>RedOcean</h2>
                </div>
            </div>
            <nav class="sidebar-nav">
                <ul class="nav-list">
                    <li><a href="index.php">Главная</a></li>
                    <li><a href="panel">Контент</a></li>
                    <li><a href="analytics?urlVideo=<?php echo urlencode($urlVideo); ?>">Аналитика</a></li>
                    <li><a href="comments-list">Комментарии</a></li>
                    <li><a href="channel-settings">Настройка канала</a></li>
                    <li><a href="settings">Настройки</a></li>
                </ul>
            </nav>
        </aside>
        <div class="container">
            <div>
                <button class="view-btn" data-type="days">Просмотры по дням</button>
                <div id="view-by-days" class="data-view hide"></div>
            </div>
            <div>
                <button class="view-btn" data-type="months">Просмотры по месяцам</button>
                <div id="view-by-months" class="data-view hide"></div>
            </div>
            <div>
                <button class="view-btn" data-type="years">Просмотры по годам</button>
                <div id="view-by-years" class="data-view hide"></div>
            </div>
            <div>
                <button class="view-btn" data-type="days">График по дням</button>
                <canvas id="chart-by-days" class="chart hide"></canvas>
            </div>
            <div>
                <button class="view-btn" data-type="months">График по месяцам</button>
                <canvas id="chart-by-months" class="chart hide"></canvas>
            </div>
            <div>
                <button class="view-btn" data-type="years">График по годам</button>
                <canvas id="chart-by-years" class="chart hide"></canvas>
            </div>
        </div>
    </main>

</body>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        var charts = {};
        var idVideo = '<?php echo $idVideo; ?>';

        $('.view-btn').click(function() {
            var type = $(this).data('type');
            var canvasId = 'chart-by-' + type;
            var ctx = $('#' + canvasId)[0].getContext('2d');

            if ($(this).text().includes('График')) {
                $('.chart').hide();
                $('.data-view').hide();
                if (charts[type] && ctx.canvas.style.display !== 'none') {
                    ctx.canvas.style.display = 'none';
                } else {
                    if (charts[type]) {
                        ctx.canvas.style.display = 'block';
                    } else {
                        $.ajax({
                            url: 'functions/fetch_data.php',
                            type: 'POST',
                            data: {
                                viewType: type,
                                idVideo: idVideo
                            },
                            success: function(data) {
                                var labels = data.map(function(item) {
                                    return item.date;
                                });
                                var dataValues = data.map(function(item) {
                                    return item.view_count;
                                });

                                var chartData = {
                                    labels: labels,
                                    datasets: [{
                                        label: 'Количество просмотров',
                                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                        borderColor: 'rgba(255, 99, 132, 1)',
                                        data: dataValues
                                    }]
                                };

                                var chartOptions = {
                                    scales: {
                                        yAxes: [{
                                            ticks: {
                                                beginAtZero: true
                                            }
                                        }]
                                    }
                                };

                                charts[type] = new Chart(ctx, {
                                    type: 'line',
                                    data: chartData,
                                    options: chartOptions
                                });

                                ctx.canvas.style.display = 'block';
                            },
                            error: function() {
                                ctx.canvas.innerHTML = '<p>Ошибка при загрузке данных.</p>';
                                ctx.canvas.style.display = 'block';
                            }
                        });
                    }
                }
            } else {
                var targetDiv = $('#view-by-' + type);
                $('.data-view').hide();
                $('.chart').hide();
                if (targetDiv.is(':visible')) {
                    targetDiv.hide();
                } else {
                    $.ajax({
                        url: 'functions/fetch_data.php',
                        type: 'POST',
                        data: {
                            viewType: type,
                            idVideo: idVideo
                        },
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
            }
        });
    });
</script>
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