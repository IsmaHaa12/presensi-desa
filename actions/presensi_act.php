<?php
session_start();
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
// FUNGSI BANTU JSON RESPONSE
// ===============================
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
// AMBIL DATA JSON DARI FRONTEND
// ===============================
$input = json_decode(file_get_contents("php://input"), true);

if (!$input) {
    kirimError('Data tidak valid.');
}

$jenis   = isset($input['jenis']) ? trim($input['jenis']) : ''; // 'masuk' atau 'pulang'
$qr_data = isset($input['qr_data']) ? trim($input['qr_data']) : '';
$foto    = isset($input['foto']) ? $input['foto'] : '';

// 1. Validasi Jenis Presensi
if (!in_array($jenis, ['masuk', 'pulang'])) {
    kirimError('Jenis presensi tidak valid.');
}

// 2. Validasi Isi QR Code Statis
if ($qr_data !== "PRESENSI_DESA_PASIR_VALID") {
    kirimError('QR Code tidak valid! Silakan scan QR Code resmi dari admin.');
}

// 3. Validasi Keberadaan Foto Selfie
if (empty($foto)) {
    kirimError('Foto selfie wajib diambil.');
}

// ===============================
// VALIDASI & DECODE FOTO BASE64
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
// FOLDER UPLOAD FOTO (SESUAIKAN PATH)
// ===============================
// Karena file ini ada di folder actions/, naik satu tingkat (../) lalu masuk ke uploads/presensi/
$folderUpload = '../uploads/presensi/';
if (!is_dir($folderUpload)) {
    mkdir($folderUpload, 0777, true);
}

$namaFile = 'presensi_' . $pegawai_id . '_' . $jenis . '_' . date('Ymd_His') . '.' . $ekstensi;
$pathFile = $folderUpload . $namaFile;

if (!file_put_contents($pathFile, $foto)) {
    kirimError('Gagal menyimpan file foto ke server.');
}

$fotoDb = 'uploads/presensi/' . $namaFile;

// ===============================
// CEK PRESENSI HARI INI DI DATABASE
// ===============================
$tanggalHariIni = date('Y-m-d');
$jamSekarang    = date('H:i:s');

$queryCek = "SELECT * FROM presensi WHERE pegawai_id = ? AND tanggal = ? LIMIT 1";
$stmtCek = $conn->prepare($queryCek);
$stmtCek->bind_param("is", $pegawai_id, $tanggalHariIni);
$stmtCek->execute();
$resultCek = $stmtCek->get_result();
$dataPresensi = $resultCek->fetch_assoc();

// ===============================
// PROSES OTOMATIS BERDASARKAN STATUS
// ===============================
if ($jenis === 'masuk') {
    // Cek apakah sudah absen masuk
    if ($dataPresensi && !empty($dataPresensi['jam_masuk'])) {
        // Jika sudah absen masuk tapi belum absen pulang, arahkan otomatis jadi absen pulang!
        if (empty($dataPresensi['jam_pulang'])) {
            $queryUpdatePulang = "UPDATE presensi SET jam_pulang = ?, foto_pulang = ? WHERE id = ?";
            $stmtUpdatePulang = $conn->prepare($queryUpdatePulang);
            $stmtUpdatePulang->bind_param("ssi", $jamSekarang, $fotoDb, $dataPresensi['id']);

            if ($stmtUpdatePulang->execute()) {
                kirimSukses('Absen pulang berhasil disimpan.');
            } else {
                kirimError('Gagal menyimpan absen pulang ke database.');
            }
        } else {
            kirimError('Anda sudah melakukan absen masuk dan pulang hari ini.');
        }
        exit;
    }

    if ($dataPresensi) {
        // Update jam masuk jika baris tanggal sudah ada
        $queryUpdateMasuk = "UPDATE presensi 
                             SET jam_masuk = ?, 
                                 foto_masuk = ?, 
                                 status_kehadiran = 'Hadir'
                             WHERE id = ?";
        $stmtUpdateMasuk = $conn->prepare($queryUpdateMasuk);
        $stmtUpdateMasuk->bind_param("ssi", $jamSekarang, $fotoDb, $dataPresensi['id']);

        if ($stmtUpdateMasuk->execute()) {
            kirimSukses('Absen masuk berhasil disimpan.');
        } else {
            kirimError('Gagal menyimpan absen masuk ke database.');
        }
    } else {
        // Insert baru jika belum ada data sama sekali hari ini
        $queryInsertMasuk = "INSERT INTO presensi 
                            (pegawai_id, tanggal, jam_masuk, foto_masuk, status_kehadiran)
                            VALUES (?, ?, ?, ?, 'Hadir')";
        $stmtInsertMasuk = $conn->prepare($queryInsertMasuk);
        $stmtInsertMasuk->bind_param("isss", $pegawai_id, $tanggalHariIni, $jamSekarang, $fotoDb);

        if ($stmtInsertMasuk->execute()) {
            kirimSukses('Absen masuk berhasil disimpan.');
        } else {
            kirimError('Gagal menyimpan absen masuk ke database.');
        }
    }
}

// ===============================
// PROSES ABSEN PULANG (JIKA DIPANGGIL EKSPLISIT)
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
                              foto_pulang = ?
                          WHERE id = ?";
    $stmtUpdatePulang = $conn->prepare($queryUpdatePulang);
    $stmtUpdatePulang->bind_param("ssi", $jamSekarang, $fotoDb, $dataPresensi['id']);

    if ($stmtUpdatePulang->execute()) {
        kirimSukses('Absen pulang berhasil disimpan.');
    } else {
        kirimError('Gagal menyimpan absen pulang ke database.');
    }
}

kirimError('Permintaan tidak dikenali.');
