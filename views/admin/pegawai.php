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

<body class="bg-slate-50 font-sans antialiased text-slate-800">

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

            <!-- Menu Aktif -->
            <a href="pegawai.php" class="flex items-center px-4 py-3 bg-slate-800 text-white rounded-2xl shadow-sm transition-all">
                <svg class="w-5 h-5 mr-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

            <a href="pengaturan.php" class="flex items-center px-4 py-3 text-slate-400 hover:bg-slate-800/60 hover:text-white rounded-2xl transition-all group mt-6">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <h2 class="text-base font-bold text-slate-900">Manajemen Data Pegawai</h2>

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

            <!-- Notifikasi Aksi -->
            <?php if (isset($_GET['msg'])): ?>
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-2xl text-sm font-medium flex items-start shadow-sm" role="alert">
                    <svg class="w-5 h-5 mr-2 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span><?= htmlspecialchars($_GET['msg']) ?></span>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
                <!-- Header Tabel & Tombol Tambah -->
                <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="font-bold text-slate-900 text-sm">Daftar Akun Pengguna</h3>
                    <button onclick="document.getElementById('modalTambah').classList.remove('hidden')" class="text-xs font-medium bg-slate-900 hover:bg-slate-800 text-white px-4 py-2.5 rounded-2xl transition shadow-sm flex items-center active:scale-[0.98]">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Tambah Pegawai
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-400 text-xs uppercase tracking-wider font-semibold">
                                <th class="px-6 py-4 border-b border-slate-100">No</th>
                                <th class="px-6 py-4 border-b border-slate-100">Nama Lengkap</th>
                                <th class="px-6 py-4 border-b border-slate-100">Username</th>
                                <th class="px-6 py-4 border-b border-slate-100">Jabatan</th>
                                <th class="px-6 py-4 border-b border-slate-100">Role Sistem</th>
                                <th class="px-6 py-4 border-b border-slate-100 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100">
                            <?php
                            $no = 1;
                            if ($result_pegawai->num_rows > 0):
                                while ($row = $result_pegawai->fetch_assoc()):
                            ?>
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        <td class="px-6 py-4 font-semibold text-slate-400 text-xs"><?= $no++ ?></td>
                                        <td class="px-6 py-4 font-bold text-slate-900"><?= htmlspecialchars($row['nama']) ?></td>
                                        <td class="px-6 py-4 text-slate-600 text-xs font-medium"><?= htmlspecialchars($row['username']) ?></td>
                                        <td class="px-6 py-4 text-slate-600 text-xs"><?= htmlspecialchars($row['jabatan']) ?></td>
                                        <td class="px-6 py-4">
                                            <?php if ($row['role'] == 'admin'): ?>
                                                <span class="bg-indigo-50 text-indigo-600 border border-indigo-200 px-3 py-1 rounded-xl text-xs font-semibold">Admin</span>
                                            <?php else: ?>
                                                <span class="bg-slate-100 text-slate-600 border border-slate-200 px-3 py-1 rounded-xl text-xs font-semibold">Pegawai</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <?php if ($row['username'] != 'admin'): ?>
                                                <div class="flex items-center justify-center gap-2">
                                                    <!-- Tombol Edit -->
                                                    <button onclick="bukaModalEdit(<?= htmlspecialchars(json_encode($row)) ?>)" class="text-slate-600 hover:text-slate-900 bg-slate-50 border border-slate-200 hover:bg-slate-100 p-2 rounded-xl transition shadow-sm" title="Edit">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                    </button>

                                                    <!-- Tombol Hapus -->
                                                    <form action="../../actions/pegawai_act.php" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus pegawai ini?');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                        <button type="submit" class="text-rose-600 hover:text-rose-700 bg-rose-50 border border-rose-200 hover:bg-rose-100 p-2 rounded-xl transition shadow-sm" title="Hapus">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </div>
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
                                    <td colspan="6" class="px-6 py-8 text-center text-slate-400 italic">Data kosong.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <!-- MODAL TAMBAH PEGAWAI -->
    <div id="modalTambah" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden border border-slate-200/80">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="font-bold text-base text-slate-900">Tambah Akun Pegawai</h3>
                <button onclick="document.getElementById('modalTambah').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form action="../../actions/pegawai_act.php" method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="add">

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Nama Lengkap</label>
                    <input type="text" name="nama" required class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-700 outline-none focus:bg-white focus:ring-2 focus:ring-slate-900 focus:border-transparent transition">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Username</label>
                        <input type="text" name="username" required class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-700 outline-none focus:bg-white focus:ring-2 focus:ring-slate-900 focus:border-transparent transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Password</label>
                        <input type="password" name="password" required class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-700 outline-none focus:bg-white focus:ring-2 focus:ring-slate-900 focus:border-transparent transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Jabatan Struktural</label>
                    <input type="text" name="jabatan" placeholder="Contoh: Kepala Desa / Kaur Keuangan" required class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-700 outline-none focus:bg-white focus:ring-2 focus:ring-slate-900 focus:border-transparent transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Role Sistem</label>
                    <select name="role" class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-700 outline-none focus:bg-white focus:ring-2 focus:ring-slate-900 focus:border-transparent transition">
                        <option value="pegawai">Pegawai (Akses PWA Mobile)</option>
                        <option value="admin">Admin (Akses Web Dashboard)</option>
                    </select>
                </div>

                <div class="pt-2 flex gap-3">
                    <button type="button" onclick="document.getElementById('modalTambah').classList.add('hidden')" class="flex-1 px-4 py-3.5 bg-slate-100 text-slate-600 rounded-2xl hover:bg-slate-200 text-xs font-semibold transition">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-3.5 bg-slate-900 text-white rounded-2xl hover:bg-slate-800 text-xs font-semibold transition shadow-sm active:scale-[0.98]">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT PEGAWAI -->
    <div id="modalEdit" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden border border-slate-200/80">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="font-bold text-base text-slate-900">Edit Akun Pegawai</h3>
                <button onclick="document.getElementById('modalEdit').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form action="../../actions/pegawai_act.php" method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Nama Lengkap</label>
                    <input type="text" name="nama" id="edit_nama" required class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-700 outline-none focus:bg-white focus:ring-2 focus:ring-slate-900 focus:border-transparent transition">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Username</label>
                        <input type="text" name="username" id="edit_username" required class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-700 outline-none focus:bg-white focus:ring-2 focus:ring-slate-900 focus:border-transparent transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Password</label>
                        <input type="password" name="password" placeholder="(Opsional)" class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-700 outline-none focus:bg-white focus:ring-2 focus:ring-slate-900 focus:border-transparent transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Jabatan Struktural</label>
                    <input type="text" name="jabatan" id="edit_jabatan" required class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-700 outline-none focus:bg-white focus:ring-2 focus:ring-slate-900 focus:border-transparent transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Role Sistem</label>
                    <select name="role" id="edit_role" class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-700 outline-none focus:bg-white focus:ring-2 focus:ring-slate-900 focus:border-transparent transition">
                        <option value="pegawai">Pegawai (Akses PWA Mobile)</option>
                        <option value="admin">Admin (Akses Web Dashboard)</option>
                    </select>
                </div>

                <div class="pt-2 flex gap-3">
                    <button type="button" onclick="document.getElementById('modalEdit').classList.add('hidden')" class="flex-1 px-4 py-3.5 bg-slate-100 text-slate-600 rounded-2xl hover:bg-slate-200 text-xs font-semibold transition">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-3.5 bg-slate-900 text-white rounded-2xl hover:bg-slate-800 text-xs font-semibold transition shadow-sm active:scale-[0.98]">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script untuk memunculkan modal edit dan mengisi datanya -->
    <script>
        function bukaModalEdit(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_nama').value = data.nama;
            document.getElementById('edit_username').value = data.username;
            document.getElementById('edit_jabatan').value = data.jabatan;
            document.getElementById('edit_role').value = data.role;

            document.getElementById('modalEdit').classList.remove('hidden');
        }
    </script>
</body>

</html>