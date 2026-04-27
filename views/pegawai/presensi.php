<?php
require_once '../../config/database.php';

// Kalau belum login, lempar balik ke halaman depan
if (!isset($_SESSION['pegawai_id'])) {
    header("Location: ../../index.php?error=Silakan login terlebih dahulu.");
    exit;
}

$nama_pegawai = $_SESSION['nama'];

// Format tanggal bahasa Indonesia
$hari = array("Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu");
$bulan = array("", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");
$tanggal_sekarang = $hari[date("w")] . ", " . date("j") . " " . $bulan[date("n")] . " " . date("Y");
?>

<?php include '../layouts/header.php'; ?>

<!-- Top Navigation -->
<header class="bg-blue-600 text-white p-4 shadow-md flex justify-between items-center">
    <div>
        <h1 class="text-lg font-bold">Halo, <?= htmlspecialchars($nama_pegawai) ?></h1>
        <p class="text-xs text-blue-200">Perangkat Desa</p>
    </div>
    <!-- Tombol Keluar mengarah ke fungsi logout -->
    <a href="../../actions/logout_act.php" class="text-sm bg-blue-700 px-3 py-1 rounded-md hover:bg-blue-800 transition">Keluar</a>
</header>

<main class="flex-1 p-4 pb-20 max-w-md mx-auto w-full">
    <!-- Status Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <h2 class="text-sm text-gray-500 mb-1">Tanggal Hari Ini</h2>
        <p class="text-lg font-bold text-gray-800" id="currentDate"><?= $tanggal_sekarang ?></p>

        <!-- Status Lokasi Box -->
        <div id="statusBox" class="mt-4 flex items-center bg-yellow-50 p-3 rounded-lg border border-yellow-100">
            <svg id="statusIcon" class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
            </svg>
            <div>
                <p class="text-xs font-semibold" id="statusJudul">Status Lokasi</p>
                <p class="text-xs text-yellow-700" id="statusTeks">Mencari titik GPS Anda...</p>
            </div>
        </div>
    </div>

    <!-- Camera UI -->
    <div class="bg-black rounded-2xl overflow-hidden aspect-[3/4] relative shadow-lg mb-6 flex items-center justify-center">
        <!-- Tag Video untuk preview kamera -->
        <video id="kamera" autoplay playsinline class="w-full h-full object-cover transform -scale-x-100"></video>
        <!-- Tag Canvas (disembunyikan) untuk menangkap foto -->
        <canvas id="kanvas" class="hidden"></canvas>
        <div class="absolute inset-0 border-4 border-dashed border-white/30 m-4 rounded-xl z-10 pointer-events-none"></div>
    </div>

    <!-- Action Buttons -->
    <div class="grid grid-cols-2 gap-4">
        <button id="btnMasuk" onclick="prosesAbsen('masuk')" class="bg-green-500 hover:bg-green-600 text-white font-bold py-4 rounded-xl shadow-md transition disabled:opacity-50">
            Absen Masuk
        </button>
        <button id="btnPulang" onclick="prosesAbsen('pulang')" class="bg-red-500 hover:bg-red-600 text-white font-bold py-4 rounded-xl shadow-md transition disabled:opacity-50">
            Absen Pulang
        </button>
    </div>
</main>

<!-- Bottom Navigation Bar (Mobile Style) -->
<nav class="fixed bottom-0 w-full max-w-md mx-auto bg-white border-t border-gray-200 flex justify-around p-3 pb-safe z-50 left-0 right-0">
    <!-- Tombol Kembali ke Beranda -->
    <a href="dashboard.php" class="flex flex-col items-center text-gray-400 hover:text-blue-600 transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
        </svg>
        <span class="text-[10px] mt-1 font-medium">Beranda</span>
    </a>

    <!-- Tombol Profil (TIDAK AKTIF) -->
    <a href="profile.php" class="flex flex-col items-center text-gray-400 hover:text-blue-600 transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
        </svg>
        <span class="text-[10px] mt-1 font-medium">Profil</span>
    </a>
</nav>

<!-- Panggil File JavaScript Utama -->
<script src="../../assets/js/camera_gps.js"></script>

<?php include '../layouts/footer.php'; ?>