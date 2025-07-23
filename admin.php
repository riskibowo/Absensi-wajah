<?php
require_once 'db_connect.php';

// ===================================================================
// BAGIAN PENGAMAN: CEK APAKAH ADMIN SUDAH LOGIN
// ===================================================================
// Jika session 'admin_logged_in' tidak ada atau nilainya bukan true,
// maka paksa pengguna kembali ke halaman login.
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login_admin.php');
    exit(); // Penting: Hentikan eksekusi skrip agar konten di bawah tidak tampil.
}
// ===================================================================


$message = '';
$is_editing_jadwal = false;
$jadwal_to_edit = [];

// ===================================================================
// BAGIAN 1: MENANGANI SEMUA AKSI (TAMBAH, UPDATE, HAPUS)
// ===================================================================

// Aksi yang dikirim via method POST (Tambah dan Update)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    // Aksi untuk menambah mata kuliah baru
    if ($action == 'add_matkul') {
        $nama_matkul = $_POST['nama_matkul'];
        $nidn_dosen = $_POST['nidn_dosen'];
        $stmt = $conn->prepare("INSERT INTO matakuliah (nama_matkul, nidn_dosen) VALUES (?, ?)");
        $stmt->bind_param("ss", $nama_matkul, $nidn_dosen);
        $message = $stmt->execute() 
            ? '<div class="notice success">Mata kuliah berhasil ditambahkan.</div>'
            : '<div class="notice error">Gagal menambahkan mata kuliah.</div>';
        $stmt->close();
    }

    // Aksi untuk menambah jadwal baru
    if ($action == 'add_jadwal') {
        $id_matkul = $_POST['id_matkul'];
        $hari = $_POST['hari'];
        $jam_mulai = $_POST['jam_mulai'];
        $jam_selesai = $_POST['jam_selesai'];
        $stmt = $conn->prepare("INSERT INTO jadwal_kuliah (id_matkul, hari, jam_mulai, jam_selesai) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $id_matkul, $hari, $jam_mulai, $jam_selesai);
        $message = $stmt->execute()
            ? '<div class="notice success">Jadwal berhasil ditambahkan.</div>'
            : '<div class="notice error">Gagal menambahkan jadwal.</div>';
        $stmt->close();
    }

    // Aksi untuk meng-update jadwal yang sudah ada
    if ($action == 'update_jadwal') {
        $id_jadwal = $_POST['id_jadwal'];
        $id_matkul = $_POST['id_matkul'];
        $hari = $_POST['hari'];
        $jam_mulai = $_POST['jam_mulai'];
        $jam_selesai = $_POST['jam_selesai'];
        $stmt = $conn->prepare("UPDATE jadwal_kuliah SET id_matkul=?, hari=?, jam_mulai=?, jam_selesai=? WHERE id_jadwal=?");
        $stmt->bind_param("isssi", $id_matkul, $hari, $jam_mulai, $jam_selesai, $id_jadwal);
         $message = $stmt->execute()
            ? '<div class="notice success">Jadwal berhasil diperbarui.</div>'
            : '<div class="notice error">Gagal memperbarui jadwal.</div>';
        $stmt->close();
    }
}

// Aksi yang dikirim via method GET (Hapus dan Persiapan Edit)
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action'])) {
    $action = $_GET['action'];

    // Aksi untuk menghapus jadwal
    if ($action == 'delete_jadwal') {
        $id_jadwal = $_GET['id_jadwal'];
        $stmt = $conn->prepare("DELETE FROM jadwal_kuliah WHERE id_jadwal = ?");
        $stmt->bind_param("i", $id_jadwal);
        $message = $stmt->execute()
            ? '<div class="notice success">Jadwal berhasil dihapus.</div>'
            : '<div class="notice error">Gagal menghapus jadwal.</div>';
        $stmt->close();
    }

    // Aksi untuk mengambil data jadwal yang akan di-edit
    if ($action == 'edit_jadwal') {
        $is_editing_jadwal = true;
        $id_jadwal = $_GET['id_jadwal'];
        $stmt = $conn->prepare("SELECT * FROM jadwal_kuliah WHERE id_jadwal = ?");
        $stmt->bind_param("i", $id_jadwal);
        $stmt->execute();
        $result = $stmt->get_result();
        $jadwal_to_edit = $result->fetch_assoc();
        $stmt->close();
    }
}


