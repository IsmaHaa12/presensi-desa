<?php
session_start();
require_once '../config/database.php';

// Validasi Keamanan: Hanya Admin yang boleh masuk sini
if (!isset($_SESSION['pegawai_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php?error=Akses Ditolak!");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Cek aksi apa yang diminta (tambah atau hapus)
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // ========================================================
    // 1. AKSI TAMBAH PEGAWAI BARU
    // ========================================================
    if ($action == 'add') {
        // Amankan data dari injeksi SQL
        $nama     = $conn->real_escape_string($_POST['nama']);
        $username = $conn->real_escape_string($_POST['username']);
        $password = md5($_POST['password']); // Hashing MD5 sederhana
        $jabatan  = $conn->real_escape_string($_POST['jabatan']);
        $role     = $conn->real_escape_string($_POST['role']);

        // Cek apakah username sudah dipakai orang lain
        $cek_username = "SELECT * FROM pegawai WHERE username = '$username'";
        $result_cek = $conn->query($cek_username);

        if ($result_cek->num_rows > 0) {
            // Kalau username sudah ada
            header("Location: ../views/admin/pegawai.php?msg=" . urlencode("Gagal! Username '$username' sudah dipakai orang lain."));
            exit;
        }

        // Jika username aman, masukkan ke database
        $sql = "INSERT INTO pegawai (username, nama, password, jabatan, role) 
                VALUES ('$username', '$nama', '$password', '$jabatan', '$role')";

        if ($conn->query($sql) === TRUE) {
            header("Location: ../views/admin/pegawai.php?msg=" . urlencode("Pegawai baru bernama '$nama' berhasil ditambahkan."));
        } else {
            header("Location: ../views/admin/pegawai.php?msg=" . urlencode("Terjadi kesalahan sistem: " . $conn->error));
        }
        exit;
    }

    // ========================================================
    // 2. AKSI HAPUS PEGAWAI
    // ========================================================
    else if ($action == 'delete') {
        $id_pegawai = intval($_POST['id']);

        // Proteksi: Jangan sampai admin menghapus akun super admin (dirinya sendiri)
        if ($id_pegawai == $_SESSION['pegawai_id']) {
            header("Location: ../views/admin/pegawai.php?msg=" . urlencode("Anda tidak bisa menghapus akun Anda sendiri!"));
            exit;
        }

        // Hapus pegawai dari database (karena di database kita pasang ON DELETE CASCADE, 
        // semua data presensi milik pegawai ini akan otomatis ikut terhapus)
        $sql_hapus = "DELETE FROM pegawai WHERE id = $id_pegawai";

        if ($conn->query($sql_hapus) === TRUE) {
            header("Location: ../views/admin/pegawai.php?msg=" . urlencode("Akun pegawai berhasil dihapus permanen."));
        } else {
            header("Location: ../views/admin/pegawai.php?msg=" . urlencode("Gagal menghapus akun: " . $conn->error));
        }
        exit;
    }
} else {
    // Kalau ada orang iseng buka file ini langsung lewat URL
    header("Location: ../views/admin/dashboard.php");
    exit;
}
