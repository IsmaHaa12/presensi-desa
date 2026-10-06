<?php
require_once '../../config/database.php';

// Validasi Keamanan Admin
if (!isset($_SESSION['pegawai_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../index.php?error=Akses Ditolak! Anda bukan Admin.");
    exit;
}

$nama_admin = $_SESSION['nama'];

// ==========================================================
// HELPER
// ==========================================================
$nama_hari  = array("Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu");
$nama_bulan = array(1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');

// Ubah status_kehadiran di database menjadi kode singkat
function kode_status($status)
{
    if (!$status) return 'TK';
    if (stripos($status, 'Izin') === 0 || stripos($status, 'Ijin') === 0) return 'I';
    if (stripos($status, 'Sakit') === 0) return 'S';
    if (stripos($status, 'Cuti') === 0) return 'C';
    if (stripos($status, 'Dinas') === 0) return 'D';
    if (stripos($status, 'Hadir') === 0 || stripos($status, 'Terlambat') === 0) return 'H';
    return '';
}

// ==========================================================
// MODE & FILTER (harian / bulanan)
// ==========================================================
$mode = (isset($_GET['mode']) && $_GET['mode'] === 'bulanan') ? 'bulanan' : 'harian';

// Filter tanggal (harian) - divalidasi supaya aman dari SQL injection
$tanggal_filter = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_filter) || !strtotime($tanggal_filter)) {
    $tanggal_filter = date('Y-m-d');
}

// Filter bulan (bulanan) format YYYY-MM
$bulan_filter = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');
if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulan_filter)) {
    $bulan_filter = date('Y-m');
}

// Default tanggal penanda tangan untuk footer laporan
$tanggal_ttd = date('d-m-Y');
$tanggal_kop = date('j-n-Y');
$hari_ini = '';
$judul_bulan = '';

// Inisialisasi variabel yang dipakai pada tampilan laporan agar tidak undefined
$info_hari = [];
$hari_kerja = 0;
$daftar_pegawai = [];
$rekap = [];
$jumlah_hari = 0;
$result_laporan = null;

$total_pegawai = 0;
$hadir = 0;
$izin = 0;
$sakit = 0;
$cuti = 0;
$dinas = 0;
$tanpa_keterangan = 0;
$tidak_hadir = 0;

// ==========================================================
// MODE HARIAN (kode asli, tidak diubah logikanya)
// ==========================================================
if ($mode === 'harian') {
    $hari_ini    = $nama_hari[date("w", strtotime($tanggal_filter))];
    $tanggal_kop = date("j-n-Y", strtotime($tanggal_filter)); // format: 1-4-2026

    // 1. Semua pegawai beserta absensinya pada tanggal tersebut
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

    // 2. Statistik untuk bagian keterangan bawah
    $query_stat = "SELECT 
        (SELECT COUNT(*) FROM pegawai WHERE role = 'pegawai') as total_pegawai,
        (SELECT COUNT(*) FROM presensi WHERE tanggal = '$tanggal_filter' AND (status_kehadiran = 'Hadir' OR status_kehadiran = 'Terlambat')) as hadir,
        (SELECT COUNT(*) FROM presensi WHERE tanggal = '$tanggal_filter' AND status_kehadiran LIKE 'Izin%') as izin,
        (SELECT COUNT(*) FROM presensi WHERE tanggal = '$tanggal_filter' AND status_kehadiran LIKE 'Sakit%') as sakit,
        (SELECT COUNT(*) FROM presensi WHERE tanggal = '$tanggal_filter' AND status_kehadiran LIKE 'Cuti%') as cuti,
        (SELECT COUNT(*) FROM presensi WHERE tanggal = '$tanggal_filter' AND status_kehadiran LIKE 'Dinas Luar%') as dinas
    ";
    $stat = $conn->query($query_stat)->fetch_assoc();

    $total_pegawai = $stat['total_pegawai'];
    $hadir = $stat['hadir'];
    $izin = $stat['izin'];
    $sakit = $stat['sakit'];
    $cuti = $stat['cuti'];
    $dinas = $stat['dinas'];
    $tanpa_keterangan = $total_pegawai - ($hadir + $izin + $sakit + $cuti + $dinas);
    if ($tanpa_keterangan < 0) $tanpa_keterangan = 0;
    $tidak_hadir = $total_pegawai - $hadir;
}

