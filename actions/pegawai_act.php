<?php
session_start();
require_once '../config/database.php';

// Validasi Keamanan: Hanya Admin yang boleh masuk sini
if (!isset($_SESSION['pegawai_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php?error=Akses Ditolak!");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Cek aksi apa yang diminta (add, edit, atau delete)
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // ========================================================
    // 1. AKSI TAMBAH PEGAWAI BARU
    // ========================================================
    if ($action == 'add') {
        $nama     = $conn->real_escape_string($_POST['nama']);
        $username = $conn->real_escape_string($_POST['username']);
        $password = md5($_POST['password']);
        $jabatan  = $conn->real_escape_string($_POST['jabatan']);
        $role     = $conn->real_escape_string($_POST['role']);

        // Cek apakah username sudah dipakai
        $cek_username = "SELECT * FROM pegawai WHERE username = '$username'";
        if ($conn->query($cek_username)->num_rows > 0) {
            header("Location: ../views/admin/pegawai.php?msg=" . urlencode("Gagal! Username '$username' sudah dipakai."));
            exit;
        }

        $sql = "INSERT INTO pegawai (username, nama, password, jabatan, role) 
                VALUES ('$username', '$nama', '$password', '$jabatan', '$role')";

        if ($conn->query($sql) === TRUE) {
            header("Location: ../views/admin/pegawai.php?msg=" . urlencode("Pegawai '$nama' berhasil ditambahkan."));
        } else {
            header("Location: ../views/admin/pegawai.php?msg=" . urlencode("Error: " . $conn->error));
        }
        exit;
    }

    // ========================================================
    // 2. AKSI EDIT DATA PEGAWAI
    // ========================================================
    else if ($action == 'edit') {
        $id       = intval($_POST['id']);
        $nama     = $conn->real_escape_string($_POST['nama']);
        $username = $conn->real_escape_string($_POST['username']);
        $jabatan  = $conn->real_escape_string($_POST['jabatan']);
        $role     = $conn->real_escape_string($_POST['role']);

        // Cek apakah username dipakai orang lain (selain dia sendiri)
        $cek_username = "SELECT id FROM pegawai WHERE username = '$username' AND id != $id";
        if ($conn->query($cek_username)->num_rows > 0) {
            header("Location: ../views/admin/pegawai.php?msg=" . urlencode("Gagal! Username '$username' sudah dipakai orang lain."));
            exit;
        }

        // Kalau password diisi, berarti mau ganti password. Kalau kosong, password lama tetap dipakai.
        if (!empty($_POST['password'])) {
            $password = md5($_POST['password']);
            $sql = "UPDATE pegawai SET nama='$nama', username='$username', password='$password', jabatan='$jabatan', role='$role' WHERE id=$id";
        } else {
            $sql = "UPDATE pegawai SET nama='$nama', username='$username', jabatan='$jabatan', role='$role' WHERE id=$id";
        }

        if ($conn->query($sql) === TRUE) {
            header("Location: ../views/admin/pegawai.php?msg=" . urlencode("Data pegawai '$nama' berhasil diperbarui."));
        } else {
            header("Location: ../views/admin/pegawai.php?msg=" . urlencode("Error update: " . $conn->error));
        }
        exit;
    }

    // ========================================================
    // 3. AKSI HAPUS PEGAWAI
    // ========================================================
    else if ($action == 'delete') {
        $id_pegawai = intval($_POST['id']);

        if ($id_pegawai == $_SESSION['pegawai_id']) {
            header("Location: ../views/admin/pegawai.php?msg=" . urlencode("Tidak bisa menghapus akun sendiri!"));
            exit;
        }

        $sql_hapus = "DELETE FROM pegawai WHERE id = $id_pegawai";
        if ($conn->query($sql_hapus) === TRUE) {
            header("Location: ../views/admin/pegawai.php?msg=" . urlencode("Akun berhasil dihapus."));
        } else {
            header("Location: ../views/admin/pegawai.php?msg=" . urlencode("Gagal menghapus: " . $conn->error));
        }
        exit;
    }
} else {
    header("Location: ../views/admin/dashboard.php");
    exit;
}
