# python_scripts/recognize_and_encode.py
import sys
import face_recognition
import numpy as np
import mysql.connector
import json

# Konfigurasi koneksi database (harus sama dengan di PHP)
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'db_absensi_pintar'
}

def connect_db():
    """Membuat koneksi ke database MySQL."""
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        return conn
    except mysql.connector.Error as e:
        print(json.dumps({"status": "error", "message": f"DB Connection Error: {e}"}))
        sys.exit(1)

def load_known_faces():
    """Memuat semua encoding wajah yang ada di database."""
    conn = connect_db()
    cursor = conn.cursor(dictionary=True)
    
    known_encodings = []
    known_data = []

    # Load dosen
    cursor.execute("SELECT nidn, nama, encoding_wajah FROM dosen")
    for row in cursor.fetchall():
        try:
            encoding = np.fromstring(row['encoding_wajah'], sep=',')
            known_encodings.append(encoding)
            known_data.append({'id': row['nidn'], 'nama': row['nama'], 'role': 'dosen'})
        except:
            continue
            
    # Load mahasiswa
    cursor.execute("SELECT nim, nama, kelas, encoding_wajah FROM mahasiswa")
    for row in cursor.fetchall():
        try:
            encoding = np.fromstring(row['encoding_wajah'], sep=',')
            known_encodings.append(encoding)
            known_data.append({'id': row['nim'], 'nama': row['nama'], 'role': 'mahasiswa', 'kelas': row['kelas']})
        except:
            continue

    cursor.close()
    conn.close()
    return known_encodings, known_data

def process_image(image_path, mode):
    """
    Memproses gambar untuk encoding atau recognition.
    mode: 'encode' atau 'recognize'
    """
    try:
        image = face_recognition.load_image_file(image_path)
        face_encodings = face_recognition.face_encodings(image)
    except Exception as e:
        print(json.dumps({"status": "error", "message": f"Image processing error: {e}"}))
        sys.exit(1)

    if not face_encodings:
        print(json.dumps({"status": "error", "message": "Wajah tidak ditemukan pada gambar."}))
        sys.exit(1)

    # Hanya ambil encoding wajah pertama yang terdeteksi
    first_face_encoding = face_encodings[0]

    if mode == 'encode':
        encoding_str = ','.join(map(str, first_face_encoding))
        print(json.dumps({"status": "success", "encoding": encoding_str}))
    
    elif mode == 'recognize':
        known_encodings, known_data = load_known_faces()
        if not known_encodings:
            print(json.dumps({"status": "error", "message": "Tidak ada data wajah di database untuk dicocokkan."}))
            return

        matches = face_recognition.compare_faces(known_encodings, first_face_encoding, tolerance=0.5)
        
        face_distances = face_recognition.face_distance(known_encodings, first_face_encoding)
        best_match_index = np.argmin(face_distances)
        
        if matches[best_match_index]:
            person_data = known_data[best_match_index]
            person_data['status'] = 'success'
            print(json.dumps(person_data))
        else:
            print(json.dumps({"status": "not_found", "message": "Wajah tidak dikenali."}))

if __name__ == '__main__':
    if len(sys.argv) != 3:
        print(json.dumps({"status": "error", "message": "Usage: python script.py <mode> <image_path>"}))
        sys.exit(1)
    
    script_mode = sys.argv[1] # 'encode' atau 'recognize'
    path_to_image = sys.argv[2]
    
    process_image(path_to_image, script_mode)