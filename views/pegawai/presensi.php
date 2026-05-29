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

<main class="flex-1 pb-20 bg-gray-50 min-h-screen max-w-md mx-auto w-full relative">

    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-b-[2.5rem] px-5 pt-10 pb-8 text-white shadow-lg">
        <div class="flex items-center gap-3">
            <a href="dashboard.php" class="w-10 h-10 rounded-full bg-white/15 flex items-center justify-center border border-white/20 hover:bg-white/20 transition">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold leading-tight">Isi Presensi</h1>
                <p class="text-xs text-blue-100 mt-1">Halo, <?= htmlspecialchars($nama_pegawai) ?></p>
            </div>
        </div>
    </div>

    <div class="px-5 -mt-5">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-4 mb-5">
            <h2 class="text-sm text-gray-500 mb-1">Tanggal Hari Ini</h2>
            <p class="text-lg font-bold text-gray-800" id="currentDate"><?= $tanggal_sekarang ?></p>

            <div id="statusBox" class="mt-4 flex items-start bg-yellow-50 p-3 rounded-xl border border-yellow-100">
                <svg id="statusIcon" class="w-5 h-5 text-yellow-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                </svg>
                <div>
                    <p class="text-xs font-semibold text-gray-800" id="statusJudul">Status Lokasi</p>
                    <p class="text-xs text-yellow-700 leading-relaxed" id="statusTeks">Mencari titik GPS Anda...</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-4 mb-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-base font-bold text-gray-800">Kamera Presensi</h3>
                <span class="text-[11px] text-gray-500 bg-gray-100 px-2 py-1 rounded-lg">Selfie wajib</span>
            </div>

            <div class="bg-black rounded-2xl overflow-hidden aspect-[3/4] relative shadow-md flex items-center justify-center">
                <video id="kamera" autoplay playsinline class="w-full h-full object-cover transform -scale-x-100"></video>
                <canvas id="kanvas" class="hidden"></canvas>
                <div class="absolute inset-0 border-4 border-dashed border-white/30 m-4 rounded-xl z-10 pointer-events-none"></div>
            </div>

            <p class="text-[11px] text-gray-500 mt-3 text-center">
                Pastikan wajah terlihat jelas dan lokasi GPS aktif sebelum melakukan presensi.
            </p>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <button id="btnMasuk" onclick="prosesAbsen('masuk')" class="bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-4 rounded-2xl shadow-md transition disabled:opacity-50 disabled:cursor-not-allowed">
                Absen Masuk
            </button>

            <button id="btnPulang" onclick="prosesAbsen('pulang')" class="bg-rose-500 hover:bg-rose-600 text-white font-bold py-4 rounded-2xl shadow-md transition disabled:opacity-50 disabled:cursor-not-allowed">
                Absen Pulang
            </button>
        </div>
    </div>
</main>

<nav class="fixed bottom-0 w-full max-w-md mx-auto bg-white border-t border-gray-200 flex justify-around p-3 pb-safe z-50 left-0 right-0">
    <a href="dashboard.php" class="flex flex-col items-center text-gray-400 hover:text-blue-600 transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
        </svg>
        <span class="text-[10px] mt-1 font-medium">Beranda</span>
    </a>

    <a href="profile.php" class="flex flex-col items-center text-gray-400 hover:text-blue-600 transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
        </svg>
        <span class="text-[10px] mt-1 font-medium">Profil</span>
    </a>
</nav>

<div id="customModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div id="modalContent" class="bg-white w-full max-w-sm rounded-[24px] shadow-2xl overflow-hidden scale-95 opacity-0 transition-all duration-200">
        <div id="modalHeader" class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
            <div id="modalIconContainer" class="w-10 h-10 rounded-full flex items-center justify-center shrink-0">
                <svg id="modalIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"></svg>
            </div>
            <h3 id="modalTitle" class="text-lg font-bold text-gray-800">Pemberitahuan</h3>
        </div>

        <div class="p-5">
            <p id="modalMessage" class="text-gray-600 text-sm leading-relaxed"></p>
        </div>

        <div class="px-5 pb-5 pt-2">
            <button id="modalButton" onclick="closeModal()" class="w-full py-3 rounded-xl font-bold text-white transition shadow-sm bg-blue-600 hover:bg-blue-700">
                Oke
            </button>
        </div>
    </div>
</div>

<script src="../../assets/js/camera_gps.js?v=10"></script>

<?php include '../layouts/footer.php'; ?>