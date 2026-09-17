<?php
require_once '../config/database.php';

// Pastikan request dari POST JSON
header('Content-Type: application/json');

if (!isset($_SESSION['pegawai_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi login telah habis. Silakan login ulang.']);
    exit;
}

$pegawai_id = $_SESSION['pegawai_id'];
$tanggal_hari_ini = date('Y-m-d');
$jam_sekarang = date('H:i:s');

// Ambil input JSON dari fetch JavaScript
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['jenis']) || !isset($data['qr_data']) || !isset($data['foto'])) {
    echo json_encode(['status' => 'error', 'message' => 'Data permintaan tidak lengkap.']);
    exit;
}

$jenis = $data['jenis']; // "masuk" atau "pulang"
$qr_data = $data['qr_data'];
$foto_base64 = $data['foto'];

// 1. Validasi Token QR Code sesuai jenisnya
$token_masuk_valid = "PRESENSI_MASUK_DESA_PASIR_VALID";
$token_pulang_valid = "PRESENSI_PULANG_DESA_PASIR_VALID";

if ($jenis === 'masuk' && $qr_data !== $token_masuk_valid) {
    echo json_encode(['status' => 'error', 'message' => 'QR Code tidak valid! Silakan scan QR Code Masuk resmi dari admin.']);
    exit;
}

if ($jenis === 'pulang' && $qr_data !== $token_pulang_valid) {
    echo json_encode(['status' => 'error', 'message' => 'QR Code tidak valid! Silakan scan QR Code Pulang resmi dari admin.']);
    exit;
}

// 2. Proses simpan foto Base64 ke folder server
$folder_penyimpanan = "../uploads/presensi/";
if (!file_exists($folder_penyimpanan)) {
    mkdir($folder_penyimpanan, 0777, true);
}

// Bersihkan format base64 header
$image_parts = explode(";base64,", $foto_base64);
$image_type_aux = explode("image/", $image_parts[0]);
$image_type = $image_type_aux[1];
$image_base64 = base64_decode($image_parts[1]);

$nama_file_foto = "presensi_" . $pegawai_id . "_" . $jenis . "_" . time() . ".jpg";
$path_file = $folder_penyimpanan . $nama_file_foto;
$path_untuk_db = "uploads/presensi/" . $nama_file_foto;

file_put_contents($path_file, $image_base64);

// 3. Logika Database Berdasarkan Jenis Presensi
if ($jenis === 'masuk') {
    // Cek apakah sudah pernah absen masuk hari ini
    $cek_presensi = $conn->query("SELECT id FROM presensi WHERE pegawai_id = '$pegawai_id' AND tanggal = '$tanggal_hari_ini'");

    if ($cek_presensi->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Anda sudah melakukan presensi masuk hari ini!']);
        exit;
    }

    // Tentukan status hadir / terlambat (Misal jam masuk lewat dari 08:00 WIB dianggap terlambat)
    $jam_batas_terlambat = "08:00:00";
    $status_kehadiran = ($jam_sekarang > $jam_batas_terlambat) ? "Terlambat" : "Hadir";

    // Insert data masuk baru ke tabel presensi
    $query_insert = "INSERT INTO presensi (pegawai_id, tanggal, jam_masuk, foto_masuk, status_kehadiran) 
                     VALUES ('$pegawai_id', '$tanggal_hari_ini', '$jam_sekarang', '$path_untuk_db', '$status_kehadiran')";

    if ($conn->query($query_insert)) {
        echo json_encode(['status' => 'success', 'message' => 'Presensi masuk berhasil direkam!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan ke database: ' . $conn->error]);
    }
} else if ($jenis === 'pulang') {
    // Cek apakah sudah absen masuk hari ini (syarat absen pulang)
    $cek_presensi = $conn->query("SELECT id, jam_pulang FROM presensi WHERE pegawai_id = '$pegawai_id' AND tanggal = '$tanggal_hari_ini'");

    if ($cek_presensi->num_rows == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Anda belum melakukan presensi masuk hari ini!']);
        exit;
    }

    $row_presensi = $cek_presensi->fetch_assoc();
    if (!empty($row_presensi['jam_pulang']) && $row_presensi['jam_pulang'] != '-') {
        echo json_encode(['status' => 'error', 'message' => 'Anda sudah melakukan presensi pulang hari ini!']);
        exit;
    }

    // Update data jam pulang dan foto pulang ke baris hari ini
    $presensi_id = $row_presensi['id'];
    $query_update = "UPDATE presensi SET jam_pulang = '$jam_sekarang', foto_pulang = '$path_untuk_db' WHERE id = '$presensi_id'";

    if ($conn->query($query_update)) {
        echo json_encode(['status' => 'success', 'message' => 'Presensi pulang berhasil direkam!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui database: ' . $conn->error]);
    }
}
