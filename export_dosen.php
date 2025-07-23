<?php
// 1. Sertakan file koneksi database
require_once 'db_connect.php';

// 2. Tentukan nama file yang akan di-download
$filename = "laporan_kehadiran_dosen_" . date('Y-m-d') . ".csv";

// 3. Atur header HTTP agar browser men-download file, bukan menampilkannya
header('Content-Type: text/csv; charset=utf-g');
header('Content-Disposition: attachment; filename="' . $filename . '";');

// 4. Buat file pointer yang terhubung ke output PHP
$output = fopen('php://output', 'w');

// 5. Tulis baris header untuk file CSV (nama-nama kolom)
fputcsv($output, array('Tanggal', 'Waktu Hadir', 'Nama Dosen', 'Mata Kuliah'));

// 6. Jalankan query SQL yang sama dengan di halaman laporan untuk mengambil data kehadiran dosen
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

// 7. Loop melalui setiap baris data dari database dan tulis ke file CSV
if ($result_dosen->num_rows > 0) {
    while ($row = $result_dosen->fetch_assoc()) {
        fputcsv($output, $row);
    }
}

// 8. Tutup koneksi database
$conn->close();
?>