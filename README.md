# Aplikasi Absensi Wajah (Facial Recognition Attendance System)

Sebuah aplikasi desktop yang dibangun dengan Python untuk sistem absensi otomatis menggunakan teknologi pengenalan wajah secara real-time.

---

## 📸 Screenshot Aplikasi

<table>
  <tr>
    <td align="center"><strong>Tampilan Login</strong></td>
    <td align="center"><strong>Tampilan Absensi</strong></td>
  </tr>
  <tr>
    <td><img src="https://github.com/user-attachments/assets/6f2f66a5-1e75-48d9-979a-14ddcc62f09f" alt="Tampilan Login Admin"></td>
    <td><img src="https://github.com/user-attachments/assets/4d01b18f-676d-4c0c-bec8-fa9a6c3f9383" alt="Tampilan Absensi"></td>
  </tr>
  <tr>
    <td align="center"><strong>Pendaftaran Wajah</strong></td>
    <td align="center"><strong>Laporan Kehadiran</strong></td>
  </tr>
  <tr>
    <td><img src="https://github.com/user-attachments/assets/2f35da31-6805-4b61-b476-d7fd41de16cb" alt="Tampilan Pendaftaran Wajah"></td>
    <td><img src="https://github.com/user-attachments/assets/55945987-e6c1-4354-9821-96472fd1adf3" alt="Tampilan Laporan Kehadiran"></td>
  </tr>
</table>

---

## ✨ Fitur Utama

-   **Login Admin:** Halaman login yang aman untuk administrator mengelola sistem.
-   **Pendaftaran Wajah:** Fitur untuk mendaftarkan wajah pengguna baru (misal: mahasiswa atau karyawan) beserta data diri seperti Nama dan NIM/NIP.
-   **Absensi Real-time:** Sistem akan mendeteksi dan mengenali wajah melalui kamera secara langsung, kemudian mencatat waktu kehadiran secara otomatis.
-   **Laporan Kehadiran:** Menampilkan data riwayat kehadiran dalam bentuk tabel yang rapi.
-   **Export ke Excel:** Memungkinkan admin untuk mengunduh laporan kehadiran dalam format file `.xlsx`.

---

## 🛠️ Teknologi yang Digunakan

* **Bahasa Pemrograman:** Python
* **Library Utama:**
    * `OpenCV` - Untuk pemrosesan gambar dan video dari kamera.
    * `face_recognition` - Untuk mendeteksi dan membandingkan wajah.
    * `Tkinter` / `PyQt5` - Untuk membangun antarmuka pengguna (GUI) desktop. (Pilih salah satu sesuai yang Anda gunakan)
    * `Pandas` & `openpyxl` - Untuk membuat dan mengekspor file Excel.
    * `Pillow` - Untuk manipulasi gambar.

---

## 🚀 Instalasi & Cara Menjalankan

Berikut adalah cara untuk menjalankan proyek ini di komputer lokal Anda.

1.  **Clone repositori ini:**
    ```bash
    git clone [https://github.com/NAMA_USER_ANDA/NAMA_REPO_ANDA.git](https://github.com/NAMA_USER_ANDA/NAMA_REPO_ANDA.git)
    cd NAMA_REPO_ANDA
    ```

2.  **Buat dan aktifkan virtual environment (disarankan):**
    ```bash
    python -m venv .venv
    # Windows
    .\.venv\Scripts\activate
    ```

3.  **Install semua library yang dibutuhkan:**
    (Pastikan Anda sudah membuat file `requirements.txt` terlebih dahulu)
    ```bash
    pip install -r requirements.txt
    ```

4.  **Jalankan aplikasi:**
    ```bash
    python nama_file_utama.py
    ```

---

## 📄 Lisensi

Proyek ini dilisensikan di bawah **MIT License**. Lihat file `LICENSE` untuk detail lebih lanjut.
