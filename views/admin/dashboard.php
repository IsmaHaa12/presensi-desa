<?php
require_once '../../config/database.php';

// Validasi Keamanan: Pastikan yang masuk beneran Admin!
if (!isset($_SESSION['pegawai_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../index.php?error=Akses Ditolak! Anda bukan Admin.");
    exit;
}

$nama_admin = $_SESSION['nama'];
$tanggal_hari_ini = date('Y-m-d');

// --- MENGAMBIL DATA STATISTIK DARI DATABASE ---
// 1. Total Pegawai (Selain Admin)
$query_total = "SELECT COUNT(*) as total FROM pegawai WHERE role = 'pegawai'";
$total_pegawai = $conn->query($query_total)->fetch_assoc()['total'];

// 2. Total Hadir Hari Ini
$query_hadir = "SELECT COUNT(*) as total FROM presensi WHERE tanggal = '$tanggal_hari_ini' AND status_kehadiran = 'Hadir'";
$total_hadir = $conn->query($query_hadir)->fetch_assoc()['total'];

// 3. Total Izin/Sakit Hari Ini
$query_izin = "SELECT COUNT(*) as total FROM presensi WHERE tanggal = '$tanggal_hari_ini' AND status_kehadiran IN ('Izin', 'Sakit', 'Cuti', 'Dinas Luar')";
$total_izin = $conn->query($query_izin)->fetch_assoc()['total'];

// 4. Belum Absen
$belum_absen = $total_pegawai - ($total_hadir + $total_izin);

// --- MENGAMBIL DATA TABEL PRESENSI HARI INI ---
$query_tabel = "
    SELECT 
        p.nama, 
        p.jabatan, 
        pr.jam_masuk, 
        pr.jam_pulang, 
        pr.status_kehadiran,
        pr.foto_masuk,
        pr.foto_pulang
    FROM pegawai p 
    LEFT JOIN presensi pr ON p.id = pr.pegawai_id AND pr.tanggal = '$tanggal_hari_ini'
    WHERE p.role = 'pegawai'
    ORDER BY p.nama ASC
";
$result_tabel = $conn->query($query_tabel);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Sistem Presensi</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .main-content {
            margin-left: 16rem;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>

<body class="bg-slate-50 antialiased text-slate-800">

    <!-- SIDEBAR KIRI (Responsive: Hidden on mobile, toggleable or drawer can be added if needed, desktop fixed) -->
    <aside class="w-64 bg-slate-900 h-screen fixed top-0 left-0 shadow-sm flex flex-col z-20 hidden md:flex">
        <!-- Logo/Judul -->
        <div class="h-16 flex items-center justify-center border-b border-slate-800 bg-slate-950">
            <svg class="w-6 h-6 text-white mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
            <h1 class="text-white text-sm font-bold tracking-wider">PRESENSI DESA</h1>
        </div>

        <!-- Menu Navigasi -->
        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            <!-- Menu Aktif -->
            <a href="dashboard.php" class="flex items-center px-4 py-3 bg-slate-800 text-white rounded-2xl shadow-sm transition-all">
                <svg class="w-5 h-5 mr-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span class="text-xs font-semibold">Dashboard Utama</span>
            </a>

            <!-- Menu Kelola Pegawai -->
            <a href="pegawai.php" class="flex items-center px-4 py-3 text-slate-400 hover:bg-slate-800/60 hover:text-white rounded-2xl transition-all group">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <span class="text-xs font-semibold">Data Pegawai</span>
            </a>

            <!-- Menu Rekap Laporan -->
            <a href="laporan.php" class="flex items-center px-4 py-3 text-slate-400 hover:bg-slate-800/60 hover:text-white rounded-2xl transition-all group">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="text-xs font-semibold">Rekap Laporan</span>
            </a>

            <!-- Menu Pengaturan Sistem -->
            <a href="pengaturan.php" class="flex items-center px-4 py-3 text-slate-400 hover:bg-slate-800/60 hover:text-white rounded-2xl transition-all group mt-6">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span class="text-xs font-semibold">Pengaturan Sistem</span>
            </a>
        </nav>

        <!-- Tombol Logout di Bawah Sidebar -->
        <div class="p-4 border-t border-slate-800 bg-slate-900">
            <a href="../../actions/logout_act.php" class="flex items-center justify-center px-4 py-3 text-rose-400 hover:bg-rose-500/10 rounded-2xl transition-all border border-rose-900/50 hover:border-rose-500/50">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                <span class="font-semibold text-xs">Keluar Akun</span>
            </a>
        </div>
    </aside>

    <!-- KONTEN KANAN -->
    <div class="main-content min-h-screen flex flex-col">
        <!-- Top Navbar -->
        <header class="bg-white h-16 border-b border-slate-200/80 flex items-center justify-between px-8 z-10 sticky top-0">
            <h2 class="text-base font-bold text-slate-900">Dashboard Statistik</h2>

            <!-- Profil Singkat Kanan Atas -->
            <div class="flex items-center gap-3">
                <div class="text-right hidden md:block">
                    <p class="text-xs font-bold text-slate-900"><?= htmlspecialchars($nama_admin) ?></p>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Operator IT</p>
                </div>
                <div class="w-10 h-10 rounded-2xl bg-slate-900 text-white flex items-center justify-center font-bold text-xs shadow-sm">
                    AD
                </div>
            </div>
        </header>

        <!-- Area Konten Utama -->
        <main class="flex-1 p-6 md:p-8 space-y-6">

            <!-- Widget Statistik (Cards) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Card 1: Total Pegawai -->
                <div class="bg-white rounded-3xl shadow-sm p-6 border border-slate-200/80">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Total Pegawai</p>
                            <h3 class="text-2xl font-black text-slate-900"><?= $total_pegawai ?></h3>
                        </div>
                        <div class="p-3 bg-slate-100 rounded-2xl text-slate-900">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Hadir Masuk -->
                <div class="bg-white rounded-3xl shadow-sm p-6 border border-slate-200/80">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600 mb-1">Hadir (Masuk)</p>
                            <h3 class="text-2xl font-black text-emerald-600"><?= $total_hadir ?></h3>
                        </div>
                        <div class="p-3 bg-emerald-50 text-emerald-600 rounded-2xl border border-emerald-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Izin/Sakit -->
                <div class="bg-white rounded-3xl shadow-sm p-6 border border-slate-200/80">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-amber-600 mb-1">Izin / Sakit</p>
                            <h3 class="text-2xl font-black text-amber-600"><?= $total_izin ?></h3>
                        </div>
                        <div class="p-3 bg-amber-50 text-amber-600 rounded-2xl border border-amber-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Belum Absen -->
                <div class="bg-white rounded-3xl shadow-sm p-6 border border-slate-200/80">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-rose-600 mb-1">Belum Absen</p>
                            <h3 class="text-2xl font-black text-rose-600"><?= $belum_absen ?></h3>
                        </div>
                        <div class="p-3 bg-rose-50 text-rose-600 rounded-2xl border border-rose-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Data Absensi Terkini -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="font-bold text-slate-900 text-sm">Status Presensi Hari Ini (<?= date('d M Y') ?>)</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-400 text-xs uppercase tracking-wider font-semibold">
                                <th class="px-6 py-4 border-b border-slate-100">Nama Pegawai</th>
                                <th class="px-6 py-4 border-b border-slate-100">Jam Masuk</th>
                                <th class="px-6 py-4 border-b border-slate-100">Jam Pulang</th>
                                <th class="px-6 py-4 border-b border-slate-100">Status</th>
                                <th class="px-6 py-4 border-b border-slate-100 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100">

                            <?php if ($result_tabel->num_rows > 0): ?>
                                <?php while ($row = $result_tabel->fetch_assoc()): ?>
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-slate-900"><?= htmlspecialchars($row['nama']) ?></div>
                                            <div class="text-xs text-slate-400 mt-0.5"><?= htmlspecialchars($row['jabatan']) ?></div>
                                        </td>

                                        <td class="px-6 py-4 font-semibold text-slate-700 text-xs">
                                            <?= $row['jam_masuk'] ? date('H:i', strtotime($row['jam_masuk'])) . ' WIB' : '<span class="text-slate-400 font-normal italic">--:--</span>' ?>
                                        </td>

                                        <td class="px-6 py-4 font-semibold text-slate-700 text-xs">
                                            <?= $row['jam_pulang'] ? date('H:i', strtotime($row['jam_pulang'])) . ' WIB' : '<span class="text-slate-400 font-normal italic">--:--</span>' ?>
                                        </td>

                                        <td class="px-6 py-4">
                                            <?php if (!$row['status_kehadiran']): ?>
                                                <span class="bg-rose-50 text-rose-600 border border-rose-200 px-3 py-1 rounded-xl text-xs font-semibold">Belum Absen</span>
                                            <?php elseif ($row['status_kehadiran'] == 'Hadir'): ?>
                                                <span class="bg-emerald-50 text-emerald-600 border border-emerald-200 px-3 py-1 rounded-xl text-xs font-semibold">Hadir</span>
                                            <?php else: ?>
                                                <span class="bg-amber-50 text-amber-600 border border-amber-200 px-3 py-1 rounded-xl text-xs font-semibold"><?= htmlspecialchars($row['status_kehadiran']) ?></span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Kolom Aksi (Tombol FOTO) -->
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex justify-center gap-2">
                                                <!-- Jika ada foto masuk -->
                                                <?php if ($row['foto_masuk']): ?>
                                                    <a href="../../assets/img/uploads/<?= htmlspecialchars($row['foto_masuk']) ?>" target="_blank" class="text-xs font-semibold text-slate-700 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl hover:bg-slate-100 transition whitespace-nowrap shadow-sm">
                                                        FOTO MASUK
                                                    </a>
                                                <?php endif; ?>

                                                <!-- Jika ada foto pulang -->
                                                <?php if ($row['foto_pulang']): ?>
                                                    <a href="../../assets/img/uploads/<?= htmlspecialchars($row['foto_pulang']) ?>" target="_blank" class="text-xs font-semibold text-slate-700 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl hover:bg-slate-100 transition whitespace-nowrap shadow-sm">
                                                        FOTO PULANG
                                                    </a>
                                                <?php endif; ?>

                                                <!-- Jika belum ada foto sama sekali -->
                                                <?php if (!$row['foto_masuk'] && !$row['foto_pulang']): ?>
                                                    <span class="text-xs text-slate-400 italic">-</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-slate-400 italic">
                                        Belum ada data pegawai di sistem.
                                    </td>
                                </tr>
                            <?php endif; ?>

                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

</body>

</html>