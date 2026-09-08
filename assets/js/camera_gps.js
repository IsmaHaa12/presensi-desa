const video = document.getElementById('kamera');
const canvas = document.getElementById('kanvas');

let latitude = null;
let longitude = null;
let accuracy = null;
let jarakKeBalai = null;

// ===============================
// ELEMEN UI
// ===============================
const btnMasuk = document.getElementById('btnMasuk');
const btnPulang = document.getElementById('btnPulang');
const statusBox = document.getElementById('statusBox');
const statusJudul = document.getElementById('statusJudul');
const statusTeks = document.getElementById('statusTeks');

const customModal = document.getElementById('customModal');
const modalContent = document.getElementById('modalContent');
const modalTitle = document.getElementById('modalTitle');
const modalMessage = document.getElementById('modalMessage');
const modalButton = document.getElementById('modalButton');
const modalIconContainer = document.getElementById('modalIconContainer');
const modalIcon = document.getElementById('modalIcon');

// ===============================
// FUNGSI MODAL CUSTOM
// ===============================
function showModal(type = 'info', title = 'Pemberitahuan', message = '', redirect = null) {
    modalTitle.innerText = title;
    modalMessage.innerText = message;

    modalButton.onclick = function () {
        closeModal();
        if (redirect) {
            setTimeout(() => {
                window.location.href = redirect;
            }, 250);
        }
    };

    if (type === 'success') {
        modalIconContainer.className = 'w-10 h-10 rounded-full flex items-center justify-center shrink-0 bg-emerald-100 text-emerald-600';
        modalIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>`;
        modalButton.className = 'w-full py-3 rounded-xl font-bold text-white transition shadow-sm bg-emerald-500 hover:bg-emerald-600';
    } else if (type === 'error') {
        modalIconContainer.className = 'w-10 h-10 rounded-full flex items-center justify-center shrink-0 bg-red-100 text-red-600';
        modalIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>`;
        modalButton.className = 'w-full py-3 rounded-xl font-bold text-white transition shadow-sm bg-red-500 hover:bg-red-600';
    } else if (type === 'warning') {
        modalIconContainer.className = 'w-10 h-10 rounded-full flex items-center justify-center shrink-0 bg-amber-100 text-amber-600';
        modalIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>`;
        modalButton.className = 'w-full py-3 rounded-xl font-bold text-white transition shadow-sm bg-amber-500 hover:bg-amber-600';
    } else {
        modalIconContainer.className = 'w-10 h-10 rounded-full flex items-center justify-center shrink-0 bg-blue-100 text-blue-600';
        modalIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01"></path>`;
        modalButton.className = 'w-full py-3 rounded-xl font-bold text-white transition shadow-sm bg-blue-600 hover:bg-blue-700';
    }

    customModal.classList.remove('hidden');
    customModal.classList.add('flex');

    setTimeout(() => {
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeModal() {
    modalContent.classList.remove('scale-100', 'opacity-100');
    modalContent.classList.add('scale-95', 'opacity-0');

    setTimeout(() => {
        customModal.classList.add('hidden');
        customModal.classList.remove('flex');
    }, 200);
}

if (customModal) {
    customModal.addEventListener('click', function (e) {
        if (e.target === customModal) {
            closeModal();
        }
    });
}

// ===============================
// HITUNG JARAK GPS (HAVERSINE)
// ===============================
function hitungJarakMeter(lat1, lon1, lat2, lon2) {
    const R = 6371000;
    const toRad = (deg) => deg * Math.PI / 180;

    const dLat = toRad(lat2 - lat1);
    const dLon = toRad(lon2 - lon1);

    const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
        Math.sin(dLon / 2) * Math.sin(dLon / 2);

    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
}

// ===============================
// TOMBOL STATE
// ===============================
function setButtonState(disabled) {
    if (btnMasuk) btnMasuk.disabled = disabled;
    if (btnPulang) btnPulang.disabled = disabled;
}

// ===============================
// UPDATE STATUS GPS KE UI
// ===============================
function setStatus(type, title, message) {
    if (!statusJudul || !statusTeks || !statusBox) return;

    statusJudul.innerText = title;
    statusTeks.innerText = message;

    if (type === 'success') {
        statusBox.className = "mt-4 flex items-start bg-emerald-50 p-3 rounded-xl border border-emerald-100";
    } else if (type === 'warning') {
        statusBox.className = "mt-4 flex items-start bg-amber-50 p-3 rounded-xl border border-amber-100";
    } else if (type === 'error') {
        statusBox.className = "mt-4 flex items-start bg-red-50 p-3 rounded-xl border border-red-100";
    } else {
        statusBox.className = "mt-4 flex items-start bg-blue-50 p-3 rounded-xl border border-blue-100";
    }
}

// ===============================
// INISIALISASI KAMERA
// ===============================
async function initCamera() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: "user" },
            audio: false
        });
        video.srcObject = stream;
    } catch (err) {
        console.error("Gagal akses kamera:", err);
        showModal('error', 'Kamera Gagal', 'Gagal mengakses kamera. Pastikan izin kamera sudah diaktifkan pada browser atau HP Anda.');
    }
}

