<?php
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
