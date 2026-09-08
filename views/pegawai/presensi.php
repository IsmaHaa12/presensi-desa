<?php
require_once '../../config/database.php';

// Kalau belum login, lempar balik ke halaman depan
if (!isset($_SESSION['pegawai_id'])) {
    header("Location: ../../index.php?error=Silakan login terlebih dahulu.");
    exit;
}

$nama_pegawai = $_SESSION['nama'];

// --- AMBIL KONFIGURASI TITIK KOORDINAT & RADIUS DARI DATABASE ---
$query_setting = "SELECT * FROM pengaturan_sistem WHERE id = 1 LIMIT 1";
$result_setting = $conn->query($query_setting);
if ($result_setting && $result_setting->num_rows > 0) {
    $setting = $result_setting->fetch_assoc();
    $lat_office = $setting['lat_balai'];
    $lng_office = $setting['lng_balai'];
    $radius_office = $setting['radius_maksimal'];
    $akurasi_office = $setting['batas_akurasi'];
} else {
    // Fallback default jika data di tabel belum ada
    $lat_office = -7.761405;
    $lng_office = 109.445026;
    $radius_office = 50;
    $akurasi_office = 50;
}

// Format tanggal bahasa Indonesia
$hari = array("Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu");
$bulan = array("", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");
$tanggal_sekarang = $hari[date("w")] . ", " . date("j") . " " . $bulan[date("n")] . " " . date("Y");
?>

<?php include '../layouts/header.php'; ?>

<!-- Wrapper utama yang responsif untuk Mobile & Desktop -->
<div class="min-h-screen bg-slate-50 flex flex-col items-center pb-24 md:pb-12">

    <!-- Navbar Khusus Desktop (Muncul di layar md ke atas) -->
    <header class="w-full max-w-4xl hidden md:flex items-center justify-between px-6 py-4 mt-6 bg-white border border-slate-200/80 rounded-2xl shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-slate-900 text-white rounded-xl flex items-center justify-center font-bold text-sm">
                PD
            </div>
            <div>
                <h1 class="text-sm font-bold text-slate-900">Presensi Desa</h1>
                <p class="text-[11px] text-slate-500">Panel Pegawai</p>
            </div>
        </div>

        <nav class="flex items-center gap-2">
            <a href="dashboard.php" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">Beranda</a>
            <a href="presensi.php" class="px-4 py-2 text-xs font-semibold bg-slate-900 text-white rounded-xl transition">Presensi</a>
            <a href="riwayat.php" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">Riwayat</a>
            <a href="izin.php" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">Izin</a>
            <a href="profile.php" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">Profil</a>
            <div class="h-4 w-[1px] bg-slate-200 mx-1"></div>
            <a href="../../actions/logout_act.php" class="px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 rounded-xl transition">Keluar</a>
        </nav>
    </header>

    <!-- Konten Utama -->
    <main class="w-full max-w-md md:max-w-4xl bg-white md:bg-transparent md:shadow-none md:my-6 md:rounded-3xl md:overflow-hidden min-h-screen md:min-h-0 flex flex-col relative">

        <!-- Header Banner -->
        <div class="bg-slate-900 md:rounded-3xl md:mx-0 px-6 pt-10 pb-16 md:py-10 text-white shadow-sm relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/5 rounded-full blur-2xl pointer-events-none"></div>

            <div class="flex items-center gap-4 relative z-10">
                <a href="dashboard.php" class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center border border-white/10 hover:bg-white/20 transition shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-white">Isi Presensi</h1>
                    <p class="text-xs md:text-sm text-slate-300 mt-1">Halo, <?= htmlspecialchars($nama_pegawai) ?></p>
                </div>
            </div>
        </div>

        <!-- Bagian Isi -->
        <div class="px-5 md:px-0 -mt-10 md:-mt-6 relative z-20 space-y-6">

            <!-- Card Info & Status Lokasi -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Tanggal Hari Ini</h2>
                <p class="text-base font-bold text-slate-800" id="currentDate"><?= $tanggal_sekarang ?></p>

                <div id="statusBox" class="mt-4 flex items-start bg-amber-50 p-4 rounded-2xl border border-amber-200">
                    <svg id="statusIcon" class="w-5 h-5 text-amber-600 mr-3 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    </svg>
                    <div>
                        <p class="text-xs font-semibold text-slate-800" id="statusJudul">Status Lokasi</p>
                        <p class="text-xs text-amber-700 leading-relaxed mt-0.5" id="statusTeks">Mencari titik GPS Anda...</p>
                    </div>
                </div>
            </div>

            <!-- Card Kamera -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-slate-900">Kamera Presensi</h3>
                    <span class="text-xs font-medium text-slate-600 bg-slate-100 px-3 py-1 rounded-xl">Selfie wajib</span>
                </div>

                <div class="bg-black rounded-2xl overflow-hidden aspect-[3/4] md:aspect-[16/9] relative shadow-inner flex items-center justify-center">
                    <video id="kamera" autoplay playsinline class="w-full h-full object-cover transform -scale-x-100"></video>
                    <canvas id="kanvas" class="hidden"></canvas>
                    <div class="absolute inset-0 border-4 border-dashed border-white/30 m-4 rounded-xl z-10 pointer-events-none"></div>
                </div>

                <p class="text-xs text-slate-500 mt-4 text-center">
                    Pastikan wajah terlihat jelas dan lokasi GPS aktif sebelum melakukan presensi.
                </p>
            </div>

            <!-- Tombol Aksi -->
            <div class="grid grid-cols-2 gap-4">
                <button id="btnMasuk" onclick="prosesAbsen('masuk')" class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-sm py-4 rounded-2xl shadow-sm transition active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed">
                    Absen Masuk
                </button>

                <button id="btnPulang" onclick="prosesAbsen('pulang')" class="bg-rose-600 hover:bg-rose-700 text-white font-medium text-sm py-4 rounded-2xl shadow-sm transition active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed">
                    Absen Pulang
                </button>
            </div>

        </div>
    </main>

    <!-- Bottom Navigation Bar: Hanya tampil di layar HP (Mobile) -->
    <nav class="fixed bottom-0 w-full max-w-md mx-auto bg-white border-t border-slate-200 flex justify-around p-3 pb-safe z-50 left-0 right-0 md:hidden shadow-lg">
        <a href="dashboard.php" class="flex flex-col items-center text-slate-400 hover:text-slate-900 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            <span class="text-[10px] mt-1 font-medium">Beranda</span>
        </a>

        <a href="profile.php" class="flex flex-col items-center text-slate-400 hover:text-slate-900 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            <span class="text-[10px] mt-1 font-medium">Profil</span>
        </a>
    </nav>

</div>

<!-- Custom Modal -->
<div id="customModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div id="modalContent" class="bg-white w-full max-w-sm rounded-3xl shadow-2xl overflow-hidden scale-95 opacity-0 transition-all duration-200">
        <div id="modalHeader" class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
            <div id="modalIconContainer" class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0">
                <svg id="modalIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"></svg>
            </div>
            <h3 id="modalTitle" class="text-base font-bold text-slate-900">Pemberitahuan</h3>
        </div>

        <div class="p-6">
            <p id="modalMessage" class="text-slate-600 text-sm leading-relaxed"></p>
        </div>

        <div class="px-6 pb-6 pt-0">
            <button id="modalButton" onclick="closeModal()" class="w-full py-3.5 rounded-2xl font-medium text-sm text-white transition shadow-sm bg-slate-900 hover:bg-slate-800">
                Oke
            </button>
        </div>
    </div>
</div>

<!-- OPER DATA DARI DATABASE KE JAVASCRIPT -->
<script>
    const LAT_BALAI = <?= $lat_office ?>;
    const LNG_BALAI = <?= $lng_office ?>;
    const RADIUS_MAKSIMAL = <?= $radius_office ?>;
    const BATAS_AKURASI = <?= $akurasi_office ?>;
</script>

<!-- FILE JS UTAMA -->
<script src="../../assets/js/camera_gps.js?v=11"></script>

<?php include '../layouts/footer.php'; ?>