// ===============================
// AMBIL LOKASI GPS
// ===============================
function getLocation() {
    if (!navigator.geolocation) {
        setStatus('error', 'GPS Tidak Didukung', 'Browser ini tidak mendukung fitur GPS.');
        setButtonState(true);
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (position) => {
            latitude = position.coords.latitude;
            longitude = position.coords.longitude;
            accuracy = position.coords.accuracy;

            jarakKeBalai = hitungJarakMeter(latitude, longitude, LAT_BALAI, LNG_BALAI);

            if (accuracy > BATAS_AKURASI) {
                setStatus(
                    'warning',
                    'GPS Kurang Akurat',
                    `Akurasi GPS masih ${Math.round(accuracy)} meter. Keluar ke area terbuka dan tunggu sampai akurasi di bawah ${BATAS_AKURASI} meter.`
                );
                setButtonState(true);
                return;
            }

            if (jarakKeBalai > RADIUS_MAKSIMAL) {
                setStatus(
                    'error',
                    'Di Luar Radius',
                    `Jarak Anda ${Math.round(jarakKeBalai)} meter dari balai desa. Maksimal radius absensi ${RADIUS_MAKSIMAL} meter.`
                );
                setButtonState(true);
                return;
            }

            setStatus(
                'success',
                'Lokasi Valid',
                `GPS siap digunakan. Jarak ${Math.round(jarakKeBalai)} meter dari balai desa, akurasi ${Math.round(accuracy)} meter. Anda sudah bisa melakukan presensi.`
            );
            setButtonState(false);
        },
        (error) => {
            console.error("GPS error:", error);
            setStatus('error', 'GPS Gagal', 'Gagal akses GPS. Tolong aktifkan lokasi di perangkat Anda.');
            setButtonState(true);

            showModal(
                'error',
                'Lokasi Tidak Tersedia',
                'Harap aktifkan fitur lokasi (GPS) di HP Anda lalu coba lagi.'
            );
        },
        {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 0
        }
    );
}

// ===============================
// PROSES ABSEN
// ===============================
function prosesAbsen(jenis) {
    if (!latitude || !longitude) {
        showModal('warning', 'Lokasi Belum Siap', 'Tunggu sebentar, lokasi GPS belum ditemukan.');
        return;
    }

    if (accuracy > BATAS_AKURASI) {
        showModal(
            'warning',
            'GPS Belum Akurat',
            `Akurasi GPS masih ${Math.round(accuracy)} meter. Silakan keluar ke area terbuka lalu tunggu sampai akurasi di bawah ${BATAS_AKURASI} meter.`
        );
        return;
    }

    if (jarakKeBalai > RADIUS_MAKSIMAL) {
        showModal(
            'error',
            'Di Luar Radius',
            `Anda berada di luar radius absensi. Jarak Anda ${Math.round(jarakKeBalai)} meter dari balai desa.`
        );
        return;
    }

    if (!video.videoWidth || !video.videoHeight) {
        showModal('warning', 'Kamera Belum Siap', 'Preview kamera belum siap. Tunggu sebentar lalu coba lagi.');
        return;
    }

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;

    const ctx = canvas.getContext('2d');
    ctx.setTransform(1, 0, 0, 1, 0, 0);
    ctx.translate(canvas.width, 0);
    ctx.scale(-1, 1);
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

    const fotoBase64 = canvas.toDataURL('image/jpeg', 0.8);

    const btn = jenis === 'masuk' ? btnMasuk : btnPulang;
    const btnLain = jenis === 'masuk' ? btnPulang : btnMasuk;
    const textAsli = btn.innerText;

    btn.innerText = 'Memproses...';
    btn.disabled = true;
    btnLain.disabled = true;

    fetch('../../actions/presensi_act.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            jenis: jenis,
            latitude: latitude,
            longitude: longitude,
            accuracy: accuracy,
            foto: fotoBase64
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showModal(
                'success',
                'Presensi Berhasil',
                data.message,
                'dashboard.php'
            );
        } else {
            showModal(
                'error',
                'Presensi Gagal',
                data.message
            );
            btn.innerText = textAsli;
            getLocation();
        }
    })
    .catch(err => {
        console.error(err);
        showModal(
            'error',
            'Kesalahan Jaringan',
            'Terjadi kesalahan saat menghubungi server. Coba lagi sebentar.'
        );
        btn.innerText = textAsli;
        getLocation();
    })
    .finally(() => {
        if (btn) {
            btn.innerText = textAsli;
        }

        if (
            jarakKeBalai !== null &&
            accuracy !== null &&
            jarakKeBalai <= RADIUS_MAKSIMAL &&
            accuracy <= BATAS_AKURASI
        ) {
            btnMasuk.disabled = false;
            btnPulang.disabled = false;
        }
    });
}

// ===============================
// LOAD AWAL
// ===============================
window.onload = () => {
    setButtonState(true);
    initCamera();
    getLocation();
};

// Refresh lokasi tiap 10 detik
setInterval(() => {
    getLocation();
}, 10000);