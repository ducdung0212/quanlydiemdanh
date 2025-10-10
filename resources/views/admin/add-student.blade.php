<body class="body">
    <div id="wrapper">
        <div id="page" class="">
            <div class="layout-wrap">
                @include('layouts_main.sidebar')
                
                <div class="section-content-right">
                    @include('layouts_main.header')
                    
                    <div class="main-content">
                        <div class="main-content-inner">
                            <div class="main-content-wrap">
                                <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                                    <h3>Quản lý Sinh Viên</h3>
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
                                            <div class="text-tiny">Quản lý Sinh Viên</div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="wg-box">
                                    <div class="flex items-center justify-between gap10 flex-wrap">
                                        <div class="wg-filter flex-grow">
                                            <form class="form-search" id="searchForm">
                                                <fieldset class="name">
                                                    <input type="text" placeholder="Tìm kiếm sinh viên..." class="" name="q"
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
                                            <a class="tf-button style-2 w208" href="#" id="importExcelBtn">
                                                <i class="icon-upload"></i>Import Excel
                                            </a>
                                            <a class="tf-button style-1 w208" href="#" data-bs-toggle="modal" data-bs-target="#addStudentModal">
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
                                                    <th style="width: 60px">STT</th>
                                                    <th>Mã SV</th>
                                                    <th>Tên</th>
                                                    <th>Lớp</th>
                                                    <th style="width: 150px">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="students-table-body">
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="divider"></div>
                                    <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                                        <div class="text-tiny text-secondary">
                                            Hiển thị <span id="pagination-start">0</span>-<span id="pagination-end">0</span> của <span id="pagination-total">0</span> sinh viên
                                        </div>
                                        <div class="pagination-controls">
                                            <nav aria-label="Page navigation">
                                                <ul class="pagination justify-content-end mb-0" id="pagination-container">
                                                </ul>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @include('layouts_main.footer')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Thêm sinh viên -->
    <div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);">
                <div class="modal-header" style="border-bottom: 1px solid #eee; padding: 20px 25px;">
                    <h5 class="modal-title" id="addStudentModalLabel" style="font-size: 1.3rem; font-weight: 600;">Thêm sinh viên mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addStudentForm">
                    <div class="modal-body" style="padding: 25px;">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="student_code" class="form-label" style="font-weight: 500;">Mã Sinh Viên *</label>
                                <input type="text" class="form-control" id="student_code" name="student_code" required
                                       style="border-radius: 8px; padding: 12px; border: 1px solid #ddd;">
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="full_name" class="form-label" style="font-weight: 500;">Họ và Tên *</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required
                                       style="border-radius: 8px; padding: 12px; border: 1px solid #ddd;">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="class" class="form-label" style="font-weight: 500;">Lớp *</label>
                                <input type="text" class="form-control" id="class" name="class_code" required
                                       style="border-radius: 8px; padding: 12px; border: 1px solid #ddd;">
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="phone" class="form-label" style="font-weight: 500;">Số Điện Thoại *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required
                                       style="border-radius: 8px; padding: 12px; border: 1px solid #ddd;">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="email" class="form-label" style="font-weight: 500;">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" required
                                       style="border-radius: 8px; padding: 12px; border: 1px solid #ddd;">
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="avatar_url" class="form-label" style="font-weight: 500;">Link Ảnh đại diện</label>
                                <input type="url" class="form-control" id="avatar_url" name="photo_url" placeholder="https://example.com/image.jpg"
                                       style="border-radius: 8px; padding: 12px; border: 1px solid #ddd;">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid #eee; padding: 20px 25px;">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                                style="border-radius: 8px; padding: 10px 20px; border: none;">Hủy</button>
                        <button type="submit" class="btn btn-primary" id="btnAddStudent"
                                style="border-radius: 8px; padding: 10px 20px; background-color: #2377FC; border: none;">
                            <span class="btn-text">Thêm</span>
                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Sửa sinh viên -->
    <div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);">
                <div class="modal-header" style="border-bottom: 1px solid #eee; padding: 20px 25px;">
                    <h5 class="modal-title" id="editStudentModalLabel" style="font-size: 1.3rem; font-weight: 600;">Sửa thông tin sinh viên</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editStudentForm">
                    <div class="modal-body" style="padding: 25px;">
                        <input type="hidden" id="editStudentId">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="editStudentCode" class="form-label" style="font-weight: 500;">Mã Sinh Viên *</label>
                                <input type="text" class="form-control" id="editStudentCode" name="student_code" required
                                       style="border-radius: 8px; padding: 12px; border: 1px solid #ddd;">
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="editFullName" class="form-label" style="font-weight: 500;">Họ và Tên *</label>
                                <input type="text" class="form-control" id="editFullName" name="full_name" required
                                       style="border-radius: 8px; padding: 12px; border: 1px solid #ddd;">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="editClass" class="form-label" style="font-weight: 500;">Lớp *</label>
                                <input type="text" class="form-control" id="editClass" name="class_code" required
                                       style="border-radius: 8px; padding: 12px; border: 1px solid #ddd;">
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="editPhone" class="form-label" style="font-weight: 500;">Số Điện Thoại *</label>
                                <input type="tel" class="form-control" id="editPhone" name="phone" required
                                       style="border-radius: 8px; padding: 12px; border: 1px solid #ddd;">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="editEmail" class="form-label" style="font-weight: 500;">Email *</label>
                                <input type="email" class="form-control" id="editEmail" name="email" required
                                       style="border-radius: 8px; padding: 12px; border: 1px solid #ddd;">
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="editAvatarUrl" class="form-label" style="font-weight: 500;">Link Ảnh đại diện</label>
                                <input type="url" class="form-control" id="editAvatarUrl" name="photo_url" placeholder="https://example.com/image.jpg"
                                       style="border-radius: 8px; padding: 12px; border: 1px solid #ddd;">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid #eee; padding: 20px 25px;">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                                style="border-radius: 8px; padding: 10px 20px; border: none;">Hủy</button>
                        <button type="submit" class="btn btn-primary" id="btnEditStudent"
                                style="border-radius: 8px; padding: 10px 20px; background-color: #2377FC; border: none;">
                            <span class="btn-text">Lưu thay đổi</span>
                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Xem chi tiết sinh viên -->
    <div class="modal fade" id="viewStudentModal" tabindex="-1" aria-labelledby="viewStudentModalLabel" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);">
                <div class="modal-header" style="border-bottom: 1px solid #eee; padding: 20px 25px;">
                    <h5 class="modal-title" id="viewStudentModalLabel" style="font-size: 1.3rem; font-weight: 600;">Thông tin chi tiết sinh viên</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding: 25px;">
                    <div class="row mb-4">
                        <div class="col-md-3 text-center">
                            <div id="studentAvatarContainer">
                                <img id="studentAvatar" src="" alt="Ảnh đại diện" class="img-fluid rounded" style="max-height: 200px;">
                                <div id="noAvatar" class="text-muted mt-2" style="display: none;">Không có ảnh</div>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <strong>Mã Sinh Viên:</strong>
                                    <div id="viewStudentCode" class="text-primary"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Họ và Tên:</strong>
                                    <div id="viewFullName"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Lớp:</strong>
                                    <div id="viewClass"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Số Điện Thoại:</strong>
                                    <div id="viewPhone"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Email:</strong>
                                    <div id="viewEmail"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #eee; padding: 20px 25px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            style="border-radius: 8px; padding: 10px 20px; border: none;">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Import Excel -->
    <div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importExcelModalLabel" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);">
                <div class="modal-header" style="border-bottom: 1px solid #eee; padding: 20px 25px;">
                    <h5 class="modal-title" id="importExcelModalLabel" style="font-size: 1.3rem; font-weight: 600;">Import danh sách sinh viên từ Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="importExcelForm" enctype="multipart/form-data">
                    <div class="modal-body" style="padding: 25px;">
                        <div class="mb-4">
                            <label for="excel_file" class="form-label" style="font-weight: 500;">Chọn file Excel</label>
                            <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx,.xls" required
                                   style="border-radius: 8px; padding: 12px; border: 1px solid #ddd;">
                            <div class="form-text">Chỉ chấp nhận file Excel (.xlsx, .xls)</div>
                        </div>
                        <div class="mb-3">
                            <div class="form-text">
                                <strong>Lưu ý:</strong> File Excel cần có các cột sau:
                                <ul>
                                    <li>Mã Sinh Viên</li>
                                    <li>Họ và Tên</li>
                                    <li>Lớp</li>
                                    <li>Số Điện Thoại</li>
                                    <li>Email</li>
                                    <li>Link Ảnh (tùy chọn)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid #eee; padding: 20px 25px;">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                                style="border-radius: 8px; padding: 10px 20px; border: none;">Hủy</button>
                        <button type="submit" class="btn btn-primary" id="btnImportExcel"
                                style="border-radius: 8px; padding: 10px 20px; background-color: #2377FC; border: none;">
                            <span class="btn-text">Import</span>
                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <link href="{{ asset('css/common_admin.css') }}" rel="stylesheet">
    <link href="{{ asset('css/student_css.css') }}" rel="stylesheet">

    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap-select.min.js') }}"></script>   
    <script src="{{ asset('js/apexcharts/apexcharts.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- Configuration ---
        const API_BASE_URL = '/api/students';
        const ITEMS_PER_PAGE = 10;
        const DEBOUNCE_DELAY = 300;

        // --- DOM Elements ---
        const wrapper = document.getElementById('wrapper');
        const searchInput = document.getElementById('searchInput');
        const searchForm = document.getElementById('searchForm');
        const tableBody = document.getElementById('students-table-body');
        const paginationContainer = document.getElementById('pagination-container');
        const paginationInfo = {
            start: document.getElementById('pagination-start'),
            end: document.getElementById('pagination-end'),
            total: document.getElementById('pagination-total')
        };
        const btnBulkDelete = document.getElementById('btnBulkDelete');
        const selectedCountSpan = document.getElementById('selectedCount');
        const selectAllCheckbox = document.getElementById('selectAll');
        const importExcelBtn = document.getElementById('importExcelBtn');
        
        // --- Modals ---
        const addStudentModal = new bootstrap.Modal(document.getElementById('addStudentModal'));
        const editStudentModal = new bootstrap.Modal(document.getElementById('editStudentModal'));
        const viewStudentModal = new bootstrap.Modal(document.getElementById('viewStudentModal'));
        const importExcelModal = new bootstrap.Modal(document.getElementById('importExcelModal'));
        const addStudentForm = document.getElementById('addStudentForm');
        const editStudentForm = document.getElementById('editStudentForm');
        const importExcelForm = document.getElementById('importExcelForm');

        // --- State ---
        let currentPage = 1;
        let currentQuery = '';
        let paginationData = null;
        let isLoading = false;
        let selectedStudents = new Set();
        
        // --- Utility Functions ---

        /**
         * Generic API fetch function with error handling and CSRF token.
         */
        async function apiFetch(url, options = {}) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            const defaultHeaders = {
                'Accept': 'application/json'
            };

            if (csrfToken) {
                defaultHeaders['X-CSRF-TOKEN'] = csrfToken;
            }

            if (options.body && typeof options.body === 'string') {
                defaultHeaders['Content-Type'] = 'application/json';
            }

            const config = {
                ...options,
                headers: { ...defaultHeaders, ...options.headers }
            };

            const response = await fetch(url, config);

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: response.statusText }));
                throw new Error(errorData.message || 'An unknown error occurred.');
            }

            return response.json();
        }
        
        const debounce = (func, delay) => {
            let timeout;
            return (...args) => {
                clearTimeout(timeout);
                timeout = setTimeout(() => func(...args), delay);
            };
        };

        const escapeHtml = (text) => {
            if (typeof text !== 'string') return text;
            return text.replace(/[&<>"']/g, m => ({'&': '&amp;','<': '&lt;','>': '&gt;','"': '&quot;',"'": '&#039;'})[m]);
        };
        
        function toggleButtonLoading(button, isLoading) {
            const btnText = button.querySelector('.btn-text');
            const spinner = button.querySelector('.spinner-border');
            button.disabled = isLoading;
            if (btnText) btnText.classList.toggle('d-none', isLoading);
            if (spinner) spinner.classList.toggle('d-none', !isLoading);
        }

        // --- Core Application Logic ---

        async function fetchStudents(page = 1, query = '') {
            if (isLoading) return;
            isLoading = true;
            tableBody.innerHTML = `<tr><td colspan="6" class="text-center">Đang tải...</td></tr>`;

            try {
                const url = `${API_BASE_URL}?page=${page}&limit=${ITEMS_PER_PAGE}&q=${encodeURIComponent(query)}`;
                const result = await apiFetch(url);

                if (result.success && result.data) {
                    paginationData = result.data;
                    currentPage = paginationData.current_page;
                    currentQuery = query;
                    updateURL(currentPage, currentQuery);
                    render();
                } else {
                    throw new Error('Invalid API response format');
                }
            } catch (error) {
                console.error("Failed to fetch students:", error);
                tableBody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Lỗi khi tải dữ liệu.</td></tr>`;
                paginationData = null;
                render();
            } finally {
                isLoading = false;
            }
        }

        function render() {
            renderTable();
            renderPagination();
            updateBulkDeleteButton();
            updateSelectAllCheckbox();
        }

        function renderTable() {
            if (!paginationData || !paginationData.data || paginationData.data.length === 0) {
                tableBody.innerHTML = `<tr><td colspan="6" class="text-center">${currentQuery ? 'Không tìm thấy sinh viên nào' : 'Không có dữ liệu'}</td></tr>`;
                return;
            }

            const { data: students, from } = paginationData;
            const rowsHtml = students.map((student, index) => {
                const isChecked = selectedStudents.has(student.student_code) ? 'checked' : '';
                const studentJson = JSON.stringify(student).replace(/"/g, '&quot;');
                return `
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" class="student-checkbox" value="${escapeHtml(student.student_code)}" ${isChecked} style="cursor: pointer;" data-action="toggle-select">
                        </td>
                        <td class="text-center">${from + index}</td>
                        <td>${escapeHtml(student.student_code)}</td>
                        <td>${escapeHtml(student.full_name)}</td>
                        <td>${escapeHtml(student.class_code || '')}</td>
                        <td>
                            <div class="list-icon-function">
                                <a href="#" data-action="view-student" data-student="${studentJson}">
                                    <div class="item view"><i class="icon-eye"></i></div>
                                </a>
                                <a href="#" data-action="edit-student" data-student="${studentJson}">
                                    <div class="item edit"><i class="icon-edit-3"></i></div>
                                </a>
                                <a href="#" data-action="delete-student" data-student_code="${escapeHtml(student.student_code)}">
                                    <div class="item text-danger delete"><i class="icon-trash-2"></i></div>
                                </a>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');

            tableBody.innerHTML = rowsHtml;
        }
        
        function renderPagination() {
            if (!paginationData || paginationData.last_page <= 1) {
                paginationContainer.innerHTML = '';
                paginationInfo.start.textContent = paginationData?.from || 0;
                paginationInfo.end.textContent = paginationData?.to || 0;
                paginationInfo.total.textContent = paginationData?.total || 0;
                return;
            }

            const { current_page: page, last_page, from, to, total } = paginationData;
            paginationInfo.start.textContent = from;
            paginationInfo.end.textContent = to;
            paginationInfo.total.textContent = total;

            const createPageLink = (p, text, isDisabled = false, isActive = false) => {
                const disabledClass = isDisabled ? 'disabled' : '';
                const activeClass = isActive ? 'active' : '';
                return `<li class="page-item ${disabledClass} ${activeClass}"><a class="page-link" href="#" data-page="${p}">${text}</a></li>`;
            };

            let paginationHtml = createPageLink(page - 1, '<i class="icon-chevron-left"></i>', page === 1);

            // Pagination logic
            const pages = [];
            for (let i = 1; i <= last_page; i++) {
                if (i === 1 || i === last_page || (i >= page - 2 && i <= page + 2)) {
                    pages.push(i);
                }
            }
            
            let lastp = 0;
            for (const p of pages) {
                if (lastp + 1 !== p) {
                    paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
                paginationHtml += createPageLink(p, p, false, p === page);
                lastp = p;
            }

            paginationHtml += createPageLink(page + 1, '<i class="icon-chevron-right"></i>', page === last_page);
            paginationContainer.innerHTML = paginationHtml;
        }

        // --- Event Handlers (using Event Delegation) ---
        
        wrapper.addEventListener('click', async (e) => {
            const target = e.target;
            const actionTarget = target.closest('[data-action]');
            
            if (actionTarget) {
                e.preventDefault();
                const { action } = actionTarget.dataset;

                switch (action) {
                    case 'view-student':
                        handleViewClick(actionTarget);
                        break;
                    case 'edit-student':
                        handleEditClick(actionTarget);
                        break;
                    case 'delete-student':
                        handleDeleteClick(actionTarget.dataset.student_code);
                        break;
                    case 'toggle-select':
                        handleCheckboxChange(target);
                        break;
                }
            } else if (target.closest('.page-link')) {
                e.preventDefault();
                const pageLink = target.closest('.page-link');
                if (pageLink.parentElement.classList.contains('disabled')) return;
                const page = parseInt(pageLink.dataset.page);
                if (page && page !== currentPage) {
                    fetchStudents(page, currentQuery);
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }
        });

        function handleViewClick(target) {
            const student = JSON.parse(target.dataset.student);
            document.getElementById('viewStudentCode').textContent = student.student_code;
            document.getElementById('viewFullName').textContent = student.full_name;
            document.getElementById('viewClass').textContent = student.class_code || '';
            document.getElementById('viewPhone').textContent = student.phone || '';
            document.getElementById('viewEmail').textContent = student.email || '';
            
            const avatarImg = document.getElementById('studentAvatar');
            const noAvatarDiv = document.getElementById('noAvatar');
            if (student.photo_url) {
                avatarImg.src = student.photo_url;
                avatarImg.style.display = 'block';
                noAvatarDiv.style.display = 'none';
            } else {
                avatarImg.style.display = 'none';
                noAvatarDiv.style.display = 'block';
            }
            
            viewStudentModal.show();
        }

        function handleEditClick(target) {
            const student = JSON.parse(target.dataset.student);
            document.getElementById('editStudentId').value = student.student_code;
            document.getElementById('editStudentCode').value = student.student_code;
            document.getElementById('editFullName').value = student.full_name;
            document.getElementById('editClass').value = student.class_code || '';
            document.getElementById('editPhone').value = student.phone || '';
            document.getElementById('editEmail').value = student.email || '';
            document.getElementById('editAvatarUrl').value = student.photo_url || '';
            editStudentModal.show();
        }

        function handleDeleteClick(studentCode) {
            if (confirm('Bạn có chắc chắn muốn xóa sinh viên này?')) {
                deleteStudent(studentCode);
            }
        }
        
        function handleCheckboxChange(checkbox) {
            const studentCode = checkbox.value;
            if (checkbox.checked) {
                selectedStudents.add(studentCode);
            } else {
                selectedStudents.delete(studentCode);
            }
            updateBulkDeleteButton();
            updateSelectAllCheckbox();
        }

        selectAllCheckbox.addEventListener('change', (e) => {
            const isChecked = e.target.checked;
            document.querySelectorAll('.student-checkbox').forEach(checkbox => {
                checkbox.checked = isChecked;
                const studentCode = checkbox.value;
                if (isChecked) {
                    selectedStudents.add(studentCode);
                } else {
                    selectedStudents.delete(studentCode);
                }
            });
            updateBulkDeleteButton();
        });

        // --- Update UI State Functions ---
        
        function updateBulkDeleteButton() {
            const count = selectedStudents.size;
            selectedCountSpan.textContent = count;
            btnBulkDelete.classList.toggle('d-none', count === 0);
        }

        function updateSelectAllCheckbox() {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            if (checkboxes.length === 0) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
                return;
            }
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            const someChecked = Array.from(checkboxes).some(cb => cb.checked);
            
            selectAllCheckbox.checked = allChecked;
            selectAllCheckbox.indeterminate = someChecked && !allChecked;
        }

        // --- CRUD Operations ---
        
        addStudentForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const button = e.currentTarget.querySelector('#btnAddStudent');
            const formData = new FormData(addStudentForm);
            const data = Object.fromEntries(formData.entries());

            if (!data.student_code || !data.full_name || !data.class_code || !data.phone || !data.email) {
                return alert('Vui lòng điền đầy đủ thông tin bắt buộc!');
            }
            
            toggleButtonLoading(button, true);
            try {
                await apiFetch(API_BASE_URL, {
                    method: 'POST',
                    body: JSON.stringify(data),
                });
                alert('Thêm sinh viên thành công!');
                addStudentModal.hide();
                addStudentForm.reset();
                fetchStudents(1, '');
            } catch (error) {
                alert(`Lỗi: ${error.message}`);
            } finally {
                toggleButtonLoading(button, false);
            }
        });

        editStudentForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const button = e.currentTarget.querySelector('#btnEditStudent');
            const studentCode = document.getElementById('editStudentId').value;
            const formData = new FormData(editStudentForm);
            const data = Object.fromEntries(formData.entries());

            if (!data.student_code || !data.full_name || !data.class_code || !data.phone || !data.email) {
                return alert('Vui lòng điền đầy đủ thông tin bắt buộc!');
            }

            toggleButtonLoading(button, true);
            try {
                await apiFetch(`${API_BASE_URL}/${studentCode}`, {
                    method: 'PUT',
                    body: JSON.stringify(data)
                });
                alert('Cập nhật sinh viên thành công!');
                editStudentModal.hide();
                fetchStudents(currentPage, currentQuery);
            } catch (error) {
                alert(`Lỗi: ${error.message}`);
            } finally {
                toggleButtonLoading(button, false);
            }
        });
        
        async function deleteStudent(studentCode) {
            try {
                await apiFetch(`${API_BASE_URL}/${studentCode}`, { method: 'DELETE' });
                alert('Xóa sinh viên thành công!');
                selectedStudents.delete(studentCode);
                const isLastItemOnPage = paginationData.data.length === 1 && currentPage > 1;
                fetchStudents(isLastItemOnPage ? currentPage - 1 : currentPage, currentQuery);
            } catch (error) {
                alert(`Lỗi: ${error.message}`);
            }
        }

        btnBulkDelete.addEventListener('click', async () => {
            if (selectedStudents.size === 0) return;
            if (!confirm(`Bạn có chắc chắn muốn xóa ${selectedStudents.size} sinh viên đã chọn?`)) return;

            try {
                await apiFetch(`${API_BASE_URL}/bulk-delete`, {
                    method: 'POST',
                    body: JSON.stringify({ student_codes: Array.from(selectedStudents) })
                });
                alert('Xóa các sinh viên đã chọn thành công!');
                selectedStudents.clear();
                fetchStudents(1, '');
            } catch (error) {
                alert(`Lỗi: ${error.message}`);
            }
        });

        // --- Import Excel ---
        importExcelBtn.addEventListener('click', (e) => {
            e.preventDefault();
            importExcelModal.show();
        });

        importExcelForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const button = e.currentTarget.querySelector('#btnImportExcel');
            const fileInput = document.getElementById('excel_file');
            const file = fileInput.files[0];

            if (!file) {
                return alert('Vui lòng chọn file Excel!');
            }

            const formData = new FormData();
            formData.append('excel_file', file);

            toggleButtonLoading(button, true);
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const headers = { 'Accept': 'application/json' };
                if (csrfToken) headers['X-CSRF-TOKEN'] = csrfToken;

                const response = await fetch(`${API_BASE_URL}/import`, {
                    method: 'POST',
                    headers: headers,
                    body: formData
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({ message: response.statusText }));
                    throw new Error(errorData.message || 'Không thể import file.');
                }

                const result = await response.json();
                alert('Import dữ liệu thành công!');
                importExcelModal.hide();
                importExcelForm.reset();
                fetchStudents(1, '');
            } catch (error) {
                alert(`Lỗi: ${error.message}`);
            } finally {
                toggleButtonLoading(button, false);
            }
        });
        
        // --- Search ---
        const handleSearch = debounce(query => fetchStudents(1, query), DEBOUNCE_DELAY);
        searchInput.addEventListener('input', e => handleSearch(e.target.value));
        searchForm.addEventListener('submit', e => {
            e.preventDefault();
            fetchStudents(1, searchInput.value);
        });

        // --- URL State Management ---
        function updateURL(page, query) {
            const url = new URL(window.location);
            url.searchParams.set('page', page);
            if (query) url.searchParams.set('q', query);
            else url.searchParams.delete('q');
            window.history.pushState({ page, query }, '', url);
        }

        function getURLParams() {
            const params = new URLSearchParams(window.location.search);
            return {
                page: parseInt(params.get('page')) || 1,
                query: params.get('q') || ''
            };
        }

        window.addEventListener('popstate', (event) => {
            const state = event.state || getURLParams();
            searchInput.value = state.query;
            fetchStudents(state.page, state.query);
        });

        // --- Initial Load ---
        const initialParams = getURLParams();
        searchInput.value = initialParams.query;
        fetchStudents(initialParams.page, initialParams.query);
    });
    </script>
</body>
</html>