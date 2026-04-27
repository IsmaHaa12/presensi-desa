    <!-- Script untuk mendaftarkan Service Worker PWA -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('Service Worker terdaftar!', reg))
                    .catch(err => console.error('Service Worker gagal:', err));
            });
        }
    </script>
    </body>

    </html>