// assets/script.js (Final dengan debugging yang dikirim ke browser)

document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('a[href="register.php"]')) { 
        initIndexPage();
    }
    if (document.getElementById('register-form')) {
        initRegisterPage();
    }
});

function initIndexPage() {
    const video = document.getElementById('video-capture');
    const canvas = document.getElementById('canvas');
    const statusTitle = document.getElementById('status-title');
    const statusMessage = document.getElementById('status-message');
    const statusBox = document.getElementById('status-box');
    const sessionInfo = document.getElementById('session-info');
    const sessionLecturer = document.getElementById('session-lecturer');
    const sessionSubject = document.getElementById('session-subject');
    const resetButton = document.getElementById('reset-button');
    let processing = false;

    navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => {
            video.srcObject = stream;
            video.onloadedmetadata = () => {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                setInterval(sendFrameToServer, 2500); 
            };
        })
        .catch(err => {
            console.error("Error accessing webcam: ", err);
            statusMessage.textContent = 'Tidak bisa mengakses webcam. Pastikan Anda memberikan izin.';
        });

    function sendFrameToServer() {
        if (processing) return; 
        processing = true;

        const context = canvas.getContext('2d');
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        const dataUrl = canvas.toDataURL('image/jpeg');
        const formData = new FormData();
        formData.append('image', dataUrl);

        fetch('api/process_frame.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => { updateStatusUI(data); })
        .catch(error => { console.error('Error processing frame:', error); })
        .finally(() => { processing = false; });
    }

    function updateStatusUI(data) {
        statusTitle.textContent = data.status.replace(/_/g, ' ').toUpperCase();
        statusMessage.innerHTML = data.message;
        statusBox.className = 'status-box';
        if (data.session_active) {
            statusBox.classList.add('active');
            sessionInfo.classList.remove('hidden');
            sessionLecturer.textContent = data.session_lecturer;
            sessionSubject.textContent = data.session_subject;
        } else {
             statusBox.classList.add('waiting');
             sessionInfo.classList.add('hidden');
        }
        if (data.status === 'attendance_success') { statusBox.classList.add('success'); }
    }

    resetButton.addEventListener('click', () => {
        if (confirm('Apakah Anda yakin ingin mereset sesi absensi?')) {
            fetch('api/reset_session.php', { method: 'POST' })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    updateStatusUI({ status: 'waiting', message: 'Sesi telah di-reset.', session_active: false });
                })
                .catch(error => console.error('Error resetting session:', error));
        }
    });
}


function initRegisterPage() {
    const video = document.getElementById('video-capture');
    const canvas = document.getElementById('canvas');
    const captureButton = document.getElementById('capture-button');
    const submitButton = document.getElementById('submit-button');
    const imageDataInput = document.getElementById('image-data');
    const roleSelect = document.getElementById('role');
    const kelasGroup = document.getElementById('kelas-group');
    const form = document.getElementById('register-form');
    const responseMessage = document.getElementById('response-message');

    navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => { video.srcObject = stream; })
        .catch(err => { console.error("Error accessing webcam: ", err); });
    
    roleSelect.addEventListener('change', () => {
        kelasGroup.style.display = (roleSelect.value === 'mahasiswa') ? 'block' : 'none';
    });

    captureButton.addEventListener('click', () => {
        const context = canvas.getContext('2d');
        context.drawImage(video, 0, 0, 400, 300);
        imageDataInput.value = canvas.toDataURL('image/jpeg');
        captureButton.textContent = 'Gambar Diambil!';
        captureButton.style.backgroundColor = '#28a745';
        submitButton.disabled = false;
    });

    form.addEventListener('submit', function(event) {
        event.preventDefault();
        
        submitButton.disabled = true;
        submitButton.textContent = 'Mendaftarkan...';
        const formData = new FormData(this);

        fetch(this.action, { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            let finalMessage = data.message;

            if (data.command_executed) {
                 finalMessage += '<br><br><strong>Perintah yang dijalankan:</strong><pre style="background-color:#f8f9fa; border: 1px solid #dee2e6; padding:10px; border-radius:5px; text-align:left; white-space:pre-wrap; word-wrap:break-word;">' + data.command_executed + '</pre>';
            }

            if (data.python_raw_output) {
                finalMessage += '<br><br><strong>Detail Error dari Python:</strong><pre style="background-color:#f8f9fa; border: 1px solid #dee2e6; padding:10px; border-radius:5px; text-align:left; white-space:pre-wrap; word-wrap:break-word;">' + data.python_raw_output + '</pre>';
            }
            
            responseMessage.innerHTML = finalMessage;
            responseMessage.style.color = (data.status === 'success') ? 'green' : 'red';

            if (data.status === 'success') {
                form.reset();
                kelasGroup.style.display = 'block';
                captureButton.textContent = 'Ambil Gambar';
                captureButton.style.backgroundColor = '#007bff';
            }
        })
        .catch(error => {
            responseMessage.textContent = 'Terjadi kesalahan jaringan atau server tidak merespons dengan JSON yang valid.';
            responseMessage.style.color = 'red';
        })
        .finally(() => {
             submitButton.disabled = true;
             submitButton.textContent = 'Daftar';
        });
    });
}
