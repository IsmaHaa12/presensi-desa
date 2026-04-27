<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = md5($_POST['password']); // Hashing MD5 sederhana

    $query = "SELECT * FROM pegawai WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $pegawai = $result->fetch_assoc();

        // Simpan data penting ke Session buat dipakai di halaman lain
        $_SESSION['pegawai_id'] = $pegawai['id'];
        $_SESSION['nama'] = $pegawai['nama'];
        $_SESSION['role'] = $pegawai['role'];

        // Cek Role, arahkan ke halaman yang sesuai
        if ($pegawai['role'] == 'admin') {
            header("Location: ../views/admin/dashboard.php"); // Ke dashboard Kepala Desa/Admin
        } else {
            // INI YANG DIUBAH!
            // Tadi tulisannya: ../views/pegawai/presensi.php
            // Sekarang diganti jadi:
            header("Location: ../views/pegawai/dashboard.php");
        }
    } else {
        // Balik ke halaman login dengan pesan error
        header("Location: ../index.php?error=Username atau Password salah!");
    }
}
