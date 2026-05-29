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

<body class="min-h-screen bg-[#dbeafe] flex items-center justify-center px-4 py-8">

    <div class="w-full max-w-md">
        <!-- Header -->
        <div class="mb-6 text-center">
            <h1 class="text-4xl md:text-5xl font-black text-black leading-tight">Sistem Presensi Desa</h1>
            <p class="text-black/80 text-sm mt-3 font-medium">
                Login pegawai untuk mengakses aplikasi presensi balai desa.
            </p>
        </div>

        <!-- Login Card -->
        <div class="bg-[#cfe0ff] border-[3px] border-black rounded-2xl p-6 shadow-[8px_8px_0px_#000000]">
            <div class="mb-5">
                <h2 class="text-2xl font-extrabold text-black">Login ke akun</h2>
                <p class="text-sm text-black/80 mt-1">Masukkan username dan password Anda</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="mb-4 bg-[#fecaca] border-[3px] border-black rounded-xl px-4 py-3 shadow-[4px_4px_0px_#000000]">
                    <p class="text-sm font-bold text-black"><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="mb-4 bg-[#bbf7d0] border-[3px] border-black rounded-xl px-4 py-3 shadow-[4px_4px_0px_#000000]">
                    <p class="text-sm font-bold text-black"><?= htmlspecialchars($success) ?></p>
                </div>
            <?php endif; ?>

            <form action="actions/login_act.php" method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-extrabold text-black mb-2">Username</label>
                    <input
                        type="text"
                        name="username"
                        placeholder="Masukkan username"
                        class="w-full rounded-xl border-[3px] border-black bg-white px-4 py-3 text-black placeholder:text-gray-500 outline-none focus:bg-yellow-50"
                        required>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-extrabold text-black">Password</label>
                        <button
                            type="button"
                            onclick="togglePassword()"
                            class="text-xs font-bold underline text-black hover:text-blue-700">
                            Lihat password
                        </button>
                    </div>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        placeholder="Masukkan password"
                        class="w-full rounded-xl border-[3px] border-black bg-white px-4 py-3 text-black placeholder:text-gray-500 outline-none focus:bg-yellow-50"
                        required>
                </div>

                <button
                    type="submit"
                    class="w-full rounded-xl border-[3px] border-black bg-[#4f8dfd] px-4 py-3 font-extrabold text-black shadow-[4px_4px_0px_#000000] transition hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_0px_#000000] active:translate-x-[4px] active:translate-y-[4px] active:shadow-none">
                    Login
                </button>
            </form>

            <div class="mt-5 rounded-xl border-[3px] border-black bg-white px-4 py-3 text-center shadow-[4px_4px_0px_#000000]">
                <p class="text-xs font-bold text-black">
                    Akses hanya untuk pegawai desa yang terdaftar dalam sistem.
                </p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>

</html>