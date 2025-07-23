<?php
// Selalu panggil session_start() di awal, kita gunakan require_once db_connect.php
require_once 'db_connect.php';

// 1. Hapus semua variabel session yang ada
$_SESSION = [];

// 2. Hancurkan session-nya
session_destroy();

// 3. Alihkan (redirect) pengguna kembali ke halaman login
header('Location: login_admin.php');
exit();
?>