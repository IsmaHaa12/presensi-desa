<?php
require_once '../../config/database.php';

// Validasi Keamanan Admin
if (!isset($_SESSION['pegawai_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../index.php?error=Akses Ditolak! Anda bukan Admin.");
    exit;
}

$nama_admin = $_SESSION['nama'];
$admin_id = $_SESSION['pegawai_id'];

// Ambil data admin terbaru
$query_admin = "SELECT * FROM pegawai WHERE id = '$admin_id' LIMIT 1";
$data_admin = $conn->query($query_admin)->fetch_assoc();

// Ambil data pengaturan sistem yang aktif (ID = 1)
$query_setting = "SELECT * FROM pengaturan_sistem WHERE id = 1 LIMIT 1";
$result_setting = $conn->query($query_setting);
if ($result_setting->num_rows > 0) {
    $setting = $result_setting->fetch_assoc();
} else {
    // Fallback jika tabel kosong
    $setting = [
        'lat_balai' => -7.761405,
        'lng_balai' => 109.445026,
        'radius_maksimal' => 50,
        'batas_akurasi' => 50
    ];
}

// Proses jika form di-submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'update_profil') {
        $nama_baru = $conn->real_escape_string($_POST['nama']);
        $username_baru = $conn->real_escape_string($_POST['username']);
        $jabatan_baru = $conn->real_escape_string($_POST['jabatan']);

        $sql_update = "UPDATE pegawai SET nama = '$nama_baru', username = '$username_baru', jabatan = '$jabatan_baru' WHERE id = '$admin_id'";
        if ($conn->query($sql_update) === TRUE) {
            $_SESSION['nama'] = $nama_baru;
            $nama_admin = $nama_baru;
            $sukses = "Informasi profil admin berhasil diperbarui.";
            $data_admin = $conn->query($query_admin)->fetch_assoc();
        } else {
            $error = "Gagal memperbarui profil: " . $conn->error;
        }
    } elseif ($action == 'update_lokasi') {
        $lat_balai = $conn->real_escape_string($_POST['lat_balai']);
        $lng_balai = $conn->real_escape_string($_POST['lng_balai']);
        $radius_maksimal = (int)$_POST['radius_maksimal'];
        $batas_akurasi = (int)$_POST['batas_akurasi'];

        // Cek apakah data pengaturan sudah ada
        $check = $conn->query("SELECT id FROM pengaturan_sistem WHERE id = 1");
        if ($check->num_rows > 0) {
            $sql_lokasi = "UPDATE pengaturan_sistem SET lat_balai = '$lat_balai', lng_balai = '$lng_balai', radius_maksimal = '$radius_maksimal', batas_akurasi = '$batas_akurasi' WHERE id = 1";
        } else {
            $sql_lokasi = "INSERT INTO pengaturan_sistem (id, lat_balai, lng_balai, radius_maksimal, batas_akurasi) VALUES (1, '$lat_balai', '$lng_balai', '$radius_maksimal', '$batas_akurasi')";
        }

        if ($conn->query($sql_lokasi) === TRUE) {
            $sukses = "Pengaturan koordinat dan radius presensi berhasil diperbarui.";
            $setting = $conn->query($query_setting)->fetch_assoc();
        } else {
            $error = "Gagal memperbarui koordinat: " . $conn->error;
        }
    } elseif ($action == 'update_password') {
        $password_lama = $_POST['password_lama'];
        $password_baru = $_POST['password_baru'];
        $konfirmasi_password = $_POST['konfirmasi_password'];

        if (md5($password_lama) !== $data_admin['password']) {
            $error = "Password lama yang Anda masukkan salah.";
        } elseif ($password_baru !== $konfirmasi_password) {
            $error = "Konfirmasi password baru tidak cocok.";
        } else {
            $password_md5 = md5($password_baru);
            $sql_pass = "UPDATE pegawai SET password = '$password_md5' WHERE id = '$admin_id'";
            if ($conn->query($sql_pass) === TRUE) {
                $sukses = "Password admin berhasil diperbarui.";
            } else {
                $error = "Gagal memperbarui password.";
            }
        }
    }
}

