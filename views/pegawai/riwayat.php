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
$query_hadir = "SELECT COUNT(*) as total FROM presensi WHERE pegawai_id = '$pegawai_id' AND MONTH(tanggal) = '$bulan_filter' AND YEAR(tanggal) = '$tahun_filter' AND status_kehadiran = 'Hadir'";
$total_hadir = $conn->query($query_hadir)->fetch_assoc()['total'];
?>

<?php include '../layouts/header.php'; ?>

<!-- Top Navigation -->
<header class="bg-blue-600 text-white p-4 shadow-md sticky top-0 z-20 flex items-center">
    <!-- Tombol Back -->
    <a href="dashboard.php" class="mr-3 p-1 rounded-full hover:bg-blue-700 transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
    </a>
    <h1 class="text-lg font-bold">Riwayat Presensi</h1>
</header>

<main class="flex-1 p-4 pb-24 max-w-md mx-auto w-full bg-gray-50 min-h-screen">

    <!-- Filter Bulan & Tahun -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form action="riwayat.php" method="GET" class="flex gap-3">
            <div class="flex-1">
                <select name="bulan" class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm font-semibold text-gray-700 outline-none focus:ring-2 focus:ring-blue-500 appearance-none">
                    <?php foreach ($nama_bulan as $key => $val): ?>
                        <option value="<?= $key ?>" <?= ($key == $bulan_filter) ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="w-24">
                <select name="tahun" class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm font-semibold text-gray-700 outline-none focus:ring-2 focus:ring-blue-500 appearance-none text-center">
                    <?php for ($i = date('Y') - 1; $i <= date('Y'); $i++): ?>
                        <option value="<?= $i ?>" <?= ($i == $tahun_filter) ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2.5 rounded-lg hover:bg-blue-700 transition shadow-sm flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </button>
        </form>

        <div class="mt-4 pt-4 border-t border-gray-100 flex justify-between items-center">
            <span class="text-sm text-gray-500">Total Hadir Bulan Ini:</span>
            <span class="text-sm font-bold bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full"><?= $total_hadir ?> Hari</span>
        </div>
    </div>

    <!-- List Riwayat (Card UI Mobile) -->
    <div class="space-y-4">
        <?php if ($result_riwayat->num_rows > 0): ?>
            <?php while ($row = $result_riwayat->fetch_assoc()): ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <!-- Header Card: Tanggal & Status -->
                    <div class="px-4 py-3 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded bg-blue-100 text-blue-600 flex flex-col items-center justify-center mr-3">
                                <span class="text-xs font-black leading-none"><?= date('d', strtotime($row['tanggal'])) ?></span>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-800"><?= date('l', strtotime($row['tanggal'])) ?></p>
                                <p class="text-[10px] text-gray-500"><?= date('F Y', strtotime($row['tanggal'])) ?></p>
                            </div>
                        </div>
                        <div>
                            <?php if ($row['status_kehadiran'] == 'Hadir'): ?>
                                <span class="bg-emerald-100 text-emerald-700 px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider">Hadir</span>
                            <?php else: ?>
                                <span class="bg-amber-100 text-amber-700 px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider"><?= $row['status_kehadiran'] ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Body Card: Waktu Masuk & Pulang -->
                    <div class="p-4 grid grid-cols-2 gap-4 divide-x divide-gray-100">
                        <!-- Info Masuk -->
                        <div>
                            <p class="text-[10px] text-gray-400 font-semibold uppercase mb-1">Masuk</p>
                            <?php if ($row['jam_masuk']): ?>
                                <p class="text-lg font-black text-gray-800 mb-1"><?= date('H:i', strtotime($row['jam_masuk'])) ?></p>
                                <?php if ($row['foto_masuk']): ?>
                                    <a href="../../assets/img/uploads/<?= htmlspecialchars($row['foto_masuk']) ?>" target="_blank" class="inline-flex items-center text-[10px] font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded hover:bg-blue-100 transition">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        Lihat Foto
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="text-sm font-medium text-gray-400 italic">--:--</p>
                            <?php endif; ?>
                        </div>

                        <!-- Info Pulang -->
                        <div class="pl-4">
                            <p class="text-[10px] text-gray-400 font-semibold uppercase mb-1">Pulang</p>
                            <?php if ($row['jam_pulang']): ?>
                                <p class="text-lg font-black text-gray-800 mb-1"><?= date('H:i', strtotime($row['jam_pulang'])) ?></p>
                                <?php if ($row['foto_pulang']): ?>
                                    <a href="../../assets/img/uploads/<?= htmlspecialchars($row['foto_pulang']) ?>" target="_blank" class="inline-flex items-center text-[10px] font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded hover:bg-blue-100 transition">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        Lihat Foto
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="text-sm font-medium text-gray-400 italic">--:--</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <!-- Jika tidak ada data -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center flex flex-col items-center">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
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

<!-- Bottom Navigation Bar (Mobile Style) - Tanpa yang aktif karena ini sub-menu -->
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