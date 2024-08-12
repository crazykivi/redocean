document.addEventListener('DOMContentLoaded', function () {
    let isMoreVideos = true;
    //let startFrom = 20;
    let startFrom = 0;
    function loadMoreVideos() {
        if (!isMoreVideos) return;

        fetch('include/load_more_videos_history.php?startFrom=' + startFrom)
            .then(response => response.text())
            .then(data => {
                // Создаем временный контейнер для ответа сервера
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = data;

                // Проверяем количество загруженных видео-блоков
                const loadedVideos = tempDiv.querySelectorAll('.video').length;

                if (loadedVideos > 0) {
                    document.querySelector('.video-list').innerHTML += data;
                    startFrom += loadedVideos; // Увеличиваем смещение на количество загруженных видео
                } else {
                    isMoreVideos = false; // Больше не выполняем запросы, если видео не загружены
                }
            })
            .catch(error => {
                console.error(error);
                isMoreVideos = false;
            });
    }

    window.addEventListener('scroll', function () {
        let scrollTop = document.documentElement.scrollTop || document.body.scrollTop; // Прокрученная высота
        let clientHeight = document.documentElement.clientHeight; // Высота видимой части окна
        let scrollHeight = document.documentElement.scrollHeight || document.body.scrollHeight; // Полная высота страницы

        // Проверяем, достиг ли пользователь порогового значения до низа страницы
        if (scrollTop + clientHeight >= scrollHeight - 200) {
            loadMoreVideos();
            adjustVideoHeight();
        }
    });
    loadMoreVideos();

    window.onbeforeunload = function () {
        window.scrollTo(0, 0);  // Прокручиваем страницу вверх перед уходом
    }
});
