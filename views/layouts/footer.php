    <!-- Script untuk mendaftarkan Service Worker PWA -->
    <!-- REGISTRASI SERVICE WORKER UNTUK PWA -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/presensi-desa/sw.js')
                    .then(reg => console.log('SW Berhasil:', reg.scope))
                    .catch(err => console.log('SW Gagal:', err));
            });
        }
    </script>
    </body>

    </html>