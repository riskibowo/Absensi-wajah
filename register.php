<?php require_once 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftarkan Wajah</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <h1>Pendaftaran Wajah Baru</h1>
        <form id="register-form" action="api/register.php" method="POST">
            <!-- Form fields (sama seperti versi Flask) -->
            <div class="form-group">
                <label for="role">Peran:</label>
                <select id="role" name="role" required>
                    <option value="mahasiswa">Mahasiswa</option>
                    <option value="dosen">Dosen</option>
                </select>
            </div>
            <div class="form-group">
                <label for="id">NIM / NIDN:</label>
                <input type="text" id="id" name="id" required>
            </div>
            <div class="form-group">
                <label for="name">Nama Lengkap:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group" id="kelas-group">
                <label for="kelas">Kelas:</label>
                <input type="text" id="kelas" name="kelas">
            </div>

            <div class="capture-section">
                <video id="video-capture" width="400" height="300" autoplay></video>
                <button type="button" id="capture-button">Ambil Gambar</button>
                <canvas id="canvas" width="400" height="300" style="display:none;"></canvas>
            </div>
            
            <input type="hidden" name="image_data" id="image-data">
            
            <button type="submit" id="submit-button" disabled>Daftar</button>
        </form>
        <div id="response-message"></div>
        <a href="index.php" class="back-link">Kembali ke Halaman Utama</a>
    </div>
    <script src="assets/script.js"></script>
</body>
</html>
