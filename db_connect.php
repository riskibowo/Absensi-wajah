<?php
// db_connect.php
// File untuk koneksi ke database

// Atur zona waktu sesuai lokasi Anda
date_default_timezone_set('Asia/Jakarta');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_absensi_pintar";

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Mulai session PHP untuk menyimpan status login/sesi
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
