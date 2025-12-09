document.addEventListener('DOMContentLoaded', function () {
    // Biến toàn cục
    let currentExamScheduleId = null;
    let stream = null;
    let capturedPhoto = null;
    let lastCaptureTime = 0;
    const captureCooldownMs = 1000;
    const captureMaxWidth = 640;
    const captureQuality = 0.7;

    // DOM Elements
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
    const overlay = document.getElementById('overlay');
    // const photo = document.getElementById('photo'); // <-- ĐÃ XÓA DÒNG NÀY
    const btnCapture = document.getElementById('btnCapture');
    const btnRetake = document.getElementById('btnRetake');
    const btnSubmit = document.getElementById('btnSubmit');
    const cameraPreview = document.getElementById('cameraPreview');
    const capturedImage = document.getElementById('capturedImage'); // Đây là div container
    const attendanceResult = document.getElementById('attendanceResult');
    const attendanceModal = new bootstrap.Modal(document.getElementById('attendanceModal'));

    // Face detection model (BlazeFace)
    let faceModel = null;
    let detectionLoopActive = false;
    let currentDetections = [];
    const DETECTION_WIDTH = 320;
    const detectionCanvas = document.createElement('canvas');
    const detectionCtx = detectionCanvas.getContext('2d');
    let lastDetectionTime = 0;
    const detectionIntervalMs = 150;

    // Preload BlazeFace model khi trang load (không chờ user mở camera)
    loadFaceModel();

    async function loadFaceModel() {
        if (faceModel) return;
        try {
            if (typeof blazeface === 'undefined') {
                console.warn('Thư viện BlazeFace (blazeface) không tồn tại. Kiểm tra file blade.php.');
                return;
            }
            console.info('Loading BlazeFace model...');
            faceModel = await blazeface.load();
            console.info('BlazeFace model loaded successfully');
        } catch (e) {
            console.error('Failed to load BlazeFace model', e);
        }
    }


    function startDetectionLoop() {
        if (!faceModel) {
            console.warn('Face model not loaded yet');
            return;
        }
        detectionLoopActive = true;
        if (overlay) overlay.style.display = 'block';
        // Sửa: Giảm tần suất (Throttle)
        // requestAnimationFrame(detectionFrame);
        detectionFrame(); // Bắt đầu vòng lặp setTimeout
    }

    function stopDetectionLoop() {
        detectionLoopActive = false;
        currentDetections = [];
        if (overlay) {
            const ctx = overlay.getContext('2d');
            ctx.clearRect(0, 0, overlay.width || 0, overlay.height || 0);
            overlay.style.display = 'none';
        }
    }

    async function detectionFrame() {
        if (!detectionLoopActive || !faceModel) return;


        try {
            if (video.readyState < 2) {

                if (detectionLoopActive) setTimeout(detectionFrame, 100);
                return;
            }
            const srcW = video.videoWidth;
            const srcH = video.videoHeight;
            if (srcW && srcH) {


                try {

                    const predictions = await faceModel.estimateFaces(video, false);

                    currentDetections = predictions || [];

                } catch (e) {
                    console.error('Detection error', e);
                    currentDetections = [];
                }
            }


            // Draw overlay
            if (overlay) {
                const vW = video.videoWidth;
                const vH = video.videoHeight;
                overlay.width = vW;
                overlay.height = vH;
                overlay.style.width = video.clientWidth + 'px';
                overlay.style.height = video.clientHeight + 'px';

                const ctx = overlay.getContext('2d');
                ctx.clearRect(0, 0, overlay.width, overlay.height);

                if (currentDetections.length > 0) {
                    ctx.strokeStyle = 'rgba(0,255,0,0.9)';
                    ctx.lineWidth = Math.max(2, Math.round(overlay.width / 300));
                    ctx.fillStyle = 'rgba(0,255,0,0.15)';

                    for (const pred of currentDetections) {
                        const [x, y] = pred.topLeft;
                        const w = pred.bottomRight[0] - pred.topLeft[0];
                        const h = pred.bottomRight[1] - pred.topLeft[1];

                        ctx.beginPath();
                        ctx.rect(x, y, w, h);
                        ctx.fill();
                        ctx.stroke();

                        if (pred.probability) {
                            const p = Math.round((Array.isArray(pred.probability) ? pred.probability[0] : pred.probability) * 100);
                            ctx.font = Math.max(12, Math.round(overlay.width / 40)) + 'px sans-serif';
                            ctx.fillStyle = 'rgba(0,255,0,0.9)';
                            ctx.fillText(p + '%', x + 4, y + 18);
                            ctx.fillStyle = 'rgba(0,255,0,0.15)';
                        }
                    }
                }
            }
        } catch (e) {
            console.error("Lỗi trong detectionFrame:", e);
        } finally {
            // Sửa: Dùng setTimeout để giảm tải
            if (detectionLoopActive) {
                setTimeout(detectionFrame, detectionIntervalMs); // Chạy lại sau 150ms
            }
        }
    }

    // Load tất cả ca thi trong ngày cho giảng viên
    async function loadTodayExamsForLecturer() {
        try {
            // Ẩn tất cả các section ngay lập tức khi bắt đầu load
            examInfoSection.style.display = 'none';
            statsSection.style.display = 'none';
            startAttendanceSection.style.display = 'none';

            const response = await fetch('/api/exam-schedules/today/all');
            const result = await response.json();

            if (result.success && result.data && result.data.length > 0) {
                const exams = result.data;

                // Tìm ca thi đang diễn ra
                const ongoingExam = exams.find(e => e.status === 'ongoing');

                if (ongoingExam) {
                    // Có ca thi đang diễn ra - Tự động tải và hiển thị
                    currentExamScheduleId = ongoingExam.id;

                    // Tải thông tin ca thi
                    await loadExamAttendanceData(ongoingExam.id);

                    // Hiển thị thông báo (delay để đảm bảo DOM đã ready)
                    setTimeout(() => {
                        showToast('Thông báo', `Ca thi ${ongoingExam.subject_name} đang diễn ra`, 'success');
                    }, 300);
                } else {
                    // Không có ca thi đang diễn ra - Hiển thị thông báo
                    setTimeout(() => {
                        showToast('Thông báo', `Bạn có ${exams.length} ca thi hôm nay, hiện tại chưa có ca nào đang diễn ra`, 'info');
                    }, 300);

                    // Hiện bảng danh sách với thông báo
                    attendanceListSection.style.display = 'block';

                    // Hiện thông báo trong bảng
                    if (attendanceTableBody) {
                        attendanceTableBody.innerHTML = `
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="icon-clock" style="font-size: 48px; opacity: 0.3; margin-bottom: 16px;"></i>
                                        <div class="mt-2" style="font-size: 18px; font-weight: 500;">Hiện tại không có ca thi đang diễn ra</div>
                                        <small class="d-block mt-2" style="color: #6c757d;">Vui lòng chờ ca thi bắt đầu</small>
                                    </div>
                                </td>
                            </tr>
                        `;
                    }
                }

            } else {
                // Không có ca thi trong ngày
                setTimeout(() => {
                    showToast('Thông báo', 'Hôm nay bạn không có ca thi nào', 'info');
                }, 300);

                // Hiện bảng danh sách với thông báo
                attendanceListSection.style.display = 'block';

                // Hiện thông báo trong bảng
                if (attendanceTableBody) {
                    attendanceTableBody.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="icon-calendar" style="font-size: 48px; opacity: 0.3; margin-bottom: 16px;"></i>
                                    <div class="mt-2" style="font-size: 18px; font-weight: 500;">Hôm nay bạn không có ca thi nào</div>
                                    <small class="d-block mt-2" style="color: #6c757d;">Vui lòng kiểm tra lại lịch giảng dạy hoặc liên hệ phòng đào tạo</small>
                                </div>
                            </td>
                        </tr>
                    `;
                }
            }
        } catch (error) {
            console.error('Error loading today exams:', error);
            showToast('Lỗi', 'Không thể tải danh sách ca thi', 'danger');
        }
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        try {
            const date = new Date(dateStr);
            if (!isNaN(date.getTime())) {
                return date.toLocaleDateString('vi-VN');
            }
        } catch (e) { }
        return dateStr;
    }

    function formatTime(timeStr) {
        return timeStr ? timeStr.substring(0, 5) : '';
    }

    function formatDuration(minutes) {
        if (!minutes || isNaN(minutes)) return '';
        return `${parseInt(minutes, 10)} phút`;
    }

    function getStatusBadge(rekognitionResult) {
        const statusMap = {
            'match': { class: 'badge bg-success', text: 'Có mặt' },
            'not_match': { class: 'badge bg-danger', text: 'Vắng mặt' },
            'unknown': { class: 'badge bg-warning', text: 'Không xác định' },
            null: { class: 'badge bg-secondary', text: '-' },
            undefined: { class: 'badge bg-secondary', text: '-' }
        };
        const statusInfo = statusMap[rekognitionResult] || statusMap[null];
        return statusInfo ? `<span class="${statusInfo.class}">${statusInfo.text}</span>` : '-';
    }

    function formatAttendanceTime(timeStr) {
        if (!timeStr) return '-';
        try {
            return new Date(timeStr).toLocaleString('vi-VN');
        } catch (e) {
            return timeStr;
        }
    }

    function escapeHtml(unsafe) {
        if (!unsafe) return '';
        return unsafe.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    async function loadExamAttendanceData(examScheduleId) {
        try {
            if (!attendanceTableBody) return;

            attendanceTableBody.innerHTML = '<tr><td colspan="6" class="text-center">Đang tải dữ liệu...</td></tr>';
            
            // Mẹo: Thêm limit lớn nếu bạn muốn hiện tất cả sinh viên (vì backend đang mặc định limit=10)
            const response = await fetch(`/api/exam-schedules/${examScheduleId}?limit=100`);
            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || `Lỗi HTTP: ${response.status}`);
            }

            if (!result.success) throw new Error(result.message || 'Không thể tải dữ liệu');

            // --- SỬA ĐOẠN NÀY ---
            const { exam, stats, students: studentsData } = result.data;
            
            // Kiểm tra: Nếu là Paginator Object thì lấy .data, nếu là mảng thì lấy chính nó
            const studentsList = (studentsData && studentsData.data) ? studentsData.data : (Array.isArray(studentsData) ? studentsData : []);
            // --------------------

            examSessionCode.textContent = exam.session_code || exam.id || '-';
            examSubjectCode.textContent = exam.subject_code || '-';
            examSubjectName.textContent = exam.subject_name || '-';
            examDate.textContent = formatDate(exam.exam_date);
            examTime.textContent = formatTime(exam.exam_time);
            
            const examDuration = document.getElementById('exam-duration');
            if (examDuration) examDuration.textContent = formatDuration(exam.duration);
            examRoom.textContent = exam.room || '-';

            totalStudents.textContent = stats.total_students || 0;
            presentCount.textContent = stats.present || 0;
            const pendingCount = document.getElementById('pending-count');
            if (pendingCount) pendingCount.textContent = stats.pending || 0;
            absentCount.textContent = stats.absent || 0;

            examInfoSection.style.display = 'block';
            statsSection.style.display = 'grid';
            startAttendanceSection.style.display = 'block';
            attendanceListSection.style.display = 'block';

            // Xử lý nút điểm danh (giữ nguyên logic của bạn)
            const canAttend = exam.can_attend;
            if (!canAttend) {
                if (btnStartAttendance) {
                    btnStartAttendance.disabled = true;
                    btnStartAttendance.classList.add('disabled');
                    // ... (logic hiển thị text nút giữ nguyên)
                    btnStartAttendance.innerHTML = '<i class="icon-camera"></i> Không thể điểm danh';
                }
            } else {
                if (btnStartAttendance) {
                    btnStartAttendance.disabled = false;
                    btnStartAttendance.classList.remove('disabled');
                    btnStartAttendance.innerHTML = '<i class="icon-camera"></i> Bắt đầu điểm danh';
                    btnStartAttendance.removeAttribute('title');
                }
            }

            // --- SỬA ĐOẠN RENDER BẢNG ---
            if (!studentsList || studentsList.length === 0) {
                attendanceTableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4"><div class="text-muted">Không có dữ liệu điểm danh</div></td></tr>';
                return;
            }

            // Backend đã transform dữ liệu phẳng (id, student_code, full_name nằm cùng cấp)
            // Xem ExamSchedulesController.php dòng 276
            attendanceTableBody.innerHTML = studentsList.map((student, index) => `
            <tr>
                    <td class="text-center">${index + 1}</td>
                    <td>${escapeHtml(student.student_code || '')}</td>
                    <td>${escapeHtml(student.full_name || '')}</td>
                    <td>${escapeHtml(student.class_code || '')}</td>
                    <td>${formatAttendanceTime(student.attendance_time)}</td>
                    <td class="text-center">${getStatusBadge(student.rekognition_result)}</td>
                </tr>
            `).join('');

        } catch (error) {
            console.error('Error loading attendance data:', error);
            showToast('Lỗi', error.message, 'danger');
            if (attendanceTableBody) {
                attendanceTableBody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">${escapeHtml(error.message)}</td></tr>`;
            }
            examInfoSection.style.display = 'none';
            statsSection.style.display = 'none';
            startAttendanceSection.style.display = 'none';
            attendanceListSection.style.display = 'block';
        }
    }

    async function startCamera() {
        try {
            // Sửa: Giảm độ phân giải video
            const constraints = {
                audio: false,
                video: {
                    facingMode: { ideal: 'environment' },
                    width: { ideal: 640 }, // Giảm
                    height: { ideal: 480 } // Giảm
                }
            };

            if (stream) stopCamera();
            stream = await navigator.mediaDevices.getUserMedia(constraints);
            video.srcObject = stream;

            await video.play();
            // Model đã được load sẵn, chỉ cần start detection loop
            if (faceModel) {
                startDetectionLoop();
            } else {
                // Nếu model chưa load xong, đợi và retry
                console.warn('Model not ready, waiting...');
                await loadFaceModel();
                if (faceModel) startDetectionLoop();
            }

        } catch (err) {
            console.error('Error accessing camera:', err);
            if (err.name === 'OverconstrainedError' || err.name === 'NotFoundError') {
                try {
                    if (stream) stopCamera();
                    // Sửa: Giảm độ phân giải video
                    stream = await navigator.mediaDevices.getUserMedia({
                        audio: false,
                        video: { width: { ideal: 640 }, height: { ideal: 480 } } // Giảm
                    });
                    video.srcObject = stream;
                    await video.play();
                    if (faceModel) startDetectionLoop();
                } catch (fallbackErr) {
                    showResult('Không thể truy cập camera. Vui lòng kiểm tra quyền truy cập.', 'error');
                }
            } else {
                showResult('Không thể truy cập camera. Vui lòng kiểm tra quyền truy cập.', 'error');
            }
        }
    }

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        stopDetectionLoop();
    }

    function capturePhoto() {
        const now = Date.now();
        if (now - lastCaptureTime < captureCooldownMs) {
            const wait = Math.ceil((captureCooldownMs - (now - lastCaptureTime)) / 1000);
            showResult(`Vui lòng đợi ${wait}s trước khi chụp lại.`, 'info');
            return;
        }

        lastCaptureTime = now;

        // Yêu cầu phải phát hiện được khuôn mặt
        if (!currentDetections || currentDetections.length === 0) {
            showResult('Không phát hiện thấy khuôn mặt nào. Vui lòng căn chỉnh camera và thử lại.', 'error');
            return; // Dừng lại nếu không có khuôn mặt
        }

        const crops = [];
        const ctx = canvas.getContext('2d');
        const fullW = video.videoWidth;
        const fullH = video.videoHeight;

        if (!fullW || !fullH) {
            showResult('Camera chưa sẵn sàng, vui lòng thử lại.', 'error');
            return;
        }

        canvas.width = fullW;
        canvas.height = fullH;
        ctx.drawImage(video, 0, 0, fullW, fullH);

        // Capture all detected faces, but cap to avoid sending too many images
        const maxCap = 10; // Giới hạn an toàn
        const maxFaces = Math.min(maxCap, currentDetections.length);

        for (let i = 0; i < maxFaces; i++) {
            const pred = currentDetections[i];
            let sx = Math.max(0, Math.floor(pred.topLeft[0]));
            let sy = Math.max(0, Math.floor(pred.topLeft[1]));
            let w = Math.max(10, Math.floor(pred.bottomRight[0] - pred.topLeft[0]));
            let h = Math.max(10, Math.floor(pred.bottomRight[1] - pred.topLeft[1]));

            if (sx + w > fullW) w = fullW - sx;
            if (sy + h > fullH) h = fullH - sy;

            const tmp = document.createElement('canvas');
            let outW = w, outH = h;
            if (w > captureMaxWidth) {
                outW = captureMaxWidth;
                outH = Math.round((h * captureMaxWidth) / w);
            }
            tmp.width = outW;
            tmp.height = outH;
            const tctx = tmp.getContext('2d');
            tctx.drawImage(canvas, sx, sy, w, h, 0, 0, outW, outH);
            crops.push(tmp.toDataURL('image/jpeg', captureQuality));
        }

        if (crops.length === 0) {
            showResult('Không thể trích xuất khuôn mặt, vui lòng thử lại.', 'error');
            return;
        }

        capturedPhoto = crops; // Giờ đây capturedPhoto LUÔN LUÔN là một mảng

        // --- BẮT ĐẦU KHỐI SỬA ---
        // Lấy VÀ XÓA NỘI DUNG CŨ
        const previewContainer = document.getElementById('capturedImage');
        previewContainer.innerHTML = ''; // Xóa ảnh xem trước cũ (nếu có)

        // TẠO VÀ CHÈN CÁC ẢNH MỚI
        crops.forEach((imgSrc, index) => {
            const imgElement = document.createElement('img');
            imgElement.src = imgSrc;
            imgElement.alt = `Ảnh chụp ${index + 1}`;
            imgElement.style.height = '120px'; // Kích thước xem trước
            imgElement.style.width = 'auto';
            imgElement.style.borderRadius = '4px';
            imgElement.style.border = '2px solid #007bff'; // Viền xanh
            previewContainer.appendChild(imgElement);
        });
        // --- KẾT THÚC KHỐI SỬA ---

        cameraPreview.classList.add('d-none');
        capturedImage.classList.remove('d-none'); // 'capturedImage' là cái div
        btnCapture.classList.add('d-none');
        btnRetake.classList.remove('d-none');
        btnSubmit.classList.remove('d-none');
        // Cập nhật thông báo
        showResult(`Đã chụp ${crops.length} khuôn mặt. Vui lòng gửi để điểm danh.`, 'info');
    }

    function retakePhoto() {
        capturedImage.classList.add('d-none');
        // --- BẮT ĐẦU KHỐI SỬA ---
        document.getElementById('capturedImage').innerHTML = ''; // Dọn dẹp các ảnh đã tạo
        // --- KẾT THÚC KHỐI SỬA ---
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

        // Kiểm tra logic mới: capturedPhoto phải là một mảng
        if (!Array.isArray(capturedPhoto)) {
            showResult('Lỗi: Dữ liệu ảnh không hợp lệ (không phải mảng). Vui lòng chụp lại.', 'error');
            return;
        }

        try {
            showResult(`Đang xử lý ${capturedPhoto.length} khuôn mặt...`, 'info');
            btnSubmit.disabled = true;

            // Hàm này gửi 1 ảnh và LƯU điểm danh (commit: true)
            const sendAndCommit = async (img) => {
                const resp = await fetch('/api/attendance/face-recognition', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ image: img, exam_schedule_id: currentExamScheduleId, commit: true })
                });
                return await resp.json();
            };

            const results = [];
            // Lặp qua từng ảnh đã cắt và gửi đi
            for (let i = 0; i < capturedPhoto.length; i++) {
                const img = capturedPhoto[i];
                try {
                    const res = await sendAndCommit(img);
                    results.push({ index: i, ok: !!(res && res.success), res });
                } catch (e) {
                    console.error('Error committing face crop:', e);
                    results.push({ index: i, ok: false, res: { success: false, message: e.message } });
                }
            }

            // Tổng hợp kết quả
            const successMap = {}; // Dùng Map để loại bỏ trùng lặp sinh viên
            const failures = [];
            for (const r of results) {
                if (r.ok && r.res.data && r.res.data.student) {
                    const s = r.res.data.student;
                    // Dùng student_code làm key
                    successMap[s.student_code] = s;
                } else {
                    // Lấy thông báo lỗi từ server (ví dụ: "Đã điểm danh rồi")
                    const msg = (r.res && r.res.message) ? r.res.message : `Khuôn mặt ${r.index + 1}: Không nhận diện được`;
                    failures.push(escapeHtml(msg));
                }
            }

            // Xây dựng thông báo kết quả
            const successes = Object.values(successMap);
            let successMsg = '';
            let errorMsg = '';

            if (successes.length > 0) {
                const names = successes.map(s => `${escapeHtml(s.full_name)} (${escapeHtml(s.student_code)})`).join(', ');
                successMsg = `Điểm danh thành công cho: ${names}`;
                // Tải lại bảng điểm danh
                setTimeout(() => loadExamAttendanceData(currentExamScheduleId), 800);
                // Ẩn modal sau 2.5 giây
                setTimeout(() => attendanceModal.hide(), 2500);
            }

            if (failures.length > 0) {
                errorMsg = `Lỗi: ${failures.join('; ')}`;
            }

            // Hiển thị kết quả tổng hợp
            if (successMsg && errorMsg) {
                showResult(`${successMsg}<br><hr>${errorMsg}`, 'info'); // Hiển thị cả hai
            } else if (successMsg) {
                showResult(successMsg, 'success');
            } else if (errorMsg) {
                showResult(errorMsg, 'error');
            } else {
                showResult('Không có kết quả xử lý.', 'info');
            }

        } catch (error) {
            console.error('Error submitting attendance:', error);
            showResult('Lỗi nghiêm trọng khi gửi điểm danh: ' + error.message, 'error');
        } finally {
            btnSubmit.disabled = false;
        }
    }


    function showResult(message, type) {
        const className = type === 'success' ? 'result-success' :
            type === 'error' ? 'result-error' : 'text-info';
        attendanceResult.innerHTML = `<div class="${className}">${message}</div>`;
    }

    // showToast được import từ toast.js component

    function showLargeNotification(examCount) {
        const overlay = document.createElement('div');
        overlay.className = 'notification-overlay';

        overlay.innerHTML = `
            <div class="notification-box">
                <div class="notification-icon">
                    <i class="icon-calendar"></i>
                </div>
                <h2 class="notification-title">
                    Hôm nay bạn có ${examCount} ca thi
                </h2>
                <p class="notification-message">
                    Vui lòng chọn ca thi để bắt đầu điểm danh
                </p>
                <button class="notification-button">Đã hiểu</button>
            </div>
        `;

        document.body.appendChild(overlay);

        const closeNotification = () => {
            overlay.classList.add('fade-out');
            setTimeout(() => overlay.remove(), 300);
        };

        overlay.querySelector('.notification-button').addEventListener('click', closeNotification);
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) closeNotification();
        });
    }

    // Event Listeners
    btnStartAttendance.addEventListener('click', () => {
        if (!currentExamScheduleId) {
            alert('Vui lòng chọn ca thi trước');
            return;
        }
        attendanceModal.show();
        startCamera();
    });

    btnCapture.addEventListener('click', capturePhoto);
    btnRetake.addEventListener('click', retakePhoto);
    btnSubmit.addEventListener('click', submitAttendance);

    document.getElementById('attendanceModal').addEventListener('hidden.bs.modal', () => {
        stopCamera();
        retakePhoto();
    });

    // Khởi tạo
    const userRole = window.userRole || 'guest';

    // Chỉ giảng viên mới có thể sử dụng chức năng điểm danh
    if (userRole === 'lecturer') {
        loadTodayExamsForLecturer();
    } else {
        attendanceListSection.style.display = 'block';
        if (attendanceTableBody) {
            attendanceTableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <div class="text-muted">
                            <i class="icon-info" style="font-size: 48px; opacity: 0.3; margin-bottom: 16px;"></i>
                            <div class="mt-2" style="font-size: 18px; font-weight: 500;">Chức năng chỉ dành cho giảng viên</div>
                            <small class="d-block mt-2" style="color: #6c757d;">Vui lòng đăng nhập bằng tài khoản giảng viên để điểm danh</small>
                        </div>
                    </td>
                </tr>
            `;
        }
    }
});