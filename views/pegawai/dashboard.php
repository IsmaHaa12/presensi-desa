<?php
require_once '../../config/database.php';

// Validasi Pegawai
if (!isset($_SESSION['pegawai_id'])) {
    header("Location: ../../index.php");
    exit;
}

$pegawai_id = $_SESSION['pegawai_id'];
$nama_pegawai = $_SESSION['nama'];

// Format tanggal hari ini untuk ditampilin (Contoh: Senin, 27 April 2026)
$hari = array("Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu");
$bulan = array("", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");
$tanggal_sekarang = $hari[date("w")] . ", " . date("j") . " " . $bulan[date("n")] . " " . date("Y");

// Tanggal untuk query ke database (Format: YYYY-MM-DD)
$tanggal_db = date('Y-m-d');

// --- AMBIL DATA ABSENSI HARI INI DARI DATABASE ---
$query_absen = "SELECT jam_masuk, jam_pulang FROM presensi WHERE pegawai_id = '$pegawai_id' AND tanggal = '$tanggal_db'";
$result_absen = $conn->query($query_absen);

// Bikin nilai default kalau belum absen
$jam_masuk = "--:--";
$jam_pulang = "--:--";

// Cek apakah ada data absen di database untuk hari ini
if ($result_absen && $result_absen->num_rows > 0) {
    $row = $result_absen->fetch_assoc();

    // Kalau kolom jam_masuk tidak kosong, ubah format jadi 08:30 (Hilangkan detik)
    if (!empty($row['jam_masuk'])) {
        $jam_masuk = date('H:i', strtotime($row['jam_masuk']));
    }

    // Kalau kolom jam_pulang tidak kosong, ubah format jadi 16:00
    if (!empty($row['jam_pulang'])) {
        $jam_pulang = date('H:i', strtotime($row['jam_pulang']));
    }
}
?>

<?php include '../layouts/header.php'; ?>

<!-- Konten Utama -->
<main class="flex-1 pb-20 bg-gray-50 min-h-screen max-w-md mx-auto w-full relative">

    <!-- Header Beranda (Background Biru Melengkung) -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-b-[2.5rem] px-6 pt-10 pb-16 text-white shadow-lg">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-blue-200 text-sm font-medium mb-1">Selamat datang kembali,</p>
                <h1 class="text-2xl font-bold"><?= htmlspecialchars($nama_pegawai) ?></h1>
                <p class="text-xs text-blue-100 mt-1">Perangkat Desa</p>
            </div>
            <!-- Avatar Placeholder -->
            <div class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center border-2 border-white/50 shadow-sm">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Kartu Status Absensi Hari Ini (Floating Card) -->
    <div class="-mt-10 px-5">
        <div class="bg-white rounded-2xl shadow-xl p-5 border border-gray-100">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-gray-800 font-bold">Absensi Hari Ini</h2>
                <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded-md"><?= $tanggal_sekarang ?></span>
            </div>

            <div class="flex justify-between items-center relative">
                <!-- Garis Penghubung (Hiasan) -->
                <div class="absolute left-1/2 top-1/2 transform -translate-x-1/2 -translate-y-1/2 w-16 h-0.5 bg-gray-200"></div>

                <!-- Waktu Masuk (DARI DATABASE) -->
                <div class="text-center bg-gray-50 p-3 rounded-xl w-[45%] border border-gray-100 z-10">
                    <p class="text-xs text-gray-500 mb-1">Masuk</p>
                    <p class="text-lg font-bold text-gray-800"><?= $jam_masuk ?></p>
                </div>

                <!-- Waktu Pulang (DARI DATABASE) -->
                <div class="text-center bg-gray-50 p-3 rounded-xl w-[45%] border border-gray-100 z-10">
                    <p class="text-xs text-gray-500 mb-1">Pulang</p>
                    <p class="text-lg font-bold text-gray-800"><?= $jam_pulang ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Menu Grid -->
    <div class="px-5 mt-8">
        <h3 class="text-gray-800 font-bold mb-4">Menu Utama</h3>
        <div class="grid grid-cols-2 gap-4">

            <!-- Tombol Menu: Presensi -->
            <a href="presensi.php" class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center justify-center gap-3 hover:bg-blue-50 transition">
                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-gray-700">Isi Presensi</span>
            </a>

            <!-- Tombol Menu: Riwayat -->
            <a href="riwayat.php" class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center justify-center gap-3 hover:bg-blue-50 transition">
                <div class="w-12 h-12 bg-green-100 text-green-600 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-gray-700">Riwayat</span>
            </a>

            <!-- Tombol Menu: Pengajuan Izin -->
            <a href="izin.php" class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center justify-center gap-3 hover:bg-blue-50 transition">
                <div class="w-12 h-12 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-gray-700">Pengajuan Izin</span>
            </a>

            <!-- Tombol Menu: Pengaturan (Pakai nama file profile.php) -->
            <a href="profile.php" class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center justify-center gap-3 hover:bg-blue-50 transition">
                <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-gray-700">Pengaturan</span>
            </a>

        </div>
    </div>
</main>

<!-- Bottom Navigation Bar (Mobile Style) -->
<nav class="fixed bottom-0 w-full max-w-md mx-auto bg-white border-t border-gray-200 flex justify-around p-3 pb-safe z-50 left-0 right-0">
    <!-- Tombol Beranda (AKTIF - BIRU) -->
    <a href="dashboard.php" class="flex flex-col items-center text-blue-600">
        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
        </svg>
        <span class="text-[10px] mt-1 font-medium">Beranda</span>
    </a>

    <!-- Tombol Profil (TIDAK AKTIF - ABU-ABU) (Pakai nama file profile.php) -->
    <a href="profile.php" class="flex flex-col items-center text-gray-400 hover:text-blue-600 transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
        </svg>
        <span class="text-[10px] mt-1 font-medium">Profil</span>
    </a>
</nav>

<?php include '../layouts/footer.php'; ?>