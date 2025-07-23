<?php
// File ini akan memulai session karena db_connect.php dipanggil
require_once 'db_connect.php'; 

// --- GANTI PASSWORD DI SINI ---
// Anda bisa mengganti 'admin123' dengan password apa pun yang Anda inginkan.
$correct_password = 'admin123';
// -----------------------------

$error_message = '';

// Cek jika form telah disubmit (ada percobaan login)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['password'])) {
        $submitted_password = $_POST['password'];
        
        // Periksa apakah password yang dimasukkan sama dengan password yang benar
        if ($submitted_password === $correct_password) {
            // Jika benar, buat sebuah session untuk menandakan admin sudah login
            $_SESSION['admin_logged_in'] = true;
            // Alihkan (redirect) ke halaman admin
            header('Location: admin.php');
            exit();
        } else {
            // Jika salah, siapkan pesan error
            $error_message = '<div class="notice error">Password yang Anda masukkan salah.</div>';
        }
    }
}

// Jika admin SUDAH login (misalnya membuka tab baru), jangan tampilkan halaman login lagi, langsung alihkan ke dasbor.
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        /* Style khusus untuk halaman login dan pesan error */
        .notice { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; font-weight: 600; text-align: center; }
        .notice.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .login-container { max-width: 450px; margin: 5rem auto 0 auto; }
        form { text-align: left; }
    </style>
</head>
<body>
<div class="container login-container">
    <h1>Login Dasbor Admin</h1>
    <p>Silakan masukkan password untuk melanjutkan.</p>

    <?php echo $error_message; // Tampilkan pesan error jika ada ?>

    <form action="login_admin.php" method="POST">
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Login</button>
    </form>
    <a href="index.php" class="back-link">Kembali ke Halaman Utama</a>
</div>
</body>
</html>