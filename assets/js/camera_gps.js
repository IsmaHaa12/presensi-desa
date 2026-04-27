const video = document.getElementById('kamera');
const canvas = document.getElementById('kanvas');

let latitude = null;
let longitude = null;

// 1. Nyalakan Kamera Depan (Selfie)
async function initCamera() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: "user" } // Memaksa pakai kamera depan
        });
        video.srcObject = stream;
    } catch (err) {
        console.error("Gagal akses kamera:", err);
        alert("Gagal mengakses kamera. Pastikan browser diizinkan mengakses kamera!");
    }
}

// 2. Ambil Lokasi GPS
function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                latitude = position.coords.latitude;
                longitude = position.coords.longitude;
                
                // Ubah UI menjadi Hijau (Sukses)
                document.getElementById('statusBox').className = "mt-4 flex items-center bg-green-50 p-3 rounded-lg border border-green-100";
                document.getElementById('statusTeks').className = "text-xs text-green-700";
                document.getElementById('statusTeks').innerText = "Titik GPS ditemukan! Siap absen.";
            },
            (error) => {
                // Ubah UI menjadi Merah (Gagal)
                document.getElementById('statusBox').className = "mt-4 flex items-center bg-red-50 p-3 rounded-lg border border-red-100";
                document.getElementById('statusTeks').className = "text-xs text-red-700";
                document.getElementById('statusTeks').innerText = "Gagal akses GPS. Tolong nyalakan Lokasi!";
                alert("Harap aktifkan fitur Lokasi (GPS) di HP Anda.");
            },
            { enableHighAccuracy: true } // Minta GPS paling akurat
        );
    } else {
        alert("Browser ini tidak mendukung fitur GPS.");
    }
}

// 3. Proses Tombol Absen Ditekan
function prosesAbsen(jenis) {
    if (!latitude || !longitude) {
        alert("Tunggu sebentar, Lokasi GPS belum ditemukan!");
        return;
    }

    // Tangkap gambar dari video ke kanvas
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    const ctx = canvas.getContext('2d');

    // Supaya hasil foto tidak terbalik (mirror)
    ctx.translate(canvas.width, 0);
    ctx.scale(-1, 1);
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

    // Ubah gambar jadi teks Base64 agar mudah dikirim ke PHP
    const fotoBase64 = canvas.toDataURL('image/jpeg', 0.8);

    // Ubah tombol jadi loading
    const btn = jenis === 'masuk' ? document.getElementById('btnMasuk') : document.getElementById('btnPulang');
    const textAsli = btn.innerText;
    btn.innerText = "Memproses...";
    btn.disabled = true;

    // Kirim Data ke file PHP menggunakan AJAX/Fetch
    fetch('../../actions/presensi_act.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            jenis: jenis,
            latitude: latitude,
            longitude: longitude,
            foto: fotoBase64
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            alert(data.message); // Notifikasi sukses
            window.location.href = "../../index.php"; // Balik ke halaman awal
        } else {
            alert("Gagal: " + data.message);
            btn.innerText = textAsli;
            btn.disabled = false;
        }
    })
    .catch(err => {
        console.error(err);
        alert("Terjadi kesalahan jaringan.");
        btn.innerText = textAsli;
        btn.disabled = false;
    });
}

// Jalankan fungsi saat halaman beres di-load
window.onload = () => {
    initCamera();
    getLocation();
};