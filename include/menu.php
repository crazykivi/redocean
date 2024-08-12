<div class='menu' id='menu' style='transform: translateX(-100%);'>
    <ul id="subscriptions-list">
        <a href="index">
            <div class="logomenu"><img src="logo.png"></div>
            <div class="logo-minimenu"><img src="logo-mini.png"></div>
        </a>
        <br>
        <li><a href='index'>Главная</a></li>
        <li><a href='themes'>Темы</a></li>
        <?php if (isset($_SESSION['userId'])) {
            echo "<li><a href='subscriptions'>Подписки</a></li>
        <li><a href='history'>История</a></li>
        <br>
        <li>Подписки</li>
        ";
        }
        ?>
        <?php
        while ($row = $stmt1->fetch(PDO::FETCH_ASSOC)) {
            // Зашифровка ID пользователя
            echo "<li><a href='channel?user={$row['nameUsers']}'>{$row['nameUsers']}</a></li>";
        } ?>
        <?php /* ?>
        <br>
        <li>Категории</li>
        <?php while ($row = $themes->fetch(PDO::FETCH_ASSOC)) {
            echo "<li><a href='#'>{$row['name_themes']}</a></li>";
        } ?>
                
        <?php */ ?>
    </ul>
</div>
<?php include("menu-panel.php"); ?>