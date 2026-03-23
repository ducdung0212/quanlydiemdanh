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
    const btnStartQrAttendance = document.getElementById('btnStartQrAttendance');

    // Student lookup elements (visible only when an exam is active)
    const studentLookupSection = document.getElementById('studentLookupSection');
    const studentLookupForm = document.getElementById('studentLookupForm');
    const studentLookupInput = document.getElementById('studentLookupInput');
    const studentLookupResult = document.getElementById('studentLookupResult');

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

    // Sort state (client triggers backend sorting)
    // Supports nested sorting between: full_name (Tên) and class_code
    let primarySortBy = '';
    let primarySortDir = 'asc';
    const sortDirMap = {
        full_name: 'asc',
        class_code: 'asc'
    };
    const attendanceTable = document.getElementById('attendance-table');
    const sortableHeaders = attendanceTable ? attendanceTable.querySelectorAll('th[data-sort]') : [];

    function updateSortIndicators() {
        if (!attendanceTable) return;
        const indicators = attendanceTable.querySelectorAll('[data-sort-indicator]');
        indicators.forEach(el => {
            const key = el.getAttribute('data-sort-indicator');
            if (!key) return;

            if (!primarySortBy) {
                el.textContent = '▲';
                return;
            }

            // When sorting is active, always show arrows for both columns.
            const dir = sortDirMap[key] || 'asc';
            el.textContent = dir === 'asc' ? '▲' : '▼';
        });
    }

    function setSort(nextSortBy) {
        if (!nextSortBy) return;
        const currentDir = sortDirMap[nextSortBy] || 'asc';
        // The table is already sorted by default (asc). Any click should reverse immediately,
        // including when switching to a different column.
        sortDirMap[nextSortBy] = currentDir === 'asc' ? 'desc' : 'asc';

        // Primary sort is the last-clicked column
        primarySortBy = nextSortBy;
        primarySortDir = sortDirMap[nextSortBy] || 'asc';

        updateSortIndicators();
        if (currentExamScheduleId) {
            loadExamAttendanceData(currentExamScheduleId);
        }
    }

    // Camera Elements
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const overlay = document.getElementById('overlay');
    const btnCapture = document.getElementById('btnCapture');
    const btnRetake = document.getElementById('btnRetake');
    const btnSubmit = document.getElementById('btnSubmit');
    const cameraPreview = document.getElementById('cameraPreview');
    const capturedImage = document.getElementById('capturedImage');
    const attendanceResult = document.getElementById('attendanceResult');
    const attendanceModal = new bootstrap.Modal(document.getElementById('attendanceModal'));
    const qrAttendanceResult = document.getElementById('qrAttendanceResult');

    // Mode switch elements
    const tabFaceMode = document.getElementById('tabFaceMode');
    const tabQrMode = document.getElementById('tabQrMode');
    const faceAttendanceSection = document.getElementById('faceAttendanceSection');
    const qrAttendanceSection = document.getElementById('qrAttendanceSection');

    // QR elements
    const qrVideo = document.getElementById('qrVideo');
    const qrOverlay = document.getElementById('qrOverlay');
    const qrOverlayCtx = qrOverlay ? qrOverlay.getContext('2d') : null;
    const btnStartQrScanner = document.getElementById('btnStartQrScanner');
    const btnStopQrScanner = document.getElementById('btnStopQrScanner');
    const qrManualInput = document.getElementById('qrManualInput');
    const btnSubmitQrManual = document.getElementById('btnSubmitQrManual');
    let activeAttendanceMode = 'face';
    let qrStream = null;
    let qrScannerActive = false;
    let lastQrValue = '';
    let lastQrScanAt = 0;
    let lastQrDecodeAt = 0;
    const qrCooldownMs = 1500;
    const qrDecodeIntervalMs = 220;
    const isQrAutoScanSupported = ('BarcodeDetector' in window);
    const isJsQrSupported = (typeof window.jsQR === 'function');
    const isAnyQrDecoderSupported = isQrAutoScanSupported || isJsQrSupported;
    const qrDetector = isQrAutoScanSupported
        ? new BarcodeDetector({ formats: ['qr_code'] })
        : null;
    const qrCanvas = document.createElement('canvas');
    const qrCanvasCtx = qrCanvas.getContext('2d', { willReadFrequently: true });

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
            if (studentLookupSection) studentLookupSection.style.display = 'none';

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

    async function lookupStudentTodayExam(studentCode) {
        const code = (studentCode || '').trim();
        if (!code) {
            if (studentLookupResult) {
                studentLookupResult.innerHTML = '<div class="alert alert-warning mb-0">Vui lòng nhập mã số sinh viên.</div>';
            }
            return;
        }

        if (studentLookupResult) {
            studentLookupResult.innerHTML = '<div class="text-muted">Đang tra cứu...</div>';
        }

        try {
            const resp = await fetch(`/api/exam-schedules/student-today-exam?student_code=${encodeURIComponent(code)}`);
            const result = await resp.json();

            if (!result || result.success !== true) {
                const msg = (result && result.message) ? result.message : 'Không thể tra cứu ca thi trong ngày';
                if (studentLookupResult) {
                    studentLookupResult.innerHTML = `<div class="alert alert-danger mb-0">${escapeHtml(msg)}</div>`;
                }
                return;
            }

            if (result.has_exam && result.data) {
                const subjectName = result.data.subject_name || '';
                const room = result.data.room || '';
                const examTime = (result.data.exam_time || '').toString().substring(0, 5);

                if (studentLookupResult) {
                    studentLookupResult.innerHTML = `
                        <div class="alert alert-success mb-0">
                            Sinh viên có ca thi môn <strong>${escapeHtml(subjectName)}</strong>, phòng <strong>${escapeHtml(room)}</strong>, giờ thi <strong>${escapeHtml(examTime)}</strong>.
                        </div>
                    `;
                }
            } else {
                if (studentLookupResult) {
                    studentLookupResult.innerHTML = '<div class="alert alert-info mb-0">Sinh viên không có ca thi nào trong ngày</div>';
                }
            }
        } catch (e) {
            console.error('lookupStudentTodayExam error:', e);
            if (studentLookupResult) {
                studentLookupResult.innerHTML = '<div class="alert alert-danger mb-0">Không thể tra cứu ca thi trong ngày</div>';
            }
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

            // Hiển thị đầy đủ danh sách (trang này không có phân trang)
            const limit = 100000;
            // Nested sort: primary + secondary (other column)
            const secondarySortBy = primarySortBy
                ? (primarySortBy === 'full_name' ? 'class_code' : 'full_name')
                : '';
            const secondarySortDir = secondarySortBy ? (sortDirMap[secondarySortBy] || 'asc') : 'asc';

            const url = `/api/exam-schedules/${examScheduleId}?limit=${limit}`
                + (primarySortBy ? `&sort_by=${encodeURIComponent(primarySortBy)}` : '')
                + (primarySortBy ? `&sort_dir=${encodeURIComponent(primarySortDir)}` : '')
                + (primarySortBy ? `&sort_by2=${encodeURIComponent(secondarySortBy)}` : '')
                + (primarySortBy ? `&sort_dir2=${encodeURIComponent(secondarySortDir)}` : '');
            const response = await fetch(url);
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
            if (studentLookupSection) studentLookupSection.style.display = 'block';
            if (studentLookupResult) studentLookupResult.innerHTML = '';

            // Xử lý nút điểm danh 
            const canAttend = exam.can_attend;
            if (!canAttend) {
                if (btnStartAttendance) {
                    btnStartAttendance.disabled = true;
                    btnStartAttendance.classList.add('disabled');
                    btnStartAttendance.innerHTML = '<i class="icon-camera"></i> Không thể điểm danh';
                }
            } else {
                if (btnStartAttendance) {
                    btnStartAttendance.disabled = false;
                    btnStartAttendance.classList.remove('disabled');
                    btnStartAttendance.innerHTML = '<i class="icon-camera"></i> Điểm danh bằng khuôn mặt';
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

            updateSortIndicators();

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
            if (studentLookupSection) studentLookupSection.style.display = 'none';
        }
    }

    if (studentLookupForm) {
        studentLookupForm.addEventListener('submit', function (e) {
            e.preventDefault();
            lookupStudentTodayExam(studentLookupInput ? studentLookupInput.value : '');
        });
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

    function showQrResult(message, type) {
        if (!qrAttendanceResult) return;
        const className = type === 'success' ? 'result-success' :
            type === 'error' ? 'result-error' : 'text-info';
        qrAttendanceResult.innerHTML = `<div class="${className}">${message}</div>`;
    }

    function setAttendanceMode(mode) {
        activeAttendanceMode = mode === 'qr' ? 'qr' : 'face';

        if (faceAttendanceSection) faceAttendanceSection.style.display = activeAttendanceMode === 'face' ? 'block' : 'none';
        if (qrAttendanceSection) qrAttendanceSection.style.display = activeAttendanceMode === 'qr' ? 'block' : 'none';

        if (tabFaceMode) tabFaceMode.classList.toggle('active', activeAttendanceMode === 'face');
        if (tabQrMode) tabQrMode.classList.toggle('active', activeAttendanceMode === 'qr');

        if (activeAttendanceMode === 'face') {
            stopQrScanner();
            startCamera();
        } else {
            stopCamera();
            retakePhoto();
            if (isAnyQrDecoderSupported) {
                startQrScanner();
            } else {
                showQrResult('Không tìm thấy bộ giải mã QR tự động. Bạn vẫn có thể nhập hoặc dán nội dung QR để điểm danh.', 'info');
            }
        }
    }

    async function startQrScanner() {
        if (!qrVideo) return;
        if (!isAnyQrDecoderSupported) {
            showQrResult('Thiết bị chưa sẵn sàng bộ giải mã QR tự động. Hãy dùng ô nhập tay bên dưới.', 'info');
            return;
        }

        try {
            if (qrStream) stopQrScanner();
            qrStream = await navigator.mediaDevices.getUserMedia({
                audio: false,
                video: {
                    facingMode: { ideal: 'environment' },
                    width: { ideal: 640 },
                    height: { ideal: 480 },
                }
            });

            qrVideo.srcObject = qrStream;
            await qrVideo.play();
            qrScannerActive = true;
            showQrResult('Đang quét QR...', 'info');
            requestAnimationFrame(scanQrFrame);
        } catch (e) {
            console.error('startQrScanner error:', e);
            showQrResult('Không thể truy cập camera để quét QR.', 'error');
        }
    }

    function stopQrScanner() {
        qrScannerActive = false;
        if (qrStream) {
            qrStream.getTracks().forEach(track => track.stop());
            qrStream = null;
        }
        if (qrVideo) {
            qrVideo.srcObject = null;
        }
        clearQrOverlay(true);
    }

    function clearQrOverlay(hide = false) {
        if (!qrOverlay || !qrOverlayCtx) return;
        qrOverlayCtx.clearRect(0, 0, qrOverlay.width || 0, qrOverlay.height || 0);
        if (hide) {
            qrOverlay.style.display = 'none';
        }
    }

    function syncQrOverlaySize(srcW, srcH) {
        if (!qrOverlay || !qrVideo || !srcW || !srcH) return;

        if (qrOverlay.width !== srcW) qrOverlay.width = srcW;
        if (qrOverlay.height !== srcH) qrOverlay.height = srcH;

        qrOverlay.style.width = qrVideo.clientWidth + 'px';
        qrOverlay.style.height = qrVideo.clientHeight + 'px';
        qrOverlay.style.display = 'block';
    }

    function drawQrOverlay(shape, srcW, srcH) {
        if (!qrOverlay || !qrOverlayCtx || !srcW || !srcH) return;

        syncQrOverlaySize(srcW, srcH);
        clearQrOverlay(false);

        if (!shape) return;

        qrOverlayCtx.strokeStyle = 'rgba(0, 255, 0, 0.95)';
        qrOverlayCtx.fillStyle = 'rgba(0, 255, 0, 0.15)';
        qrOverlayCtx.lineWidth = Math.max(2, Math.round(srcW / 260));

        if (shape.type === 'rect') {
            const x = shape.x || 0;
            const y = shape.y || 0;
            const w = shape.w || 0;
            const h = shape.h || 0;
            qrOverlayCtx.beginPath();
            qrOverlayCtx.rect(x, y, w, h);
            qrOverlayCtx.fill();
            qrOverlayCtx.stroke();
            return;
        }

        if (shape.type === 'polygon' && Array.isArray(shape.points) && shape.points.length >= 4) {
            const pts = shape.points;
            qrOverlayCtx.beginPath();
            qrOverlayCtx.moveTo(pts[0].x, pts[0].y);
            for (let i = 1; i < pts.length; i++) {
                qrOverlayCtx.lineTo(pts[i].x, pts[i].y);
            }
            qrOverlayCtx.closePath();
            qrOverlayCtx.fill();
            qrOverlayCtx.stroke();
        }
    }

    async function scanQrFrame() {
        if (!qrScannerActive || !qrVideo) return;

        try {
            if (qrVideo.readyState >= 2) {
                const srcW = qrVideo.videoWidth || 0;
                const srcH = qrVideo.videoHeight || 0;
                const now = Date.now();
                if (now - lastQrDecodeAt < qrDecodeIntervalMs) {
                    if (srcW > 0 && srcH > 0) {
                        syncQrOverlaySize(srcW, srcH);
                    }
                    return;
                }

                lastQrDecodeAt = now;
                let value = '';
                let detectedShape = null;

                if (qrDetector) {
                    const barcodes = await qrDetector.detect(qrVideo);
                    if (Array.isArray(barcodes) && barcodes.length > 0) {
                        const hit = barcodes[0];
                        value = (hit.rawValue || '').trim();

                        if (Array.isArray(hit.cornerPoints) && hit.cornerPoints.length >= 4) {
                            detectedShape = {
                                type: 'polygon',
                                points: hit.cornerPoints.map(p => ({ x: p.x, y: p.y }))
                            };
                        } else if (hit.boundingBox) {
                            detectedShape = {
                                type: 'rect',
                                x: hit.boundingBox.x,
                                y: hit.boundingBox.y,
                                w: hit.boundingBox.width,
                                h: hit.boundingBox.height,
                            };
                        }
                    }
                } else if (isJsQrSupported && qrCanvasCtx) {
                    if (srcW > 0 && srcH > 0) {
                        const maxW = 640;
                        let outW = srcW;
                        let outH = srcH;

                        if (srcW > maxW) {
                            outW = maxW;
                            outH = Math.round((srcH * maxW) / srcW);
                        }

                        qrCanvas.width = outW;
                        qrCanvas.height = outH;
                        qrCanvasCtx.drawImage(qrVideo, 0, 0, outW, outH);

                        const imageData = qrCanvasCtx.getImageData(0, 0, outW, outH);
                        const qrResult = window.jsQR(imageData.data, outW, outH, {
                            inversionAttempts: 'attemptBoth'
                        });

                        if (qrResult && qrResult.data) {
                            value = String(qrResult.data).trim();

                            const scaleX = srcW / outW;
                            const scaleY = srcH / outH;
                            detectedShape = {
                                type: 'polygon',
                                points: [
                                    qrResult.location.topLeftCorner,
                                    qrResult.location.topRightCorner,
                                    qrResult.location.bottomRightCorner,
                                    qrResult.location.bottomLeftCorner,
                                ].map(p => ({
                                    x: p.x * scaleX,
                                    y: p.y * scaleY,
                                }))
                            };
                        }
                    }
                }

                if (srcW > 0 && srcH > 0) {
                    drawQrOverlay(detectedShape, srcW, srcH);
                }

                if (value && (value !== lastQrValue || (now - lastQrScanAt) > qrCooldownMs)) {
                    lastQrValue = value;
                    lastQrScanAt = now;
                    if (qrManualInput) qrManualInput.value = value;
                    await submitQrAttendance(value);
                }
            }
        } catch (e) {
            // Keep scanning even when a frame fails.
        } finally {
            if (qrScannerActive) {
                requestAnimationFrame(scanQrFrame);
            }
        }
    }

    async function submitQrAttendance(rawQrValue) {
        const qrContent = (rawQrValue || '').trim();
        if (!qrContent) {
            showQrResult('Vui lòng quét hoặc nhập nội dung QR.', 'error');
            return;
        }

        if (!currentExamScheduleId) {
            showQrResult('Không tìm thấy ca thi đang hoạt động.', 'error');
            return;
        }

        try {
            showQrResult('Đang xử lý mã QR...', 'info');
            if (btnSubmitQrManual) btnSubmitQrManual.disabled = true;

            const resp = await fetch('/api/attendance/qr-scan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    qr_content: qrContent,
                    exam_schedule_id: currentExamScheduleId,
                    commit: true,
                })
            });

            const result = await resp.json();
            if (!result.success) {
                showQrResult(escapeHtml(result.message || 'Điểm danh QR thất bại'), 'error');
                return;
            }

            const student = result.data && result.data.student ? result.data.student : null;
            const msg = student
                ? `Điểm danh thành công: ${escapeHtml(student.full_name)} (${escapeHtml(student.student_code)})`
                : 'Điểm danh QR thành công';
            showQrResult(msg, 'success');
            setTimeout(() => loadExamAttendanceData(currentExamScheduleId), 500);
        } catch (e) {
            console.error('submitQrAttendance error:', e);
            showQrResult('Lỗi khi gửi điểm danh QR: ' + escapeHtml(e.message), 'error');
        } finally {
            if (btnSubmitQrManual) btnSubmitQrManual.disabled = false;
        }
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

    function retakePhoto(clearResult = true) {
        capturedImage.classList.add('d-none');
        // --- BẮT ĐẦU KHỐI SỬA ---
        document.getElementById('capturedImage').innerHTML = ''; // Dọn dẹp các ảnh đã tạo
        // --- KẾT THÚC KHỐI SỬA ---
        cameraPreview.classList.remove('d-none');
        btnCapture.classList.remove('d-none');
        btnRetake.classList.add('d-none');
        btnSubmit.classList.add('d-none');
        capturedPhoto = null;
        if (clearResult) attendanceResult.innerHTML = '';
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

            retakePhoto(false);

        } catch (error) {
            console.error('Error submitting attendance:', error);
            showResult('Lỗi nghiêm trọng khi gửi điểm danh: ' + error.message, 'error');
            retakePhoto(false);
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
        setAttendanceMode('face');
    });

    if (btnStartQrAttendance) {
        btnStartQrAttendance.addEventListener('click', () => {
            if (!currentExamScheduleId) {
                alert('Vui lòng chọn ca thi trước');
                return;
            }
            attendanceModal.show();
            setAttendanceMode('qr');
        });
    }

    if (tabFaceMode) {
        tabFaceMode.addEventListener('click', () => setAttendanceMode('face'));
    }

    if (tabQrMode) {
        tabQrMode.addEventListener('click', () => setAttendanceMode('qr'));
    }

    if (btnStartQrScanner) {
        btnStartQrScanner.addEventListener('click', startQrScanner);
    }

    if (qrManualInput) {
        qrManualInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                submitQrAttendance(qrManualInput.value);
            }
        });
    }

    if (btnStopQrScanner) {
        btnStopQrScanner.addEventListener('click', () => {
            stopQrScanner();
            showQrResult('Đã dừng quét QR.', 'info');
        });
    }

    if (btnSubmitQrManual) {
        btnSubmitQrManual.addEventListener('click', () => {
            submitQrAttendance(qrManualInput ? qrManualInput.value : '');
        });
    }

    btnCapture.addEventListener('click', capturePhoto);
    btnRetake.addEventListener('click', retakePhoto);
    btnSubmit.addEventListener('click', submitAttendance);

    document.getElementById('attendanceModal').addEventListener('hidden.bs.modal', () => {
        stopCamera();
        stopQrScanner();
        retakePhoto();
        if (qrManualInput) qrManualInput.value = '';
        if (qrAttendanceResult) qrAttendanceResult.innerHTML = '';
    });

    // Khởi tạo
    const userRole = window.userRole || 'guest';

    // Click-to-sort headers
    if (sortableHeaders && sortableHeaders.length > 0) {
        sortableHeaders.forEach(th => {
            th.addEventListener('click', () => {
                const key = th.getAttribute('data-sort');
                setSort(key);
            });
        });
    }

    // Ensure arrows are visible on first render
    updateSortIndicators();

    // Chỉ giảng viên mới có thể sử dụng chức năng điểm danh
    if (userRole === 'lecturer') {
        loadTodayExamsForLecturer();

        if (!isAnyQrDecoderSupported) {
            if (btnStartQrScanner) btnStartQrScanner.disabled = true;
            if (btnStopQrScanner) btnStopQrScanner.disabled = true;
        }
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