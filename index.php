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
    <title>Masuk - Sistem Presensi Desa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-50 min-h-screen flex items-center justify-center p-6">

    <div class="w-full max-w-sm">
        <!-- Minimalist Brand Header -->
        <div class="text-center mb-8">
            <div class="w-12 h-12 bg-slate-900 text-white rounded-2xl mx-auto flex items-center justify-center shadow-md mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-slate-900">Presensi Desa</h1>
            <p class="text-sm text-slate-500 mt-1">Silakan masuk menggunakan akun Anda</p>
        </div>

        <!-- Card Container -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-8 shadow-sm">

            <?php if (!empty($error)): ?>
                <div class="mb-6 flex items-center gap-3 bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-2xl text-sm">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="mb-6 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-2xl text-sm">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <p><?= htmlspecialchars($success) ?></p>
                </div>
            <?php endif; ?>

            <form action="actions/login_act.php" method="POST" class="space-y-5">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Username</label>
                    <input
                        type="text"
                        name="username"
                        placeholder="Masukkan username"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-transparent transition"
                        required>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Password</label>
                    </div>
                    <div class="relative">
                        <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="••••••••"
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-transparent transition pr-12"
                            required>
                        <button
                            type="button"
                            onclick="togglePassword()"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition">
                            <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <button
                    type="submit"
                    class="w-full mt-2 py-3 bg-slate-900 hover:bg-slate-800 text-white font-medium text-sm rounded-xl shadow-sm transition active:scale-[0.98]">
                    Masuk
                </button>
            </form>
        </div>

        <!-- Footer Note -->
        <p class="text-center text-xs text-slate-400 mt-6">
            &copy; <?= date('Y') ?> Sistem Presensi Desa. All rights reserved.
        </p>
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