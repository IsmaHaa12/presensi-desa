<?php
require_once '../../config/database.php';

// Validasi Keamanan: Pastikan yang masuk beneran Admin!
if (!isset($_SESSION['pegawai_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../index.php?error=Akses Ditolak! Anda bukan Admin.");
    exit;
}

$nama_admin = $_SESSION['nama'];

// --- AMBIL SEMUA DATA PEGAWAI DARI DATABASE ---
$query_pegawai = "SELECT * FROM pegawai ORDER BY role ASC, nama ASC";
$result_pegawai = $conn->query($query_pegawai);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pegawai - Sistem Presensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .main-content {
            margin-left: 16rem;
        }
    </style>
</head>

<body class="bg-gray-50 font-sans antialiased text-gray-800">

    <!-- SIDEBAR KIRI (Sama seperti Dashboard) -->
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

            <!-- Menu Aktif -->
            <a href="pegawai.php" class="flex items-center px-4 py-3 bg-blue-600 text-white rounded-lg shadow-md transition-all">
                <svg class="w-5 h-5 mr-3 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
    <div class="main-content min-h-screen flex flex-col">
        <!-- Top Navbar -->
        <header class="bg-white h-16 shadow-sm flex items-center justify-between px-8 z-10 sticky top-0">
            <h2 class="text-xl font-bold text-slate-800">Manajemen Data Pegawai</h2>

            <div class="flex items-center gap-3">
                <div class="text-right hidden md:block">
                    <p class="text-sm font-bold text-slate-800"><?= htmlspecialchars($nama_admin) ?></p>
                    <p class="text-xs text-slate-500 font-medium uppercase tracking-wide">Operator IT</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold shadow-md">
                    AD
                </div>
            </div>
        </header>

        <!-- Area Konten Utama -->
        <main class="flex-1 p-8">

            <!-- Notifikasi Aksi -->
            <?php if (isset($_GET['msg'])): ?>
                <div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 mb-6 rounded shadow-sm" role="alert">
                    <p class="font-bold">Berhasil</p>
                    <p><?= htmlspecialchars($_GET['msg']) ?></p>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <!-- Header Tabel & Tombol Tambah -->
                <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="font-bold text-slate-800">Daftar Akun Pengguna</h3>
                    <button onclick="document.getElementById('modalTambah').classList.remove('hidden')" class="text-sm font-semibold bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition shadow-sm flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Tambah Pegawai
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider font-bold">
                                <th class="px-6 py-4 border-b border-slate-200">No</th>
                                <th class="px-6 py-4 border-b border-slate-200">Nama Lengkap</th>
                                <th class="px-6 py-4 border-b border-slate-200">Username</th>
                                <th class="px-6 py-4 border-b border-slate-200">Jabatan</th>
                                <th class="px-6 py-4 border-b border-slate-200">Role Sistem</th>
                                <th class="px-6 py-4 border-b border-slate-200 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100">
                            <?php
                            $no = 1;
                            if ($result_pegawai->num_rows > 0):
                                while ($row = $result_pegawai->fetch_assoc()):
                            ?>
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="px-6 py-4 font-medium text-slate-500"><?= $no++ ?></td>
                                        <td class="px-6 py-4 font-bold text-slate-800"><?= htmlspecialchars($row['nama']) ?></td>
                                        <td class="px-6 py-4 text-slate-600"><?= htmlspecialchars($row['username']) ?></td>
                                        <td class="px-6 py-4 text-slate-600"><?= htmlspecialchars($row['jabatan']) ?></td>
                                        <td class="px-6 py-4">
                                            <?php if ($row['role'] == 'admin'): ?>
                                                <span class="bg-indigo-100 text-indigo-700 px-2.5 py-1 rounded-md text-xs font-bold border border-indigo-200">Admin</span>
                                            <?php else: ?>
                                                <span class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-md text-xs font-bold border border-slate-200">Pegawai</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <?php if ($row['username'] != 'admin'): // Mencegah penghapusan akun super admin 
                                            ?>
                                                <form action="../../actions/pegawai_act.php" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus pegawai ini?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                    <button type="submit" class="text-rose-500 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 p-2 rounded transition" title="Hapus">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-xs text-slate-400 italic">Protected</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php
                                endwhile;
                            else:
                                ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-slate-500 italic">Data kosong.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <!-- MODAL TAMBAH PEGAWAI -->
    <div id="modalTambah" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h3 class="font-bold text-lg text-slate-800">Tambah Akun Pegawai</h3>
                <button onclick="document.getElementById('modalTambah').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form action="../../actions/pegawai_act.php" method="POST" class="p-6">
                <input type="hidden" name="action" value="add">

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="nama" required class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Username (Login)</label>
                            <input type="text" name="username" required class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Password Baru</label>
                            <input type="password" name="password" required class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Jabatan Struktural</label>
                        <input type="text" name="jabatan" placeholder="Contoh: Kepala Desa / Kaur Keuangan" required class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Role Sistem</label>
                        <select name="role" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition bg-white">
                            <option value="pegawai">Pegawai (Akses PWA Mobile)</option>
                            <option value="admin">Admin (Akses Web Dashboard)</option>
                        </select>
                    </div>
                </div>

                <div class="mt-8 flex gap-3">
                    <button type="button" onclick="document.getElementById('modalTambah').classList.add('hidden')" class="flex-1 px-4 py-2.5 border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-50 font-semibold transition">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold transition shadow-sm">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

</body>

</html>