<?php
require_once 'config/database.php';

// Jika sudah login, langsung arahkan ke halaman presensi tanpa harus login lagi
if (isset($_SESSION['pegawai_id'])) {
    header("Location: views/pegawai/dashboard.php");
    exit;
}
?>
<?php include 'views/layouts/header.php'; ?>

<main class="flex-1 flex items-center justify-center p-4">
    <div class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-sm">
        <div class="text-center mb-8">
            <!-- Icon / Logo Desa -->
            <div class="w-20 h-20 bg-blue-100 rounded-full mx-auto mb-4 flex items-center justify-center">
                <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Presensi Desa</h1>
            <p class="text-sm text-gray-500 mt-1">Silakan masuk ke akun Anda</p>
        </div>

        <!-- Menampilkan kotak peringatan merah jika login gagal -->
        <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 text-sm" role="alert">
                <strong class="font-bold">Gagal!</strong>
                <span class="block sm:inline"><?= htmlspecialchars($_GET['error']) ?></span>
            </div>
        <?php endif; ?>

        <form action="actions/login_act.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input type="text" name="username" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Masukkan username" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="••••••••" required>
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg mt-6 transition duration-200">
                Masuk
            </button>
        </form>
    </div>
</main>

<?php include 'views/layouts/footer.php'; ?>