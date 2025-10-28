@extends('layouts.app')

@section('content')
   

    <div class="face-registration">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Đăng ký khuôn mặt hàng loạt</h3>
            <h4 class="text-muted">Tải lên nhiều ảnh; tên tệp phải là <code>[MSSV].jpg</code></h4>
        </div>

        <div class="wg-box p-4 shadow-sm">
            <div id="dropzone" class="border rounded p-3 mb-3" style="position:relative;overflow:hidden;background:#fbfcfe; cursor: pointer;">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:56px;height:56px;background:#eef6ff;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                        <i class="icon-download" style="font-size:22px;color:var(--Main);"></i>
                    </div>
                    <div>
                        <div class="fw-bold">Kéo & thả ảnh vào đây hoặc bấm để chọn</div> <br>
                        <div class="fw">Hỗ trợ JPEG/PNG. Tên tệp phải chứa mã sinh viên để tự động đăng ký.</div>
                    </div>
                </div>
                <input type="file" id="face_image_input" class="form-control" accept="image/jpeg,image/png" multiple style="opacity:0;position:absolute;inset:0;width:100%;height:100%;cursor:pointer;" aria-label="Chọn ảnh">
            </div>

            <div id="fileList" class="mb-3">
                <!-- Danh sách file sẽ hiển thị ở đây -->
            </div>

            <div class="d-flex gap-2">
                <button id="btnRegisterFace" class="btn btn-primary btn-lg">
                    <i class="icon-user-plus me-2"></i> Đăng ký (các) khuôn mặt
                </button>
                <button id="btnClear" class="btn btn-outline-secondary">Xóa danh sách</button>
            </div>

            <div id="register_status" class="mt-3"></div>
        </div>

        <meta name="csrf-token" content="{{ csrf_token() }}">

    </div>

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

    // Keep an internal list of selected files (File objects)
    let selectedFiles = [];

    function renderFileList() {
        fileListEl.innerHTML = '';
        if (selectedFiles.length === 0) {
            fileListEl.innerHTML = '<div class="text-muted">Chưa có tệp nào được chọn.</div>';
            return;
        }

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
            meta.innerHTML = `<div class="fw-semibold">${file.name}</div><div class="text-muted small">${Math.round(file.size/1024)} KB</div>`;

            const progressWrap = document.createElement('div');
            progressWrap.style.width = '160px';
            progressWrap.innerHTML = `<div class="progress" style="height:8px;"><div class="progress-bar" role="progressbar" style="width:0%" aria-valuenow="0"></div></div>`;

            const removeBtn = document.createElement('button');
            removeBtn.className = 'btn btn-sm btn-outline-danger ms-2';
            removeBtn.textContent = 'Xóa';
            removeBtn.addEventListener('click', () => {
                selectedFiles.splice(idx,1);
                renderFileList();
            });

            item.appendChild(thumb);
            item.appendChild(meta);
            item.appendChild(progressWrap);
            item.appendChild(removeBtn);

            fileListEl.appendChild(item);
        });
    }

    // Handle file input selection
    fileInput.addEventListener('change', function(e){
        const files = Array.from(e.target.files || []);
        // append but avoid duplicates by name
        files.forEach(f => {
            if (!selectedFiles.some(sf => sf.name === f.name && sf.size === f.size)) selectedFiles.push(f);
        });
        renderFileList();
    });

    // Drag & drop support
    ['dragenter','dragover','dragleave','drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e){ e.preventDefault(); e.stopPropagation(); }

    dropzone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = Array.from(dt.files || []).filter(f => /^image\//.test(f.type));
        files.forEach(f => { if (!selectedFiles.some(sf => sf.name === f.name && sf.size === f.size)) selectedFiles.push(f); });
        renderFileList();
    });

    // clear list
    btnClear.addEventListener('click', function(){ selectedFiles = []; fileInput.value = ''; renderFileList(); showStatus('Danh sách đã được xóa', 'info'); });

    // Register button — upload using presigned URLs and show progress
    btnRegister.addEventListener('click', async function() {
        if (selectedFiles.length === 0) {
            showStatus('Vui lòng chọn (các) tệp ảnh. Tên tệp phải là [MSSV].jpg', 'error');
            return;
        }

        btnRegister.disabled = true;
        showStatus(`Đã chọn ${selectedFiles.length} tệp. Đang xin quyền tải lên...`, 'info');

        const fileInfos = selectedFiles.map(f => ({ file_name: f.name, file_type: f.type }));

        try {
            const response = await fetch('/api/students/generate-upload-urls', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ files: fileInfos })
            });

            const data = await response.json();
            if (!data.success) throw new Error(data.message || 'Không thể lấy URL từ server.');

            const uploadUrls = data.data;
            showStatus('Đang tải ảnh lên S3 (song song)...', 'info');

            // perform uploads with progress using XHR
            const uploadPromises = selectedFiles.map((file, idx) => new Promise((resolve) => {
                const urlInfo = uploadUrls.find(u => u.file_name === file.name);
                if (!urlInfo || !urlInfo.success) { resolve({ file: file.name, ok: false, msg: 'Server không cấp quyền' }); return; }

                const xhr = new XMLHttpRequest();
                xhr.open('PUT', urlInfo.upload_url, true);
                // Don't send cookies/credentials with S3 PUT
                xhr.withCredentials = false;

                // If server included ContentType in the presign, set header to match
                try { xhr.setRequestHeader('Content-Type', file.type); } catch (e) { console.warn('Could not set Content-Type header on XHR', e); }

                console.log('Uploading', file.name, urlInfo.upload_url);

                xhr.upload.addEventListener('progress', function(e){
                    if (e.lengthComputable) {
                        const percent = Math.round((e.loaded / e.total) * 100);
                        // update progress bar for this file
                        const bar = fileListEl.querySelectorAll('.progress-bar')[idx];
                        if (bar) { bar.style.width = percent+'%'; bar.setAttribute('aria-valuenow', percent); }
                    }
                });

                xhr.onerror = function(evt) {
                    console.error('XHR error uploading', file.name, evt, xhr.status, xhr.statusText);
                    resolve({ file: file.name, ok: false, msg: 'Network error or CORS (status: ' + xhr.status + ')'});
                };

                xhr.onabort = function() {
                    console.warn('XHR aborted for', file.name);
                    resolve({ file: file.name, ok: false, msg: 'Aborted'});
                };

                xhr.onreadystatechange = function(){
                    if (xhr.readyState === 4) {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            console.log('Upload succeeded for', file.name, xhr.status);
                            resolve({ file: file.name, ok: true });
                        } else {
                            // Log responseText to help diagnose (may be empty for S3)
                            console.error('Upload failed for', file.name, 'status=', xhr.status, 'response=', xhr.responseText);
                            resolve({ file: file.name, ok: false, msg: 'S3 lỗi: ' + xhr.status + (xhr.responseText ? ' - ' + xhr.responseText : '') });
                        }
                    }
                };

                // Send file
                try {
                    xhr.send(file);
                } catch (err) {
                    console.error('XHR send error for', file.name, err);
                    resolve({ file: file.name, ok: false, msg: 'XHR send error' });
                }
            }));

            const results = await Promise.all(uploadPromises);
            showStatus('--- KẾT QUẢ TẢI LÊN ---', 'info');
            results.forEach(r => appendStatus((r.ok? '✅ ':'❌ ') + r.file + (r.msg ? ' ('+r.msg+')' : ''), r.ok ? 'success' : 'error'));

        } catch (err) {
            console.error(err);
            showStatus('Lỗi: ' + err.message, 'error');
        } finally {
            btnRegister.disabled = false;
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
});
</script>
@endpush