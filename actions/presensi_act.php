<?php
require_once '../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['pegawai_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Sesi login Anda telah habis. Silakan login kembali.'
    ]);
    exit;
}

$pegawai_id = $_SESSION['pegawai_id'];

// ===============================
// KONFIGURASI LOKASI BALAI DESA (DINAMIS DARI DATABASE)
// ===============================
$query_setting = "SELECT * FROM pengaturan_sistem LIMIT 1";
$result_setting = $conn->query($query_setting);

if ($result_setting && $result_setting->num_rows > 0) {
    $setting = $result_setting->fetch_assoc();
    define('LAT_BALAI', (float)$setting['lat_balai']);
    define('LNG_BALAI', (float)$setting['lng_balai']);
    define('RADIUS_MAKSIMAL', (int)$setting['radius_maksimal']);
    define('BATAS_AKURASI', (int)$setting['batas_akurasi']);
} else {
    // Nilai fallback (cadangan) jika tabel pengaturan kosong
    define('LAT_BALAI', -7.761405);
    define('LNG_BALAI', 109.445026);
    define('RADIUS_MAKSIMAL', 50);
    define('BATAS_AKURASI', 50);
}

// ===============================
// FUNGSI BANTU
// ===============================
function hitungJarakMeter($lat1, $lon1, $lat2, $lon2)
{
    $earthRadius = 6371000;

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat / 2) * sin($dLat / 2) +
        cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
        sin($dLon / 2) * sin($dLon / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earthRadius * $c;
}

function kirimError($pesan)
{
    echo json_encode([
        'status' => 'error',
        'message' => $pesan
    ]);
    exit;
}

function kirimSukses($pesan)
{
    echo json_encode([
        'status' => 'success',
        'message' => $pesan
    ]);
    exit;
}

// ===============================
// AMBIL DATA JSON
// ===============================
$input = json_decode(file_get_contents("php://input"), true);

if (!$input) {
    kirimError('Data tidak valid.');
}

$jenis     = isset($input['jenis']) ? trim($input['jenis']) : '';
$latitude  = isset($input['latitude']) ? (float)$input['latitude'] : 0;
$longitude = isset($input['longitude']) ? (float)$input['longitude'] : 0;
$accuracy  = isset($input['accuracy']) ? (float)$input['accuracy'] : 999;
$foto      = isset($input['foto']) ? $input['foto'] : '';

if (!in_array($jenis, ['masuk', 'pulang'])) {
    kirimError('Jenis presensi tidak valid.');
}

if (empty($latitude) || empty($longitude)) {
    kirimError('Lokasi GPS tidak ditemukan.');
}

if ($accuracy > BATAS_AKURASI) {
    kirimError('Akurasi GPS masih lemah (' . round($accuracy) . ' meter). Dekatkan ke area terbuka lalu coba lagi.');
}

$jarak = hitungJarakMeter($latitude, $longitude, LAT_BALAI, LNG_BALAI);

if ($jarak > RADIUS_MAKSIMAL) {
    kirimError('Anda berada di luar radius absensi. Jarak Anda sekitar ' . round($jarak) . ' meter dari balai desa.');
}

if (empty($foto)) {
    kirimError('Foto selfie wajib diambil.');
}

// ===============================
// VALIDASI FOTO BASE64
// ===============================
if (!preg_match('/^data:image\/(\w+);base64,/', $foto, $type)) {
    kirimError('Format foto tidak valid.');
}

$foto = substr($foto, strpos($foto, ',') + 1);
$foto = base64_decode($foto);

if ($foto === false) {
    kirimError('Gagal membaca data foto.');
}

$ekstensi = strtolower($type[1]);
if (!in_array($ekstensi, ['jpg', 'jpeg', 'png'])) {
    $ekstensi = 'jpg';
}

// ===============================
// FOLDER UPLOAD FOTO
// ===============================
$folderUpload = '../uploads/presensi/';
if (!is_dir($folderUpload)) {
    mkdir($folderUpload, 0777, true);
}

