<?php
require_once '../../config/database.php';

// Validasi Keamanan Admin
if (!isset($_SESSION['pegawai_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../index.php?error=Akses Ditolak! Anda bukan Admin.");
    exit;
}

$nama_admin = $_SESSION['nama'];
$admin_id = $_SESSION['pegawai_id'];

// Proses jika form di-submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password_baru = md5($_POST['password_baru']);
    $sql = "UPDATE pegawai SET password = '$password_baru' WHERE id = '$admin_id'";

    if ($conn->query($sql) === TRUE) {
        $sukses = "Password admin berhasil diperbarui.";
    } else {
        $error = "Gagal memperbarui password.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Sistem - Presensi Desa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .main-content {
            margin-left: 16rem;
        }
    </style>
</head>

<body class="bg-gray-50 font-sans antialiased text-gray-800">

    <!-- SIDEBAR KIRI -->
    <aside class="w-64 bg-slate-900 h-screen fixed top-0 left-0 shadow-2xl flex flex-col z-20">
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

            <a href="laporan.php" class="flex items-center px-4 py-3 text-slate-300 hover:bg-slate-800 hover:text-white rounded-lg transition-all group">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="font-medium">Rekap Laporan</span>
            </a>

            <!-- Menu Aktif -->
            <a href="pengaturan.php" class="flex items-center px-4 py-3 bg-blue-600 text-white rounded-lg shadow-md transition-all mt-6">
                <svg class="w-5 h-5 mr-3 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
    <div class="main-content min-h-screen flex flex-col">
        <!-- Top Navbar -->
        <header class="bg-white h-16 shadow-sm flex items-center justify-between px-8 z-10 sticky top-0">
            <h2 class="text-xl font-bold text-slate-800">Pengaturan Akun Admin</h2>
            <div class="flex items-center gap-3">
                <div class="text-right hidden md:block">
                    <p class="text-sm font-bold text-slate-800"><?= htmlspecialchars($nama_admin) ?></p>
                    <p class="text-xs text-slate-500 font-medium uppercase tracking-wide">Operator IT</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold shadow-md">AD</div>
            </div>
        </header>

        <!-- Area Konten Utama -->
        <main class="flex-1 p-8">

            <?php if (isset($sukses)): ?>
                <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded-xl mb-6 shadow-sm flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <?= htmlspecialchars($sukses) ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden max-w-lg">
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="font-bold text-slate-800">Ganti Password Admin</h3>
                    <p class="text-sm text-slate-500 mt-1">Ubah kata sandi Anda secara berkala untuk menjaga keamanan.</p>
                </div>

                <form action="" method="POST" class="p-6">
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Username Admin</label>
                        <input type="text" value="admin" disabled class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-lg text-slate-500 cursor-not-allowed">
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Password Baru</label>
                        <input type="password" name="password_baru" required class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition">
                    </div>

                    <button type="submit" class="bg-blue-600 text-white font-bold py-2.5 px-6 rounded-lg hover:bg-blue-700 transition shadow-sm w-full">
                        Simpan Password Baru
                    </button>
                </form>
            </div>

        </main>
    </div>

</body>

</html>