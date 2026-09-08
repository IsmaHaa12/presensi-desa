<?php
require_once '../../config/database.php';

// Validasi pegawai
if (!isset($_SESSION['pegawai_id'])) {
    header("Location: ../../index.php");
    exit;
}

$pegawai_id   = $_SESSION['pegawai_id'];
$nama_pegawai = $_SESSION['nama'] ?? 'Pegawai Desa';
$jabatan      = $_SESSION['jabatan'] ?? 'Perangkat Desa';

// Greeting sesuai waktu
$jam_sekarang = (int) date('H');
if ($jam_sekarang >= 4 && $jam_sekarang < 11) {
    $greeting = "Selamat pagi";
} elseif ($jam_sekarang >= 11 && $jam_sekarang < 15) {
    $greeting = "Selamat siang";
} elseif ($jam_sekarang >= 15 && $jam_sekarang < 18) {
    $greeting = "Selamat sore";
} else {
    $greeting = "Selamat malam";
}

// Format tanggal Indonesia
$nama_hari = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
$nama_bulan = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

$tanggal_tampil = $nama_hari[date("w")] . ", " . date("j") . " " . $nama_bulan[date("n")] . " " . date("Y");
$tanggal_db = date('Y-m-d');

// Default nilai
$jam_masuk  = '--:--';
$jam_pulang = '--:--';
$status_presensi = 'Belum Absen';
$status_class = 'bg-rose-50 text-rose-600 border border-rose-200';
$info_presensi = 'Anda belum melakukan absensi hari ini.';

// Ambil data absensi hari ini
$query_absen = "SELECT jam_masuk, jam_pulang 
                FROM presensi 
                WHERE pegawai_id = ? AND tanggal = ? 
                LIMIT 1";

$stmt_absen = $conn->prepare($query_absen);
$stmt_absen->bind_param("is", $pegawai_id, $tanggal_db);
$stmt_absen->execute();
$result_absen = $stmt_absen->get_result();

if ($result_absen && $result_absen->num_rows > 0) {
    $row = $result_absen->fetch_assoc();

    if (!empty($row['jam_masuk'])) {
        $jam_masuk = date('H:i', strtotime($row['jam_masuk']));
    }

    if (!empty($row['jam_pulang'])) {
        $jam_pulang = date('H:i', strtotime($row['jam_pulang']));
    }

    if (!empty($row['jam_masuk']) && empty($row['jam_pulang'])) {
        $status_presensi = 'Sudah Absen Masuk';
        $status_class = 'bg-amber-50 text-amber-600 border border-amber-200';
        $info_presensi = 'Anda sudah absen masuk, tetapi belum melakukan absen pulang.';
    } elseif (!empty($row['jam_masuk']) && !empty($row['jam_pulang'])) {
        $status_presensi = 'Absensi Lengkap';
        $status_class = 'bg-emerald-50 text-emerald-600 border border-emerald-200';
        $info_presensi = 'Absensi masuk dan pulang hari ini sudah lengkap.';
    }
}
?>

