<?php
// api/register.php (Path Python sudah diperbaiki sesuai screenshot Anda)
require_once '../db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Metode request tidak valid.']);
    exit();
}

if (empty($_POST['role']) || empty($_POST['id']) || empty($_POST['name']) || empty($_POST['image_data'])) {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']);
    exit();
}

$role = $_POST['role'];
$id = $_POST['id'];
$name = $_POST['name'];
$kelas = $_POST['kelas'] ?? '';
$imageData = $_POST['image_data'];

list($type, $imageData) = explode(';', $imageData);
list(, $imageData) = explode(',', $imageData);
$imageData = base64_decode($imageData);

$uploadDir = '../uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}
$fileName = uniqid() . '.jpg';
$filePath = $uploadDir . $fileName;
file_put_contents($filePath, $imageData);

// ======================= PATH SUDAH DIPERBAIKI =======================
// Path ini diambil langsung dari hasil "where python" di CMD Anda.
$python_path = 'C:\Users\ASUS\AppData\Local\Programs\Python\Python311\python.exe'; 
// ===================================================================

$script_path = realpath('../python_scripts/recognize_and_encode.py');
$image_path_real = realpath($filePath);


if (!$script_path || !$image_path_real) {
    echo json_encode(['status' => 'error', 'message' => 'File skrip Python atau file gambar tidak ditemukan oleh PHP.']);
    unlink($filePath);
    exit();
}

// Perintah yang benar untuk menangani spasi di path (jika ada)
$command = '"' . $python_path . '" "' . $script_path . '" "encode" "' . $image_path_real . '"';

// Jalankan perintah dan tangkap output error juga
$python_output = shell_exec($command . " 2>&1"); 

unlink($filePath); 

if (empty($python_output)) {
    echo json_encode(['status' => 'error', 'message' => 'Tidak ada output dari skrip Python.', 'command_executed' => $command]);
    exit();
}

$result = json_decode($python_output, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Gagal mem-parsing output dari Python.', 
        'python_raw_output' => $python_output,
        'command_executed' => $command
    ]);
    exit();
}

if (isset($result['status']) && $result['status'] == 'success') {
    $encoding = $result['encoding'];
    
    try {
        if ($role == 'mahasiswa') {
            $stmt = $conn->prepare("INSERT INTO mahasiswa (nim, nama, kelas, encoding_wajah) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $id, $name, $kelas, $encoding);
        } else {
            $stmt = $conn->prepare("INSERT INTO dosen (nidn, nama, encoding_wajah) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $id, $name, $encoding);
        }
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => ucfirst($role) . ' ' . htmlspecialchars($name) . ' berhasil didaftarkan.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan ke database: ' . $stmt->error]);
        }
        $stmt->close();

    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
             echo json_encode(['status' => 'error', 'message' => 'Gagal: NIM/NIDN "' . htmlspecialchars($id) . '" sudah terdaftar.']);
        } else {
             echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
        }
    }

} else {
    echo json_encode(['status' => 'error', 'message' => $result['message'] ?? 'Terjadi kesalahan tidak diketahui di skrip Python.', 'command_executed' => $command, 'python_raw_output' => $python_output]);
}

$conn->close();
?>
