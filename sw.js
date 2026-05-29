self.addEventListener('install', (event) => {
    console.log('[Service Worker] Terinstal');
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    console.log('[Service Worker] Aktif');
});

self.addEventListener('fetch', (event) => {
    // Biarkan kosong. Ini cuma syarat wajib biar muncul tombol Install PWA.
});