// ==========================================================
// MODE BULANAN (BARU)
// ==========================================================
if ($mode === 'bulanan') {
    $tahun_f      = (int) substr($bulan_filter, 0, 4);
    $bulan_f      = (int) substr($bulan_filter, 5, 2);
    $jumlah_hari  = (int) date('t', strtotime($bulan_filter . '-01'));
    $tgl_awal     = $bulan_filter . '-01';
    $tgl_akhir    = sprintf('%s-%02d', $bulan_filter, $jumlah_hari);
    $hari_ini_str = date('Y-m-d');

    // Daftar pegawai
    $result_pegawai = $conn->query("SELECT id, nama, jabatan FROM pegawai WHERE role = 'pegawai' ORDER BY id ASC");
    $daftar_pegawai = [];
    while ($p = $result_pegawai->fetch_assoc()) {
        $daftar_pegawai[] = $p;
    }

    // Seluruh presensi pada bulan tsb -> $presensi[pegawai_id][tanggal(1-31)] = kode
    $presensi = [];
    $result_presensi = $conn->query("SELECT pegawai_id, tanggal, status_kehadiran FROM presensi WHERE tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'");
    while ($r = $result_presensi->fetch_assoc()) {
        $d = (int) date('j', strtotime($r['tanggal']));
        $presensi[$r['pegawai_id']][$d] = kode_status($r['status_kehadiran']);
    }

    // Info tiap hari: libur (Sabtu/Minggu) atau tidak
    $info_hari = [];
    $hari_kerja = 0;
    for ($d = 1; $d <= $jumlah_hari; $d++) {
        $tgl = sprintf('%s-%02d', $bulan_filter, $d);
        $dow = (int) date('w', strtotime($tgl));
        $libur = ($dow === 0 || $dow === 6);
        $info_hari[$d] = ['tgl' => $tgl, 'libur' => $libur];
        if (!$libur) $hari_kerja++;
    }

    // Hitung rekap per pegawai
    $rekap = [];
    foreach ($daftar_pegawai as $p) {
        $pid = $p['id'];
        $baris = ['H' => 0, 'I' => 0, 'S' => 0, 'C' => 0, 'D' => 0, 'TK' => 0, 'hari' => []];
        for ($d = 1; $d <= $jumlah_hari; $d++) {
            $kode = isset($presensi[$pid][$d]) ? $presensi[$pid][$d] : null;

            if ($kode === null) {
                // Tidak ada data: TK hanya untuk hari kerja yang sudah lewat/hari ini
                if (!$info_hari[$d]['libur'] && $info_hari[$d]['tgl'] <= $hari_ini_str) {
                    $kode = 'TK';
                } else {
                    $kode = '';
                }
            }

            if ($kode !== '' && isset($baris[$kode])) $baris[$kode]++;
            $baris['hari'][$d] = $kode;
        }
        $rekap[$pid] = $baris;
    }

    $judul_bulan   = strtoupper($nama_bulan[$bulan_f]) . ' ' . $tahun_f;
    $tanggal_ttd   = $jumlah_hari . '-' . $bulan_f . '-' . $tahun_f; // akhir bulan
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $mode === 'harian' ? 'Rekap Absensi Desa Pasir' : 'Rekap Bulanan Absensi Desa Pasir' ?></title>
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

        /* Ukuran kertas: harian = A4 portrait, bulanan = A4 landscape */
        @media print {
            <?php if ($mode === 'bulanan'): ?>@page {
                size: A4 landscape;
                margin: 10mm;
            }

            <?php else: ?>@page {
                size: A4 portrait;
                margin: 12mm;
            }

            <?php endif; ?>
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

            /* Meniru font Times New Roman resmi pemerintahan khusus area cetak */
            .font-resmi {
                font-family: 'Times New Roman', Times, serif !important;
            }

            /* Border tabel hitam tegas */
            .tabel-resmi {
                border-collapse: collapse;
                width: 100%;
                margin-top: 15px;
            }

            .tabel-resmi th,
            .tabel-resmi td {
                border: 1px solid black !important;
                padding: 5px 8px;
                font-size: 11pt;
                color: black !important;
            }

            .tabel-resmi th {
                text-align: center;
            }

            /* Kop Surat */
            .garis-kop {
                border-bottom: 3px solid black !important;
                margin-top: 5px;
                margin-bottom: 2px;
            }

            .garis-kop-tipis {
                border-bottom: 1px solid black !important;
                margin-bottom: 20px;
            }

            /* Tabel rekap bulanan lebih rapat supaya muat 1 halaman landscape */
            .tabel-resmi.tabel-bulanan th,
            .tabel-resmi.tabel-bulanan td {
                padding: 2px 1px;
                font-size: 8pt;
            }

            .tabel-resmi.tabel-bulanan td.kolom-teks {
                padding: 2px 4px;
            }

            .scroll-bulanan {
                overflow: visible !important;
            }

            .sel-libur {
                background-color: #d1d5db !important;
            }
        }

        /* Tampilan layar untuk tabel bulanan */
        .tabel-bulanan th,
        .tabel-bulanan td {
            border: 1px solid #000;
            padding: 3px 2px;
            font-size: 10px;
            text-align: center;
        }

        .tabel-bulanan td.kolom-teks {
            text-align: left;
            padding: 3px 6px;
            white-space: nowrap;
        }

        .sel-libur {
            background-color: #d1d5db;
        }
    </style>
</head>

<body class="bg-slate-50 antialiased text-slate-800">

    <!-- SIDEBAR KIRI (Disembunyikan saat di-print) -->
    <aside class="w-64 bg-slate-900 h-screen fixed top-0 left-0 shadow-sm flex flex-col z-20 hidden md:flex no-print">
        <div class="h-16 flex items-center justify-center border-b border-slate-800 bg-slate-950">
            <svg class="w-6 h-6 text-white mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
            <h1 class="text-white text-sm font-bold tracking-wider">PRESENSI DESA</h1>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            <a href="dashboard.php" class="flex items-center px-4 py-3 text-slate-400 hover:bg-slate-800/60 hover:text-white rounded-2xl transition-all group">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span class="text-xs font-semibold">Dashboard Utama</span>
            </a>

            <a href="pegawai.php" class="flex items-center px-4 py-3 text-slate-400 hover:bg-slate-800/60 hover:text-white rounded-2xl transition-all group">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <span class="text-xs font-semibold">Data Pegawai</span>
            </a>

            <!-- Menu Aktif -->
            <a href="laporan.php" class="flex items-center px-4 py-3 bg-slate-800 text-white rounded-2xl shadow-sm transition-all">
                <svg class="w-5 h-5 mr-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="text-xs font-semibold">Rekap Laporan</span>
            </a>

            <a href="pengaturan.php" class="flex items-center px-4 py-3 text-slate-400 hover:bg-slate-800/60 hover:text-white rounded-2xl transition-all group mt-6">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span class="text-xs font-semibold">Pengaturan Sistem</span>
            </a>
        </nav>

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
    <div class="main-content min-h-screen flex flex-col print-area">

        <!-- Top Navbar (Disembunyikan saat diprint) -->
        <header class="no-print bg-white h-16 border-b border-slate-200/80 flex items-center justify-between px-8 z-10 sticky top-0">
            <h2 class="text-base font-bold text-slate-900">
                <?= $mode === 'harian' ? 'Cetak Laporan Harian (Format Desa)' : 'Cetak Rekap Bulanan (Format Desa)' ?>
            </h2>
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
        <main class="flex-1 p-6 md:p-8 print:p-0 bg-slate-50 print:bg-white space-y-6">

            <!-- Tab Pilihan Jenis Laporan (Sembunyi saat diprint) -->
            <div class="no-print flex gap-2">
                <a href="laporan.php?mode=harian&tanggal=<?= $tanggal_filter ?>"
                    class="px-5 py-2.5 rounded-2xl text-xs font-semibold transition <?= $mode === 'harian' ? 'bg-slate-900 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-500 hover:bg-slate-100' ?>">
                    Laporan Harian
                </a>
                <a href="laporan.php?mode=bulanan&bulan=<?= $bulan_filter ?>"
                    class="px-5 py-2.5 rounded-2xl text-xs font-semibold transition <?= $mode === 'bulanan' ? 'bg-slate-900 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-500 hover:bg-slate-100' ?>">
                    Rekap Bulanan
                </a>
            </div>

            <!-- Form Filter & Tombol Cetak (Sembunyi saat diprint) -->
            <div class="no-print bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm flex flex-wrap items-end justify-between gap-4">
                <form action="laporan.php" method="GET" class="flex flex-wrap gap-4 items-end">
                    <input type="hidden" name="mode" value="<?= $mode ?>">

                    <?php if ($mode === 'harian'): ?>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Pilih Tanggal Presensi</label>
                            <input type="date" name="tanggal" value="<?= $tanggal_filter ?>" class="px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-700 outline-none focus:bg-white focus:ring-2 focus:ring-slate-900 focus:border-transparent transition">
                        </div>
                    <?php else: ?>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Pilih Bulan Presensi</label>
                            <input type="month" name="bulan" value="<?= $bulan_filter ?>" class="px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-700 outline-none focus:bg-white focus:ring-2 focus:ring-slate-900 focus:border-transparent transition">
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="bg-slate-900 text-white px-5 py-3 rounded-2xl hover:bg-slate-800 transition font-medium text-xs shadow-sm active:scale-[0.98]">
                        Tampilkan
                    </button>
                </form>

                <!-- Tombol Cetak PDF -->
                <button onclick="window.print()" class="flex items-center gap-2 bg-emerald-600 text-white px-5 py-3 rounded-2xl hover:bg-emerald-700 transition font-medium text-xs shadow-sm active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Cetak Kertas A4 (Format Desa)
                </button>
            </div>

            <?php if ($mode === 'harian'): ?>
                <!-- ========================================== -->
                <!-- FORMAT CETAK HARIAN DESA PASIR -->
                <!-- ========================================== -->
                <div class="bg-white md:rounded-3xl md:border md:border-slate-200/80 md:shadow-sm p-8 print-area font-resmi text-black">

                    <!-- Kop Surat -->
                    <div class="text-center mb-6">
                        <h3 class="text-sm font-bold uppercase m-0 leading-tight">PEMERINTAH KABUPATEN KEBUMEN</h3>
                        <h3 class="text-sm font-bold uppercase m-0 leading-tight">KECAMATAN AYAH</h3>
                        <h2 class="text-lg font-bold uppercase m-0 leading-tight">DESA PASIR</h2>
                        <p class="text-xs m-0 font-sans">Jln. Karangbolong - Logending No. 212 Kecamatan Ayah Kabupaten Kebumen KP. 54473</p>
                        <p class="text-[10px] m-0 italic font-sans">website: https://pasir.kec-ayah.kebumenkab.go.id/ email: pemdespasir@gmail.com</p>
                        <div class="garis-kop border-b-2 border-black mt-2 mb-0.5"></div>
                        <div class="garis-kop-tipis border-b border-black mb-5"></div>
                    </div>

                    <!-- Judul Laporan -->
                    <div class="text-center mb-6">
                        <h4 class="text-[11pt] font-bold m-0 leading-tight">DAFTAR HADIR APARATUR PEMERINTAH DESA MASUK/PULANG</h4>
                        <h4 class="text-[11pt] font-bold m-0 leading-tight">DESA PASIR KECAMATAN AYAH</h4>
                    </div>

                    <!-- Info Hari/Tanggal -->
                    <table class="text-[11pt] font-bold mb-3 border-none font-sans">
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
                    <table class="tabel-resmi w-full mb-6 font-sans border-collapse">
                        <thead>
                            <tr>
                                <th class="w-10 border border-black p-2 bg-slate-100 print:bg-transparent">NO.</th>
                                <th class="w-64 text-left px-2 border border-black p-2 bg-slate-100 print:bg-transparent">NAMA</th>
                                <th class="w-56 text-left px-2 border border-black p-2 bg-slate-100 print:bg-transparent">JABATAN</th>
                                <th class="w-24 border border-black p-2 bg-slate-100 print:bg-transparent">WAKTU MASUK</th>
                                <th class="w-24 border border-black p-2 bg-slate-100 print:bg-transparent">WAKTU PULANG</th>
                                <th class="w-20 border border-black p-2 bg-slate-100 print:bg-transparent">KET</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            if ($result_laporan->num_rows > 0):
                                while ($row = $result_laporan->fetch_assoc()):
                            ?>
                                    <tr>
                                        <td class="text-center border border-black p-1.5"><?= $no++ ?></td>
                                        <td class="px-2 border border-black p-1.5"><?= htmlspecialchars(strtoupper($row['nama'])) ?></td>
                                        <td class="px-2 border border-black p-1.5"><?= htmlspecialchars($row['jabatan']) ?></td>

                                        <!-- Jam Masuk -->
                                        <td class="text-center border border-black p-1.5">
                                            <?php if ($row['jam_masuk'] && $row['jam_masuk'] != '-') {
                                                echo date('H.i', strtotime($row['jam_masuk']));
                                            } else {
                                                echo "-";
                                            } ?>
                                        </td>

                                        <!-- Jam Pulang -->
                                        <td class="text-center border border-black p-1.5">
                                            <?php if ($row['jam_pulang'] && $row['jam_pulang'] != '-') {
                                                echo date('H.i', strtotime($row['jam_pulang']));
                                            } else {
                                                echo "-";
                                            } ?>
                                        </td>

                                        <!-- Keterangan -->
                                        <td class="text-center font-bold border border-black p-1.5">
                                            <?php
                                            $status = $row['status_kehadiran'];
                                            if (!$status) {
                                                echo 'TK';
                                            } else if (stripos($status, 'Izin') === 0 || stripos($status, 'Ijin') === 0) {
                                                echo 'I';
                                            } else if (stripos($status, 'Sakit') === 0) {
                                                echo 'S';
                                            } else if (stripos($status, 'Cuti') === 0) {
                                                echo 'C';
                                            } else if (stripos($status, 'Dinas Luar') === 0 || stripos($status, 'Dinas') === 0) {
                                                echo 'D';
                                            } else {
                                                echo '';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                            <?php endwhile;
                            endif; ?>
                        </tbody>
                    </table>

                    <!-- BAGIAN KETERANGAN (BAWAH TABEL) -->
                    <div class="flex justify-between items-start mt-8 text-[11pt] font-sans">
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
                <!-- SELESAI FORMAT HARIAN -->

            <?php else: ?>
                <!-- ========================================== -->
                <!-- FORMAT CETAK REKAP BULANAN DESA PASIR -->
                <!-- ========================================== -->
                <div class="bg-white md:rounded-3xl md:border md:border-slate-200/80 md:shadow-sm p-8 print-area font-resmi text-black">

                    <!-- Kop Surat -->
                    <div class="text-center mb-6">
                        <h3 class="text-sm font-bold uppercase m-0 leading-tight">PEMERINTAH KABUPATEN KEBUMEN</h3>
                        <h3 class="text-sm font-bold uppercase m-0 leading-tight">KECAMATAN AYAH</h3>
                        <h2 class="text-lg font-bold uppercase m-0 leading-tight">DESA PASIR</h2>
                        <p class="text-xs m-0 font-sans">Jln. Karangbolong - Logending No. 212 Kecamatan Ayah Kabupaten Kebumen KP. 54473</p>
                        <p class="text-[10px] m-0 italic font-sans">website: https://pasir.kec-ayah.kebumenkab.go.id/ email: pemdespasir@gmail.com</p>
                        <div class="garis-kop border-b-2 border-black mt-2 mb-0.5"></div>
                        <div class="garis-kop-tipis border-b border-black mb-5"></div>
                    </div>

                    <!-- Judul Laporan -->
                    <div class="text-center mb-4">
                        <h4 class="text-[11pt] font-bold m-0 leading-tight">REKAPITULASI DAFTAR HADIR APARATUR PEMERINTAH DESA</h4>
                        <h4 class="text-[11pt] font-bold m-0 leading-tight">DESA PASIR KECAMATAN AYAH</h4>
                        <h4 class="text-[11pt] font-bold m-0 leading-tight">BULAN <?= $judul_bulan ?></h4>
                    </div>

                    <!-- TABEL REKAP BULANAN -->
                    <div class="scroll-bulanan overflow-x-auto">
                        <table class="tabel-resmi tabel-bulanan w-full mb-4 font-sans border-collapse">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="bg-slate-100 print:bg-transparent">NO.</th>
                                    <th rowspan="2" class="bg-slate-100 print:bg-transparent">NAMA</th>
                                    <th rowspan="2" class="bg-slate-100 print:bg-transparent">JABATAN</th>
                                    <th colspan="<?= $jumlah_hari ?>" class="bg-slate-100 print:bg-transparent">TANGGAL</th>
                                    <th colspan="6" class="bg-slate-100 print:bg-transparent">JUMLAH</th>
                                </tr>
                                <tr>
                                    <?php for ($d = 1; $d <= $jumlah_hari; $d++): ?>
                                        <th class="<?= $info_hari[$d]['libur'] ? 'sel-libur' : 'bg-slate-100 print:bg-transparent' ?>"><?= $d ?></th>
                                    <?php endfor; ?>
                                    <th class="bg-slate-100 print:bg-transparent">H</th>
                                    <th class="bg-slate-100 print:bg-transparent">I</th>
                                    <th class="bg-slate-100 print:bg-transparent">S</th>
                                    <th class="bg-slate-100 print:bg-transparent">C</th>
                                    <th class="bg-slate-100 print:bg-transparent">D</th>
                                    <th class="bg-slate-100 print:bg-transparent">TK</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                if (count($daftar_pegawai) > 0):
                                    foreach ($daftar_pegawai as $p):
                                        $r = $rekap[$p['id']];
                                ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td class="kolom-teks"><?= htmlspecialchars(strtoupper($p['nama'])) ?></td>
                                            <td class="kolom-teks"><?= htmlspecialchars($p['jabatan']) ?></td>

                                            <?php for ($d = 1; $d <= $jumlah_hari; $d++): ?>
                                                <td class="font-bold <?= ($info_hari[$d]['libur'] && $r['hari'][$d] === '') ? 'sel-libur' : '' ?>">
                                                    <?= $r['hari'][$d] ?>
                                                </td>
                                            <?php endfor; ?>

                                            <td class="font-bold"><?= $r['H'] ?></td>
                                            <td class="font-bold"><?= $r['I'] ?></td>
                                            <td class="font-bold"><?= $r['S'] ?></td>
                                            <td class="font-bold"><?= $r['C'] ?></td>
                                            <td class="font-bold"><?= $r['D'] ?></td>
                                            <td class="font-bold"><?= $r['TK'] ?></td>
                                        </tr>
                                    <?php endforeach;
                                else: ?>
                                    <tr>
                                        <td colspan="<?= 9 + (isset($jumlah_hari) ? $jumlah_hari : 0) ?>" class="py-4">Belum ada data pegawai.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- KETERANGAN & TANDA TANGAN -->
                    <div class="flex justify-between items-start mt-6 text-[10pt] font-sans">
                        <div class="w-2/3">
                            <p class="font-bold underline mb-1">Keterangan :</p>
                            <table class="border-none leading-tight mb-3">
                                <tr>
                                    <td class="w-8">H</td>
                                    <td>: Hadir (termasuk terlambat)</td>
                                </tr>
                                <tr>
                                    <td>I</td>
                                    <td>: Ijin</td>
                                </tr>
                                <tr>
                                    <td>S</td>
                                    <td>: Sakit</td>
                                </tr>
                                <tr>
                                    <td>C</td>
                                    <td>: Cuti</td>
                                </tr>
                                <tr>
                                    <td>D</td>
                                    <td>: Dinas (perjalanan dinas dalam/luar daerah)</td>
                                </tr>
                                <tr>
                                    <td>TK</td>
                                    <td>: Tanpa Keterangan</td>
                                </tr>
                            </table>
                            <p class="text-[9pt] italic">* Kolom berwarna abu-abu = hari libur (Sabtu/Minggu). Jumlah hari kerja bulan ini: <?= isset($hari_kerja) ? $hari_kerja : 0 ?> hari.</p>
                        </div>

                        <div class="w-1/3 text-center pt-2">
                            <p class="mb-1">Pasir, <?= $tanggal_ttd ?></p>
                            <p class="mb-20">Kepala Desa Pasir</p>
                            <p class="font-bold underline uppercase">PURYONO</p>
                        </div>
                    </div>

                </div>
                <!-- SELESAI FORMAT BULANAN -->
            <?php endif; ?>

        </main>
    </div>

</body>

</html>