<?php
require_once '../../config/database.php';

// Validasi Pegawai
if (!isset($_SESSION['pegawai_id'])) {
    header("Location: ../../index.php");
    exit;
}

$pegawai_id    = $_SESSION['pegawai_id'];
$nama_pegawai  = $_SESSION['nama'] ?? 'Pegawai';

// Ambil data pegawai secara lengkap
$query_pegawai   = "SELECT * FROM pegawai WHERE id = '$pegawai_id' LIMIT 1";
$result_pegawai  = $conn->query($query_pegawai);
$data_pegawai    = $result_pegawai->fetch_assoc();
$jabatan         = $data_pegawai['jabatan'] ?? 'Perangkat Desa';
$username        = $data_pegawai['username'] ?? '-';
$created_at      = $data_pegawai['created_at'] ?? date('Y-m-d');

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
               AND status_kehadiran IN ('Izin', 'Sakit', 'Cuti', 'Dinas Luar')";
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
            <a href="presensi.php" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">Presensi</a>
            <a href="riwayat.php" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">Riwayat</a>
            <a href="izin.php" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">Izin</a>
            <a href="profile.php" class="px-4 py-2 text-xs font-semibold bg-slate-900 text-white rounded-xl transition">Profil</a>
            <div class="h-4 w-[1px] bg-slate-200 mx-1"></div>
            <a href="../../actions/logout_act.php" class="px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 rounded-xl transition">Keluar</a>
        </nav>
    </header>

    <!-- Konten Utama -->
    <main class="w-full max-w-md md:max-w-4xl bg-white md:bg-transparent md:shadow-none md:my-6 md:rounded-3xl md:overflow-hidden min-h-screen md:min-h-0 flex flex-col relative">

        <!-- Header Profil Banner -->
        <div class="bg-slate-900 md:rounded-3xl md:mx-0 px-6 pt-10 pb-16 md:py-10 text-white shadow-sm relative overflow-hidden text-center md:text-left">
            <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/5 rounded-full blur-2xl pointer-events-none"></div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-white">Profil Akun</h1>
            <p class="text-xs md:text-sm text-slate-300 mt-1">Informasi akun dan statistik kehadiran pegawai</p>
        </div>

        <!-- Bagian Konten -->
        <div class="px-5 md:px-0 -mt-10 md:-mt-6 relative z-20 space-y-6">

            <!-- Card Info Profil -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6 flex flex-col sm:flex-row items-center gap-5 text-center sm:text-left">
                <div class="w-20 h-20 bg-slate-100 text-slate-900 rounded-2xl flex items-center justify-center shrink-0 border border-slate-200/60 shadow-inner">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div class="w-full">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Nama Pegawai</p>
                    <h2 class="text-xl font-bold text-slate-900 leading-snug">
                        <?= htmlspecialchars($nama_pegawai) ?>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        <?= htmlspecialchars($jabatan) ?>
                    </p>
                </div>
            </div>

            <!-- Detail Informasi Akun (Tambahan agar lebih berisi) -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6">
                <h3 class="text-slate-900 font-bold text-base mb-4">Informasi Akun</h3>

                <div class="space-y-4">
                    <div class="flex justify-between items-center py-2.5 border-b border-slate-100 text-sm">
                        <span class="text-slate-400 font-medium">Username</span>
                        <span class="font-semibold text-slate-800"><?= htmlspecialchars($username) ?></span>
                    </div>
                    <div class="flex justify-between items-center py-2.5 border-b border-slate-100 text-sm">
                        <span class="text-slate-400 font-medium">Jabatan</span>
                        <span class="font-semibold text-slate-800"><?= htmlspecialchars($jabatan) ?></span>
                    </div>
                    <div class="flex justify-between items-center py-2.5 text-sm">
                        <span class="text-slate-400 font-medium">Bergabung Sejak</span>
                        <span class="font-semibold text-slate-800"><?= date('d M Y', strtotime($created_at)) ?></span>
                    </div>
                </div>
            </div>

            <!-- Statistik Presensi -->
            <div>
                <h3 class="text-slate-900 font-bold text-base mb-4">Statistik Presensi</h3>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

                    <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-200/80">
                        <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600 mb-2">Total Hadir</p>
                        <p class="text-2xl font-black text-emerald-600"><?= $total_hadir ?></p>
                    </div>

                    <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-200/80">
                        <p class="text-xs font-semibold uppercase tracking-wider text-amber-600 mb-2">Izin / Sakit</p>
                        <p class="text-2xl font-black text-amber-600"><?= $total_izin ?></p>
                    </div>

                    <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-200/80">
                        <p class="text-xs font-semibold uppercase tracking-wider text-rose-600 mb-2">Belum Absen</p>
                        <p class="text-2xl font-black text-rose-600"><?= $belum_absen_hari_ini ?></p>
                    </div>

                    <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-200/80">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2">Persentase</p>
                        <p class="text-2xl font-black text-slate-900"><?= $persentase_kehadiran ?>%</p>
                    </div>

                </div>
            </div>

            <!-- Tombol Keluar Akun -->
            <div class="pt-2">
                <a href="../../actions/logout_act.php"
                    class="flex items-center justify-center gap-2 w-full bg-rose-50 border border-rose-200 text-rose-600 hover:bg-rose-100 font-medium text-sm py-3.5 rounded-2xl shadow-sm transition active:scale-[0.98]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                    </svg>
                    <span>Keluar Akun</span>
                </a>
            </div>

        </div>
    </main>

    <!-- Bottom Navigation Bar: Hanya tampil di layar HP (Mobile) -->
    <nav class="fixed bottom-0 w-full max-w-md mx-auto bg-white border-t border-slate-200 flex justify-around p-3 pb-safe z-50 left-0 right-0 md:hidden shadow-lg">
        <!-- Beranda -->
        <a href="dashboard.php" class="flex flex-col items-center text-slate-400 hover:text-slate-900 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            <span class="text-[10px] mt-1 font-medium">Beranda</span>
        </a>

        <!-- Profil (Aktif) -->
        <a href="profile.php" class="flex flex-col items-center text-slate-900">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
            </svg>
            <span class="text-[10px] mt-1 font-medium">Profil</span>
        </a>
    </nav>

</div>

<?php include '../layouts/footer.php'; ?>