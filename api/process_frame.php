<?php
// api/process_frame.php (Versi Baru - Absensi Berdasarkan Jadwal)

require_once '../db_connect.php';

header('Content-Type: application/json');

// Respon default
$response = [
    'status' => 'waiting',
    'message' => 'Arahkan wajah ke kamera...'
];

if (empty($_POST['image'])) {
    $response['message'] = "Tidak ada data gambar yang diterima.";
    echo json_encode($response);
    exit();
}

// 1. Terima dan simpan frame sementara
$imageData = $_POST['image'];
list($type, $imageData) = explode(';', $imageData);
list(, $imageData) = explode(',', $imageData);
$imageData = base64_decode($imageData);

$uploadDir = '../uploads/';
if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
$filePath = $uploadDir . 'frame.jpg';
file_put_contents($filePath, $imageData);

// 2. Panggil Python untuk mengenali wajah
$python_path = 'C:\Users\ASUS\AppData\Local\Programs\Python\Python311\python.exe';
$script_path = realpath('../python_scripts/recognize_and_encode.py');
$image_path_real = realpath($filePath);

if (!$script_path || !$image_path_real) {
     $response['message'] = "Gagal menemukan path python script atau gambar.";
     echo json_encode($response);
     exit();
}

$command = '"' . $python_path . '" "' . $script_path . '" "recognize" "' . $image_path_real . '"';
$python_output = shell_exec($command . " 2>&1");
unlink($filePath); // Hapus gambar sementara setelah diproses

$recognized_person = json_decode($python_output, true);

