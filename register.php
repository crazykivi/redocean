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

?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="logo-mini.png">
    <title>Регистрация</title>
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

        /* Авторизация */

        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }

        .login-box {
            background-color: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 450px;
            width: 100%;
            text-align: center;
        }

        .login-header {
            margin-bottom: 20px;
        }

        .logo-form {
            width: 75px;
            margin-bottom: 10px;
        }

        .login-header h1 {
            color: #202124;
            margin: 0;
            font-size: 24px;
        }

        .login-header p {
            color: #5f6368;
            margin: 10px 0 20px 0;
        }

        .form-group {
            position: relative;
            margin-bottom: 20px;
        }

        .form-group input {
            width: 100%;
            padding: 10px 10px 10px 0;
            font-size: 16px;
            border: none;
            border-bottom: 2px solid #ccc;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-bottom: 2px solid #1a73e8;
        }

        .form-group label {
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            color: #5f6368;
            pointer-events: none;
            transition: top 0.3s, font-size 0.3s;
        }

        .form-group input:focus+label,
        .form-group input:not(:placeholder-shown)+label {
            top: 0;
            font-size: 12px;
            color: #1a73e8;
        }

        .forgot-link {
            display: block;
            margin-bottom: 20px;
            color: #1a73e8;
            text-decoration: none;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            background-color: #1a73e8;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #0059c1;
        }
    </style>
    <link rel="stylesheet" href="css/header.css">
    <style>
        html {
            height: 90%;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            background-color: #f0f0f0;
            font-family: Arial, sans-serif;
            height: 100%;
        }
    </style>
</head>

<body>
    <div id="overlaybackground" style="display: none;"></div>
    <?php include("include/header.php"); ?>
    <?php include_once "include/menu.php"; ?>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <img src="logo.png" alt="RedOcean Logo" class="logo-form">
                <h1>Регистрация</h1>
            </div>
            <form id="registerForm">
                <div class="form-group">
                    <input type="text" id="nameUsers" name="username" required>
                    <label for="username">Логин</label>
                </div>
                <div class="form-group">
                    <input type="password" id="passUsers" name="passUsers" required>
                    <label for="username">Пароль</label>
                </div>
                <div class="form-actions">
                    <a href="login" class="create-account-link">Уже есть аккаунт?</a>
                    <button type="submit" class="btn">Регистрация</button>
                </div>
            </form>
        </div>
    </div>
</body>
<script>
    document.getElementById('registerForm').addEventListener('submit', function(event) {
        event.preventDefault();
        const nameUsers = document.getElementById('nameUsers').value;
        const passUsers = document.getElementById('passUsers').value;

        fetch('functions/register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    nameUsers: nameUsers,
                    passUsers: passUsers
                })
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
            })
            .catch(error => console.error('Ошибка:', error));
    });
</script>
<script src="scripts/menu.js"></script>

</html>