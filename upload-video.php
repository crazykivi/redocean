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

if (!isset($_SESSION['userId'])) {
    header('Location: login.php');
    exit();
}
$query = "SELECT * FROM `themes_video`";
$themes = $pdo->query($query);
$themes2 = $pdo->query($query); ?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="logo-mini.png">
    <title>Загрузка видео</title>
    <link rel="stylesheet" href="css/header.css">
    <style>
        body,
        html {
            height: 100%;
            margin: 0;
            font-family: Arial, sans-serif;
            /* background: #f1f1f1; */
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .upload-area {
            z-index: 999;
            background: #fff;
            border: 1px dashed #ccc;
            padding: 30px;
            width: 500px;
            text-align: center;
            border-radius: 20px;
        }

        .upload-area h2 {
            margin: 0 0 20px;
            color: #333;
        }

        .upload-area p {
            margin: 20px 0;
            color: #666;
        }

        .upload-btn {
            background: #e53e3e;
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
        }

        .upload-btn:hover {
            background: #c53030;
        }

        .video-info {
            background-color: #fff;
            /* Белый фон */
            border-radius: 8px;
            /* Скругленные углы */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            /* Тень вокруг блока */
            padding: 20px;
            /* Отступы внутри блока */
            max-width: 600px;
            /* Максимальная ширина блока */
            margin: 40px auto;
            /* Центрирование блока на странице */
        }

        .form-group {
            margin-bottom: 15px;
            /* Отступы между полями формы */
        }

        .form-group label {
            display: block;
            /* Лейблы показывать как блочные элементы */
            margin-bottom: 5px;
            /* Отступ снизу для лейбла */
        }

        .form-group input[type="text"],
        .form-group select,
        .form-group input[type="file"] {
            width: 100%;
            /* Ширина текстового поля, выпадающего списка и поля для файла */
            padding: 10px;
            /* Паддинг для полей */
            border: 1px solid #ddd;
            /* Граница полей */
            border-radius: 4px;
            /* Скругление углов полей */
        }

        /* Стили для кнопки */
        .video-info button {
            background-color: #007bff;
            /* Синий фон кнопки */
            color: white;
            /* Белый текст кнопки */
            padding: 10px 15px;
            /* Паддинг для кнопки */
            border: none;
            /* Без границы */
            border-radius: 4px;
            /* Скругление углов кнопки */
            cursor: pointer;
            /* Курсор в виде указателя */
            font-size: 16px;
            /* Размер текста кнопки */
            margin-top: 10px;
            /* Отступ сверху для кнопки */
        }

        .video-info button:hover {
            background-color: #0056b3;
            /* Темно-синий фон кнопки при наведении */
        }

        /* Стили для прогресс бара */
        .progress-bar {
            width: 100%;
            background-color: #e0e0e0;
            border-radius: 4px;
            margin: 20px 0;
        }

        .progress {
            background-color: #007bff;
            height: 10px;
            border-radius: 4px;
            width: 0%;
            /* Начальная ширина полосы прогресса */
        }
    </style>
</head>

<body>
    <?php include("include/header.php"); ?>
    <?php include("include/menu.php"); ?>
    <div class="upload-area">
        <div class="upload-video-first-step" id="upload-video-first-step">
            <h2>Загрузка видео</h2>
            <p>Перетащите сюда видеофайл или нажмите на кнопку ниже</p>
            <div class="progress-bar" id="progressBar" style="display:none;">
                <div class="progress" id="progress"></div>
            </div>
            <form id="uploadForm" enctype="multipart/form-data">
                <input type="file" name="video" id="fileInput" accept="video/*" />
                <button class="upload-btn" type="button" id="uploadButton">Выбор файла</button>
            </form>
            <div id="progressBar" style="display:none;">
                <div id="progress" style="width: 0%;"></div>
            </div>
        </div>
        <div class="upload-video-second-step" id="upload-video-second-step" style="display:none;">
            <form id="videoInfoForm">
                <div class="form-group" disabled>
                    <label for="videoTitle">Название видео</label>
                    <input type="text" id="videoTitle" name="videoTitle" placeholder="Тут будут вводиться название видео">
                    <input type="hidden" id="videoId" name="videoId">
                </div>
                <div class="form-group">
                    <label for="videoTheme">Выбор темы видео</label>
                    <select id="videoTheme" name="videoTheme">
                        <?php
                        while ($row = $themes2->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='{$row['id_themes_video']}'>{$row['name_themes']}</option>";
                        } ?>
                        <!-- Опции тем будут сгенерированы здесь -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="videoPreview">Загрузить превью</label>
                    <input type="file" id="videoPreview" name="videoPreview" accept="image/*">
                </div>

                <button class="upload-btn" id="uploadButtonSecondPart" type="submit">Далее</button>
            </form>
        </div>
        <!--<form action="functions/uploadVideo.php" method="post" enctype="multipart/form-data">
            <input type="file" name="video" />
            <input type="submit" value="Загрузить" />
        </form> -->
    </div>
    <script>
        document.getElementById('uploadButton').addEventListener('click', function() {
            var fileInput = document.getElementById('fileInput');
            var file = fileInput.files[0];

            if (file) { // Убедимся, что файл был выбран
                var formData = new FormData();
                formData.append('video', file); // 'video' - это ключ, по которому мы получаем файл на сервере ($_FILES['video'])

                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'functions/uploadVideo.php', true); // 'upload.php' - это скрипт на сервере для обработки загрузки

                xhr.upload.onprogress = function(e) {
                    if (e.lengthComputable) {
                        var percentage = (e.loaded / e.total) * 100;
                        document.getElementById('progressBar').style.display = 'block';
                        document.getElementById('progress').style.width = percentage + '%';
                    }
                };

                xhr.onload = function() {
                    if (this.status == 200) {
                        try {
                            const response = JSON.parse(this.responseText);
                            console.log("Ответ сервера:", response);
                            if (response.success) {
                                alert('Файл успешно загружен!');
                                // Здесь скрываем блок загрузки и показываем блок с информацией о видео
                                document.getElementById('upload-video-first-step').style.display = 'none'; // Скрываем поле загрузки
                                const uploadedFileName = response.fileName;
                                const idVideo = response.idVideo;
                                const videoTitleInput = document.getElementById('videoTitle');
                                const videoIdInput = document.getElementById('videoId');
                                const baseName = uploadedFileName.substring(0, uploadedFileName.lastIndexOf('.')) || uploadedFileName;
                                videoTitleInput.value = baseName;
                                videoIdInput.value = idVideo;
                                document.getElementById('upload-video-second-step').style.display = 'block'; // Показываем поле информации
                            } else {
                                alert('Произошла ошибка при загрузке файла.');
                            }
                        } catch (e) {
                            console.error("Ошибка разбора JSON:", e);
                            alert('Ошибка обработки ответа сервера.');
                        }
                    } else {
                        alert('Произошла ошибка при загрузке файла.');
                    }
                };

                xhr.send(formData);
            } else {
                alert('Пожалуйста, выберите файл для загрузки.');
            }
        });

        document.getElementById('videoInfoForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Предотвращаем обычную отправку формы

            var formData = new FormData(this); // Создаем объект FormData из формы

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'functions/uploadVideoSecondPart.php', true); // Укажите путь к вашему скрипту обработчику

            xhr.onload = function() {
                if (this.status == 200) {
                    try {
                        const response = JSON.parse(this.responseText);
                        if (response.error) {
                            console.error("Ошибка сервера:", response.error);
                            alert(response.error);
                        } else {
                            alert(response.message || 'Видео успешно загружено');
                            window.location.href = 'index.php';
                        }
                    } catch (e) {
                        console.error("Ошибка разбора JSON:", e);
                        alert('Ошибка обработки ответа сервера.');
                    }
                } else {
                    console.error('Произошла ошибка при отправке формы');
                }
            };

            xhr.onerror = function() {
                console.error('Произошла ошибка при отправке формы');
            };

            xhr.send(formData); // Отправляем данные формы
        });
    </script>
    <script src="scripts/menu.js"></script>
</body>


</html>