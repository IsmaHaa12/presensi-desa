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
    AND status_kehadiran IN ('Izin', 'Sakit')
";
$total_izin = $conn->query($query_izin)->fetch_assoc()['total'] ?? 0;
?>

<?php include '../layouts/header.php'; ?>

<!-- Konten Utama -->
<main class="flex-1 pb-20 bg-gray-50 min-h-screen max-w-md mx-auto w-full relative">

    <!-- Header Biru -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-b-[2.5rem] px-5 pt-10 pb-8 text-white shadow-lg">
        <div class="flex items-center gap-3">
            <a href="dashboard.php" class="w-10 h-10 rounded-full bg-white/15 flex items-center justify-center border border-white/20 hover:bg-white/20 transition">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold leading-tight">Riwayat Presensi</h1>
                <p class="text-xs text-blue-100 mt-1">Lihat data presensi bulanan kamu</p>
            </div>
        </div>
    </div>

    <!-- Filter Floating -->
    <div class="-mt-5 px-5">
        <div class="bg-white rounded-2xl shadow-xl p-4 border border-gray-100">
            <form action="riwayat.php" method="GET" class="flex gap-2 items-center">
                <div class="flex-1">
                    <select name="bulan" class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-semibold text-gray-700 outline-none focus:ring-2 focus:ring-blue-500">
                        <?php foreach ($nama_bulan as $key => $val): ?>
                            <option value="<?= $key ?>" <?= ($key == $bulan_filter) ? 'selected' : '' ?>>
                                <?= $val ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="w-24">
                    <select name="tahun" class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-semibold text-gray-700 outline-none focus:ring-2 focus:ring-blue-500 text-center">
                        <?php for ($i = date('Y') - 1; $i <= date('Y'); $i++): ?>
                            <option value="<?= $i ?>" <?= ($i == $tahun_filter) ? 'selected' : '' ?>>
                                <?= $i ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <button type="submit" class="bg-blue-600 text-white w-11 h-11 rounded-xl hover:bg-blue-700 transition shadow-sm flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"></path>
                    </svg>
                </button>
            </form>

            <div class="grid grid-cols-2 gap-3 mt-4">
                <div class="bg-emerald-50 rounded-xl border border-emerald-100 p-3">
                    <p class="text-[11px] font-semibold text-emerald-600 mb-1">Total Hadir</p>
                    <p class="text-xl font-black text-emerald-700"><?= $total_hadir ?> <span class="text-sm font-bold">Hari</span></p>
                </div>
                <div class="bg-amber-50 rounded-xl border border-amber-100 p-3">
                    <p class="text-[11px] font-semibold text-amber-600 mb-1">Izin / Sakit</p>
                    <p class="text-xl font-black text-amber-700"><?= $total_izin ?> <span class="text-sm font-bold">Hari</span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- List Riwayat -->
    <div class="px-5 mt-6 space-y-4">
        <?php if ($result_riwayat && $result_riwayat->num_rows > 0): ?>
            <?php while ($row = $result_riwayat->fetch_assoc()): ?>
                <?php
                $hari_inggris = date('l', strtotime($row['tanggal']));
                $hari_indonesia = $nama_hari[$hari_inggris] ?? $hari_inggris;
                $tanggal_angka = date('d', strtotime($row['tanggal']));
                $bulan_tahun = $nama_bulan[date('m', strtotime($row['tanggal']))] . ' ' . date('Y', strtotime($row['tanggal']));
                ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                    <!-- Header Card -->
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between bg-gray-50/70">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex flex-col items-center justify-center">
                                <span class="text-xs font-black leading-none"><?= $tanggal_angka ?></span>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800"><?= $hari_indonesia ?></p>
                                <p class="text-[11px] text-gray-500"><?= $bulan_tahun ?></p>
                            </div>
                        </div>

                        <div>
                            <?php if ($row['status_kehadiran'] == 'Hadir'): ?>
                                <span class="bg-emerald-100 text-emerald-700 px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wide">Hadir</span>
                            <?php else: ?>
                                <span class="bg-amber-100 text-amber-700 px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wide">
                                    <?= htmlspecialchars($row['status_kehadiran']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Body Card -->
                    <div class="p-4 grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                            <p class="text-[10px] text-gray-400 font-semibold uppercase mb-1">Masuk</p>
                            <?php if (!empty($row['jam_masuk'])): ?>
                                <p class="text-lg font-black text-gray-800 mb-2"><?= date('H:i', strtotime($row['jam_masuk'])) ?></p>
                                <?php if (!empty($row['foto_masuk'])): ?>
                                    <a href="../../assets/img/uploads/<?= htmlspecialchars($row['foto_masuk']) ?>" target="_blank" class="inline-flex items-center text-[10px] font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded-lg hover:bg-blue-100 transition">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        Lihat Foto
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="text-base font-semibold text-gray-400 italic">--:--</p>
                            <?php endif; ?>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                            <p class="text-[10px] text-gray-400 font-semibold uppercase mb-1">Pulang</p>
                            <?php if (!empty($row['jam_pulang'])): ?>
                                <p class="text-lg font-black text-gray-800 mb-2"><?= date('H:i', strtotime($row['jam_pulang'])) ?></p>
                                <?php if (!empty($row['foto_pulang'])): ?>
                                    <a href="../../assets/img/uploads/<?= htmlspecialchars($row['foto_pulang']) ?>" target="_blank" class="inline-flex items-center text-[10px] font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded-lg hover:bg-blue-100 transition">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        Lihat Foto
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="text-base font-semibold text-gray-400 italic">--:--</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <p class="text-gray-500 font-medium">Belum ada riwayat absensi di bulan ini.</p>
                <p class="text-xs text-gray-400 mt-1">Silakan lakukan absensi terlebih dahulu.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Bottom Navigation -->
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

<?php include '../layouts/footer.php'; ?>