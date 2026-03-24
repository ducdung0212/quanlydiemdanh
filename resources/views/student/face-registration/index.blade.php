@extends('layouts.app')

@section('content')
    <div class="flex items-center flex-wrap justify-between gap20 mb-27">
        <h3>Đổi ảnh cá nhân</h3>
    </div>

    <div class="wg-box">
        <div id="window-status" class="mb-3 text-muted fs-3">Đang kiểm tra thời gian cho phép đổi ảnh...</div>

        <div class="mb-3">
            <label for="self-photo-input" class="form-label fs-3">Chọn ảnh (tên ảnh phải là mã sinh viên của bạn, ví dụ:
                DH52025001.jpg)</label>
            <input type="file" id="self-photo-input" class="form-control fs-3" accept="image/jpeg,image/png">
        </div>

        <!-- Preview Section -->
        <div id="preview-section" class="mb-3" style="display: none;">
            <label class="form-label fs-3 d-block">Xem trước ảnh:</label>
            <img id="preview-image" src="" alt="Preview" class="img-fluid rounded mb-3"
                style="max-width: 300px; max-height: 400px; border: 2px solid #ddd; padding: 10px;">
            <div class="d-flex gap-2">
                <button id="btn-confirm-photo" class="btn btn-success fs-3">Xác nhận ảnh này</button>
                <button id="btn-change-photo" class="btn btn-secondary fs-3">Chọn ảnh khác</button>
            </div>
        </div>

        <!-- Upload Button (hidden until photo confirmed) -->
        <div id="upload-button-section" style="display: none;">
            <button id="btn-self-photo-upload" class="btn btn-primary fs-3">Tải ảnh lên</button>
        </div>

        <div id="self-photo-result" class="mt-3 fs-3"></div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusEl = document.getElementById('window-status');
            const fileInput = document.getElementById('self-photo-input');
            const uploadBtn = document.getElementById('btn-self-photo-upload');
            const resultEl = document.getElementById('self-photo-result');
            const previewSection = document.getElementById('preview-section');
            const previewImage = document.getElementById('preview-image');
            const uploadButtonSection = document.getElementById('upload-button-section');
            const confirmPhotoBtn = document.getElementById('btn-confirm-photo');
            const changePhotoBtn = document.getElementById('btn-change-photo');

            let canRegister = false;
            let photoConfirmed = false;

            async function loadWindowStatus() {
                try {
                    const response = await fetch('/api/student/face-registration/window', {
                        headers: {
                            'Accept': 'application/json',
                        },
                    });
                    const payload = await response.json();

                    if (!response.ok || !payload.success) {
                        throw new Error(payload.message || 'Không thể kiểm tra thời gian đổi ảnh.');
                    }

                    canRegister = !!payload.can_register;
                    statusEl.className = canRegister ? 'mb-3 text-success fs-3' : 'mb-3 text-danger fs-3';
                    statusEl.textContent = payload.message || '';
                } catch (error) {
                    canRegister = false;
                    statusEl.className = 'mb-3 text-danger fs-3';
                    statusEl.textContent = error.message;
                }
            }

            // Handle file selection to show preview
            fileInput.addEventListener('change', function() {
                const file = fileInput.files?.[0];

                if (!file) {
                    previewSection.style.display = 'none';
                    uploadButtonSection.style.display = 'none';
                    photoConfirmed = false;
                    resultEl.innerHTML = '';
                    return;
                }

                // Validate file type
                if (!['image/jpeg', 'image/png'].includes(file.type)) {
                    resultEl.innerHTML = '<div class="text-danger">Vui lòng chọn ảnh JPG hoặc PNG.</div>';
                    previewSection.style.display = 'none';
                    uploadButtonSection.style.display = 'none';
                    photoConfirmed = false;
                    return;
                }

                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewSection.style.display = 'block';
                    uploadButtonSection.style.display = 'none';
                    photoConfirmed = false;
                    resultEl.innerHTML = '';
                };
                reader.readAsDataURL(file);
            });

            confirmPhotoBtn.addEventListener('click', function() {
                photoConfirmed = true;
                previewSection.style.display = 'none';
                uploadButtonSection.style.display = 'block';
                resultEl.innerHTML = '';
            });

            changePhotoBtn.addEventListener('click', function() {
                fileInput.value = '';
                photoConfirmed = false;
                previewSection.style.display = 'none';
                uploadButtonSection.style.display = 'none';
                resultEl.innerHTML = '';
            });

            uploadBtn.addEventListener('click', async function() {
                resultEl.innerHTML = '';

                if (!canRegister) {
                    resultEl.innerHTML =
                        '<div class="text-danger">Hiện tại bạn chưa được phép đổi ảnh.</div>';
                    return;
                }

                if (!photoConfirmed) {
                    resultEl.innerHTML =
                        '<div class="text-danger">Vui lòng xem trước và xác nhận ảnh trước khi tải lên.</div>';
                    return;
                }

                const file = fileInput.files?.[0];
                if (!file) {
                    resultEl.innerHTML =
                        '<div class="text-danger">Vui lòng chọn một ảnh trước khi tải lên.</div>';
                    return;
                }

                try {
                    uploadBtn.disabled = true;

                    const generateRes = await fetch(
                        '/api/student/face-registration/generate-upload-url', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content'),
                            },
                            body: JSON.stringify({
                                file_name: file.name,
                                file_type: file.type,
                            }),
                        });

                    const generatePayload = await generateRes.json();
                    if (!generateRes.ok || !generatePayload.success) {
                        throw new Error(generatePayload.message || 'Không tạo được URL tải ảnh.');
                    }

                    const uploadRes = await fetch(generatePayload.upload_url, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': file.type,
                        },
                        body: file,
                    });

                    if (!uploadRes.ok) {
                        throw new Error('Tải ảnh lên S3 thất bại.');
                    }

                    const confirmRes = await fetch('/api/student/face-registration/confirm-upload', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                        },
                        body: JSON.stringify({
                            file_name: file.name,
                        }),
                    });

                    const confirmPayload = await confirmRes.json();
                    if (!confirmRes.ok || !confirmPayload.success) {
                        throw new Error(confirmPayload.message ||
                            'Không thể xác nhận ảnh sau khi tải lên.');
                    }

                    resultEl.innerHTML = `<div class="text-success">${confirmPayload.message}</div>`;
                    fileInput.value = '';
                    previewSection.style.display = 'none';
                    uploadButtonSection.style.display = 'none';
                    photoConfirmed = false;
                } catch (error) {
                    resultEl.innerHTML = `<div class="text-danger">${error.message}</div>`;
                } finally {
                    uploadBtn.disabled = false;
                }
            });

            loadWindowStatus();
        });
    </script>
@endpush
