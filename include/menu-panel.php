<div class='menu2' id='menu2'>
    <ul>
        <?php if (isset($_SESSION['userId'])) {
            switch ($role) {
                case 'Администратор':
                    echo "<li><a href='management'>Панель администратора</a></li>";
                    break;
                case 'Модератор':
                    echo "<li><a href='management'>Панель модератора</a></li>";
                    break;
            }
        } ?>
        <?php if (isset($_SESSION['userId'])) {
            echo "
            <li><a href='panel'>Творческая студия</a></li>
            <li><a href='settings'>Настройки</a></li>
            <li><a href='functions/logout.php'>Выйти из аккаунта</a></li>";
        } else {
            echo "<li><a href='login'>Войти в аккаунт</a></li>";
        }
        ?>
    </ul>
</div>