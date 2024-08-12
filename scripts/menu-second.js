
document.getElementById('menu2').style.transform = 'translateX(100%)';

document.querySelector('.user-logo').addEventListener('click', function (event) {
    var menu2 = document.getElementById('menu2');

    // Проверяем текущее состояние меню и переключаем его
    if (menu2.style.transform === 'translateX(0%)') {
        menu2.style.transform = 'translateX(100%)'; // Если меню открыто, закрываем его
    } else {
        menu2.style.transform = 'translateX(0%)'; // Если меню закрыто, открываем его
    }

    // Предотвращаем всплывание события, чтобы не сработал обработчик на window
    event.stopPropagation();
});

window.addEventListener('click', function () {
    var menu2 = document.getElementById('menu2');
    // Закрываем меню, если оно открыто и клик произошел вне его области
    if (menu2.style.transform === 'translateX(0%)') {
        menu2.style.transform = 'translateX(100%)';
    }
});

// Отменяем всплывание событий внутри menu2, чтобы клик внутри не закрывал его
document.getElementById('menu2').addEventListener('click', function (event) {
    event.stopPropagation();
}); */

//БЫЛО ЭТО
/*
document.querySelector('.user-logo').addEventListener('click', function () {
    var menu2 = document.getElementById('menu2');
    if (menu2.style.transform === 'translateX(0%)') {
        menu2.style.transform = 'translateX(100%)'; // Скрываем меню
    } else {
        menu2.style.transform = 'translateX(0%)'; // Показываем меню
    }
});
*/