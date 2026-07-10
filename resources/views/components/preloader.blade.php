<div id="preloader"
    style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #ffffff; z-index: 99999; display: flex; justify-content: center; align-items: center; transition: opacity 0.5s ease;">
    <img src="{{ asset('images/icon.png') }}" alt="Loading..."
        style="width: 80px; height: auto; animation: pulse 1.5s infinite;">
</div>

<style>
    @keyframes pulse {
        0% {
            transform: scale(0.95);
            opacity: 0.8;
        }

        50% {
            transform: scale(1.1);
            opacity: 1;
        }

        100% {
            transform: scale(0.95);
            opacity: 0.8;
        }
    }
</style>

<script>
    window.addEventListener('load', function () {
        const preloader = document.getElementById('preloader');
        if (preloader) {
            preloader.style.opacity = '0';
            setTimeout(() => {
                preloader.style.display = 'none';
            }, 500);
        }
    });
</script>