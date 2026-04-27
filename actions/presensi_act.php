<?php
// MATIKAN SEMUA ERROR DISPLAY (PENTING BIAR JSON TIDAK RUSAK!)
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once '../config/database.php';
require_once '../config/helper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['pegawai_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi habis, silakan login ulang.']);
    exit;
}

$pegawai_id = $_SESSION['pegawai_id'];
$tanggal_hari_ini = date('Y-m-d');
$waktu_sekarang = date('H:i:s');

// Tangkap data JSON dari JS
$json = file_get_contents("php://input");
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Data tidak terkirim dengan benar."]);
    exit;
}

$jenis     = $data['jenis']; // 'masuk' atau 'pulang'
$lat_user  = $data['latitude'];
$lng_user  = $data['longitude'];
$fotoBase64 = $data['foto'];

// --- 1. CEK RADIUS GPS ---
// (Batas saya bikin 999.999 meter biar kamu bisa ngetes dari rumah tanpa error)
$lat_balai = -7.761405;
$lng_balai = 109.445026;
$jarak = hitungJarak($lat_user, $lng_user, $lat_balai, $lng_balai);
$radius_maksimal = 999999;

if ($jarak > $radius_maksimal) {
    echo json_encode(["status" => "error", "message" => "Anda di luar radius Balai Desa! ($jarak meter)"]);
    exit;
}

// --- 2. UPLOAD FOTO SELFIE ---
try {
    $image_parts = explode(";base64,", $fotoBase64);
    $image_type_aux = explode("image/", $image_parts[0]);
    $image_type = $image_type_aux[1];
    $image_base64 = base64_decode($image_parts[1]);

    $fileName = $pegawai_id . '_' . $jenis . '_' . time() . '.' . $image_type;
    $folderPath = '../assets/img/uploads/';

    if (!is_dir($folderPath)) {
        mkdir($folderPath, 0777, true);
    }

    file_put_contents($folderPath . $fileName, $image_base64);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Gagal memproses foto kamera."]);
    exit;
}

// --- 3. SIMPAN KE DATABASE ---
$cek_query = "SELECT * FROM presensi WHERE pegawai_id = '$pegawai_id' AND tanggal = '$tanggal_hari_ini'";
$cek_result = $conn->query($cek_query);

if ($jenis == 'masuk') {
    if ($cek_result->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Anda sudah melakukan absen masuk hari ini!"]);
        exit;
    }

    $sql = "INSERT INTO presensi (pegawai_id, tanggal, jam_masuk, foto_masuk, lat_masuk, lng_masuk, status_kehadiran) 
            VALUES ('$pegawai_id', '$tanggal_hari_ini', '$waktu_sekarang', '$fileName', '$lat_user', '$lng_user', 'Hadir')";
} else if ($jenis == 'pulang') {
    if ($cek_result->num_rows == 0) {
        echo json_encode(["status" => "error", "message" => "Anda belum absen masuk hari ini!"]);
        exit;
    }

    $row = $cek_result->fetch_assoc();
    if ($row['jam_pulang'] != null) {
        echo json_encode(["status" => "error", "message" => "Anda sudah absen pulang hari ini!"]);
        exit;
    }

    $sql = "UPDATE presensi SET 
            jam_pulang = '$waktu_sekarang', 
            foto_pulang = '$fileName', 
            lat_pulang = '$lat_user', 
            lng_pulang = '$lng_user' 
            WHERE pegawai_id = '$pegawai_id' AND tanggal = '$tanggal_hari_ini'";
}

// EKSEKUSI FINAL
if ($conn->query($sql) === TRUE) {
    echo json_encode([
        "status" => "success",
        "message" => "Berhasil absen $jenis! Jarak Anda: $jarak Meter."
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Gagal simpan ke database."]);
}
exit; // Pastikan tidak ada output HTML nyasar setelah ini