// Statistik tambahan
$total_pegawai_count = $conn->query("SELECT COUNT(*) as total FROM pegawai WHERE role='pegawai'")->fetch_assoc()['total'];
$total_admin_count = $conn->query("SELECT COUNT(*) as total FROM pegawai WHERE role='admin'")->fetch_assoc()['total'];
$total_presensi_count = $conn->query("SELECT COUNT(*) as total FROM presensi")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Sistem - Presensi Desa</title>
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

    <!-- SIDEBAR KIRI -->
    <aside class="w-64 bg-slate-900 h-screen fixed top-0 left-0 shadow-sm flex flex-col z-20 hidden md:flex">
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

            <a href="laporan.php" class="flex items-center px-4 py-3 text-slate-400 hover:bg-slate-800/60 hover:text-white rounded-2xl transition-all group">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="text-xs font-semibold">Rekap Laporan</span>
            </a>

            <!-- Menu Aktif -->
            <a href="pengaturan.php" class="flex items-center px-4 py-3 bg-slate-800 text-white rounded-2xl shadow-sm transition-all mt-6">
                <svg class="w-5 h-5 mr-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756.2924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
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
    <div class="main-content min-h-screen flex flex-col">
        <!-- Top Navbar -->
        <header class="bg-white h-16 border-b border-slate-200/80 flex items-center justify-between px-8 z-10 sticky top-0">
            <h2 class="text-base font-bold text-slate-900">Pengaturan Sistem & Akun</h2>
            <div class="flex items-center gap-3">
                <div class="text-right hidden md:block">
                    <p class="text-xs font-bold text-slate-900"><?= htmlspecialchars($nama_admin) ?></p>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Operator IT</p>
                </div>
                <div class="w-10 h-10 rounded-2xl bg-slate-900 text-white flex items-center justify-center font-bold text-xs shadow-sm">AD</div>
            </div>
        </header>

        <!-- Area Konten Utama -->
        <main class="flex-1 p-6 md:p-8 space-y-6">

            <?php if (isset($sukses)): ?>
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-2xl text-sm font-medium flex items-start shadow-sm" role="alert">
                    <svg class="w-5 h-5 mr-2 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span><?= htmlspecialchars($sukses) ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-2xl text-sm font-medium flex items-start shadow-sm" role="alert">
                    <svg class="w-5 h-5 mr-2 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <!-- Grid Pengaturan -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Kolom Kiri & Tengah: Edit Profil & Titik Lokasi Kantor -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Edit Profil Admin -->
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
                        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                            <h3 class="font-bold text-slate-900 text-sm">Informasi Profil Admin</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Perbarui informasi identitas akun admin Anda.</p>
                        </div>

                        <form action="" method="POST" class="p-6 space-y-4">
                            <input type="hidden" name="action" value="update_profil">

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Nama Lengkap</label>
                                <input type="text" name="nama" value="<?= htmlspecialchars($data_admin['nama']) ?>" required class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-700 outline-none focus:bg-white focus:ring-2 focus:ring-slate-900 focus:border-transparent transition">
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Username</label>
                                    <input type="text" name="username" value="<?= htmlspecialchars($data_admin['username']) ?>" required class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-700 outline-none focus:bg-white focus:ring-2 focus:ring-slate-900 focus:border-transparent transition">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Jabatan</label>
                                    <input type="text" name="jabatan" value="<?= htmlspecialchars($data_admin['jabatan'] ?? 'Administrator') ?>" required class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-700 outline-none focus:bg-white focus:ring-2 focus:ring-slate-900 focus:border-transparent transition">
                                </div>
                            </div>

                            <div class="pt-2">
                                <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-medium text-xs py-3.5 px-6 rounded-2xl shadow-sm transition active:scale-[0.98]">
                                    Simpan Perubahan Profil
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Pengaturan Koordinat GPS & Radius Kantor -->
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
                        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                            <h3 class="font-bold text-slate-900 text-sm">Titik Koordinat & Radius Kantor (Balai Desa)</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Atur titik pusat lokasi GPS dan batas radius maksimal absen pegawai.</p>
                        </div>

                        <form action="" method="POST" class="p-6 space-y-4">
                            <input type="hidden" name="action" value="update_lokasi">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Latitude Kantor</label>
                                    <input type="text" name="lat_balai" value="<?= htmlspecialchars($setting['lat_balai']) ?>" required class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-700 outline-none focus:bg-white focus:ring-2 focus:ring-slate-900 focus:border-transparent transition">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Longitude Kantor</label>
                                    <input type="text" name="lng_balai" value="<?= htmlspecialchars($setting['lng_balai']) ?>" required class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-700 outline-none focus:bg-white focus:ring-2 focus:ring-slate-900 focus:border-transparent transition">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Radius Maksimal (Meter)</label>
                                    <input type="number" name="radius_maksimal" value="<?= htmlspecialchars($setting['radius_maksimal']) ?>" required class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-700 outline-none focus:bg-white focus:ring-2 focus:ring-slate-900 focus:border-transparent transition">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Batas Akurasi GPS (Meter)</label>
                                    <input type="number" name="batas_akurasi" value="<?= htmlspecialchars($setting['batas_akurasi']) ?>" required class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-700 outline-none focus:bg-white focus:ring-2 focus:ring-slate-900 focus:border-transparent transition">
                                </div>
                            </div>

                            <div class="pt-2">
                                <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-medium text-xs py-3.5 px-6 rounded-2xl shadow-sm transition active:scale-[0.98]">
                                    Simpan Pengaturan Lokasi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Kolom Kanan: Informasi Sistem -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden flex flex-col justify-between">
                    <div>
                        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                            <h3 class="font-bold text-slate-900 text-sm">Informasi Sistem</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Ringkasan data operasional desa.</p>
                        </div>

                        <div class="p-6 space-y-4 text-sm">
                            <div class="flex justify-between items-center py-2 border-b border-slate-100">
                                <span class="text-slate-400 font-medium text-xs">Total Pegawai Terdaftar</span>
                                <span class="font-bold text-slate-900"><?= $total_pegawai_count ?> Orang</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-100">
                                <span class="text-slate-400 font-medium text-xs">Total Akun Admin</span>
                                <span class="font-bold text-slate-900"><?= $total_admin_count ?> Akun</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-100">
                                <span class="text-slate-400 font-medium text-xs">Total Log Presensi Masuk</span>
                                <span class="font-bold text-slate-900"><?= $total_presensi_count ?> Data</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-slate-400 font-medium text-xs">Versi Aplikasi</span>
                                <span class="font-bold text-slate-900">v2.1 Pro</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-slate-50/50 border-t border-slate-100">
                        <p class="text-[11px] text-slate-400 text-center leading-relaxed">
                            Sistem Presensi Pemerintah Desa Pasir &copy; <?= date('Y') ?>. Seluruh hak cipta dilindungi.
                        </p>
                    </div>
                </div>

                <!-- Ganti Password Admin (Lebar Penuh) -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden lg:col-span-3">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="font-bold text-slate-900 text-sm">Keamanan & Ganti Password</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Ubah kata sandi secara berkala untuk mengamankan panel kendali admin.</p>
                    </div>

                    <form action="" method="POST" class="p-6 space-y-4">
                        <input type="hidden" name="action" value="update_password">

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Password Lama</label>
                                <input type="password" name="password_lama" required placeholder="••••••••" class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-700 outline-none focus:bg-white focus:ring-2 focus:ring-slate-900 focus:border-transparent transition">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Password Baru</label>
                                <input type="password" name="password_baru" required placeholder="••••••••" class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-700 outline-none focus:bg-white focus:ring-2 focus:ring-slate-900 focus:border-transparent transition">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Konfirmasi Password Baru</label>
                                <input type="password" name="konfirmasi_password" required placeholder="••••••••" class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-700 outline-none focus:bg-white focus:ring-2 focus:ring-slate-900 focus:border-transparent transition">
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-medium text-xs py-3.5 px-6 rounded-2xl shadow-sm transition active:scale-[0.98]">
                                Perbarui Password
                            </button>
                        </div>
                    </form>
                </div>

            </div>

        </main>
    </div>

</body>

</html>