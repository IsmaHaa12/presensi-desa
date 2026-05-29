<?php
require_once '../../config/database.php';

// Validasi Keamanan: Pastikan yang masuk beneran Pegawai!
if (!isset($_SESSION['pegawai_id'])) {
    header("Location: ../../index.php?error=Silakan login terlebih dahulu.");
    exit;
}

$pegawai_id = $_SESSION['pegawai_id'];
$tanggal_hari_ini = date('Y-m-d');
$waktu_sekarang = date('H:i:s');

// --- PROSES SUBMIT FORM IZIN ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jenis_izin = $conn->real_escape_string($_POST['jenis_izin']);
    $keterangan = $conn->real_escape_string($_POST['keterangan']);

    // Cek apakah hari ini sudah absen/izin
    $cek_query = "SELECT * FROM presensi WHERE pegawai_id = '$pegawai_id' AND tanggal = '$tanggal_hari_ini'";
    $cek_result = $conn->query($cek_query);

    if ($cek_result->num_rows > 0) {
        $error = "Anda sudah mengisi presensi atau izin untuk hari ini.";
    } else {
        // Simpan ke tabel presensi
        $sql = "INSERT INTO presensi (pegawai_id, tanggal, jam_masuk, status_kehadiran, lat_masuk) 
                VALUES ('$pegawai_id', '$tanggal_hari_ini', '$waktu_sekarang', '$jenis_izin', '$keterangan')";

        if ($conn->query($sql) === TRUE) {
            $sukses = "Pengajuan " . $jenis_izin . " berhasil dikirim.";
        } else {
            $error = "Terjadi kesalahan: " . $conn->error;
        }
    }
}
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
                <h1 class="text-2xl font-bold leading-tight">Pengajuan Izin</h1>
                <p class="text-xs text-blue-100 mt-1">Ajukan izin atau sakit untuk hari ini</p>
            </div>
        </div>
    </div>

    <div class="px-5 -mt-5">
        <!-- Alert sukses -->
        <?php if (isset($sukses)): ?>
            <div class="bg-emerald-100 border border-emerald-300 text-emerald-700 px-4 py-3 rounded-2xl mb-4 text-sm font-medium flex items-start shadow-sm">
                <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span><?= htmlspecialchars($sukses) ?></span>
            </div>
        <?php endif; ?>

        <!-- Alert error -->
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-2xl mb-4 text-sm font-medium flex items-start shadow-sm">
                <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <!-- Card Form -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-100 bg-gray-50/60">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-orange-100 text-orange-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800">Form Ketidakhadiran</h2>
                        <p class="text-xs text-gray-500 mt-1">Isi data jika berhalangan hadir ke Balai Desa.</p>
                    </div>
                </div>
            </div>

            <form action="" method="POST" class="p-5 space-y-4">
                <!-- Jenis Izin -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Keterangan</label>
                    <div class="relative">
                        <select name="jenis_izin" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 outline-none focus:ring-2 focus:ring-blue-500 appearance-none transition">
                            <option value="" disabled selected>-- Pilih Keterangan --</option>
                            <option value="Sakit">Sakit (S)</option>
                            <option value="Izin">Izin (I)</option>
                            <option value="Cuti">Cuti (C)</option>
                            <option value="Dinas Luar">Dinas Luar (D)</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Keterangan -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Alasan Detail</label>
                    <textarea name="keterangan" rows="4" required placeholder="Contoh: Mengurus KK di Kecamatan / Demam berdarah" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 outline-none focus:ring-2 focus:ring-blue-500 transition resize-none"></textarea>
                </div>

                <!-- Info kecil -->
                <div class="bg-blue-50 border border-blue-100 rounded-xl p-3">
                    <p class="text-xs text-blue-700 leading-relaxed">
                        Pengajuan ini hanya bisa dilakukan satu kali dalam satu hari. Jika hari ini Anda sudah mengisi presensi atau izin, maka form tidak dapat dikirim lagi.
                    </p>
                </div>

                <!-- Tombol submit -->
                <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3.5 rounded-xl hover:bg-blue-700 transition shadow-md mt-2 flex justify-center items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                    Kirim Pengajuan
                </button>
            </form>
        </div>
    </div>
</main>

<!-- Bottom Navigation Bar -->
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