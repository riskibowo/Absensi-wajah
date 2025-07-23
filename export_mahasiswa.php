<?php
// 1. Sertakan file koneksi database
require_once 'db_connect.php';

// 2. Tentukan nama file yang akan di-download
$filename = "laporan_absensi_mahasiswa_" . date('Y-m-d') . ".csv";

// 3. Atur header HTTP untuk download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '";');

// 4. Buat file pointer
$output = fopen('php://output', 'w');

// 5. Tulis baris header untuk file CSV
fputcsv($output, array('Tanggal', 'Waktu Hadir', 'Nama Mahasiswa', 'NIM', 'Kelas', 'Mata Kuliah'));

// 6. Jalankan query SQL yang sama dengan di halaman laporan untuk mengambil data absensi mahasiswa
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

// 7. Loop melalui setiap baris data dan tulis ke file CSV
if ($result_mahasiswa->num_rows > 0) {
    while ($row = $result_mahasiswa->fetch_assoc()) {
        fputcsv($output, $row);
    }
}

// 8. Tutup koneksi database
$conn->close();
?>