// ===================================================================
// BAGIAN 2: MENGAMBIL SEMUA DATA UNTUK DITAMPILKAN
// ===================================================================

$dosen_list = $conn->query("SELECT nidn, nama FROM dosen ORDER BY nama");
$matakuliah_list = $conn->query("
    SELECT mk.id_matkul, mk.nama_matkul, d.nama as nama_dosen 
    FROM matakuliah mk LEFT JOIN dosen d ON mk.nidn_dosen = d.nidn ORDER BY mk.nama_matkul
");
$jadwal_list = $conn->query("
    SELECT jk.id_jadwal, mk.nama_matkul, jk.hari, jk.jam_mulai, jk.jam_selesai 
    FROM jadwal_kuliah jk JOIN matakuliah mk ON jk.id_matkul = mk.id_matkul ORDER BY jk.hari, jk.jam_mulai
");
$hari_map = ["Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu"];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dasbor Admin - Sistem Absensi</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .admin-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .form-section {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 12px;
            padding: 1.5rem;
        }
        
        .data-section {
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 12px;
            padding: 1.5rem;
        }
        
        .notice {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-weight: 600;
            grid-column: 1 / -1;
        }
        
        .notice.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .notice.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .action-links a {
            margin-right: 10px;
            text-decoration: none;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
        }
        
        .action-links a.edit {
            color: #007bff;
            background-color: #e3f2fd;
        }
        
        .action-links a.delete {
            color: #dc3545;
            background-color: #ffebee;
        }
        
        .action-links a:hover {
            opacity: 0.8;
        }
        
        .table-container {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
        }
        
        button[type="submit"] {
            background-color: #007bff;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            margin-right: 0.5rem;
        }
        
        button[type="submit"]:hover {
            background-color: #0056b3;
        }
        
        .button-link {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            color: white;
        }
        
        .back-link {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            margin: 0.5rem 1rem 0.5rem 0;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
        }
        
        .back-link:hover {
            opacity: 0.8;
        }
        
        h2 {
            margin-top: 0;
            color: #343a40;
            border-bottom: 2px solid #007bff;
            padding-bottom: 0.5rem;
        }
        
        h3 {
            color: #495057;
            margin-bottom: 1rem;
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .admin-layout {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Dasbor Admin</h1>
    <p>Kelola data mata kuliah dan jadwal perkuliahan dari sini.</p>
    
    <!-- Section Mata Kuliah -->
    <div class="admin-layout">
        <?php if ($message): ?>
            <div class="notice <?php echo strpos($message, 'berhasil') !== false ? 'success' : 'error'; ?>">
                <?php echo strip_tags($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="form-section">
            <h2>Tambah Mata Kuliah</h2>
            <form action="admin.php" method="POST">
                <input type="hidden" name="action" value="add_matkul">
                <div class="form-group">
                    <label for="nama_matkul">Nama Mata Kuliah:</label>
                    <input type="text" id="nama_matkul" name="nama_matkul" required>
                </div>
                <div class="form-group">
                    <label for="nidn_dosen">Dosen Pengampu:</label>
                    <select id="nidn_dosen" name="nidn_dosen" required>
                        <option value="">-- Pilih Dosen --</option>
                        <?php while($dosen = $dosen_list->fetch_assoc()): ?>
                            <option value="<?php echo $dosen['nidn']; ?>"><?php echo htmlspecialchars($dosen['nama']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit">Tambah Mata Kuliah</button>
            </form>
        </div>
        
        <div class="data-section">
            <h2>Daftar Mata Kuliah</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Mata Kuliah</th>
                            <th>Dosen Pengampu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php mysqli_data_seek($matakuliah_list, 0); ?>
                        <?php if ($matakuliah_list->num_rows > 0): while($mk = $matakuliah_list->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($mk['nama_matkul']); ?></td>
                                <td><?php echo htmlspecialchars($mk['nama_dosen'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="2">Belum ada data mata kuliah.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Section Jadwal Kuliah -->
    <div class="admin-layout">
        <div class="form-section">
            <h2><?php echo $is_editing_jadwal ? 'Edit' : 'Tambah'; ?> Jadwal Kuliah</h2>
            <form action="admin.php" method="POST">
                <input type="hidden" name="action" value="<?php echo $is_editing_jadwal ? 'update_jadwal' : 'add_jadwal'; ?>">
                <input type="hidden" name="id_jadwal" value="<?php echo $jadwal_to_edit['id_jadwal'] ?? ''; ?>">
                <div class="form-group">
                    <label for="id_matkul">Pilih Mata Kuliah:</label>
                    <select id="id_matkul" name="id_matkul" required>
                        <option value="">-- Pilih Mata Kuliah --</option>
                        <?php mysqli_data_seek($matakuliah_list, 0); ?>
                        <?php while($mk = $matakuliah_list->fetch_assoc()): ?>
                            <option value="<?php echo $mk['id_matkul']; ?>" <?php if(isset($jadwal_to_edit['id_matkul']) && $jadwal_to_edit['id_matkul'] == $mk['id_matkul']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($mk['nama_matkul']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="hari">Pilih Hari:</label>
                    <select id="hari" name="hari" required>
                        <?php foreach($hari_map as $index => $nama_hari): ?>
                            <option value="<?php echo $index; ?>" <?php if(isset($jadwal_to_edit['hari']) && $jadwal_to_edit['hari'] == $index) echo 'selected'; ?>>
                                <?php echo $nama_hari; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="jam_mulai">Jam Mulai:</label>
                    <input type="time" id="jam_mulai" name="jam_mulai" value="<?php echo $jadwal_to_edit['jam_mulai'] ?? ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="jam_selesai">Jam Selesai:</label>
                    <input type="time" id="jam_selesai" name="jam_selesai" value="<?php echo $jadwal_to_edit['jam_selesai'] ?? ''; ?>" required>
                </div>
                <button type="submit"><?php echo $is_editing_jadwal ? 'Update Jadwal' : 'Tambah Jadwal'; ?></button>
                <?php if ($is_editing_jadwal): ?>
                    <a href="admin.php" class="button-link" style="background-color: #6c757d;">Batal Edit</a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="data-section">
            <h2>Daftar Jadwal</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Mata Kuliah</th>
                            <th>Hari</th>
                            <th>Jam Mulai</th>
                            <th>Jam Selesai</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($jadwal_list->num_rows > 0): while($jadwal = $jadwal_list->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($jadwal['nama_matkul']); ?></td>
                                <td><?php echo htmlspecialchars($hari_map[$jadwal['hari']]); ?></td>
                                <td><?php echo htmlspecialchars($jadwal['jam_mulai']); ?></td>
                                <td><?php echo htmlspecialchars($jadwal['jam_selesai']); ?></td>
                                <td class="action-links">
                                    <a href="admin.php?action=edit_jadwal&id_jadwal=<?php echo $jadwal['id_jadwal']; ?>" class="edit">Edit</a>
                                    <a href="admin.php?action=delete_jadwal&id_jadwal=<?php echo $jadwal['id_jadwal']; ?>" class="delete" onclick="return confirm('Anda yakin ingin menghapus jadwal ini?');">Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="5">Belum ada data jadwal.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div style="margin-top: 2rem;">
        <a href="index.php" class="back-link">Kembali ke Halaman Absensi</a>
        <a href="logout_admin.php" class="back-link" style="background-color: #dc3545;">Logout Admin</a>
    </div>
</div>
</body>
</html>
<?php
$conn->close();
?>