<?php include '../layouts/header.php'; ?>

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
            <a href="dashboard.php" class="px-4 py-2 text-xs font-semibold bg-slate-900 text-white rounded-xl transition">Beranda</a>
            <a href="presensi.php" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">Presensi</a>
            <a href="riwayat.php" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">Riwayat</a>
            <a href="izin.php" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">Izin</a>
            <a href="profile.php" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">Profil</a>
            <div class="h-4 w-[1px] bg-slate-200 mx-1"></div>
            <a href="../../actions/logout_act.php" class="px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 rounded-xl transition">Keluar</a>
        </nav>
    </header>

    <main class="w-full max-w-md md:max-w-4xl bg-white md:bg-transparent md:shadow-none md:my-6 md:rounded-3xl md:overflow-hidden min-h-screen md:min-h-0 flex flex-col relative">

        <!-- Header Banner -->
        <div class="bg-slate-900 md:rounded-3xl md:mx-0 px-6 pt-10 pb-16 md:py-10 text-white shadow-sm relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/5 rounded-full blur-2xl pointer-events-none"></div>

            <div class="flex justify-between items-center gap-4 relative z-10">
                <div>
                    <p class="text-slate-400 text-xs md:text-sm font-medium mb-1"><?= htmlspecialchars($greeting) ?>,</p>
                    <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-white"><?= htmlspecialchars($nama_pegawai) ?></h1>
                    <p class="text-xs md:text-sm text-slate-300 mt-1"><?= htmlspecialchars($jabatan) ?></p>
                </div>

                <div class="w-12 h-12 md:w-16 md:h-16 bg-white/10 rounded-2xl flex items-center justify-center border border-white/10 shadow-sm shrink-0">
                    <svg class="w-6 h-6 md:w-8 md:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Konten Utama -->
        <div class="px-5 md:px-0 -mt-10 md:-mt-6 relative z-20 space-y-6">

            <!-- Card Absensi Hari Ini -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6">
                <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-5 gap-3">
                    <div>
                        <h2 class="text-slate-900 font-bold text-base">Absensi Hari Ini</h2>
                        <p class="text-xs text-slate-500 mt-0.5"><?= htmlspecialchars($info_presensi) ?></p>
                    </div>
                    <span class="text-xs font-medium text-slate-600 bg-slate-100 px-3 py-1.5 rounded-xl self-start sm:self-auto">
                        <?= htmlspecialchars($tanggal_tampil) ?>
                    </span>
                </div>

                <!-- Badge status -->
                <div class="mb-5">
                    <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-semibold <?= $status_class ?>">
                        <?= htmlspecialchars($status_presensi) ?>
                    </span>
                </div>

                <!-- Jam Masuk & Pulang Grid -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center bg-slate-50 p-4 rounded-2xl border border-slate-100">
                        <p class="text-xs font-medium text-slate-400 mb-1">Masuk</p>
                        <p class="text-xl font-bold text-slate-800"><?= htmlspecialchars($jam_masuk) ?></p>
                    </div>

                    <div class="text-center bg-slate-50 p-4 rounded-2xl border border-slate-100">
                        <p class="text-xs font-medium text-slate-400 mb-1">Pulang</p>
                        <p class="text-xl font-bold text-slate-800"><?= htmlspecialchars($jam_pulang) ?></p>
                    </div>
                </div>
            </div>

            <!-- Menu Utama -->
            <div>
                <h3 class="text-slate-900 font-bold text-base mb-4">Menu Utama</h3>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

                    <a href="presensi.php" class="bg-white p-5 rounded-3xl shadow-sm border border-slate-200/80 flex flex-col items-center justify-center gap-3 hover:border-slate-400 hover:shadow-md transition group">
                        <div class="w-12 h-12 bg-slate-100 text-slate-900 rounded-2xl flex items-center justify-center group-hover:bg-slate-900 group-hover:text-white transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                        </div>
                        <span class="text-xs font-semibold text-slate-700">Isi Presensi</span>
                    </a>

                    <a href="riwayat.php" class="bg-white p-5 rounded-3xl shadow-sm border border-slate-200/80 flex flex-col items-center justify-center gap-3 hover:border-slate-400 hover:shadow-md transition group">
                        <div class="w-12 h-12 bg-slate-100 text-slate-900 rounded-2xl flex items-center justify-center group-hover:bg-slate-900 group-hover:text-white transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                        </div>
                        <span class="text-xs font-semibold text-slate-700">Riwayat</span>
                    </a>

                    <a href="izin.php" class="bg-white p-5 rounded-3xl shadow-sm border border-slate-200/80 flex flex-col items-center justify-center gap-3 hover:border-slate-400 hover:shadow-md transition group">
                        <div class="w-12 h-12 bg-slate-100 text-slate-900 rounded-2xl flex items-center justify-center group-hover:bg-slate-900 group-hover:text-white transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <span class="text-xs font-semibold text-slate-700">Pengajuan Izin</span>
                    </a>

                    <a href="profile.php" class="bg-white p-5 rounded-3xl shadow-sm border border-slate-200/80 flex flex-col items-center justify-center gap-3 hover:border-slate-400 hover:shadow-md transition group">
                        <div class="w-12 h-12 bg-slate-100 text-slate-900 rounded-2xl flex items-center justify-center group-hover:bg-slate-900 group-hover:text-white transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <span class="text-xs font-semibold text-slate-700">Profil</span>
                    </a>

                </div>
            </div>

        </div>
    </main>

    <!-- Bottom Navigation: Hanya tampil di layar HP (Mobile) -->
    <nav class="fixed bottom-0 w-full max-w-md mx-auto bg-white border-t border-slate-200 flex justify-around p-3 pb-safe z-50 left-0 right-0 md:hidden shadow-lg">
        <a href="dashboard.php" class="flex flex-col items-center text-slate-900">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
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

<?php include '../layouts/footer.php'; ?>