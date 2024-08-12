<?php
$urlVideo = $_GET['urlVideo'];

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
// Проверка на вход пользователя
if (!isset($_SESSION['userId'])) {
    die("Access denied: You are not logged in.");
}

$authorID = $currentUserId;
$currentUserId = $_SESSION['userId']; // Идентификатор текущего пользователя
$urlVideo = $_GET['urlVideo'] ?? ''; // Получаем URL видео из запроса, с проверкой на наличие

$authorId = NULL; // Устанавливаем переменную в NULL по умолчанию

// SQL запрос для проверки, является ли пользователь автором видео
$sql = "SELECT authorsID FROM `video` WHERE urlVideo = :urlVideo";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':urlVideo', $urlVideo, PDO::PARAM_STR);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $authorId = $row['authorsID']; // Обновляем переменную, если есть результат

    if ($authorId != $currentUserId) {
        // Перенаправляем на главную страницу
        header('Location: redocean/index.php'); // Укажите корректный URL главной страницы
        exit(); // Остановка скрипта после перенаправления
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
            /* Ширина полей ввода и кнопок */
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
                    <li><a href="index">Главная</a></li>
                    <li><a href="panel">Контент</a></li>
                    <li><a href="analytics?urlVideo=<?php echo $urlVideo; ?>">Аналитика</a></li>
                    <li><a href="comments-list">Комментарии</a></li>
                    <li><a href="channel-settings">Настройка канала</a></li>
                    <li><a href="settings">Настройки</a></li>
                </ul>
            </nav>
        </aside>
        <div class="main-content" id="main-content">
            <h1>Сведения о Видео</h1>
            <form id="videoForm" method="post" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="text-fields">
                        <label for="video-title">Название</label>
                        <input type="text" id="video-title" name="video-title" maxlength="150">

                        <label for="video-description">Описание</label>
                        <textarea placeholder="Описание отсутствует" id="video-description" name="video-description"></textarea>

                        <input type="hidden" id="urlVideo" name="urlVideo" value="<?php echo $urlVideo; ?>">
                        <input type="hidden" id="selectedPreview" name="selectedPreview">

                        <button type="submit">Сохранить изменения</button>
                    </div>
                    <div class="video-preview">
                        <img id="preview-image" src="video/stock-video.png" alt="Превью видео">
                        <input type="file" id="video-image" name="video-image" accept="image/png, image/jpeg" onchange="previewVideoThumbnail();">
                    </div>
                </div>
            </form>
            <button id="deleteVideoButton">Удалить видео</button>
            <form id="uploadForm" method="post" enctype="multipart/form-data">
                <h2>Загрузка маркеров</h2>
                <label for="imageFiles">Выберите картинку для маркера:</label>
                <input type="file" id="imageFiles" name="imageFiles[]" accept="image/*" multiple onchange="previewImages()" />
                <input type="hidden" id="urlVideo" name="urlVideo" value="<?php echo $urlVideo; ?>">
                <input type="hidden" id="selectedPreview" name="selectedPreview" value="">
                <div id="imagePreview"></div>
                <button type="submit">Save</button>
            </form>
            <h2>Уже существующие маркеры:</h2>
            <div id="markersContainer"></div>
            <button id="deleteAllMarkers">Удалить все маркеры</button>
        </div>
    </main>

</body>
<script>
    document.getElementById('deleteVideoButton').addEventListener('click', function() {
        const urlVideo = document.getElementById('urlVideo').value;

        if (confirm('Вы уверены, что хотите удалить это видео?')) {
            fetch('functions/delete_video.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        urlVideo: urlVideo
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Видео успешно удалено.');
                        window.location.href = 'panel'; // Перенаправление на список видео
                    } else {
                        alert('Ошибка: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
    });
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
<script>
    /*
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const urlVideo = urlParams.get('urlVideo');

        // Предполагается, что вы получаете данные из API или сервера
        fetch(`api/getVideoDetails?urlVideo=${urlVideo}`)
            .then(response => response.json())
            .then(data => {
                // Установка названия видео и описания
                document.getElementById('video-title').value = data.nameVideo;
                document.getElementById('video-description').value = data.description || 'Описание отсутствует';

                // Создание элемента изображения для превью видео
                const img = document.createElement('img');
                img.src = `video/${urlVideo}.PNG`; // Путь к изображению
                img.alt = 'Превью видео';
                img.onerror = function() {
                    this.src = 'video/stock-video.png';
                }; // Запасное изображение

                // Вставка изображения в блок превью
                const previewDiv = document.querySelector('.video-preview');
                previewDiv.innerHTML = ''; // Очистка предыдущего содержимого
                previewDiv.appendChild(img);
            })
            .catch(error => console.error('Ошибка:', error));
    });
    */
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const urlVideo = urlParams.get('urlVideo');

        // Предполагается, что вы получаете данные из API или сервера
        fetch(`api/getVideoDetails?urlVideo=${urlVideo}`)
            .then(response => response.json())
            .then(data => {
                // Установка названия видео и описания
                document.getElementById('video-title').value = data.nameVideo;
                document.getElementById('video-description').value = data.description || 'Описание отсутствует';

                // Создание элемента изображения для превью видео
                const img = document.createElement('img');
                img.src = `video/${urlVideo}.PNG`; // Путь к изображению
                img.alt = 'Превью видео';
                img.onerror = function() {
                    this.src = 'video/stock-video.png';
                }; // Запасное изображение

                // Вставка изображения в блок превью
                const previewDiv = document.querySelector('.video-preview');
                previewDiv.innerHTML = ''; // Очистка предыдущего содержимого
                previewDiv.appendChild(img);

            })
            .catch(error => console.error('Ошибка:', error));
    });

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const urlVideo = urlParams.get('urlVideo');

        function fetchMarkers() {
            fetch(`functions/get_markers.php?urlVideo=${urlVideo}`)
                .then(response => response.json())
                .then(displayMarkers)
                .catch(error => console.error('Ошибка:', error));
        }

        function displayMarkers(markers) {
            const markersContainer = document.getElementById('markersContainer');
            markersContainer.innerHTML = '';

            markers.forEach(marker => {
                const markerElement = document.createElement('div');
                markerElement.classList.add('marker');

                const img = document.createElement('img');
                img.src = `video/${urlVideo}/${marker.image_name}`;
                img.alt = 'Marker Image';
                img.onerror = () => img.src = 'path/to/default-image.png'; //ДОБАВИТЬ СЮДА КАРТИНКУ

                const nameLabel = document.createElement('input');
                nameLabel.type = 'text';
                nameLabel.value = marker.name_marker;
                nameLabel.className = 'name-label';

                /*
                const timeInput = document.createElement('input');
                timeInput.type = 'time';
                timeInput.value = marker.time_marker;
                timeInput.className = 'time-input';
                */

                const timeInput = document.createElement('input');
                timeInput.type = 'time';
                timeInput.value = marker.time_marker;
                timeInput.className = 'time-input';
                timeInput.step = 1; // Разрешить выбор секунд
                timeInput.min = "00:00:00"; // Минимальное время
                timeInput.max = "99:00:00"; // Максимальное время (99:00 минут)

                const updateButton = document.createElement('button');
                updateButton.textContent = 'Обновить маркер';
                updateButton.onclick = () => updateMarker(marker.id_marker, nameLabel.value, timeInput.value);

                const deleteButton = document.createElement('button');
                deleteButton.textContent = 'Удалить маркер';
                deleteButton.onclick = () => deleteMarker(marker.id_marker);

                markerElement.appendChild(img);
                markerElement.appendChild(nameLabel);
                markerElement.appendChild(timeInput);
                markerElement.appendChild(updateButton);
                markerElement.appendChild(deleteButton);
                markersContainer.appendChild(markerElement);
            });
        }

        function updateMarker(markerId, newName, newTime) {
            fetch(`functions/update_marker.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        markerId,
                        newName,
                        newTime
                    })
                }).then(response => response.text())
                .then(data => {
                    alert(data);
                    fetchMarkers();
                })
                .catch(error => console.error('Ошибка при обновлении:', error));
        }

        function deleteMarker(markerId) {
            fetch(`functions/delete_marker.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        markerId
                    })
                }).then(response => response.text())
                .then(data => {
                    alert(data);
                    fetchMarkers(); // Обновляем список маркеров после удаления
                })
                .catch(error => console.error('Ошибка при удалении:', error));
        }

        document.getElementById('deleteAllMarkers').onclick = function() {
            fetch(`functions/delete_all_markers.php?urlVideo=${urlVideo}`, {
                    method: 'POST'
                }).then(response => response.text())
                .then(data => {
                    alert(data);
                    fetchMarkers(); // Обновляем список маркеров после удаления всех
                })
                .catch(error => console.error('Ошибка при удалении всех маркеров:', error));
        };

        fetchMarkers();
    });

    document.getElementById('videoForm').addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(this);

        fetch('functions/editVideoDescription.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                alert(data);
            })
            .catch(error => {
                console.error('Error:', error);
            });
    });

    function previewVideoThumbnail() {
        const input = document.getElementById('video-image');
        const file = input.files[0];
        const preview = document.getElementById('preview-image');
        const selectedPreviewInput = document.getElementById('selectedPreview');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result; // Обновление изображения превью
                selectedPreviewInput.value = input.value; // Сохранение выбранного файла
            };
            reader.readAsDataURL(file);
        }
    }

    function previewImages() {
        const imagePreview = document.getElementById('imagePreview');
        imagePreview.innerHTML = ''; // Clear previous content

        const files = document.getElementById('imageFiles').files;

        Array.from(files).forEach((file, index) => {
            const reader = new FileReader();

            reader.onload = function(e) {
                const imageContainer = document.createElement('div');
                imageContainer.classList.add('image-container');

                const img = document.createElement('img');
                img.src = e.target.result;

                const timeInput = document.createElement('input');
                timeInput.type = 'time';
                timeInput.name = `time_marker[${index}]`;
                timeInput.step = 1; // Allow seconds selection
                timeInput.min = "00:00";
                timeInput.max = "99:59"; // Limit to 99 minutes

                const textInput = document.createElement('input');
                textInput.type = 'text';
                textInput.name = `name_marker[${index}]`;
                textInput.placeholder = 'Marker name';

                imageContainer.appendChild(img);
                imageContainer.appendChild(timeInput);
                imageContainer.appendChild(textInput);

                imagePreview.appendChild(imageContainer);
            };

            reader.readAsDataURL(file);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('uploadForm');

        form.addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent standard submission

            const formData = new FormData(form);

            fetch('functions/saveMarkers.php', {
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.text())
                .then(data => {
                    console.log(data); // Log response
                    alert(data); // Display response
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        });
    });
</script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>


</html>