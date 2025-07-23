<?php require_once 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Absensi Wajah (XAMPP)</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <h1>Sistem Absensi Wajah Otomatis</h1>
        <div class="video-container">
            <!-- Video feed dari webcam akan ditampilkan di sini oleh JS -->
            <video id="video-capture" autoplay muted playsinline></video>
            <canvas id="canvas" style="display:none;"></canvas>
        </div>
        <div id="status-box" class="status-box waiting">
            <h2 id="status-title">Memuat...</h2>
            <p id="status-message">Menghubungkan ke kamera dan sistem...</p>
            <div id="session-info" class="hidden">
                <p><strong>Dosen:</strong> <span id="session-lecturer"></span></p>
                <p><strong>Mata Kuliah:</strong> <span id="session-subject"></span></p>
            </div>
        </div>
        <div class="actions">
            <button id="reset-button">Reset Sesi</button>
            <a href="register.php" class="button-link">Daftarkan Wajah Baru</a>
            <a href="laporan.php" class="button-link">Lihat Laporan Absensi</a>
        </div>
    </div>
    <script src="assets/script.js"></script>
</body>
</html>
