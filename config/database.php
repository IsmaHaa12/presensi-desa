<?php
// Paksa session berjalan di semua IP/domain akses
ini_set('session.cookie_domain', ''); // Kosongkan domain biar session berlaku di semua IP
ini_set('session.cookie_samesite', 'Lax'); // Izinkan session lintas akses lokal

session_start(); // Mulai session untuk nyimpan data login

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
