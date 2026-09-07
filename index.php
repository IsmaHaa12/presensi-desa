<?php
session_start();

if (isset($_SESSION['pegawai_id'])) {
    header("Location: views/pegawai/dashboard.php");
    exit;
}

$error = isset($_GET['error']) ? $_GET['error'] : '';
$success = isset($_GET['success']) ? $_GET['success'] : '';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Presensi Desa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gradient-to-br from-blue-600 via-blue-500 to-sky-400 flex items-center justify-center px-4 py-8">

    <div class="w-full max-w-md">
        <!-- Header / Branding -->
        <div class="text-center text-white mb-6">
            <div class="w-20 h-20 mx-auto rounded-3xl bg-white/15 backdrop-blur-md border border-white/20 flex items-center justify-center shadow-lg mb-4">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold leading-tight">Sistem Presensi Desa</h1>
            <p class="text-blue-100 text-sm mt-2">Silakan login untuk melanjutkan ke aplikasi presensi pegawai desa.</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-[28px] shadow-2xl border border-white/40 overflow-hidden">
            <div class="px-6 pt-7 pb-5 text-center">
                <h2 class="text-2xl font-bold text-gray-800">Masuk Akun</h2>
                <p class="text-sm text-gray-500 mt-1">Gunakan akun pegawai yang sudah terdaftar</p>
            </div>

            <div class="px-6 pb-7">
                <?php if (!empty($error)): ?>
                    <div class="mb-4 flex items-start gap-3 bg-red-50 border border-red-100 text-red-700 px-4 py-3 rounded-2xl">
                        <svg class="w-5 h-5 mt-0.5 shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M10.29 3.86l-7.5 13A1 1 0 003.66 18h16.68a1 1 0 00.87-1.5l-7.5-13a1 1 0 00-1.74 0z"></path>
                        </svg>
                        <p class="text-sm leading-relaxed"><?= htmlspecialchars($error) ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="mb-4 flex items-start gap-3 bg-emerald-50 border border-emerald-100 text-emerald-700 px-4 py-3 rounded-2xl">
                        <svg class="w-5 h-5 mt-0.5 shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <p class="text-sm leading-relaxed"><?= htmlspecialchars($success) ?></p>
                    </div>
                <?php endif; ?>

                <form action="actions/login_act.php" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </span>
                            <input
                                type="text"
                                name="username"
                                placeholder="Masukkan username"
                                class="w-full pl-12 pr-4 py-3.5 rounded-2xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2h-1V9a5 5 0 00-10 0v2H6a2 2 0 00-2 2v6a2 2 0 002 2zm3-10V9a3 3 0 016 0v2H9z"></path>
                                </svg>
                            </span>
                            <input
                                type="password"
                                name="password"
                                id="password"
                                placeholder="Masukkan password"
                                class="w-full pl-12 pr-12 py-3.5 rounded-2xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                required>
                            <button
                                type="button"
                                onclick="togglePassword()"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600 transition">
                                <svg id="eyeOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold py-3.5 rounded-2xl shadow-lg hover:shadow-xl hover:scale-[1.01] active:scale-[0.99] transition">
                        Masuk Sekarang
                    </button>
                </form>

                <div class="mt-5 text-center">
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Akses hanya untuk pegawai yang telah terdaftar pada sistem presensi desa.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
        }
    </script>
</body>

</html>