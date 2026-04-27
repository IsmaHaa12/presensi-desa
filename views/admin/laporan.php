<?php
require_once '../../config/database.php';

// Validasi Keamanan Admin
if (!isset($_SESSION['pegawai_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../index.php?error=Akses Ditolak! Anda bukan Admin.");
    exit;
}

$nama_admin = $_SESSION['nama'];

// Ambil filter tanggal dari URL (jika tidak ada, gunakan tanggal hari ini)
// Karena format kertasnya per HARI (bukan bulanan), kita ubah filternya jadi per hari
$tanggal_filter = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

// Format tanggal untuk Kop Surat (Contoh: Rabu, 1-4-2026)
$hari = array("Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu");
$hari_ini = $hari[date("w", strtotime($tanggal_filter))];
$tanggal_kop = date("j-n-Y", strtotime($tanggal_filter)); // format: 1-4-2026

// --- 1. AMBIL SEMUA PEGAWAI BESERTA ABSENSINYA PADA TANGGAL TERSEBUT ---
$query_laporan = "
    SELECT 
        p.nama, 
        p.jabatan, 
        pr.jam_masuk, 
        pr.jam_pulang, 
        pr.status_kehadiran 
    FROM pegawai p
    LEFT JOIN presensi pr ON p.id = pr.pegawai_id AND pr.tanggal = '$tanggal_filter'
    WHERE p.role = 'pegawai'
    ORDER BY p.id ASC
";
$result_laporan = $conn->query($query_laporan);

// --- 2. HITUNG STATISTIK UNTUK BAGIAN KETERANGAN BAWAH ---
$query_stat = "SELECT 
    (SELECT COUNT(*) FROM pegawai WHERE role = 'pegawai') as total_pegawai,
    (SELECT COUNT(*) FROM presensi WHERE tanggal = '$tanggal_filter' AND status_kehadiran = 'Hadir') as hadir,
    (SELECT COUNT(*) FROM presensi WHERE tanggal = '$tanggal_filter' AND status_kehadiran = 'Izin') as izin,
    (SELECT COUNT(*) FROM presensi WHERE tanggal = '$tanggal_filter' AND status_kehadiran = 'Sakit') as sakit,
    (SELECT COUNT(*) FROM presensi WHERE tanggal = '$tanggal_filter' AND status_kehadiran = 'Cuti') as cuti,
    (SELECT COUNT(*) FROM presensi WHERE tanggal = '$tanggal_filter' AND status_kehadiran = 'Dinas Luar') as dinas
";
$stat = $conn->query($query_stat)->fetch_assoc();

$total_pegawai = $stat['total_pegawai'];
$hadir = $stat['hadir'];
$izin = $stat['izin'];
$sakit = $stat['sakit'];
$cuti = $stat['cuti'];
$dinas = $stat['dinas'];
$tanpa_keterangan = $total_pegawai - ($hadir + $izin + $sakit + $cuti + $dinas);
$tidak_hadir = $total_pegawai - $hadir;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Absensi Desa Pasir</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .main-content {
            margin-left: 16rem;
        }

        /* PENGATURAN KHUSUS CETAK PDF / KERTAS A4 */
        @media print {

            aside,
            header,
            .no-print {
                display: none !important;
            }

            .main-content {
                margin-left: 0 !important;
                padding: 0 !important;
            }

            body {
                background-color: white !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .print-area {
                width: 100%;
                margin: 0;
                padding: 0;
            }

            /* Meniru font Times New Roman resmi pemerintahan */
            .font-resmi {
                font-family: 'Times New Roman', Times, serif;
            }

            /* Border tabel hitam tegas */
            .tabel-resmi {
                border-collapse: collapse;
                width: 100%;
                margin-top: 15px;
            }

            .tabel-resmi th,
            .tabel-resmi td {
                border: 1px solid black;
                padding: 5px 8px;
                font-size: 11pt;
                color: black;
            }

            .tabel-resmi th {
                text-align: center;
            }

            /* Kop Surat */
            .garis-kop {
                border-bottom: 3px solid black;
                margin-top: 5px;
                margin-bottom: 2px;
            }

            .garis-kop-tipis {
                border-bottom: 1px solid black;
                margin-bottom: 20px;
            }
        }
    </style>
</head>

<body class="bg-gray-50 font-sans antialiased text-gray-800">

    <!-- SIDEBAR KIRI (Disembunyikan saat di-print) -->
    <aside class="w-64 bg-slate-900 h-screen fixed top-0 left-0 shadow-2xl flex flex-col z-20 no-print">
        <div class="h-16 flex items-center justify-center border-b border-slate-800 bg-slate-950">
            <svg class="w-7 h-7 text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
            <h1 class="text-white text-lg font-bold tracking-wider">PRESENSI DESA</h1>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            <a href="dashboard.php" class="flex items-center px-4 py-3 text-slate-300 hover:bg-slate-800 hover:text-white rounded-lg transition-all group">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span class="font-medium">Dashboard Utama</span>
            </a>

            <a href="pegawai.php" class="flex items-center px-4 py-3 text-slate-300 hover:bg-slate-800 hover:text-white rounded-lg transition-all group">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <span class="font-medium">Data Pegawai</span>
            </a>

            <!-- Menu Aktif -->
            <a href="laporan.php" class="flex items-center px-4 py-3 bg-blue-600 text-white rounded-lg shadow-md transition-all">
                <svg class="w-5 h-5 mr-3 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="font-medium">Rekap Laporan (Cetak)</span>
            </a>

            <a href="pengaturan.php" class="flex items-center px-4 py-3 text-slate-300 hover:bg-slate-800 hover:text-white rounded-lg transition-all group mt-6">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span class="font-medium">Pengaturan Sistem</span>
            </a>
        </nav>

        <div class="p-4 border-t border-slate-800 bg-slate-900">
            <a href="../../actions/logout_act.php" class="flex items-center justify-center px-4 py-2.5 text-red-400 hover:bg-red-500 hover:text-white rounded-lg transition-all border border-red-900 hover:border-red-500">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                <span class="font-bold text-sm uppercase tracking-wide">Logout</span>
            </a>
        </div>
    </aside>

    <!-- KONTEN KANAN -->
    <div class="main-content min-h-screen flex flex-col print-area">

        <!-- Top Navbar (Disembunyikan saat diprint) -->
        <header class="no-print bg-white h-16 shadow-sm flex items-center justify-between px-8 z-10 sticky top-0">
            <h2 class="text-xl font-bold text-slate-800">Cetak Laporan Harian (Sesuai Format Desa)</h2>
            <div class="flex items-center gap-3">
                <div class="text-right hidden md:block">
                    <p class="text-sm font-bold text-slate-800"><?= htmlspecialchars($nama_admin) ?></p>
                    <p class="text-xs text-slate-500 font-medium uppercase tracking-wide">Operator IT</p>
                </div>
            </div>
        </header>

        <!-- Area Konten Utama -->
        <main class="flex-1 p-8 print:p-4 bg-white m-8 mt-6 print:m-0 rounded-xl shadow-sm border border-slate-200 print:border-none print:shadow-none">

            <!-- Form Filter Tanggal (Sembunyi saat diprint) -->
            <div class="no-print bg-slate-50 border border-slate-200 rounded-lg p-4 mb-8 flex flex-wrap items-end justify-between gap-4">
                <form action="laporan.php" method="GET" class="flex gap-4 items-end">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wide">Pilih Tanggal Presensi</label>
                        <input type="date" name="tanggal" value="<?= $tanggal_filter ?>" class="px-4 py-2 border border-slate-300 rounded-md focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                    </div>
                    <button type="submit" class="bg-slate-800 text-white px-4 py-2 rounded-md hover:bg-slate-700 transition font-medium shadow-sm">
                        Tampilkan
                    </button>
                </form>

                <!-- Tombol Cetak PDF -->
                <button onclick="window.print()" class="flex items-center gap-2 bg-emerald-600 text-white px-4 py-2 rounded-md hover:bg-emerald-700 transition font-medium shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Cetak Kertas A4 (Format Desa)
                </button>
            </div>

            <!-- ========================================== -->
            <!-- MULAI FORMAT PDF CETAK RESMI DESA PASIR -->
            <!-- ========================================== -->
            <div class="font-resmi text-black">

                <!-- Kop Surat -->
                <div class="text-center mb-6">
                    <h3 class="text-sm font-bold uppercase m-0 leading-tight">PEMERINTAH KABUPATEN KEBUMEN</h3>
                    <h3 class="text-sm font-bold uppercase m-0 leading-tight">KECAMATAN AYAH</h3>
                    <h2 class="text-lg font-bold uppercase m-0 leading-tight">DESA PASIR</h2>
                    <p class="text-xs m-0">Jln. Karangbolong - Logending No. 212 Kecamatan Ayah Kabupaten Kebumen KP. 54473</p>
                    <p class="text-[10px] m-0 italic">website: https://pasir.kec-ayah.kebumenkab.go.id/ email: pemdespasir@gmail.com</p>
                    <div class="garis-kop"></div>
                    <div class="garis-kop-tipis"></div>
                </div>

                <!-- Judul Laporan -->
                <div class="text-center mb-6">
                    <h4 class="text-[11pt] font-bold m-0 leading-tight">DAFTAR HADIR APARATUR PEMERINTAH DESA MASUK/PULANG</h4>
                    <h4 class="text-[11pt] font-bold m-0 leading-tight">DESA PASIR KECAMATAN AYAH</h4>
                </div>

                <!-- Info Hari/Tanggal -->
                <table class="text-[11pt] font-bold mb-3 border-none">
                    <tr>
                        <td class="w-24 pb-1">HARI</td>
                        <td class="pb-1">: <?= $hari_ini ?></td>
                    </tr>
                    <tr>
                        <td>TANGGAL</td>
                        <td>: <?= $tanggal_kop ?></td>
                    </tr>
                </table>

                <!-- TABEL UTAMA PRESENSI -->
                <table class="tabel-resmi w-full mb-6">
                    <thead>
                        <tr>
                            <th class="w-10">NO.</th>
                            <th class="w-64 text-left px-2">NAMA</th>
                            <th class="w-56 text-left px-2">JABATAN</th>
                            <th class="w-24">WAKTU MASUK</th>
                            <th class="w-24">WAKTU PULANG</th>
                            <th class="w-20">KET</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if ($result_laporan->num_rows > 0):
                            while ($row = $result_laporan->fetch_assoc()):
                        ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td class="px-2"><?= htmlspecialchars(strtoupper($row['nama'])) ?></td>
                                    <td class="px-2"><?= htmlspecialchars($row['jabatan']) ?></td>

                                    <!-- Jam Masuk -->
                                    <td class="text-center">
                                        <?php if ($row['jam_masuk']) {
                                            echo date('H.i', strtotime($row['jam_masuk']));
                                        } else {
                                            echo "-";
                                        } ?>
                                    </td>

                                    <!-- Jam Pulang -->
                                    <td class="text-center">
                                        <?php if ($row['jam_pulang']) {
                                            echo date('H.i', strtotime($row['jam_pulang']));
                                        } else {
                                            echo "-";
                                        } ?>
                                    </td>

                                    <!-- Keterangan (Sakit, Izin, TK) -->
                                    <td class="text-center font-bold">
                                        <?php
                                        if ($row['status_kehadiran'] == 'Izin') echo 'I';
                                        else if ($row['status_kehadiran'] == 'Sakit') echo 'S';
                                        else if ($row['status_kehadiran'] == 'Cuti') echo 'C';
                                        else if ($row['status_kehadiran'] == 'Dinas Luar') echo 'D';
                                        else if (!$row['status_kehadiran']) echo 'TK';
                                        else echo ''; // Kosong kalau Hadir biasa
                                        ?>
                                    </td>
                                </tr>
                        <?php endwhile;
                        endif; ?>
                    </tbody>
                </table>

                <!-- BAGIAN KETERANGAN (BAWAH TABEL) -->
                <div class="flex justify-between items-start mt-8 text-[11pt]">
                    <!-- Kiri: Statistik -->
                    <div class="w-1/2">
                        <table class="border-none leading-tight mb-4">
                            <tr>
                                <td class="w-32">Jumlah</td>
                                <td>: ...... <?= $total_pegawai ?> ...... Orang</td>
                            </tr>
                            <tr>
                                <td>Hadir</td>
                                <td>: ...... <?= $hadir ?> ...... Orang</td>
                            </tr>
                            <tr>
                                <td>Tidak Hadir</td>
                                <td>: ...... <?= $tidak_hadir ?> ...... Orang</td>
                            </tr>
                        </table>

                        <p class="font-bold underline mb-1">Keterangan Tidak Hadir :</p>
                        <table class="border-none leading-tight mb-4">
                            <tr>
                                <td class="w-32">Ijin ( I )</td>
                                <td>: ...... <?= $izin ?> ...... Orang</td>
                            </tr>
                            <tr>
                                <td>Sakit ( S )</td>
                                <td>: ...... <?= $sakit ?> ...... Orang</td>
                            </tr>
                            <tr>
                                <td>Cuti ( C )</td>
                                <td>: ...... <?= $cuti ?> ...... Orang</td>
                            </tr>
                            <tr>
                                <td>Dinas ( D )</td>
                                <td>: ...... <?= $dinas ?> ...... Orang</td>
                            </tr>
                            <tr>
                                <td>Tanpa Keterangan ( TK )</td>
                                <td>: ...... <?= $tanpa_keterangan ?> ...... Orang</td>
                            </tr>
                        </table>

                        <p class="text-[9pt] italic mt-6">* Keterangan: Dinas (D) yaitu Aparatur Pemerintah Desa pada hari yang berkenaan melaksanakan perjalanan dinas dalam daerah atau luar daerah.</p>
                    </div>

                    <!-- Kanan: Tanda Tangan Kades -->
                    <div class="w-1/3 text-center mr-8 pt-8">
                        <p class="mb-1">Pasir, <?= $tanggal_kop ?></p>
                        <p class="mb-24">Kepala Desa Pasir</p>
                        <p class="font-bold underline uppercase">PURYONO</p>
                    </div>
                </div>

            </div>
            <!-- SELESAI FORMAT PDF -->

        </main>
    </div>

</body>

</html>