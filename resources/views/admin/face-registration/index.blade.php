@extends('layouts.app')

@section('content')
    <div class="face-registration">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Đăng ký khuôn mặt hàng loạt</h3>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-primary fs-3" id="btnOpenWindowModal" data-bs-toggle="modal"
                    data-bs-target="#faceWindowModal">
                    Bật khung giờ đổi ảnh
                </button>
                <button type="button" class="btn btn-outline-danger fs-3" id="btnCloseWindow">
                    Tắt khung giờ hiện tại
                </button>
            </div>
        </div>

        <div class="wg-box p-3 mb-3 border rounded shadow-sm bg-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <div class="text-muted small text-uppercase fw-semibold mb-1" style="letter-spacing: 0.5px;">
                        Khung giờ đổi ảnh cho sinh viên
                    </div>
                    <div id="windowSummary" class="fw-bold fs-5 text-dark">
                        Đang tải trạng thái...
                    </div>
                </div>
                <span id="windowBadge" class="badge bg-secondary rounded-pill px-3 py-2">
                    Đang kiểm tra
                </span>
            </div>

            <hr class="text-muted opacity-25 my-2">

            <div id="windowDetail" class="mt-2 text-muted small">
                Vui lòng đợi trong giây lát...
            </div>
        </div>

        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs mb-3" id="registrationTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fs-5 py-3 px-4" id="individual-tab" data-bs-toggle="tab"
                    data-bs-target="#individual" type="button" role="tab">
                    <i class="icon-user me-2 fs-4"></i>Đăng ký từng file
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fs-5 py-3 px-4" id="batch-tab" data-bs-toggle="tab" data-bs-target="#batch"
                    type="button" role="tab">
                    <i class="icon-users me-2 fs-4"></i>Đăng ký theo lớp (Folder)
                </button>
            </li>
        </ul>

        <div class="tab-content" id="registrationTabsContent">
            <!-- Tab 1: Individual Registration -->
            <div class="tab-pane fade show active" id="individual" role="tabpanel">
                <div class="wg-box p-4 shadow-sm">
                    <h4 class="text-muted mb-3">Tải lên nhiều ảnh; tên tệp phải là <code>DHxxxxxxxx.jpg</code> (x là chữ số)
                    </h4>

                    <div id="dropzone" class="border rounded p-3 mb-3"
                        style="position:relative;overflow:hidden;background:#fbfcfe; cursor: pointer;">
                        <div class="d-flex align-items-center gap-3">
                            <div
                                style="width:56px;height:56px;background:#eef6ff;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                                <i class="icon-download" style="font-size:22px;color:var(--Main);"></i>
                            </div>
                            <div>
                                <div class="fw-bold">Kéo & thả ảnh vào đây hoặc bấm để chọn</div> <br>
                                <div class="fw">Hỗ trợ JPEG/PNG. Tên tệp phải chứa mã sinh viên để tự động đăng ký.
                                </div>
                            </div>
                        </div>
                        <input type="file" id="face_image_input" class="form-control" accept="image/jpeg,image/png"
                            multiple style="opacity:0;position:absolute;inset:0;width:100%;height:100%;cursor:pointer;"
                            aria-label="Chọn ảnh">
                    </div>

                    <div id="fileList" class="mb-3">
                        <!-- Danh sách file sẽ hiển thị ở đây -->
                    </div>

                    <!-- Floating Action Button -->
                    <div id="floatingBtnIndividual" class="floating-action-btn" style="display: none;">
                        <button class="btn btn-primary shadow-lg"
                            onclick="document.getElementById('btnRegisterFace').click()">
                            <i class="icon-user-plus me-2"></i> Đăng ký
                        </button>
                    </div>

                    <div class="d-flex gap-2">
                        <button id="btnRegisterFace" class="btn btn-primary"
                            style="padding: 12px 24px; font-size: 1.1rem; font-weight: 600;">
                            <i class="icon-user-plus me-2" style="font-size: 1.3em;"></i> Đăng ký (các) khuôn mặt
                        </button>
                        <button id="btnClear" class="btn btn-outline-secondary"
                            style="padding: 12px 24px; font-size: 1.3rem;">Xóa danh sách</button>
                    </div>

                    <div id="register_status" class="mt-3"></div>
                </div>
            </div>

            <!-- Tab 2: Batch Registration by Class -->
            <div class="tab-pane fade" id="batch" role="tabpanel">
                <div class="wg-box p-4 shadow-sm">
                    <div id="folderDropzone" class="border rounded p-4 mb-3"
                        style="position:relative;overflow:hidden;background:#fbfcfe; cursor: pointer; min-height: 150px;">
                        <div class="text-center">
                            <div class="mb-3"
                                style="width:80px;height:80px;background:#eef6ff;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;">
                                <i class="icon-folder" style="font-size:32px;color:var(--Main);"></i>
                            </div>
                            <div class="fw-bold fs-5 mb-2">Chọn folder hoặc kéo thả ảnh vào đây</div>
                            <div class="text-muted mb-3">Hỗ trợ chọn folder cha chứa nhiều lớp, hoặc kéo thả nhiều ảnh từ
                                nhiều folder</div>
                            <button type="button" class="btn btn-primary"
                                onclick="document.getElementById('folder_input').click()">
                                <i class="icon-folder-plus me-2"></i>Chọn folder
                            </button>
                        </div>
                        <input type="file" id="folder_input" class="form-control" accept="image/jpeg,image/png"
                            multiple webkitdirectory directory
                            style="opacity:0;position:absolute;inset:0;width:100%;height:100%;cursor:pointer;display:none;"
                            aria-label="Chọn folder">
                    </div>

                    <div id="folderFileList" class="mb-3">
                        <!-- Danh sách file sẽ hiển thị ở đây -->
                    </div>

                    <!-- Floating Action Button -->
                    <div id="floatingBtnBatch" class="floating-action-btn" style="display: none;">
                        <button class="btn btn-primary shadow-lg"
                            onclick="document.getElementById('btnRegisterFolder').click()">
                            <i class="icon-users me-2"></i> Đăng ký
                        </button>
                    </div>

                    <div class="d-flex gap-2">
                        <button id="btnRegisterFolder" class="btn btn-primary"
                            style="padding: 12px 24px; font-size: 1.1rem; font-weight: 600;">
                            <i class="icon-users me-2" style="font-size: 1.3em;"></i> Đăng ký hàng loạt
                        </button>
                        <button id="btnClearFolder" class="btn btn-outline-secondary"
                            style="padding: 12px 24px; font-size: 1.3rem;">Xóa danh sách</button>
                    </div>

                    <div id="folder_status" class="mt-3"></div>
                </div>
            </div>
        </div>

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <div class="modal fade" id="faceWindowModal" tabindex="-1" aria-labelledby="faceWindowModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="faceWindowModalLabel">Bật khung giờ đổi ảnh cá nhân</h5>
                        <button type="button" class="btn-close fs-3" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="faceWindowForm">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="windowName" class="form-label fs-3">Tên đợt (tùy chọn)</label>
                                <input type="text" class="form-control fs-3" id="windowName" name="name"
                                    placeholder="Ví dụ: Đợt cập nhật ảnh tháng 03">
                            </div>
                            <div class="mb-3">
                                <label for="windowStartsAt" class="form-label fs-3">Bắt đầu <span
                                        class="text-danger fs-3">*</span></label>
                                <input type="datetime-local" class="form-control fs-3" id="windowStartsAt" name="starts_at"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label for="windowEndsAt" class="form-label fs-3">Kết thúc <span
                                        class="text-danger fs-3">*</span></label>
                                <input type="datetime-local" class="form-control fs-3" id="windowEndsAt" name="ends_at"
                                    required>
                            </div>
                            <div class="mb-0">
                                <label for="windowNote" class="form-label fs-3">Ghi chú</label>
                                <textarea class="form-control fs-3" id="windowNote" name="note" rows="3"
                                    placeholder="Nội dung thông báo cho sinh viên"></textarea>
                            </div>
                            <div id="windowFormStatus" class="mt-2"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary fs-3" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-primary fs-3" id="btnSubmitWindow">Lưu và bật</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <style>
        .floating-action-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
            animation: fadeInUp 0.3s ease-in-out;
        }

        .floating-action-btn button {
            border-radius: 50px !important;
            padding: 20px 45px !important;
            font-size: 1.25rem !important;
            font-weight: 600 !important;
            transition: all 0.3s ease;
        }

        .floating-action-btn button:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3) !important;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const btnRegister = document.getElementById('btnRegisterFace');
            const btnClear = document.getElementById('btnClear');
            const fileInput = document.getElementById('face_image_input');
            const dropzone = document.getElementById('dropzone');
            const fileListEl = document.getElementById('fileList');
            const statusDiv = document.getElementById('register_status');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const windowSummaryEl = document.getElementById('windowSummary');
            const windowBadgeEl = document.getElementById('windowBadge');
            const windowDetailEl = document.getElementById('windowDetail');
            const btnCloseWindow = document.getElementById('btnCloseWindow');
            const windowForm = document.getElementById('faceWindowForm');
            const windowFormStatus = document.getElementById('windowFormStatus');
            const btnSubmitWindow = document.getElementById('btnSubmitWindow');
            const faceWindowModalEl = document.getElementById('faceWindowModal');
            const faceWindowModal = faceWindowModalEl ? new bootstrap.Modal(faceWindowModalEl) : null;

            function formatDateTimeDisplay(dateTimeValue) {
                if (!dateTimeValue) return '';
                const d = new Date(dateTimeValue);
                if (isNaN(d.getTime())) return dateTimeValue;
                return d.toLocaleString('vi-VN');
            }

            function setWindowFormStatus(message, type = 'info') {
                const cls = type === 'error' ? 'text-danger' : type === 'success' ? 'text-success' : 'text-info';
                windowFormStatus.innerHTML = `<div class="${cls}">${message}</div>`;
            }

            async function loadCurrentWindowStatus() {
                try {
                    const response = await fetch('/api/admin/face-registration-window/current', {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    const payload = await response.json();
                    if (!response.ok || !payload.success) {
                        throw new Error(payload.message || 'Không tải được trạng thái khung giờ đổi ảnh.');
                    }

                    if (!payload.is_open || !payload.data) {
                        windowBadgeEl.className = 'badge bg-danger';
                        windowBadgeEl.textContent = 'Đang tắt';
                        windowSummaryEl.textContent = 'Hiện chưa có khung giờ đổi ảnh đang hoạt động.';
                        windowDetailEl.textContent = 'Sinh viên chưa thể tự đổi ảnh cá nhân.';
                        return;
                    }

                    const windowData = payload.data;
                    windowBadgeEl.className = 'badge bg-success';
                    windowBadgeEl.textContent = 'Đang bật';
                    windowSummaryEl.textContent = windowData.name || 'Khung giờ đổi ảnh đang hoạt động';

                    const startsAt = formatDateTimeDisplay(windowData.starts_at);
                    const endsAt = formatDateTimeDisplay(windowData.ends_at);
                    const note = windowData.note ? ` | Ghi chú: ${windowData.note}` : '';
                    windowDetailEl.textContent = `Từ ${startsAt} đến ${endsAt}${note}`;
                } catch (error) {
                    windowBadgeEl.className = 'badge bg-secondary';
                    windowBadgeEl.textContent = 'Lỗi';
                    windowSummaryEl.textContent = 'Không thể kiểm tra trạng thái khung giờ đổi ảnh.';
                    windowDetailEl.textContent = error.message || 'Vui lòng thử lại sau.';
                }
            }

            windowForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                setWindowFormStatus('Đang lưu khung giờ...', 'info');
                btnSubmitWindow.disabled = true;

                const formData = new FormData(windowForm);
                const body = {
                    name: formData.get('name') || null,
                    starts_at: formData.get('starts_at'),
                    ends_at: formData.get('ends_at'),
                    note: formData.get('note') || null,
                };

                try {
                    const response = await fetch('/api/admin/face-registration-window', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify(body),
                    });

                    const payload = await response.json();
                    if (!response.ok || !payload.success) {
                        throw new Error(payload.message || 'Không thể bật khung giờ đổi ảnh.');
                    }

                    setWindowFormStatus(payload.message || 'Đã bật khung giờ đổi ảnh.', 'success');
                    await loadCurrentWindowStatus();

                    setTimeout(() => {
                        faceWindowModal?.hide();
                        windowForm.reset();
                        windowFormStatus.innerHTML = '';
                    }, 600);
                } catch (error) {
                    setWindowFormStatus(error.message || 'Có lỗi xảy ra khi bật khung giờ.', 'error');
                } finally {
                    btnSubmitWindow.disabled = false;
                }
            });

            btnCloseWindow.addEventListener('click', async function() {
                if (!confirm('Bạn có chắc muốn tắt khung giờ đổi ảnh hiện tại?')) {
                    return;
                }

                btnCloseWindow.disabled = true;
                try {
                    const response = await fetch('/api/admin/face-registration-window/close-current', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                    });

                    const payload = await response.json();
                    if (!response.ok || !payload.success) {
                        throw new Error(payload.message || 'Không thể tắt khung giờ hiện tại.');
                    }

                    await loadCurrentWindowStatus();
                    showStatus(payload.message || 'Đã tắt khung giờ đổi ảnh.', 'success');
                } catch (error) {
                    showStatus(error.message || 'Có lỗi khi tắt khung giờ.', 'error');
                } finally {
                    btnCloseWindow.disabled = false;
                }
            });

            loadCurrentWindowStatus();

            // Keep an internal list of selected files (File objects)
            let selectedFiles = [];

            // Validation pattern: DH followed by 8 digits (case-insensitive)
            const filenamePattern = /^DH\d{8}$/i;
            const allowedExts = ['jpg', 'jpeg', 'png'];

            function renderFileList() {
                fileListEl.innerHTML = '';
                if (selectedFiles.length === 0) {
                    fileListEl.innerHTML = '<div class="text-muted">Chưa có tệp nào được chọn.</div>';
                    document.getElementById('floatingBtnIndividual').style.display = 'none';
                    return;
                }

                // Show floating button when has files
                document.getElementById('floatingBtnIndividual').style.display = 'block';

                selectedFiles.forEach((file, idx) => {
                    const item = document.createElement('div');
                    item.className = 'd-flex align-items-center gap-3 mb-2 p-2 border rounded';

                    const thumb = document.createElement('div');
                    thumb.style.width = '64px';
                    thumb.style.height = '64px';
                    thumb.style.flexShrink = '0';
                    thumb.style.overflow = 'hidden';
                    thumb.style.borderRadius = '6px';
                    thumb.style.background = '#f8f9fa';
                    const img = document.createElement('img');
                    img.style.width = '100%';
                    img.style.height = '100%';
                    img.style.objectFit = 'cover';
                    img.alt = file.name;
                    img.src = URL.createObjectURL(file);
                    thumb.appendChild(img);

                    const meta = document.createElement('div');
                    meta.style.flex = '1';
                    meta.innerHTML =
                        `<div class="fw-semibold">${file.name}</div><div class="text-muted small">${Math.round(file.size/1024)} KB</div>`;

                    const progressWrap = document.createElement('div');
                    progressWrap.style.width = '160px';
                    progressWrap.innerHTML =
                        `<div class="progress" style="height:8px;"><div class="progress-bar progress-bar-${idx}" role="progressbar" style="width:0%" aria-valuenow="0"></div></div>`;

                    const removeBtn = document.createElement('button');
                    removeBtn.className = 'btn btn-sm btn-outline-danger ms-2';
                    removeBtn.textContent = 'Xóa';
                    removeBtn.addEventListener('click', () => {
                        selectedFiles.splice(idx, 1);
                        renderFileList();
                    });

                    item.appendChild(thumb);
                    item.appendChild(meta);
                    item.appendChild(progressWrap);
                    item.appendChild(removeBtn);

                    fileListEl.appendChild(item);
                });
            }

            // Handle file input selection with filename validation
            fileInput.addEventListener('change', function(e) {
                const files = Array.from(e.target.files || []);
                const invalid = [];
                files.forEach(f => {
                    const ext = (f.name.split('.').pop() || '').toLowerCase();
                    const base = f.name.replace(/\.[^/.]+$/, '').trim();
                    if (!allowedExts.includes(ext) || !filenamePattern.test(base)) {
                        invalid.push(f.name);
                        return;
                    }
                    if (!selectedFiles.some(sf => sf.name === f.name && sf.size === f.size))
                        selectedFiles.push(f);
                });

                if (invalid.length) {
                    appendStatus('❌ Một số tệp bị bỏ (tên/định dạng không hợp lệ): ' + invalid.join(', '),
                        'error');
                }

                renderFileList();
            });

            // Drag & drop support
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            dropzone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const files = Array.from(dt.files || []).filter(f => /^image\//.test(f.type));
                const invalid = [];
                files.forEach(f => {
                    const ext = (f.name.split('.').pop() || '').toLowerCase();
                    const base = f.name.replace(/\.[^/.]+$/, '').trim();
                    if (!allowedExts.includes(ext) || !filenamePattern.test(base)) {
                        invalid.push(f.name);
                        return;
                    }
                    if (!selectedFiles.some(sf => sf.name === f.name && sf.size === f.size))
                        selectedFiles.push(f);
                });

                if (invalid.length) {
                    appendStatus('❌ Một số tệp bị bỏ (tên/định dạng không hợp lệ): ' + invalid.join(', '),
                        'error');
                }

                renderFileList();
            });

            // clear list
            btnClear.addEventListener('click', function() {
                selectedFiles = [];
                fileInput.value = '';
                renderFileList();
                showStatus('Danh sách đã được xóa', 'info');
                document.getElementById('floatingBtnIndividual').style.display = 'none';
            });

            // Register button — upload using presigned URLs and show progress
            btnRegister.addEventListener('click', async function() {
                if (selectedFiles.length === 0) {
                    showStatus(
                        'Vui lòng chọn (các) tệp ảnh hợp lệ. Tên tệp phải là DHxxxxxxx.jpg (x là chữ số)',
                        'error');
                    return;
                }

                btnRegister.disabled = true;
                document.getElementById('floatingBtnIndividual').style.display = 'none';
                showStatus(`Đã chọn ${selectedFiles.length} tệp. Đang xin quyền tải lên...`, 'info');

                const fileInfos = selectedFiles.map(f => ({
                    file_name: f.name,
                    file_type: f.type
                }));

                try {
                    const response = await fetch('/api/students/generate-upload-urls', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            files: fileInfos
                        })
                    });

                    const data = await response.json();
                    if (!data.success) throw new Error(data.message || 'Không thể lấy URL từ server.');

                    const uploadUrls = data.data;
                    showStatus('Đang tải ảnh lên S3 (song song)...', 'info');
                    const uploadPromises = selectedFiles.map((file, idx) => new Promise((resolve) => {
                        const urlInfo = uploadUrls.find(u => u.file_name === file.name);
                        if (!urlInfo || !urlInfo.success) {
                            resolve({
                                file: file.name,
                                ok: false,
                                msg: 'Server không cấp quyền'
                            });
                            return;
                        }

                        const xhr = new XMLHttpRequest();
                        xhr.open('PUT', urlInfo.upload_url, true);
                        xhr.withCredentials = false;
                        try {
                            xhr.setRequestHeader('Content-Type', file.type);
                        } catch (e) {
                            console.warn('Could not set Content-Type header on XHR', e);
                        }

                        console.log('Uploading', file.name, urlInfo.upload_url);

                        xhr.upload.addEventListener('progress', function(e) {
                            if (e.lengthComputable) {
                                const percent = Math.round((e.loaded / e.total) *
                                    100);
                                const bar = fileListEl.querySelector(
                                    `.progress-bar-${idx}`);
                                if (bar) {
                                    bar.style.width = percent + '%';
                                    bar.setAttribute('aria-valuenow', percent);
                                }
                            }
                        });

                        xhr.onerror = function(evt) {
                            console.error('XHR error uploading', file.name, evt, xhr
                                .status, xhr.statusText);
                            resolve({
                                file: file.name,
                                ok: false,
                                msg: 'Network error or CORS (status: ' + xhr
                                    .status + ')'
                            });
                        };

                        xhr.onabort = function() {
                            console.warn('XHR aborted for', file.name);
                            resolve({
                                file: file.name,
                                ok: false,
                                msg: 'Aborted'
                            });
                        };

                        xhr.onreadystatechange = function() {
                            if (xhr.readyState === 4) {
                                if (xhr.status >= 200 && xhr.status < 300) {
                                    console.log('Upload succeeded for', file.name, xhr
                                        .status);
                                    resolve({
                                        file: file.name,
                                        ok: true
                                    });
                                } else {
                                    console.error('Upload failed for', file.name,
                                        'status=', xhr.status, 'response=', xhr
                                        .responseText);
                                    resolve({
                                        file: file.name,
                                        ok: false,
                                        msg: 'S3 lỗi: ' + xhr.status + (xhr
                                            .responseText ? ' - ' + xhr
                                            .responseText : '')
                                    });
                                }
                            }
                        };
                        try {
                            xhr.send(file);
                        } catch (err) {
                            console.error('XHR send error for', file.name, err);
                            resolve({
                                file: file.name,
                                ok: false,
                                msg: 'XHR send error'
                            });
                        }
                    }));

                    const results = await Promise.all(uploadPromises);

                    // Gọi API confirm upload cho những file thành công
                    const successfulUploads = results.filter(r => r.ok).map(r => {
                        const studentCode = r.file.replace(/\.[^/.]+$/, '').replace(/_.+$/, '')
                            .trim();
                        return {
                            student_code: studentCode,
                            file_name: r.file
                        };
                    });

                    console.log('Successful uploads to confirm:', successfulUploads);

                    if (successfulUploads.length > 0) {
                        try {
                            const confirmResponse = await fetch('/students/confirm-upload', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                },
                                body: JSON.stringify({
                                    uploads: successfulUploads
                                })
                            });

                            const confirmData = await confirmResponse.json();
                            console.log('Confirm response:', confirmData);
                            if (confirmData.success) {
                                showStatus('✅ ' + confirmData.message, 'success');
                            } else {
                                showStatus('⚠️ ' + (confirmData.message || 'Lỗi khi lưu database'),
                                    'warning');
                            }
                        } catch (confirmErr) {
                            console.error('Confirm upload error:', confirmErr);
                            showStatus('⚠️ Upload thành công nhưng lỗi khi lưu database', 'warning');
                        }
                    }

                    // Hiển thị kết quả
                    const successCount = results.filter(r => r.ok).length;
                    const failedResults = results.filter(r => !r.ok);

                    showStatus(`✅ Tải lên thành công: ${successCount}/${results.length} ảnh`,
                        'success');

                    if (failedResults.length > 0) {
                        showStatus('--- CÁC ẢNH THẤT BẠI ---', 'error');
                        failedResults.forEach(r => appendStatus('❌ ' + r.file + (r.msg ? ' (' + r.msg +
                            ')' : ''), 'error'));
                    }

                } catch (err) {
                    console.error(err);
                    showStatus('Lỗi: ' + err.message, 'error');
                } finally {
                    btnRegister.disabled = false;
                    if (selectedFiles.length > 0) {
                        document.getElementById('floatingBtnIndividual').style.display = 'block';
                    }
                }
            });

            // Hàm hiển thị trạng thái
            function showStatus(message, type) {
                let colorClass = 'text-info';
                if (type === 'success') colorClass = 'text-success';
                if (type === 'error') colorClass = 'text-danger';
                statusDiv.innerHTML = `<div class="${colorClass}">${message}</div>`;
            }

            // Hàm nối thêm trạng thái (để xem log)
            function appendStatus(message, type) {
                let colorClass = 'text-info';
                if (type === 'success') colorClass = 'text-success';
                if (type === 'error') colorClass = 'text-danger';
                statusDiv.innerHTML += `<div class="${colorClass}" style="font-size: 0.95em;">${message}</div>`;
            }

            // initial render
            renderFileList();

            // ========================================
            // TAB 2: BATCH REGISTRATION BY FOLDER
            // ========================================

            const folderInput = document.getElementById('folder_input');
            const folderDropzone = document.getElementById('folderDropzone');
            const folderFileListEl = document.getElementById('folderFileList');
            const folderStatusDiv = document.getElementById('folder_status');
            const btnRegisterFolder = document.getElementById('btnRegisterFolder');
            const btnClearFolder = document.getElementById('btnClearFolder');

            let folderFiles = [];

            function renderFolderFileList() {
                folderFileListEl.innerHTML = '';
                if (folderFiles.length === 0) {
                    folderFileListEl.innerHTML = '<div class="text-muted">Chưa có tệp nào được chọn.</div>';
                    document.getElementById('floatingBtnBatch').style.display = 'none';
                    return;
                }

                // Show floating button when has files
                document.getElementById('floatingBtnBatch').style.display = 'block';

                let html = '<div class="mb-3"><strong>Tìm thấy ' + folderFiles.length + ' ảnh</strong></div>';

                folderFiles.forEach((file, globalIdx) => {
                    html += `<div class="d-flex align-items-center gap-3 mb-2 p-2 border rounded bg-white" data-file-idx="${globalIdx}">
                        <div style="width:64px;height:64px;overflow:hidden;border-radius:6px;background:#f8f9fa;flex-shrink:0;">
                            <img src="${URL.createObjectURL(file)}" style="width:100%;height:100%;object-fit:cover;" alt="${file.name}">
                        </div>
                        <div style="flex:1;">
                            <div class="fw-semibold">${file.name}</div>
                            <div class="text-muted small">${Math.round(file.size/1024)} KB</div>
                        </div>
                        <div class="progress-container-folder-${globalIdx}" style="width:160px;">
                            <div class="progress" style="height:8px;">
                                <div class="progress-bar progress-bar-folder-${globalIdx}" role="progressbar" style="width:0%" aria-valuenow="0"></div>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-outline-danger ms-2 btn-remove-folder-file" data-idx="${globalIdx}">Xóa</button>
                    </div>`;
                });

                folderFileListEl.innerHTML = html;

                // Add event listeners for remove buttons
                document.querySelectorAll('.btn-remove-folder-file').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const idx = parseInt(this.getAttribute('data-idx'));
                        folderFiles.splice(idx, 1);
                        renderFolderFileList();
                    });
                });
            }

            function extractStudentCode(filename) {
                // Extract DHxxxxxxxx from filename
                const match = filename.match(/DH\d{8}/i);
                return match ? match[0].toUpperCase() : null;
            }

            folderInput.addEventListener('change', function(e) {
                const files = Array.from(e.target.files || []);
                folderFiles = [];

                files.forEach(f => {
                    const ext = (f.name.split('.').pop() || '').toLowerCase();
                    const code = extractStudentCode(f.name);

                    if (allowedExts.includes(ext) && code) {
                        folderFiles.push(f);
                    }
                });

                if (folderFiles.length === 0) {
                    showFolderStatus(
                        '❌ Không tìm thấy file hợp lệ nào. Tên file phải chứa mã sinh viên (DHxxxxxxxx)',
                        'error');
                } else {
                    showFolderStatus(`✅ Đã chọn ${folderFiles.length} ảnh hợp lệ`, 'success');
                }

                renderFolderFileList();
            });

            // Drag & drop for folder
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                folderDropzone.addEventListener(eventName, preventDefaults, false);
            });

            folderDropzone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const files = Array.from(dt.files || []).filter(f => /^image\//.test(f.type));
                const newFiles = [];

                files.forEach(f => {
                    const ext = (f.name.split('.').pop() || '').toLowerCase();
                    const code = extractStudentCode(f.name);

                    if (allowedExts.includes(ext) && code) {
                        // Check if file not already added
                        const exists = folderFiles.some(existing =>
                            existing.name === f.name && existing.size === f.size
                        );
                        if (!exists) {
                            newFiles.push(f);
                        }
                    }
                });

                if (newFiles.length > 0) {
                    folderFiles = [...folderFiles, ...newFiles];
                    showFolderStatus(`✅ Đã thêm ${newFiles.length} ảnh. Tổng: ${folderFiles.length} ảnh`,
                        'success');
                } else if (files.length > 0) {
                    showFolderStatus('❌ Không tìm thấy file hợp lệ mới', 'error');
                }

                renderFolderFileList();
            });

            btnClearFolder.addEventListener('click', () => {
                folderFiles = [];
                folderInput.value = '';
                renderFolderFileList();
                folderStatusDiv.innerHTML = '';
                document.getElementById('floatingBtnBatch').style.display = 'none';
            });

            btnRegisterFolder.addEventListener('click', async () => {
                if (folderFiles.length === 0) {
                    showFolderStatus('❌ Vui lòng chọn folder chứa ảnh', 'error');
                    return;
                }

                btnRegisterFolder.disabled = true;
                document.getElementById('floatingBtnBatch').style.display = 'none';
                showFolderStatus('⏳ Đang xử lý...', 'info');

                try {
                    // Step 1: Get presigned URLs
                    const fileInfos = folderFiles.map(f => ({
                        file_name: f.name,
                        file_type: f.type
                    }));

                    const response = await fetch('/api/students/generate-upload-urls', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            files: fileInfos
                        })
                    });

                    if (!response.ok) throw new Error('Lỗi khi tạo URL upload');
                    const data = await response.json();
                    if (!data.success) throw new Error(data.message || 'Unknown error');

                    const urlsData = data.data;
                    showFolderStatus('📤 Bắt đầu tải lên ' + folderFiles.length + ' ảnh...', 'info');

                    const uploadPromises = urlsData.map((item, index) => new Promise((resolve) => {
                        if (!item.success) {
                            appendFolderStatus('❌ ' + item.file_name + ': ' + item.message,
                                'error');
                            resolve({
                                file: item.file_name,
                                ok: false,
                                msg: item.message
                            });
                            return;
                        }

                        const file = folderFiles.find(f => f.name === item.file_name);
                        if (!file) {
                            resolve({
                                file: item.file_name,
                                ok: false,
                                msg: 'File not found'
                            });
                            return;
                        }

                        const xhr = new XMLHttpRequest();
                        xhr.open('PUT', item.upload_url, true);
                        xhr.setRequestHeader('Content-Type', file.type);
                        const code = extractStudentCode(file.name);
                        const fileIndex = folderFiles.findIndex(f => f === file);

                        xhr.upload.onprogress = function(e) {
                            if (e.lengthComputable) {
                                const percent = Math.round((e.loaded / e.total) * 100);
                                const progressBar = document.querySelector(
                                    `.progress-bar-folder-${fileIndex}`);
                                if (progressBar) {
                                    progressBar.style.width = percent + '%';
                                    progressBar.setAttribute('aria-valuenow', percent);
                                }
                            }
                        };

                        xhr.onerror = function() {
                            resolve({
                                file: file.name,
                                ok: false,
                                msg: 'Network error'
                            });
                        };

                        xhr.onreadystatechange = function() {
                            if (xhr.readyState === 4) {
                                if (xhr.status >= 200 && xhr.status < 300) {
                                    resolve({
                                        file: file.name,
                                        ok: true
                                    });
                                } else {
                                    resolve({
                                        file: file.name,
                                        ok: false,
                                        msg: 'Upload failed: ' + xhr.status
                                    });
                                }
                            }
                        };

                        try {
                            xhr.send(file);
                        } catch (err) {
                            resolve({
                                file: file.name,
                                ok: false,
                                msg: 'Send error'
                            });
                        }
                    }));

                    const results = await Promise.all(uploadPromises);
                    const success = results.filter(r => r.ok).length;
                    const failed = results.filter(r => !r.ok).length;

                    // Gọi API confirm upload cho những file thành công
                    const successfulUploads = results.filter(r => r.ok).map(r => {
                        const studentCode = extractStudentCode(r.file);
                        return {
                            student_code: studentCode,
                            file_name: r.file
                        };
                    });

                    console.log('Folder - Successful uploads to confirm:', successfulUploads);

                    if (successfulUploads.length > 0) {
                        try {
                            const confirmResponse = await fetch('/students/confirm-upload', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                },
                                body: JSON.stringify({
                                    uploads: successfulUploads
                                })
                            });

                            const confirmData = await confirmResponse.json();
                            console.log('Folder - Confirm response:', confirmData);
                            if (confirmData.success) {
                                showFolderStatus('✅ ' + confirmData.message, 'success');
                            } else {
                                showFolderStatus('⚠️ ' + (confirmData.message ||
                                    'Lỗi khi lưu database'), 'warning');
                            }
                        } catch (confirmErr) {
                            console.error('Confirm upload error:', confirmErr);
                            showFolderStatus('⚠️ Upload thành công nhưng lỗi khi lưu database',
                                'warning');
                        }
                    }

                    // Hiển thị kết quả
                    showFolderStatus(`✅ Tải lên thành công: ${success}/${results.length} ảnh`,
                        success > 0 ? 'success' : 'error');

                    const failedResults = results.filter(r => !r.ok);
                    if (failedResults.length > 0) {
                        appendFolderStatus('--- CÁC ẢNH THẤT BẠI ---', 'error');
                        failedResults.forEach(r => {
                            appendFolderStatus('❌ ' + r.file + ': ' + (r.msg ||
                                'Unknown error'), 'error');
                        });
                    }

                } catch (err) {
                    console.error(err);
                    showFolderStatus('❌ Lỗi: ' + err.message, 'error');
                } finally {
                    btnRegisterFolder.disabled = false;
                    if (folderFiles.length > 0) {
                        document.getElementById('floatingBtnBatch').style.display = 'block';
                    }
                }
            });

            function showFolderStatus(message, type) {
                let colorClass = 'text-info';
                if (type === 'success') colorClass = 'text-success';
                if (type === 'error') colorClass = 'text-danger';
                folderStatusDiv.innerHTML = `<div class="${colorClass}">${message}</div>`;
            }

            function appendFolderStatus(message, type) {
                let colorClass = 'text-info';
                if (type === 'success') colorClass = 'text-success';
                if (type === 'error') colorClass = 'text-danger';
                folderStatusDiv.innerHTML +=
                    `<div class="${colorClass}" style="font-size: 0.9em;">${message}</div>`;
            }

            renderFolderFileList();
        });
    </script>
@endpush
