<?php
session_start();
if (!isset($_SESSION['pegawai_id'])) {
    header("Location: ../../index.php");
    exit;
}

$nama_pegawai = $_SESSION['nama'];
?>

<?php include '../layouts/header.php'; ?>

<!-- Top Navigation -->
<header class="bg-blue-600 text-white p-4 shadow-md text-center">
    <h1 class="text-lg font-bold">Profil Akun</h1>
</header>

<main class="flex-1 p-4 pb-20 max-w-md mx-auto w-full bg-gray-50 min-h-screen">

    <!-- Info Profil -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col items-center mt-4">
        <div class="w-24 h-24 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mb-4">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
        </div>
        <h2 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($nama_pegawai) ?></h2>
        <p class="text-sm text-gray-500">Perangkat Desa</p>
    </div>

    <!-- Menu Profil -->
    <div class="mt-6 space-y-3">
        <a href="#" class="flex items-center justify-between bg-white p-4 rounded-xl border border-gray-100 shadow-sm hover:bg-gray-50">
            <div class="flex items-center text-gray-700">
                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <span class="text-sm font-medium">Ganti Password</span>
            </div>
            <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </a>

        <!-- Tombol Logout Dipindah ke Sini -->
        <a href="../../actions/logout_act.php" class="flex items-center justify-between bg-red-50 p-4 rounded-xl border border-red-100 shadow-sm hover:bg-red-100 transition">
            <div class="flex items-center text-red-600">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                <span class="text-sm font-bold">Keluar Akun</span>
            </div>
        </a>
    </div>

</main>

<!-- Bottom Navigation Bar (Profil Aktif) -->
<nav class="fixed bottom-0 w-full max-w-md mx-auto bg-white border-t border-gray-200 flex justify-around p-3 pb-safe z-50 left-0 right-0">
    <a href="dashboard.php" class="flex flex-col items-center text-gray-400 hover:text-blue-600 transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
        </svg>
        <span class="text-[10px] mt-1 font-medium">Beranda</span>
    </a>

    <a href="profil.php" class="flex flex-col items-center text-blue-600">
        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
        </svg>
        <span class="text-[10px] mt-1 font-medium">Profil</span>
    </a>
</nav>

<?php include '../layouts/footer.php'; ?>