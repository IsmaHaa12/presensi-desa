<?php
// config/database.php - Dilengkapi pengecekan session agar aman dan tidak warning

// Mulai session hanya jika belum aktif sama sekali
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_domain', '');
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'presensi_db';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set timezone ke WIB
date_default_timezone_set('Asia/Jakarta');
?>