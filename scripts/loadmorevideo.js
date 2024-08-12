document.addEventListener('DOMContentLoaded', function () {
    let isMoreVideos = true;
    let startFrom = 0;
    let searchQuery = document.getElementById('search-input') ? document.getElementById('search-input').value : '';
    let themes = new URLSearchParams(window.location.search).get('themes');

    function loadMoreVideos() {
        if (!isMoreVideos) return;

        let url = 'include/load_more_videos.php?startFrom=' + startFrom;
        if (searchQuery) url += '&search=' + encodeURIComponent(searchQuery);
        if (themes) url += '&themes=' + encodeURIComponent(themes);

        fetch(url)
            .then(response => response.text())
            .then(data => {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = data;
                const loadedVideos = tempDiv.querySelectorAll('.video').length;

                if (loadedVideos > 0) {
                    document.querySelector('.video-list').innerHTML += data;
                    startFrom += loadedVideos;
                } else {
                    if (document.querySelector('.video-list').childElementCount === 0) {
                        document.querySelector('.video-list').innerHTML = '<div class="no-videos">Видео по выбранной теме не найдены.</div>';
                    }
                    isMoreVideos = false;
                }
            })
            .catch(error => {
                console.error('Ошибка загрузки видео:', error);
                document.querySelector('.video-list').innerHTML = '<div class="no-videos">Произошла ошибка при загрузке видео.</div>';
                isMoreVideos = false;
            });
    }

    window.addEventListener('scroll', function () {
        let scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
        let clientHeight = document.documentElement.clientHeight;
        let scrollHeight = document.documentElement.scrollHeight;

        if (scrollTop + clientHeight >= scrollHeight - 200) {
            loadMoreVideos();
        }
    });

    loadMoreVideos();

    window.onbeforeunload = function () {
        window.scrollTo(0, 0);
    }
});