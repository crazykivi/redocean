// Функция для переключения состояния меню
function toggleMenu() {
    var menu = document.getElementById('menu');
    var menu2 = document.getElementById('menu2');
    // Переключаем стиль transform для меню
    if (menu.style.transform === 'translateX(0%)') {
        menu.style.transform = 'translateX(-100%)';
    } else {
        menu.style.transform = 'translateX(0%)';
        menu2.style.transform = 'translateX(100%)'; // Скрываем меню
    }
}

// Добавляем обработчик события для кнопки меню
document.querySelector('.menu-button').addEventListener('click', function (event) {
    // Переключаем меню
    toggleMenu();
    // Предотвращаем всплывание события, чтобы не сработал обработчик на window
    event.stopPropagation();
});

// Добавляем обработчик события на window, чтобы закрыть меню при клике вне его
window.addEventListener('click', function () {
    var menu = document.getElementById('menu');
    // Закрываем меню, если оно открыто
    if (menu.style.transform === 'translateX(0%)') {
        menu.style.transform = 'translateX(-100%)';
    }
});

// Для закрытия меню при загрузке страницы (если оно должно быть изначально закрыто)
document.getElementById('menu').style.transform = 'translateX(-100%)';

document.querySelector('.user-logo').addEventListener('click', function () {
    var menu2 = document.getElementById('menu2');
    if (menu2.style.transform === 'translateX(0%)') {
        menu2.style.transform = 'translateX(100%)'; // Скрываем меню
    } else {
        menu2.style.transform = 'translateX(0%)'; // Показываем меню
    }
});