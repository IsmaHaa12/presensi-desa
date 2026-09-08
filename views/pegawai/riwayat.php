<?php
require_once '../../config/database.php';

// Validasi Keamanan: Pastikan yang masuk beneran Pegawai!
if (!isset($_SESSION['pegawai_id'])) {
    header("Location: ../../index.php?error=Silakan login terlebih dahulu.");
    exit;
}

$pegawai_id = $_SESSION['pegawai_id'];
$nama_pegawai = $_SESSION['nama'];

// Ambil filter bulan dan tahun (default: bulan ini)
$bulan_filter = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun_filter = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Array nama bulan buat dropdown
$nama_bulan = [
    '01' => 'Januari',
    '02' => 'Februari',
    '03' => 'Maret',
    '04' => 'April',
    '05' => 'Mei',
    '06' => 'Juni',
    '07' => 'Juli',
    '08' => 'Agustus',
    '09' => 'September',
    '10' => 'Oktober',
    '11' => 'November',
    '12' => 'Desember'
];

// Nama hari Indonesia
$nama_hari = [
    'Sunday' => 'Minggu',
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu'
];

// --- AMBIL DATA RIWAYAT ABSENSI KHUSUS PEGAWAI INI ---
$query_riwayat = "
    SELECT * FROM presensi 
    WHERE pegawai_id = '$pegawai_id' 
    AND MONTH(tanggal) = '$bulan_filter' 
    AND YEAR(tanggal) = '$tahun_filter'
    ORDER BY tanggal DESC
";
$result_riwayat = $conn->query($query_riwayat);

// Hitung total hadir bulan ini
$query_hadir = "
    SELECT COUNT(*) as total 
    FROM presensi 
    WHERE pegawai_id = '$pegawai_id' 
    AND MONTH(tanggal) = '$bulan_filter' 
    AND YEAR(tanggal) = '$tahun_filter' 
    AND status_kehadiran = 'Hadir'
";
$total_hadir = $conn->query($query_hadir)->fetch_assoc()['total'] ?? 0;

// Hitung total izin/sakit bulan ini
$query_izin = "
    SELECT COUNT(*) as total 
    FROM presensi 
    WHERE pegawai_id = '$pegawai_id' 
    AND MONTH(tanggal) = '$bulan_filter' 
    AND YEAR(tanggal) = '$tahun_filter' 
    AND status_kehadiran IN ('Izin', 'Sakit', 'Cuti', 'Dinas Luar')
