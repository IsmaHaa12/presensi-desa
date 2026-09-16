<?php
// Panggil library phpqrcode yang sudah lu unduh dan simpan di folder libraries/ atau vendor/
// Gunakan __DIR__ agar path akurat meskipun script dipanggil dari file lain
require_once __DIR__ . '/../libraries/phpqrcode/qrlib.php';

// Teks rahasia yang wajib sama persis dengan validasi di sistem pegawai
$isi_qr = "PRESENSI_DESA_PASIR_VALID";

// Lokasi dan nama file output penyimpanan gambar (masuk ke folder assets/img/)
$path_file = __DIR__ . '/../assets/img/qr-code-presensi.png';

// Pastikan folder assets/img/ ada, kalau belum buat otomatis
if (!is_dir(__DIR__ . '/../assets/img/')) {
    mkdir(__DIR__ . '/../assets/img/', 0777, true);
}

// Pastikan library phpqrcode benar-benar ter-include sebelum dipakai
if (!class_exists('QRcode')) {
    die('Library phpqrcode tidak ditemukan atau gagal di-load.');
}

// Generate QR Code ke dalam bentuk gambar PNG (Ukuran level L, ukuran pixel 6, margin 2)
// Use string 'L' for error correction level to avoid undefined constant if library constants not loaded
\QRcode::png($isi_qr, $path_file, 'L', 6, 2);

echo "<h3>Berhasil!</h3>";
echo "<p>File QR Code berhasil di-generate dan disimpan di: <b>assets/img/qr-code-presensi.png</b></p>";
echo "<br><a href='../views/admin/dashboard.php'>Kembali ke Dashboard Admin</a>";