// 3. LOGIKA BARU: Proses Absensi Berdasarkan Jadwal, Tanpa Sesi
if (isset($recognized_person['status']) && $recognized_person['status'] == 'success') {
    $role = $recognized_person['role'];
    $id = $recognized_person['id'];
    $nama = $recognized_person['nama'];

    // Siapkan variabel waktu
    $now = new DateTime();
    $current_day = ($now->format('w') == 0) ? 6 : $now->format('w') - 1; // 0=Senin, 1=Selasa, .. 6=Minggu
    $current_time = $now->format('H:i:s');
    $today = $now->format('Y-m-d');

    // === LOGIKA UNTUK MAHASISWA ===
    if ($role == 'mahasiswa') {
        // Cari jadwal kuliah yang aktif saat ini (tanpa perlu tahu siapa dosennya)
        $stmt_jadwal = $conn->prepare("SELECT mk.id_matkul, mk.nama_matkul FROM jadwal_kuliah jk JOIN matakuliah mk ON jk.id_matkul = mk.id_matkul WHERE jk.hari = ? AND jk.jam_mulai <= ? AND jk.jam_selesai >= ? LIMIT 1");
        $stmt_jadwal->bind_param("iss", $current_day, $current_time, $current_time);
        $stmt_jadwal->execute();
        $result_jadwal = $stmt_jadwal->get_result();

        if ($result_jadwal->num_rows > 0) {
            $schedule = $result_jadwal->fetch_assoc();
            $id_matkul = $schedule['id_matkul'];
            $nama_matkul = $schedule['nama_matkul'];

            // Cek apakah mahasiswa sudah absen untuk matkul ini hari ini
            $stmt_cek = $conn->prepare("SELECT id_absensi FROM absensi WHERE nim = ? AND id_matkul = ? AND tanggal = ?");
            $stmt_cek->bind_param("sis", $id, $id_matkul, $today);
            $stmt_cek->execute();
            $result_cek = $stmt_cek->get_result();

            if ($result_cek->num_rows == 0) {
                // Jika belum ada, masukkan data absensi
                $stmt_absen = $conn->prepare("INSERT INTO absensi (nim, id_matkul, tanggal, waktu_hadir, status) VALUES (?, ?, ?, ?, 'Hadir')");
                $stmt_absen->bind_param("siss", $id, $id_matkul, $today, $current_time);
                if ($stmt_absen->execute()) {
                    $response['status'] = 'attendance_success';
                    $response['message'] = "Absen berhasil: " . htmlspecialchars($nama) . " untuk mata kuliah " . htmlspecialchars($nama_matkul);
                } else {
                    $response['status'] = 'db_error';
                    $response['message'] = "Gagal menyimpan absensi: " . $stmt_absen->error;
                }
                $stmt_absen->close();
            } else {
                // Jika sudah absen
                $response['status'] = 'already_attended';
                $response['message'] = htmlspecialchars($nama) . " sudah tercatat hadir.";
            }
            $stmt_cek->close();
        } else {
            // Jika tidak ada jadwal
            $response['status'] = 'no_schedule';
            $response['message'] = "Mahasiswa " . htmlspecialchars($nama) . " terdeteksi, tapi tidak ada jadwal kuliah saat ini.";
        }
        $stmt_jadwal->close();
    } 
    // === LOGIKA UNTUK DOSEN ===
    else if ($role == 'dosen') {
        // Cari jadwal yang sesuai untuk dosen ini
        $stmt_jadwal = $conn->prepare("SELECT mk.id_matkul, mk.nama_matkul FROM jadwal_kuliah jk JOIN matakuliah mk ON jk.id_matkul = mk.id_matkul WHERE mk.nidn_dosen = ? AND jk.hari = ? AND jk.jam_mulai <= ? AND jk.jam_selesai >= ?");
        $stmt_jadwal->bind_param("siss", $id, $current_day, $current_time, $current_time);
        $stmt_jadwal->execute();
        $result_jadwal = $stmt_jadwal->get_result();
        
        if ($result_jadwal->num_rows > 0) {
            $schedule = $result_jadwal->fetch_assoc();
            $id_matkul = $schedule['id_matkul'];
            $nama_matkul = $schedule['nama_matkul'];

            // Cek apakah dosen sudah tercatat hadir hari ini
            $stmt_cek = $conn->prepare("SELECT id_kehadiran FROM kehadiran_dosen WHERE nidn = ? AND id_matkul = ? AND tanggal = ?");
            $stmt_cek->bind_param("sis", $id, $id_matkul, $today);
            $stmt_cek->execute();
            $result_cek = $stmt_cek->get_result();

            if($result_cek->num_rows == 0) {
                // Jika belum, catat kehadiran dosen
                $stmt_hadir = $conn->prepare("INSERT INTO kehadiran_dosen (nidn, id_matkul, tanggal, waktu_hadir) VALUES (?, ?, ?, ?)");
                $stmt_hadir->bind_param("siss", $id, $id_matkul, $today, $current_time);
                if($stmt_hadir->execute()){
                    $response['status'] = 'lecturer_attendance_success';
                    $response['message'] = "Kehadiran Dosen " . htmlspecialchars($nama) . " untuk mata kuliah " . htmlspecialchars($nama_matkul) . " berhasil dicatat.";
                } else {
                    $response['status'] = 'db_error';
                    $response['message'] = 'Gagal menyimpan kehadiran dosen.';
                }
                $stmt_hadir->close();
            } else {
                $response['status'] = 'already_attended';
                $response['message'] = "Dosen " . htmlspecialchars($nama) . " sudah tercatat hadir.";
            }
            $stmt_cek->close();

        } else {
            $response['status'] = 'no_schedule';
            $response['message'] = "Dosen " . htmlspecialchars($nama) . " terdeteksi, tapi tidak ada jadwal mengajar saat ini.";
        }
        $stmt_jadwal->close();
    }
} else if (isset($recognized_person['status']) && $recognized_person['status'] == 'not_found') {
    $response['status'] = 'not_found';
    $response['message'] = "Wajah tidak dikenali. Silakan coba lagi.";
}

// Menutup koneksi database dan mengirim respon
$conn->close();
echo json_encode($response);
?>