$namaFile = 'presensi_' . $pegawai_id . '_' . $jenis . '_' . date('Ymd_His') . '.' . $ekstensi;
$pathFile = $folderUpload . $namaFile;

if (!file_put_contents($pathFile, $foto)) {
    kirimError('Gagal menyimpan foto presensi.');
}

$fotoDb = 'uploads/presensi/' . $namaFile;

// ===============================
// CEK PRESENSI HARI INI
// ===============================
$tanggalHariIni = date('Y-m-d');
$jamSekarang = date('H:i:s');

$queryCek = "SELECT * FROM presensi WHERE pegawai_id = ? AND tanggal = ? LIMIT 1";
$stmtCek = $conn->prepare($queryCek);
$stmtCek->bind_param("is", $pegawai_id, $tanggalHariIni);
$stmtCek->execute();
$resultCek = $stmtCek->get_result();
$dataPresensi = $resultCek->fetch_assoc();

// ===============================
// ABSEN MASUK
// ===============================
if ($jenis === 'masuk') {
    if ($dataPresensi && !empty($dataPresensi['jam_masuk'])) {
        kirimError('Anda sudah melakukan absen masuk hari ini.');
    }

    if ($dataPresensi) {
        $queryUpdateMasuk = "UPDATE presensi 
                             SET jam_masuk = ?, 
                                 foto_masuk = ?, 
                                 lat_masuk = ?, 
                                 lng_masuk = ?, 
                                 accuracy_masuk = ?, 
                                 status_kehadiran = 'Hadir'
                             WHERE id = ?";
        $stmtUpdateMasuk = $conn->prepare($queryUpdateMasuk);

        $stmtUpdateMasuk->bind_param(
            "ssdddi",
            $jamSekarang,
            $fotoDb,
            $latitude,
            $longitude,
            $accuracy,
            $dataPresensi['id']
        );

        if ($stmtUpdateMasuk->execute()) {
            kirimSukses('Absen masuk berhasil disimpan.');
        } else {
            kirimError('Gagal menyimpan absen masuk ke database.');
        }
    } else {
        $queryInsertMasuk = "INSERT INTO presensi 
                            (pegawai_id, tanggal, jam_masuk, foto_masuk, lat_masuk, lng_masuk, accuracy_masuk, status_kehadiran)
                            VALUES (?, ?, ?, ?, ?, ?, ?, 'Hadir')";
        $stmtInsertMasuk = $conn->prepare($queryInsertMasuk);

        $stmtInsertMasuk->bind_param(
            "isssddd",
            $pegawai_id,
            $tanggalHariIni,
            $jamSekarang,
            $fotoDb,
            $latitude,
            $longitude,
            $accuracy
        );

        if ($stmtInsertMasuk->execute()) {
            kirimSukses('Absen masuk berhasil disimpan.');
        } else {
            kirimError('Gagal menyimpan absen masuk ke database.');
        }
    }
}

// ===============================
// ABSEN PULANG
// ===============================
if ($jenis === 'pulang') {
    if (!$dataPresensi || empty($dataPresensi['jam_masuk'])) {
        kirimError('Anda belum melakukan absen masuk hari ini.');
    }

    if (!empty($dataPresensi['jam_pulang'])) {
        kirimError('Anda sudah melakukan absen pulang hari ini.');
    }

    $queryUpdatePulang = "UPDATE presensi 
                          SET jam_pulang = ?, 
                              foto_pulang = ?, 
                              lat_pulang = ?, 
                              lng_pulang = ?, 
                              accuracy_pulang = ?
                          WHERE id = ?";
    $stmtUpdatePulang = $conn->prepare($queryUpdatePulang);

    $stmtUpdatePulang->bind_param(
        "ssdddi",
        $jamSekarang,
        $fotoDb,
        $latitude,
        $longitude,
        $accuracy,
        $dataPresensi['id']
    );

    if ($stmtUpdatePulang->execute()) {
        kirimSukses('Absen pulang berhasil disimpan.');
    } else {
        kirimError('Gagal menyimpan absen pulang ke database.');
    }
}

kirimError('Permintaan tidak dikenali.');
