<?php
require_once '../../config/database.php';

// Kalau belum login, lempar balik ke halaman depan
if (!isset($_SESSION['pegawai_id'])) {
    header("Location: ../../index.php?error=Silakan login terlebih dahulu.");
    exit;
}

$nama_pegawai = $_SESSION['nama'];

// Format tanggal bahasa Indonesia
$hari = array("Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu");
$bulan = array("", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");
$tanggal_sekarang = $hari[date("w")] . ", " . date("j") . " " . $bulan[date("n")] . " " . date("Y");
?>

<?php include '../layouts/header.php'; ?>

<!-- Library HTML5 QR Code Scanner CDN -->
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<!-- Wrapper utama yang responsif untuk Mobile & Desktop -->
<div class="min-h-screen bg-slate-50 flex flex-col items-center pb-24 md:pb-12">

    <!-- Navbar Khusus Desktop -->
    <header class="w-full max-w-4xl hidden md:flex items-center justify-between px-6 py-4 mt-6 bg-white border border-slate-200/80 rounded-2xl shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-slate-900 text-white rounded-xl flex items-center justify-center font-bold text-sm">
                PD
            </div>
            <div>
                <h1 class="text-sm font-bold text-slate-900">Presensi Desa</h1>
                <p class="text-[11px] text-slate-500">Panel Pegawai</p>
            </div>
        </div>

        <nav class="flex items-center gap-2">
            <a href="dashboard.php" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">Beranda</a>
            <a href="presensi.php" class="px-4 py-2 text-xs font-semibold bg-slate-900 text-white rounded-xl transition">Presensi</a>
            <a href="riwayat.php" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">Riwayat</a>
            <a href="izin.php" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">Izin</a>
            <a href="profile.php" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">Profil</a>
            <div class="h-4 w-[1px] bg-slate-200 mx-1"></div>
            <a href="../../actions/logout_act.php" class="px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 rounded-xl transition">Keluar</a>
        </nav>
    </header>

    <!-- Konten Utama -->
    <main class="w-full max-w-md md:max-w-4xl bg-white md:bg-transparent md:shadow-none md:my-6 md:rounded-3xl md:overflow-hidden min-h-screen md:min-h-0 flex flex-col relative">

        <!-- Header Banner -->
        <div class="bg-slate-900 md:rounded-3xl md:mx-0 px-6 pt-10 pb-16 md:py-10 text-white shadow-sm relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/5 rounded-full blur-2xl pointer-events-none"></div>

            <div class="flex items-center gap-4 relative z-10">
                <a href="dashboard.php" class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center border border-white/10 hover:bg-white/20 transition shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-white">Isi Presensi</h1>
                    <p class="text-xs md:text-sm text-slate-300 mt-1">Halo, <?= htmlspecialchars($nama_pegawai) ?></p>
                </div>
            </div>
        </div>

        <!-- Bagian Isi -->
        <div class="px-5 md:px-0 -mt-10 md:-mt-6 relative z-20 space-y-6">

            <!-- Card Info & Status QR Scan -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Tanggal Hari Ini</h2>
                <p class="text-base font-bold text-slate-800" id="currentDate"><?= $tanggal_sekarang ?></p>

                <div id="statusBox" class="mt-4 flex items-start bg-indigo-50 p-4 rounded-2xl border border-indigo-200">
                    <svg id="statusIcon" class="w-5 h-5 text-indigo-600 mr-3 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8H2a1 1 0 00-1 1v3a1 1 0 001 1h3m10-6h3a1 1 0 011 1v3a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1 1v3a1 1 0 001 1h3a1 1 0 001-1v-3a1 1 0 00-1-1H9z"></path>
                    </svg>
                    <div>
                        <p class="text-xs font-semibold text-slate-800" id="statusJudul">Status Pemindaian</p>
                        <p class="text-xs text-indigo-700 leading-relaxed mt-0.5" id="statusTeks">Arahkan kamera ke QR Code Kantor atau pilih gambar dari galeri.</p>
                    </div>
                </div>
            </div>

            <!-- Card Kamera Scanner QR & Opsi Galeri -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 id="kameraTitle" class="text-base font-bold text-slate-900">Scanner QR Code Kantor</h3>
                    <span id="kameraBadge" class="text-xs font-medium text-indigo-600 bg-indigo-50 px-3 py-1 rounded-xl">Aktif</span>
                </div>

                <!-- Tampilan Kotak Scanner -->
                <div class="bg-black rounded-2xl overflow-hidden aspect-[3/4] md:aspect-[16/9] relative shadow-inner flex items-center justify-center">
                    <div id="reader" class="w-full h-full object-cover"></div>

                    <!-- Kamera Selfie Tersembunyi -->
                    <video id="kamera" autoplay playsinline class="w-full h-full object-cover transform -scale-x-100 hidden"></video>
                    <canvas id="kanvas" class="hidden"></canvas>

                    <div class="absolute inset-0 border-4 border-dashed border-white/30 m-4 rounded-xl z-10 pointer-events-none"></div>
                </div>

                <!-- Tombol Alternatif: Upload QR dari Galeri -->
                <div class="pt-2">
                    <label for="qr-input-file" class="w-full flex items-center justify-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs py-3 rounded-2xl border border-slate-200 cursor-pointer transition">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Atau Pilih Gambar QR Code dari Galeri
                    </label>
                    <input type="file" id="qr-input-file" accept="image/*" class="hidden">
                </div>

                <p id="kameraInfo" class="text-xs text-slate-500 text-center">
                    Pastikan QR Code valid. Presensi dan swafoto akan terekam otomatis.
                </p>
            </div>

        </div>
    </main>

    <!-- Bottom Navigation Bar (Mobile) -->
    <nav class="fixed bottom-0 w-full max-w-md mx-auto bg-white border-t border-slate-200 flex justify-around p-3 pb-safe z-50 left-0 right-0 md:hidden shadow-lg">
        <a href="dashboard.php" class="flex flex-col items-center text-slate-400 hover:text-slate-900 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            <span class="text-[10px] mt-1 font-medium">Beranda</span>
        </a>

        <a href="profile.php" class="flex flex-col items-center text-slate-400 hover:text-slate-900 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            <span class="text-[10px] mt-1 font-medium">Profil</span>
        </a>
    </nav>

</div>

<!-- Custom Modal -->
<div id="customModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div id="modalContent" class="bg-white w-full max-w-sm rounded-3xl shadow-2xl overflow-hidden scale-95 opacity-0 transition-all duration-200">
        <div id="modalHeader" class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
            <div id="modalIconContainer" class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0">
                <svg id="modalIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"></svg>
            </div>
            <h3 id="modalTitle" class="text-base font-bold text-slate-900">Pemberitahuan</h3>
        </div>

        <div class="p-6">
            <p id="modalMessage" class="text-slate-600 text-sm leading-relaxed"></p>
        </div>

        <div class="px-6 pb-6 pt-0">
            <button id="modalButton" onclick="closeModal()" class="w-full py-3.5 rounded-2xl font-medium text-sm text-white transition shadow-sm bg-slate-900 hover:bg-slate-800">
                Oke
            </button>
        </div>
    </div>
</div>

<!-- SCRIPT JAVASCRIPT STABIL -->
<script>
    let isProcessing = false;
    const html5QrCode = new Html5Qrcode("reader");

    // 1. Jalankan Kamera Live Saat Halaman Dimuat
    window.addEventListener('load', () => {
        html5QrCode.start({
                facingMode: "environment"
            }, {
                fps: 10,
                qrbox: {
                    width: 220,
                    height: 220
                }
            },
            (decodedText, decodedResult) => {
                handleScannedData(decodedText);
            },
            (errorMessage) => {}
        ).catch(err => {
            console.error("Gagal membuka kamera:", err);
        });
    });

    // 2. Fitur Scan QR dari Galeri Menggunakan Native html5-qrcode scanFile
    document.getElementById('qr-input-file').addEventListener('change', e => {
        if (e.target.files.length === 0 || isProcessing) return;

        const imageFile = e.target.files[0];
        isProcessing = true;

        html5QrCode.stop().catch(() => {}).finally(() => {
            const tempScanner = new Html5Qrcode("reader");
            tempScanner.scanFile(imageFile, true)
                .then(decodedText => {
                    handleScannedData(decodedText);
                })
                .catch(err => {
                    isProcessing = false;
                    showModal("Gagal Membaca QR", "QR Code tidak ditemukan pada gambar tersebut. Pastikan memilih gambar yang jelas.", "error", () => {
                        window.location.reload();
                    });
                });
        });
    });

    // 3. Handler Utama Ketika QR Berhasil Terbaca
    function handleScannedData(decodedText) {
        if (isProcessing && decodedText !== "PRESENSI_DESA_PASIR_VALID") return;
        isProcessing = true;

        if (decodedText === "PRESENSI_DESA_PASIR_VALID") {
            html5QrCode.stop().catch(() => {}).finally(() => {
                document.getElementById('statusBox').className = "mt-4 flex items-start bg-emerald-50 p-4 rounded-2xl border border-emerald-200";
                document.getElementById('statusIcon').className = "w-5 h-5 text-emerald-600 mr-3 mt-0.5 shrink-0";
                document.getElementById('statusJudul').innerText = "QR Code Valid Terdeteksi!";
                document.getElementById('statusTeks').innerText = "Mengambil swafoto otomatis...";
                document.getElementById('statusTeks').className = "text-xs text-emerald-700 leading-relaxed mt-0.5";

                captureSelfieAndSend();
            });
        } else {
            isProcessing = false;
            showModal("Peringatan", "QR Code tidak dikenali atau salah! Gunakan QR Code resmi kantor.", "error");
        }
    }

    // 4. Ambil Selfie Otomatis & Kirim ke Backend PHP
    function captureSelfieAndSend() {
        navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: "user"
                },
                audio: false
            })
            .then(stream => {
                const video = document.getElementById('kamera');
                video.srcObject = stream;
                video.classList.remove('hidden');

                setTimeout(() => {
                    const canvas = document.getElementById('kanvas');
                    canvas.width = video.videoWidth || 480;
                    canvas.height = video.videoHeight || 640;

                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                    const fotoBase64 = canvas.toDataURL('image/jpeg', 0.8);

                    stream.getTracks().forEach(track => track.stop());

                    kirimDataPresensi("masuk", fotoBase64);
                }, 1000);
            })
            .catch(err => {
                console.error("Gagal mengakses kamera depan:", err);
                showModal("Error Kamera", "Tidak dapat mengakses kamera depan untuk swafoto.", "error");
                isProcessing = false;
            });
    }

    // 5. Kirim Data ke Backend (presensi_act.php)
    // 5. Kirim Data ke Backend (presensi_act.php)
    function kirimDataPresensi(jenis, fotoBase64) {
        fetch('../../actions/presensi_act.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    jenis: jenis,
                    qr_data: "PRESENSI_DESA_PASIR_VALID",
                    foto: fotoBase64
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Tampilkan modal sukses, lalu otomatis arahkan ke dashboard dalam 1.5 detik
                    showModal("Berhasil", data.message + " Mengalihkan ke beranda...", "success");
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 1500);
                } else {
                    showModal("Gagal", data.message, "error", () => {
                        window.location.reload();
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showModal("Kesalahan", "Terjadi kesalahan koneksi ke server.", "error", () => {
                    window.location.reload();
                });
            });
    }

    // Fungsi Pengatur Modal Bawaan Tema dengan Sistem Callback
    function showModal(title, message, type = 'success', callback = null) {
        const modal = document.getElementById('customModal');
        const content = document.getElementById('modalContent');
        const titleEl = document.getElementById('modalTitle');
        const msgEl = document.getElementById('modalMessage');
        const iconContainer = document.getElementById('modalIconContainer');
        const iconEl = document.getElementById('modalIcon');
        const btnEl = document.getElementById('modalButton');

        titleEl.innerText = title;
        msgEl.innerText = message;

        if (type === 'success') {
            iconContainer.className = "w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0";
            iconEl.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>';
            btnEl.className = "w-full py-3.5 rounded-2xl font-medium text-sm text-white transition shadow-sm bg-emerald-600 hover:bg-emerald-700";
        } else {
            iconContainer.className = "w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0";
            iconEl.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>';
            btnEl.className = "w-full py-3.5 rounded-2xl font-medium text-sm text-white transition shadow-sm bg-rose-600 hover:bg-rose-700";
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);

        window.modalCallback = callback;
    }

    function closeModal() {
        const modal = document.getElementById('customModal');
        const content = document.getElementById('modalContent');

        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
            if (window.modalCallback) {
                window.modalCallback();
                window.modalCallback = null;
            }
        }, 200);
    }
</script>

<?php include '../layouts/footer.php'; ?>