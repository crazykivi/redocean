<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Загрузка видео</title>
<style>
    body, html {
        height: 100%;
        margin: 0;
        font-family: Arial, sans-serif;
        background: #f1f1f1;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .header {
        position: absolute;
        top: 0;
        width: 100%;
        background: #fff;
        padding: 10px 0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .logo {
        margin-left: 20px;
        font-size: 24px;
        color: #e53e3e;
    }
    .upload-area {
        background: #fff;
        border: 1px dashed #ccc;
        padding: 30px;
        width: 500px;
        text-align: center;
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
    .menu-icon {
        margin-right: 20px;
        font-size: 24px;
    }
    .search {
        position: absolute;
        top: 10px;
        right: 60px;
    }
    .search input {
        padding: 5px;
    }
    .search button {
        padding: 5px;
        background: #e53e3e;
        color: white;
        border: none;
        cursor: pointer;
    }
</style>
</head>
<body>
    <div class="header">
        <div class="logo">REDOCEAN</div>
        <div class="menu-icon">&#9776;</div>
        <div class="search">
            <input type="text" placeholder="Поиск">
            <button>Поиск</button>
        </div>
    </div>
    <div class="upload-area">
        <h2>Загрузка видео</h2>
        <p>Перетащите сюда видеофайл<br>или</p>
        <button class="upload-btn">Выбор файла</button>
    </div>
</body>
</html>