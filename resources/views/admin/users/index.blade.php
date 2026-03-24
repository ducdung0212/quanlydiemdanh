@extends('layouts.app')

@section('content')
    <div class="flex items-center flex-wrap justify-between gap20 mb-27">
        <h3>Quản lý tài khoản</h3>
        <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
            <li>
                <a href="{{ route('dashboard') }}">
                    <div class="text-tiny">Dashboard</div>
                </a>
            </li>
            <li>
                <i class="icon-chevron-right"></i>
            </li>
            <li>
                <div class="text-tiny">Quản lý tài khoản</div>
            </li>
        </ul>
    </div>

    <div class="wg-box">
        <div class="flex items-center justify-between gap10 flex-wrap">
            <div class="wg-filter flex-grow">
                <form class="form-search" id="searchForm">
                    <fieldset class="name">
                        <input type="text" placeholder="Tìm kiếm tài khoản..." class="" name="q"
                            tabindex="2" value="" aria-required="true" id="searchInput">
                    </fieldset>
                    <div class="button-submit">
                        <button class="" type="submit"><i class="icon-search"></i></button>
                    </div>
                </form>
            </div>
            <div class="d-flex gap10 align-items-center">
                <button class="btn btn-danger d-none" id="btnBulkDelete" style="padding: 8px 16px; border-radius: 8px;">
                    <i class="icon-trash-2"></i> Xóa đã chọn (<span id="selectedCount">0</span>)
                </button>
                <button class="tf-button style-2 w208" data-bs-toggle="modal" data-bs-target="#importUserModal"
                    style="background-color: #17a2b8;">
                    <i class="icon-upload"></i> Import Excel
                </button>
                <a class="tf-button style-1 w208" href="#" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="icon-plus"></i>Thêm mới
                </a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th style="width: 50px">
                            <input type="checkbox" id="selectAll" style="cursor: pointer;">
                        </th>
                        <th style="width: 80px">STT</th>
                        <th>Tên</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th style="width: 120px">Action</th>
                    </tr>
                </thead>
                <tbody id="users-table-body">
                </tbody>
            </table>
        </div>

        <div class="divider"></div>
        <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
            <div class="text-tiny text-secondary">
                Hiển thị <span id="pagination-start">0</span>-<span id="pagination-end">0</span> của <span
                    id="pagination-total">0</span> tài khoản
            </div>
            <div class="pagination-controls">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-end mb-0" id="pagination-container">
                    </ul>
                </nav>
            </div>
        </div>
    </div>
    <div id="modal-container"></div>

    <!-- Import User Modal -->
    <div class="modal fade" id="importUserModal" tabindex="-1" aria-labelledby="importUserModalLabel" aria-hidden="true"
        data-bs-backdrop="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fs-3" id="importUserModalLabel">Import danh sách tài khoản từ Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="import-form" class="form-group local-forms">
                        @csrf

                        <div class="mb-3">
                            <label for="import-file" class="form-label fs-3">Chọn file Excel
                                <span class="text-danger">*</span>
                            </label>
                            <input type="file" id="import-file" name="excel_file" class="form-control fs-3"
                                accept=".xlsx,.xls" required>
                            <div class="form-text fs-3">Chỉ chấp nhận file Excel (.xlsx, .xls)</div>
                        </div>

                        <input type="hidden" name="token" id="import_token">
                        <input type="hidden" name="heading_row" id="import_heading_row">

                        <div class="mb-3 d-none" id="headingsPreview">
                            <div class="form-text fs-3 mb-2">Các cột tìm thấy trong file:</div>
                            <div id="headingsList" class="d-flex flex-wrap gap-2 fs-3"></div>
                        </div>

                        <div class="mb-3">
                            <div class="form-text fs-3">
                                <strong>Quy trình:</strong>
                                <ol class="mb-0 mt-2">
                                    <li>Bước 1: Chọn file rồi bấm Tiếp tục để tải tiêu đề cột.</li>
                                    <li>Bước 2: Map cột và bấm Import.</li>
                                </ol>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="role-select" class="form-label fs-3">Chọn vai trò
                                <span class="text-danger">*</span>
                            </label>
                            <select id="role-select" name="role" class="form-select fs-3" required>
                                <option value="">-- Chọn vai trò --</option>
                                <option value="admin">Admin</option>
                                <option value="lecturer">Giảng viên</option>
                                <option value="student">Sinh viên</option>
                            </select>
                        </div>

                        <div class="d-none" id="mappingSection">
                            <div class="alert alert-secondary fs-3" role="alert">
                                Chọn cột tương ứng. Trường <strong>Email</strong> là bắt buộc.
                            </div>
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fs-3">Email <span class="text-danger">*</span></label>
                                    <select class="form-select fs-3 column-mapping" data-field="email"
                                        data-required="true">
                                        <option value="">-- Chọn cột --</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fs-3">Tên hiển thị</label>
                                    <select class="form-select fs-3 column-mapping" data-field="name">
                                        <option value="">(Bỏ qua)</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6" id="mappingPasswordWrap">
                                    <label class="form-label fs-3">Mật khẩu từ file</label>
                                    <select class="form-select fs-3 column-mapping" data-field="password">
                                        <option value="">(Bỏ qua)</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6" id="mappingStudentWrap">
                                    <label class="form-label fs-3">Mã sinh viên</label>
                                    <select class="form-select fs-3 column-mapping" data-field="student_code">
                                        <option value="">(Bỏ qua)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fs-3">Mật khẩu</label>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" id="password-default"
                                        name="password_source" value="default" checked>
                                    <label class="form-check-label fs-3" for="password-default">
                                        Sử dụng mật khẩu mặc định
                                    </label>
                                </div>
                                <input type="password" id="default-password" name="default_password"
                                    class="form-control fs-3 mt-2" placeholder="Nhập mật khẩu mặc định" required>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" id="password-column"
                                        name="password_source" value="column">
                                    <label class="form-check-label fs-3" for="password-column">
                                        Lấy mật khẩu từ cột trong file (cần map trường mật khẩu)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div id="alert-section"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary fs-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary fs-3" id="submit-btn">
                        <i class="fas fa-upload"></i> Import Ngay
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/users-index.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('import-form');
            const fileInput = document.getElementById('import-file');
            const roleSelect = document.getElementById('role-select');
            const defaultPasswordInput = document.getElementById('default-password');
            const passwordDefaultRadio = document.getElementById('password-default');
            const passwordColumnRadio = document.getElementById('password-column');
            const submitBtn = document.getElementById('submit-btn');
            const alertSection = document.getElementById('alert-section');
            const headingTokenInput = document.getElementById('import_token');
            const headingRowInput = document.getElementById('import_heading_row');
            const headingsPreview = document.getElementById('headingsPreview');
            const headingsList = document.getElementById('headingsList');
            const mappingSection = document.getElementById('mappingSection');
            const mappingPasswordWrap = document.getElementById('mappingPasswordWrap');
            const mappingStudentWrap = document.getElementById('mappingStudentWrap');
            const importModalEl = document.getElementById('importUserModal');
            const importModal = new bootstrap.Modal(importModalEl);

            let isPreviewReady = false;
            let detectedHeadings = [];

            function updatePasswordRequirement() {
                if (passwordDefaultRadio.checked) {
                    defaultPasswordInput.required = true;
                    defaultPasswordInput.disabled = false;
                    mappingPasswordWrap.classList.add('d-none');
                } else {
                    defaultPasswordInput.required = false;
                    defaultPasswordInput.disabled = true;
                    defaultPasswordInput.value = '';
                    mappingPasswordWrap.classList.remove('d-none');
                }

                if (isPreviewReady) {
                    applyRequiredByContext();
                }
            }

            function normalizeColumn(value) {
                return value
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^a-z0-9]+/g, '_')
                    .replace(/^_+|_+$/g, '');
            }

            function setSubmitBtnText(text) {
                submitBtn.innerHTML = `<i class="fas fa-upload"></i> ${text}`;
            }

            function resetMappingState() {
                isPreviewReady = false;
                detectedHeadings = [];
                headingTokenInput.value = '';
                headingRowInput.value = '';
                headingsList.innerHTML = '';
                headingsPreview.classList.add('d-none');
                mappingSection.classList.add('d-none');
                document.querySelectorAll('.column-mapping').forEach((select) => {
                    const firstOption = select.dataset.field === 'email' ?
                        '<option value="">-- Chọn cột --</option>' :
                        '<option value="">(Bỏ qua)</option>';
                    select.innerHTML = firstOption;
                });
                setSubmitBtnText('Tiếp tục');
            }

            function applyRequiredByContext() {
                const isStudent = roleSelect.value === 'student';
                mappingStudentWrap.classList.toggle('d-none', !isStudent);

                document.querySelectorAll('.column-mapping').forEach((select) => {
                    const field = select.dataset.field;
                    if (field === 'email') {
                        select.dataset.required = 'true';
                    } else if (field === 'student_code') {
                        select.dataset.required = isStudent ? 'true' : 'false';
                    } else if (field === 'password') {
                        select.dataset.required = passwordColumnRadio.checked ? 'true' : 'false';
                    } else {
                        select.dataset.required = 'false';
                    }
                });
            }

            function populateMappingOptions(headings) {
                const optionsHtml = headings
                    .map((heading) => `<option value="${heading}">${heading}</option>`)
                    .join('');

                document.querySelectorAll('.column-mapping').forEach((select) => {
                    const firstOption = select.dataset.field === 'email' ?
                        '<option value="">-- Chọn cột --</option>' :
                        '<option value="">(Bỏ qua)</option>';
                    select.innerHTML = firstOption + optionsHtml;
                });

                const aliases = {
                    email: ['email', 'mail', 'e_mail'],
                    name: ['name', 'full_name', 'ho_ten', 'ho_va_ten', 'ten'],
                    password: ['password', 'mat_khau', 'pass'],
                    student_code: ['student_code', 'mssv', 'ma_sv', 'ma_sinh_vien'],
                };

                document.querySelectorAll('.column-mapping').forEach((select) => {
                    const field = select.dataset.field;
                    const wanted = aliases[field] || [field];
                    const matched = headings.find((heading) => wanted.includes(normalizeColumn(heading)));
                    if (matched) {
                        select.value = matched;
                    }
                });

                applyRequiredByContext();
            }

            function getMappingPayload() {
                const mapping = {};
                document.querySelectorAll('.column-mapping').forEach((select) => {
                    if (select.value) {
                        mapping[select.dataset.field] = select.value;
                    }
                });
                return mapping;
            }

            function validateRequiredMapping() {
                const requiredMissing = [];
                document.querySelectorAll('.column-mapping').forEach((select) => {
                    if (select.dataset.required === 'true' && !select.value) {
                        requiredMissing.push(select.dataset.field);
                    }
                });

                if (requiredMissing.length > 0) {
                    showAlert('danger', `Thiếu mapping bắt buộc: ${requiredMissing.join(', ')}`);
                    return false;
                }

                return true;
            }

            function showAlert(type, message) {
                alertSection.innerHTML =
                    `<div class="alert alert-${type} alert-dismissible fade show fs-3" role="alert">${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>`;
            }

            roleSelect.addEventListener('change', function() {
                if (isPreviewReady) {
                    applyRequiredByContext();
                }
            });

            passwordDefaultRadio.addEventListener('change', updatePasswordRequirement);
            passwordColumnRadio.addEventListener('change', updatePasswordRequirement);

            updatePasswordRequirement();
            resetMappingState();

            submitBtn.addEventListener('click', async function(e) {
                e.preventDefault();

                if (!roleSelect.value) {
                    showAlert('danger', 'Vui lòng chọn vai trò');
                    return;
                }

                if (passwordDefaultRadio.checked && !defaultPasswordInput.value) {
                    showAlert('danger', 'Vui lòng nhập mật khẩu mặc định');
                    return;
                }

                submitBtn.disabled = true;
                showAlert('info', '<strong>Đang xử lý...</strong> Vui lòng chờ');

                try {
                    if (!isPreviewReady) {
                        if (!fileInput.files.length) {
                            showAlert('danger', 'Vui lòng chọn file Excel');
                            submitBtn.disabled = false;
                            return;
                        }

                        const previewData = new FormData();
                        previewData.append('excel_file', fileInput.files[0]);

                        const previewRes = await fetch("{{ route('admin.users.import.preview') }}", {
                            method: 'POST',
                            body: previewData,
                            headers: {
                                Accept: 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').getAttribute('content'),
                            },
                        });

                        const previewPayload = await previewRes.json();
                        if (!previewRes.ok || !previewPayload.success) {
                            throw new Error(previewPayload.message || 'Không đọc được tiêu đề cột.');
                        }

                        detectedHeadings = previewPayload.headings || [];
                        headingTokenInput.value = previewPayload.token || '';
                        headingRowInput.value = previewPayload.heading_row || 1;

                        headingsList.innerHTML = detectedHeadings
                            .map((heading) => `<span class="badge bg-secondary fs-3">${heading}</span>`)
                            .join('');

                        headingsPreview.classList.remove('d-none');
                        mappingSection.classList.remove('d-none');
                        populateMappingOptions(detectedHeadings);
                        isPreviewReady = true;
                        setSubmitBtnText('Import');
                        showAlert('success', 'Đã tải tiêu đề cột. Vui lòng map trường và bấm Import.');
                    } else {
                        if (!validateRequiredMapping()) {
                            submitBtn.disabled = false;
                            return;
                        }

                        const importData = new FormData();
                        importData.append('token', headingTokenInput.value);
                        importData.append('heading_row', headingRowInput.value || '1');
                        importData.append('role', roleSelect.value);
                        importData.append('use_password_column', passwordColumnRadio.checked ? '1' :
                            '0');
                        if (defaultPasswordInput.value) {
                            importData.append('default_password', defaultPasswordInput.value);
                        }

                        const mapping = getMappingPayload();
                        Object.keys(mapping).forEach((field) => {
                            importData.append(`mapping[${field}]`, mapping[field]);
                        });

                        const importRes = await fetch("{{ route('admin.users.import') }}", {
                            method: 'POST',
                            body: importData,
                            headers: {
                                Accept: 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').getAttribute('content'),
                            },
                        });

                        const importPayload = await importRes.json();
                        if (!importRes.ok || !importPayload.success) {
                            throw new Error(importPayload.message || 'Import thất bại.');
                        }

                        showAlert('success', `<strong>Thành công!</strong> ${importPayload.message}`);

                        setTimeout(() => {
                            importModal.hide();
                            location.reload();
                        }, 1200);
                    }
                } catch (error) {
                    showAlert('danger', `Lỗi: ${error.message}`);
                } finally {
                    submitBtn.disabled = false;
                }
            });

            fileInput.addEventListener('change', function() {
                resetMappingState();
                alertSection.innerHTML = '';
            });

            importModalEl.addEventListener('hidden.bs.modal', function() {
                form.reset();
                alertSection.innerHTML = '';
                updatePasswordRequirement();
                resetMappingState();
            });
        });
    </script>
@endpush
