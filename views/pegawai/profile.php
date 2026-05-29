<?php
require_once '../../config/database.php';

// Validasi Pegawai
if (!isset($_SESSION['pegawai_id'])) {
    header("Location: ../../index.php");
    exit;
}

$pegawai_id    = $_SESSION['pegawai_id'];
$nama_pegawai  = $_SESSION['nama'] ?? 'Pegawai';

// Ambil data pegawai
$query_pegawai   = "SELECT * FROM pegawai WHERE id = '$pegawai_id' LIMIT 1";
$result_pegawai  = $conn->query($query_pegawai);
$data_pegawai    = $result_pegawai->fetch_assoc();
$jabatan         = $data_pegawai['jabatan'] ?? 'Perangkat Desa';

// =========================
// STATISTIK PRESENSI
// =========================

// Total Hadir
$query_hadir = "SELECT COUNT(*) as total FROM presensi 
                WHERE pegawai_id = '$pegawai_id' 
                AND status_kehadiran = 'Hadir'";
$total_hadir = $conn->query($query_hadir)->fetch_assoc()['total'] ?? 0;

// Total Izin / Sakit
$query_izin = "SELECT COUNT(*) as total FROM presensi 
               WHERE pegawai_id = '$pegawai_id' 
               AND status_kehadiran IN ('Izin', 'Sakit')";
$total_izin = $conn->query($query_izin)->fetch_assoc()['total'] ?? 0;

// Total presensi
$query_total = "SELECT COUNT(*) as total FROM presensi 
                WHERE pegawai_id = '$pegawai_id'";
$total_presensi = $conn->query($query_total)->fetch_assoc()['total'] ?? 0;

// Cek absen hari ini
$tanggal_db = date('Y-m-d');
$query_hari_ini = "SELECT COUNT(*) as total FROM presensi 
                   WHERE pegawai_id = '$pegawai_id' 
                   AND tanggal = '$tanggal_db'";
$cek_hari_ini = $conn->query($query_hari_ini)->fetch_assoc()['total'] ?? 0;
$belum_absen_hari_ini = ($cek_hari_ini > 0) ? 0 : 1;

// Persentase kehadiran
$persentase_kehadiran = ($total_presensi > 0)
    ? round(($total_hadir / $total_presensi) * 100)
    : 0;
?>

<?php include '../layouts/header.php'; ?>

<!-- Konten Utama -->
<main class="flex-1 pb-20 bg-gray-50 min-h-screen max-w-md mx-auto w-full relative">

    <!-- Header Profil (biru, sama tinggi dengan beranda) -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-b-[2.5rem] px-6 pt-10 pb-10 text-white shadow-lg">
        <h1 class="text-2xl font-bold text-center">Profil Akun</h1>
    </div>

    <!-- Card Profil (mirip style header di beranda) -->
    <div class="-mt-10 px-5">
        <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 flex items-center gap-4">
            <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center">
                <svg class="w-9 h-9 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">Nama Pegawai</p>
                <h2 class="text-lg font-bold text-gray-900 leading-snug">
                    <?= htmlspecialchars($nama_pegawai) ?>
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    <?= htmlspecialchars($jabatan) ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Statistik Presensi -->
    <div class="px-5 mt-6">
        <h3 class="text-gray-800 font-bold mb-3">Statistik Presensi</h3>

        <div class="grid grid-cols-2 gap-4">

            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[11px] text-emerald-600 font-semibold mb-1">Total Hadir</p>
                <p class="text-2xl font-black text-emerald-700"><?= $total_hadir ?></p>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[11px] text-amber-600 font-semibold mb-1">Izin / Sakit</p>
                <p class="text-2xl font-black text-amber-700"><?= $total_izin ?></p>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[11px] text-rose-600 font-semibold mb-1">Belum Absen Hari Ini</p>
                <p class="text-2xl font-black text-rose-700"><?= $belum_absen_hari_ini ?></p>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[11px] text-blue-600 font-semibold mb-1">Persentase Kehadiran</p>
                <p class="text-2xl font-black text-blue-700"><?= $persentase_kehadiran ?>%</p>
            </div>

        </div>
    </div>

    <!-- Logout -->
    <div class="px-5 mt-6 mb-4">
        <a href="../../actions/logout_act.php"
            class="flex items-center justify-between bg-red-50 border border-red-100 text-red-600 px-4 py-3 rounded-2xl shadow-sm hover:bg-red-100 transition">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 
                             0l3-3m0 0l-3-3m3 3H9" />
                </svg>
                <span class="text-sm font-semibold">Keluar Akun</span>
            </div>
        </a>
    </div>
</main>

<!-- Bottom Navigation Bar (SAMA seperti di dashboard) -->
<nav class="fixed bottom-0 w-full max-w-md mx-auto bg-white border-t border-gray-200 flex justify-around p-3 pb-safe z-50 left-0 right-0">
    <!-- Beranda (abu-abu) -->
    <a href="dashboard.php" class="flex flex-col items-center text-gray-400 hover:text-blue-600 transition">
        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 
                     1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
        </svg>
        <span class="text-[10px] mt-1 font-medium">Beranda</span>
    </a>

    <!-- Profil (AKTIF - BIRU) -->
    <a href="profile.php" class="flex flex-col items-center text-blue-600">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
        </svg>
        <span class="text-[10px] mt-1 font-medium">Profil</span>
    </a>
</nav>

<?php include '../layouts/footer.php'; ?>