<?php
require_once 'db_connect.php';

// Query untuk mengambil laporan kehadiran dosen
// Menggabungkan (JOIN) 3 tabel: kehadiran_dosen, dosen, dan matakuliah
$sql_dosen = "
    SELECT 
        kd.tanggal, 
        kd.waktu_hadir, 
        d.nama AS nama_dosen, 
        mk.nama_matkul 
    FROM 
        kehadiran_dosen AS kd
    JOIN 
        dosen AS d ON kd.nidn = d.nidn
    JOIN 
        matakuliah AS mk ON kd.id_matkul = mk.id_matkul
    ORDER BY 
        kd.tanggal DESC, kd.waktu_hadir DESC
";
$result_dosen = $conn->query($sql_dosen);

// Query untuk mengambil laporan absensi mahasiswa
// Menggabungkan (JOIN) 3 tabel: absensi, mahasiswa, dan matakuliah
$sql_mahasiswa = "
    SELECT 
        a.tanggal, 
        a.waktu_hadir, 
        m.nama AS nama_mahasiswa, 
        m.nim, 
        m.kelas, 
        mk.nama_matkul 
    FROM 
        absensi AS a
    JOIN 
        mahasiswa AS m ON a.nim = m.nim
    JOIN 
        matakuliah AS mk ON a.id_matkul = mk.id_matkul
    ORDER BY 
        a.tanggal DESC, a.waktu_hadir DESC
";
$result_mahasiswa = $conn->query($sql_mahasiswa);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Absensi</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Laporan Kehadiran</h1>

        <div class="header-section">
            <h2>Kehadiran Dosen</h2>
            <a href="export_dosen.php" class="button-link" style="background-color:#198754;">Export ke Excel (CSV)</a>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Waktu Hadir</th>
                        <th>Nama Dosen</th>
                        <th>Mata Kuliah</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_dosen->num_rows > 0): ?>
                        <?php while($row = $result_dosen->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(date('d F Y', strtotime($row['tanggal']))); ?></td>
                                <td><?php echo htmlspecialchars($row['waktu_hadir']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_dosen']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_matkul']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">Belum ada data kehadiran dosen.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="header-section">
            <h2>Absensi Mahasiswa</h2>
            <a href="export_mahasiswa.php" class="button-link" style="background-color:#198754;">Export ke Excel (CSV)</a>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Waktu Hadir</th>
                        <th>Nama Mahasiswa</th>
                        <th>NIM</th>
                        <th>Kelas</th>
                        <th>Mata Kuliah</th>
                    </tr>
                </thead>
                <tbody>
                     <?php if ($result_mahasiswa->num_rows > 0): ?>
                        <?php while($row = $result_mahasiswa->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(date('d F Y', strtotime($row['tanggal']))); ?></td>
                                <td><?php echo htmlspecialchars($row['waktu_hadir']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_mahasiswa']); ?></td>
                                <td><?php echo htmlspecialchars($row['nim']); ?></td>
                                <td><?php echo htmlspecialchars($row['kelas']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_matkul']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">Belum ada data absensi mahasiswa.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <a href="index.php" class="back-link">Kembali ke Halaman Utama</a>
    </div>
</body>
</html>
<?php
// Menutup koneksi database
$conn->close();
?>