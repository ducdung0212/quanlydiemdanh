document.addEventListener('DOMContentLoaded', function () {
    // Biến toàn cục
    let currentExamScheduleId = null;
    let stream = null;
    let capturedPhoto = null;
    // Đã loại bỏ các biến không cần thiết (availableVideoDevices, currentVideoDeviceIndex, attemptedAutoRear)

    // DOM Elements
    const examScheduleSelect = document.getElementById('examScheduleSelect');
    const btnLoadExam = document.getElementById('btnLoadExam');
    const examInfoSection = document.getElementById('examInfoSection');
    const statsSection = document.getElementById('statsSection');
    const startAttendanceSection = document.getElementById('startAttendanceSection');
    const attendanceListSection = document.getElementById('attendanceListSection');
    const btnStartAttendance = document.getElementById('btnStartAttendance');

    // Exam info elements
    const examSessionCode = document.getElementById('exam-session-code');
    const examSubjectCode = document.getElementById('exam-subject-code');
    const examSubjectName = document.getElementById('exam-subject-name');
    const examDate = document.getElementById('exam-date');
    const examTime = document.getElementById('exam-time');
    const examRoom = document.getElementById('exam-room');

    // Stats elements
    const totalStudents = document.getElementById('total-students');
    const presentCount = document.getElementById('present-count');
    const absentCount = document.getElementById('absent-count');

    // Table elements
    const attendanceTableBody = document.getElementById('attendance-table-body');

    // Camera Elements
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const photo = document.getElementById('photo');
    const btnCapture = document.getElementById('btnCapture');
    const btnRetake = document.getElementById('btnRetake');
    const btnSubmit = document.getElementById('btnSubmit');
    const cameraPreview = document.getElementById('cameraPreview');
    const capturedImage = document.getElementById('capturedImage');
    const attendanceResult = document.getElementById('attendanceResult');
    const attendanceModal = new bootstrap.Modal(document.getElementById('attendanceModal'));

    // Load danh sách ca thi
    async function loadExamSchedules() {
        try {
            const response = await fetch('/api/exam-schedules?page=1&per_page=100');
            const result = await response.json();

            if (result.success && result.data) {
                const schedules = result.data.data || [];
                examScheduleSelect.innerHTML = '<option value="">-- Chọn ca thi --</option>';
                
                schedules.forEach(schedule => {
                    const option = document.createElement('option');
                    option.value = schedule.id;
                    option.textContent = `${schedule.subject_code} - ${schedule.subject_name} - ${formatDate(schedule.exam_date)} ${formatTime(schedule.exam_time)}`;
                    examScheduleSelect.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error loading exam schedules:', error);
        }
    }

    // Format date
    function formatDate(dateStr) {
        if (!dateStr) return '';
        
        try {
            const date = new Date(dateStr);
            if (!isNaN(date.getTime())) {
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = date.getFullYear();
                return `${day}/${month}/${year}`;
            }
        } catch (e) {
            console.error('Error parsing date:', e);
        }
        
        const parts = dateStr.split('-');
        if (parts.length === 3 && parts[0].length === 4) {
            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        }
        
        return dateStr;
    }

    // Format time
    function formatTime(timeStr) {
        if (!timeStr) return '';
        return timeStr.substring(0, 5);
    }

    // Get status badge
    function getStatusBadge(status) {
        const statusMap = {
            'present': { class: 'badge bg-success', text: 'Có mặt' },
            'late': { class: 'badge bg-success', text: 'Có mặt' }, 
            'absent': { class: 'badge bg-danger', text: 'Vắng mặt' }
        };

        const statusInfo = statusMap[status];
        if (statusInfo) {
            return `<span class="${statusInfo.class}">${statusInfo.text}</span>`;
        }
        return '-';
    }

    // Format attendance time
    function formatAttendanceTime(timeStr) {
        if (!timeStr) return '-';
        try {
            const date = new Date(timeStr);
            return date.toLocaleString('vi-VN');
        } catch (e) {
            return timeStr;
        }
    }

    // Escape HTML
    function escapeHtml(unsafe) {
        if (!unsafe) return '';
        return unsafe
            .toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/\"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Load thông tin ca thi và điểm danh
    async function loadExamAttendanceData(examScheduleId) {
        try {
            if (!attendanceTableBody) return;
            
            const API_URL = `/api/exam-schedules/${examScheduleId}`;
            attendanceTableBody.innerHTML = '<tr><td colspan="6" class="text-center">Đang tải dữ liệu...</td></tr>';
            const response = await fetch(API_URL);

            if (!response.ok) {
                throw new Error(response.status === 404 ? 'Không tìm thấy ca thi' : `Lỗi HTTP: ${response.status}`);
            }

            const result = await response.json();
            if (!result.success) {
                throw new Error(result.message || 'Không thể tải dữ liệu');
            }

            const { exam, stats, students } = result.data;

            // Update exam info
            examSessionCode.textContent = exam.session_code || exam.id || '-';
            examSubjectCode.textContent = exam.subject_code || '-';
            examSubjectName.textContent = exam.subject_name || '-';
            examDate.textContent = formatDate(exam.exam_date);
            examTime.textContent = formatTime(exam.exam_time);
            examRoom.textContent = exam.room || '-';

            // Update stats
            totalStudents.textContent = stats.total_students || 0;
            presentCount.textContent = stats.present || 0;
            absentCount.textContent = stats.absent || 0;

            // Show sections
            examInfoSection.style.display = 'block';
            statsSection.style.display = 'grid';
            startAttendanceSection.style.display = 'block';
            attendanceListSection.style.display = 'block';

            // Update students table
            if (!students || students.length === 0) {
                attendanceTableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="text-muted">
                                <i class="icon-info" style="font-size: 24px; opacity: 0.5;"></i>
                                <div class="mt-2">Không có dữ liệu điểm danh cho ca thi này</div>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }

            const rowsHtml = students.map((student, index) => `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td>${escapeHtml(student.student_code || '')}</td>
                    <td>${escapeHtml(student.full_name || '')}</td>
                    <td>${escapeHtml(student.class_code || '')}</td>
                    <td>${formatAttendanceTime(student.attendance_time)}</td>
                    <td class="text-center">${getStatusBadge(student.status)}</td>
                </tr>
            `).join('');

            attendanceTableBody.innerHTML = rowsHtml;

        } catch (error) {
            console.error('Error loading attendance data:', error);
            if (attendanceTableBody) {
                attendanceTableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center text-danger">
                            <i class="icon-alert-circle"></i> ${error.message}
                        </td>
                    </tr>
                `;
            }
            // Hide sections on error
            examInfoSection.style.display = 'none';
            statsSection.style.display = 'none';
            startAttendanceSection.style.display = 'none';
            attendanceListSection.style.display = 'none';
        }
    }

    // Camera functions
    
    // *** ĐÃ LOẠI BỎ ***
    // Hàm enumerateVideoDevices() không còn cần thiết

    // *** ĐÃ CẬP NHẬT ***
    async function startCamera() {
        try {
            // Luôn yêu cầu camera sau (môi trường)
            const constraints = {
                audio: false,
                video: {
                    facingMode: { ideal: 'environment' }, // Ưu tiên camera sau
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                }
            };

            // Dừng stream cũ nếu có
            if (stream) stopCamera();

            stream = await navigator.mediaDevices.getUserMedia(constraints);
            video.srcObject = stream;

        } catch (err) {
            console.error('Error accessing rear camera:', err);
            
            // Thử lại với camera mặc định nếu không tìm thấy camera sau
            // (Lỗi 'OverconstrainedError' hoặc 'NotFoundError' thường xảy ra khi không có camera sau)
            if (err.name === 'OverconstrainedError' || err.name === 'NotFoundError') {
                console.warn('Rear camera not found or failed, trying default camera...');
                try {
                    const fallbackConstraints = {
                        audio: false,
                        video: { // Không yêu cầu cụ thể, để trình duyệt tự chọn
                            width: { ideal: 1280 },
                            height: { ideal: 720 }
                        }
                    };
                    if (stream) stopCamera(); // Đảm bảo dừng stream cũ
                    stream = await navigator.mediaDevices.getUserMedia(fallbackConstraints);
                    video.srcObject = stream;
                } catch (fallbackErr) {
                    console.error('Error accessing any camera:', fallbackErr);
                    showResult('Không thể truy cập camera. Vui lòng kiểm tra quyền truy cập.', 'error');
                }
            } else {
                // Các lỗi khác (ví dụ: lỗi quyền truy cập - PermissionDeniedError)
                console.error('Error accessing camera:', err);
                showResult('Không thể truy cập camera. Vui lòng kiểm tra quyền truy cập.', 'error');
            }
        }
    }

    // *** ĐÃ CẬP NHẬT ***
    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
            // đã bỏ 'attemptedAutoRear'
        }
    }

    function capturePhoto() {
        const context = canvas.getContext('2d');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        capturedPhoto = canvas.toDataURL('image/jpeg');
        photo.src = capturedPhoto;
        
        cameraPreview.classList.add('d-none');
        capturedImage.classList.remove('d-none');
        
        btnCapture.classList.add('d-none');
        btnRetake.classList.remove('d-none');
        btnSubmit.classList.remove('d-none');
        
        showResult('Ảnh đã được chụp. Vui lòng gửi để điểm danh.', 'info');
    }

    function retakePhoto() {
        capturedImage.classList.add('d-none');
        cameraPreview.classList.remove('d-none');
        
        btnCapture.classList.remove('d-none');
        btnRetake.classList.add('d-none');
        btnSubmit.classList.add('d-none');
        
        capturedPhoto = null;
        attendanceResult.innerHTML = '';
    }

   async function submitAttendance() {
        if (!capturedPhoto || !currentExamScheduleId) {
            showResult('Vui lòng chụp ảnh trước khi gửi điểm danh.', 'error');
            return;
        }

        try {
            showResult('Đang xử lý nhận diện khuôn mặt...', 'info');
            btnSubmit.disabled = true;
            
            const response = await fetch('/api/attendance/face-recognition', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    image: capturedPhoto, 
                    exam_schedule_id: currentExamScheduleId
                })
            });

            const result = await response.json();

            if (result.success) {
                const student = result.data.student;
                const confidence = result.data.confidence;
                
                showResult(
                    `✅ Điểm danh thành công!<br>` +
                    `<strong>${student.full_name}</strong> (${student.student_code})<br>` +
                    `Lớp: ${student.class_name || 'N/A'}<br>` +
                    `Độ tin cậy: ${confidence}%`,
                    'success'
                );
                
                setTimeout(() => {
                    loadExamAttendanceData(currentExamScheduleId);
                    setTimeout(() => {
                        attendanceModal.hide();
                    }, 3000);
                }, 2000);
            } else {
                showResult(result.message || 'Không thể nhận diện sinh viên. Vui lòng thử lại.', 'error');
            }
        } catch (error) {
            console.error('Error submitting attendance:', error);
            showResult('Lỗi khi gửi điểm danh: ' + error.message, 'error');
        } finally {
            btnSubmit.disabled = false;
        }
    }

    function showResult(message, type) {
        const className = type === 'success' ? 'result-success' : 
                         type === 'error' ? 'result-error' : 'text-info';
        attendanceResult.innerHTML = `<div class="${className}">${message}</div>`;
    }

    // Event Listeners
    btnLoadExam.addEventListener('click', function() {
        const selectedExamId = examScheduleSelect.value;
        if (!selectedExamId) {
            alert('Vui lòng chọn ca thi');
            return;
        }
        currentExamScheduleId = selectedExamId;
        loadExamAttendanceData(selectedExamId);
    });

    btnStartAttendance.addEventListener('click', function() {
        if (!currentExamScheduleId) {
            alert('Vui lòng chọn ca thi trước');
            return;
        }
        attendanceModal.show();
        (async () => {
            try {
                await startCamera(); // Hàm startCamera đã được đơn giản hóa
            } catch (err) {
                console.error('Error during camera init:', err);
            }
        })();
    });

    btnCapture.addEventListener('click', capturePhoto);
    btnRetake.addEventListener('click', retakePhoto);
    btnSubmit.addEventListener('click', submitAttendance);

    // *** ĐÃ LOẠI BỎ ***
    // Trình xử lý sự kiện cho 'btnSwitchCamera' đã bị xóa.

    // Xử lý sự kiện khi modal đóng
    document.getElementById('attendanceModal').addEventListener('hidden.bs.modal', function() {
        stopCamera();
        retakePhoto(); // Reset trạng thái
    });

    // Khởi tạo
    loadExamSchedules();
});