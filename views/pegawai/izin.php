<?php
require_once '../../config/database.php';

// Kalau belum login, lempar balik ke halaman depan
if (!isset($_SESSION['pegawai_id'])) {
    header("Location: ../../index.php?error=Silakan login terlebih dahulu.");
    exit;
}

$pegawai_id = $_SESSION['pegawai_id'];
$nama_pegawai = $_SESSION['nama'];

// Proses jika form disubmit
$error = '';
$sukses = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status_kehadiran = $_POST['status_kehadiran'] ?? ''; // Izin, Sakit, Cuti, Dinas Luar
    $alasan = $conn->real_escape_string($_POST['alasan'] ?? '');
    $tanggal_hari_ini = date('Y-m-d');

    if (empty($status_kehadiran) || empty($alasan)) {
        $error = "Semua kolom wajib diisi!";
    } else {
        // Cek apakah hari ini sudah ada presensi atau izin
        $cek = $conn->query("SELECT id FROM presensi WHERE pegawai_id = '$pegawai_id' AND tanggal = '$tanggal_hari_ini'");
        if ($cek->num_rows > 0) {
            $error = "Anda sudah melakukan presensi atau mengajukan ketidakhadiran untuk hari ini.";
        } else {
            // Simpan ke database
            $sql = "INSERT INTO presensi (pegawai_id, tanggal, status_kehadiran, jam_masuk, jam_pulang) 
                    VALUES ('$pegawai_id', '$tanggal_hari_ini', '$status_kehadiran - $alasan', NULL, NULL)";

            if ($conn->query($sql) === TRUE) {
                $sukses = "Pengajuan berhasil dikirim dan menunggu verifikasi.";
            } else {
                $error = "Gagal menyimpan pengajuan: " . $conn->error;
            }
        }
    }
}
?>

<?php include '../layouts/header.php'; ?>

<div class="min-h-screen bg-slate-50 flex flex-col items-center pb-24 md:pb-12">
    <!-- Navbar Desktop -->
    <header class="w-full max-w-4xl hidden md:flex items-center justify-between px-6 py-4 mt-6 bg-white border border-slate-200/80 rounded-2xl shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-slate-900 text-white rounded-xl flex items-center justify-center font-bold text-sm">PD</div>
            <div>
                <h1 class="text-sm font-bold text-slate-900">Presensi Desa</h1>
                <p class="text-[11px] text-slate-500">Panel Pegawai</p>
            </div>
        </div>
        <nav class="flex items-center gap-2">
            <a href="dashboard.php" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">Beranda</a>
            <a href="presensi.php" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">Presensi</a>
            <a href="riwayat.php" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">Riwayat</a>
            <a href="izin.php" class="px-4 py-2 text-xs font-semibold bg-slate-900 text-white rounded-xl transition">Izin</a>
            <a href="profile.php" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">Profil</a>
            <div class="h-4 w-[1px] bg-slate-200 mx-1"></div>
            <a href="../../actions/logout_act.php" class="px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 rounded-xl transition">Keluar</a>
        </nav>
    </header>

    <!-- Konten Utama -->
    <main class="w-full max-w-md md:max-w-4xl bg-white md:bg-transparent md:shadow-none md:my-6 md:rounded-3xl md:overflow-hidden min-h-screen md:min-h-0 flex flex-col relative">
        <!-- Header Banner -->
        <div class="bg-slate-900 md:rounded-3xl md:mx-0 px-6 pt-10 pb-16 md:py-10 text-white shadow-sm relative overflow-hidden">
            <div class="flex items-center gap-4 relative z-10">
                <a href="dashboard.php" class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center border border-white/10 hover:bg-white/20 transition shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-white">Pengajuan Ketidakhadiran</h1>
                    <p class="text-xs md:text-sm text-slate-300 mt-1">Form Izin, Sakit, Cuti, atau Dinas Luar</p>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="px-5 md:px-0 -mt-10 md:-mt-6 relative z-20 space-y-6">
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6 md:p-8">

                <?php if (!empty($error)): ?>
                    <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-2xl text-xs font-semibold">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-2xl text-xs font-semibold">
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Jenis Pengajuan</label>
                        <select name="status_kehadiran" required class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-700 outline-none focus:bg-white focus:ring-2 focus:ring-slate-900 transition">
                            <option value="">-- Pilih Status --</option>
                            <option value="Izin">Izin</option>
                            <option value="Sakit">Sakit</option>
                            <option value="Cuti">Cuti</option>
                            <option value="Dinas Luar">Dinas Luar</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Alasan / Keterangan</label>
                        <textarea name="alasan" rows="4" required placeholder="Tuliskan alasan atau keterangan lengkap..." class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-700 outline-none focus:bg-white focus:ring-2 focus:ring-slate-900 transition"></textarea>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider py-4 rounded-2xl shadow-sm transition active:scale-[0.98]">
                            Kirim Pengajuan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Bottom Nav Mobile -->
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