";
$total_izin = $conn->query($query_izin)->fetch_assoc()['total'] ?? 0;
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
            <a href="riwayat.php" class="px-4 py-2 text-xs font-semibold bg-slate-900 text-white rounded-xl transition">Riwayat</a>
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
                    <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-white">Riwayat Presensi</h1>
                    <p class="text-xs md:text-sm text-slate-300 mt-1">Lihat data presensi bulanan kamu</p>
                </div>
            </div>
        </div>

        <!-- Bagian Konten -->
        <div class="px-5 md:px-0 -mt-10 md:-mt-6 relative z-20 space-y-6">

            <!-- Card Filter & Statistik -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6">
                <form action="riwayat.php" method="GET" class="flex gap-2 items-center">
                    <div class="flex-1">
                        <select name="bulan" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-700 outline-none focus:bg-white focus:ring-2 focus:ring-slate-900 focus:border-transparent transition">
                            <?php foreach ($nama_bulan as $key => $val): ?>
                                <option value="<?= $key ?>" <?= ($key == $bulan_filter) ? 'selected' : '' ?>>
                                    <?= $val ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="w-28">
                        <select name="tahun" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-700 outline-none focus:bg-white focus:ring-2 focus:ring-slate-900 focus:border-transparent text-center transition">
                            <?php for ($i = date('Y') - 1; $i <= date('Y'); $i++): ?>
                                <option value="<?= $i ?>" <?= ($i == $tahun_filter) ? 'selected' : '' ?>>
                                    <?= $i ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <button type="submit" class="bg-slate-900 text-white w-11 h-11 rounded-2xl hover:bg-slate-800 transition shadow-sm flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"></path>
                        </svg>
                    </button>
                </form>

                <div class="grid grid-cols-2 gap-4 mt-5">
                    <div class="bg-emerald-50 rounded-2xl border border-emerald-200 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600 mb-1">Total Hadir</p>
                        <p class="text-xl font-black text-emerald-700"><?= $total_hadir ?> <span class="text-xs font-semibold">Hari</span></p>
                    </div>
                    <div class="bg-amber-50 rounded-2xl border border-amber-200 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wider text-amber-600 mb-1">Izin / Sakit</p>
                        <p class="text-xl font-black text-amber-700"><?= $total_izin ?> <span class="text-xs font-semibold">Hari</span></p>
                    </div>
                </div>
            </div>

            <!-- List Riwayat (Responsive Grid untuk Desktop) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php if ($result_riwayat && $result_riwayat->num_rows > 0): ?>
                    <?php while ($row = $result_riwayat->fetch_assoc()): ?>
                        <?php
                        $hari_inggris = date('l', strtotime($row['tanggal']));
                        $hari_indonesia = $nama_hari[$hari_inggris] ?? $hari_inggris;
                        $tanggal_angka = date('d', strtotime($row['tanggal']));
                        $bulan_tahun = $nama_bulan[date('m', strtotime($row['tanggal']))] . ' ' . date('Y', strtotime($row['tanggal']));
                        ?>
                        <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden flex flex-col justify-between">

                            <!-- Header Card -->
                            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl bg-slate-900 text-white flex flex-col items-center justify-center font-bold text-xs">
                                        <?= $tanggal_angka ?>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-900"><?= $hari_indonesia ?></p>
                                        <p class="text-[11px] text-slate-500"><?= $bulan_tahun ?></p>
                                    </div>
                                </div>

                                <div>
                                    <?php if ($row['status_kehadiran'] == 'Hadir'): ?>
                                        <span class="bg-emerald-50 text-emerald-600 border border-emerald-200 px-3 py-1 rounded-xl text-xs font-semibold">Hadir</span>
                                    <?php else: ?>
                                        <span class="bg-amber-50 text-amber-600 border border-amber-200 px-3 py-1 rounded-xl text-xs font-semibold">
                                            <?= htmlspecialchars($row['status_kehadiran']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Body Card -->
                            <div class="p-5 grid grid-cols-2 gap-4">
                                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Masuk</p>
                                    <?php if (!empty($row['jam_masuk'])): ?>
                                        <p class="text-lg font-bold text-slate-800 mb-2"><?= date('H:i', strtotime($row['jam_masuk'])) ?></p>
                                        <?php if (!empty($row['foto_masuk'])): ?>
                                            <a href="../../assets/img/uploads/<?= htmlspecialchars($row['foto_masuk']) ?>" target="_blank" class="inline-flex items-center text-xs font-semibold text-slate-700 bg-white border border-slate-200 px-3 py-1.5 rounded-xl hover:bg-slate-100 transition shadow-sm">
                                                <svg class="w-3.5 h-3.5 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                </svg>
                                                Lihat Foto
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <p class="text-base font-semibold text-slate-400 italic">--:--</p>
                                    <?php endif; ?>
                                </div>

                                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Pulang</p>
                                    <?php if (!empty($row['jam_pulang'])): ?>
                                        <p class="text-lg font-bold text-slate-800 mb-2"><?= date('H:i', strtotime($row['jam_pulang'])) ?></p>
                                        <?php if (!empty($row['foto_pulang'])): ?>
                                            <a href="../../assets/img/uploads/<?= htmlspecialchars($row['foto_pulang']) ?>" target="_blank" class="inline-flex items-center text-xs font-semibold text-slate-700 bg-white border border-slate-200 px-3 py-1.5 rounded-xl hover:bg-slate-100 transition shadow-sm">
                                                <svg class="w-3.5 h-3.5 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                </svg>
                                                Lihat Foto
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <p class="text-base font-semibold text-slate-400 italic">--:--</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-1 md:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-200/80 p-8 text-center">
                        <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-slate-200/60">
                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <p class="text-slate-700 font-semibold text-sm">Belum ada riwayat absensi di bulan ini.</p>
                        <p class="text-xs text-slate-400 mt-1">Silakan lakukan absensi terlebih dahulu.</p>
                    </div>
                <?php endif; ?>
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

<?php include '../layouts/footer.php'; ?>