document.addEventListener('DOMContentLoaded', function () {
    // --- Configuration ---
    const API_BASE_URL = '/api/exam-schedules';
    const DEBOUNCE_DELAY = 300;

    // --- DOM Elements ---
    const searchInput = document.getElementById('searchInput');
    const searchForm = document.getElementById('searchForm');
    const dateFilter = document.getElementById('dateFilter');
    const tableBody = document.getElementById('exam-schedules-table-body');
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
    const btnAddExamSchedule = document.getElementById('btnAddExamSchedule');

    // --- State ---
    let currentPage = 1;
    let currentQuery = '';
    let currentDate = '';
    let paginationData = null;
    let isLoading = false;
    const selectedSchedules = new Set();
    let importExcelModal;
    let examScheduleFormManager = null;

    // --- Core Application Logic ---

    // Helper: pad number to 2 digits
    function pad(n) {
        return String(n).padStart(2, '0');
    }

    // Format various date inputs to dd-mm-YYYY
    function formatDate(dateStr) {
        if (!dateStr) return '';
        const datePart = String(dateStr).split('T')[0];
        const parts = datePart.split('-');
        if (parts.length === 3) {
            return `${parts[2]}-${parts[1]}-${parts[0]}`;
        }
        const d = new Date(dateStr);
        if (!isNaN(d.getTime())) {
            return `${pad(d.getDate())}-${pad(d.getMonth() + 1)}-${d.getFullYear()}`;
        }
        return String(dateStr);
    }

    // Format time strings like '09:51:06' to 'HH:MM'
    function formatTime(timeStr) {
        if (!timeStr) return '';
        let t = String(timeStr).split('.')[0];
        if (t.includes('T')) t = t.split('T')[1] || t;
        const parts = t.split(':');
        if (parts.length >= 2) {
            return `${pad(parts[0])}:${pad(parts[1])}`;
        }
        return t;
    }

    // Format duration
    function formatDuration(minutes) {
        if (!minutes || isNaN(minutes)) return '';
        const mins = parseInt(minutes, 10);
        return `${mins} phút`;
    }

    async function fetchExamSchedules(page = 1, query = '', date = '') {
        if (isLoading) return;
        isLoading = true;
        tableBody.innerHTML = '<tr><td colspan="10" class="text-center">Đang tải...</td></tr>';

        try {
            const url = `${API_BASE_URL}?page=${page}&q=${encodeURIComponent(query)}&date=${encodeURIComponent(date)}`;
            const result = await apiFetch(url);

            if (result.success && result.data) {
                paginationData = result.data;
                currentPage = paginationData.current_page;
                currentQuery = query;
                currentDate = date;
                updateURL(currentPage, currentQuery, date);
                render();
            } else {
                throw new Error('Invalid API response format');
            }
        } catch (error) {
            console.error('Failed to fetch exam schedules:', error);
            tableBody.innerHTML = '<tr><td colspan="10" class="text-center text-danger">Lỗi khi tải dữ liệu.</td></tr>';
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
            tableBody.innerHTML = `<tr><td colspan="10" class="text-center">${currentQuery || currentDate ? 'Không tìm thấy lịch thi nào' : 'Không có dữ liệu'}</td></tr>`;
            return;
        }

        const { data: schedules, from } = paginationData;
        const rowsHtml = schedules.map((schedule, index) => {
            const isChecked = selectedSchedules.has(schedule.id) ? 'checked' : '';
            return `
                <tr>
                    <td class="text-center">
                        <input type="checkbox" class="schedule-checkbox" value="${escapeHtml(schedule.id)}" ${isChecked} style="cursor: pointer;" data-action="toggle-select">
                    </td>
                    <td class="text-center">${from + index}</td>
                    <td>${escapeHtml(schedule.session_code || schedule.id || '')}</td>
                    <td>${escapeHtml(schedule.subject_code || '')}</td>
                    <td>${escapeHtml(schedule.subject_name || '')}</td>
                    <td>${escapeHtml(formatDate(schedule.exam_date || ''))}</td>
                    <td>${escapeHtml(formatTime(schedule.exam_time || ''))}</td>
                    <td>${escapeHtml(formatDuration(schedule.duration))}</td>
                    <td>${escapeHtml(schedule.room || '')}</td>
                    <td>
                        <div class="list-icon-function">
                            <a href="#" data-action="edit-schedule" data-schedule_id="${escapeHtml(schedule.id)}" title="Chỉnh sửa">
                                <div class="item text-primary"><i class="icon-edit"></i></div>
                            </a>
                            <a href="/show/${escapeHtml(schedule.id)}" data-action="view-attendance" data-schedule_id="${escapeHtml(schedule.id)}" title="Chi tiết điểm danh">
                                <div class="item view"><i class="icon-clipboard"></i></div>
                            </a>
                            <a href="#" data-action="manage-students" data-schedule_id="${escapeHtml(schedule.id)}" title="Quản lý sinh viên">
                                <div class="item text-info"><i class="icon-users"></i></div>
                            </a>
                            <a href="#" data-action="manage-supervisors" data-schedule_id="${escapeHtml(schedule.id)}" title="Quản lý giám thị">
                                <div class="item text-warning"><i class="icon-user-check"></i></div>
                            </a>
                            <a href="#" data-action="delete-schedule" data-schedule_id="${escapeHtml(schedule.id)}" title="Xóa">
                                <div class="item text-danger delete"><i class="icon-trash-2"></i></div>
                            </a>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        tableBody.innerHTML = rowsHtml;
        // Sau khi render, cập nhật lại trạng thái checked cho các checkbox
        const checkboxes = tableBody.querySelectorAll('.schedule-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectedSchedules.has(checkbox.value);
        });
    }

    function renderPagination() {
        if (!paginationData) {
            paginationContainer.innerHTML = '';
            updatePaginationInfo(paginationInfo, paginationData);
            return;
        }
        paginationContainer.innerHTML = renderPaginationHTML(paginationData);
        updatePaginationInfo(paginationInfo, paginationData);
    }

    function updateBulkDeleteButton() {
        const count = selectedSchedules.size;
        selectedCountSpan.textContent = count;
        if (count > 0) btnBulkDelete.classList.remove('d-none');
        else btnBulkDelete.classList.add('d-none');
    }

    function toggleScheduleSelection(scheduleId, isSelected) {
        if (isSelected) selectedSchedules.add(scheduleId);
        else selectedSchedules.delete(scheduleId);
        updateBulkDeleteButton();
        updateSelectAllCheckbox();
    }

    function updateSelectAllCheckbox() {
        const checkboxes = tableBody.querySelectorAll('.schedule-checkbox');
        if (checkboxes.length === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
            return;
        }
        const allSelected = Array.from(checkboxes).every(cb => selectedSchedules.has(cb.value));
        const someSelected = Array.from(checkboxes).some(cb => selectedSchedules.has(cb.value));

        selectAllCheckbox.checked = allSelected;
        selectAllCheckbox.indeterminate = !allSelected && someSelected;
    }

    function toggleSelectAll(event) {
        const isChecked = event.target.checked;
        if (isChecked) {
            fetch('/api/exam-schedules?limit=100000')
                .then(res => res.json())
                .then(result => {
                    if (result.success && result.data && result.data.data) {
                        result.data.data.forEach(schedule => {
                            selectedSchedules.add(String(schedule.id));
                        });
                        const checkboxes = tableBody.querySelectorAll('.schedule-checkbox');
                        checkboxes.forEach(checkbox => {
                            checkbox.checked = true;
                        });
                        updateBulkDeleteButton();
                        updateSelectAllCheckbox();
                        // Cập nhật số lượng cạnh nút export
                        const exportSelectedCount = document.getElementById('exportSelectedCount');
                        if (exportSelectedCount) exportSelectedCount.textContent = selectedSchedules.size;
                        const btnExportSelected = document.getElementById('btnExportSelected');
                        if (btnExportSelected) {
                            if (selectedSchedules.size > 0) btnExportSelected.classList.remove('d-none');
                            else btnExportSelected.classList.add('d-none');
                        }
                    }
                });
        } else {
            selectedSchedules.clear();
            const checkboxes = tableBody.querySelectorAll('.schedule-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            updateBulkDeleteButton();
            updateSelectAllCheckbox();
            // Cập nhật số lượng cạnh nút export
            const exportSelectedCount = document.getElementById('exportSelectedCount');
            if (exportSelectedCount) exportSelectedCount.textContent = 0;
            const btnExportSelected = document.getElementById('btnExportSelected');
            if (btnExportSelected) btnExportSelected.classList.add('d-none');
        }
    }

    async function deleteSchedule(scheduleId) {
        if (!confirm('Bạn có chắc chắn muốn xóa lịch thi này?')) return;
        try {
            const result = await apiFetch(`${API_BASE_URL}/${scheduleId}`, { method: 'DELETE' });
            if (result.success) {
                showToast('Thành công', result.message, 'success');
                selectedSchedules.delete(scheduleId);
                if (tableBody.querySelectorAll('tr').length === 1 && currentPage > 1) {
                    await fetchExamSchedules(currentPage - 1, currentQuery, currentDate);
                } else {
                    await fetchExamSchedules(currentPage, currentQuery, currentDate);
                }
            } else {
                showToast('Lỗi', result.message, 'danger');
            }
        } catch (error) {
            showToast('Lỗi', error.message || 'Không thể xóa lịch thi', 'danger');
        }
    }

    async function bulkDeleteSchedules() {
        const count = selectedSchedules.size;
        if (count === 0) return;
        if (!confirm(`Bạn có chắc chắn muốn xóa ${count} lịch thi đã chọn?`)) return;
        const scheduleIds = Array.from(selectedSchedules);
        try {
            const result = await apiFetch(`${API_BASE_URL}/bulk-delete`, {
                method: 'POST',
                body: JSON.stringify({ schedule_ids: scheduleIds })
            });
            if (result.success) {
                showToast('Thành công', result.message, 'success');
                selectedSchedules.clear();
                await fetchExamSchedules(1, '', '');
            } else {
                showToast('Lỗi', result.message, 'danger');
            }
        } catch (error) {
            showToast('Lỗi', error.message || 'Không thể xóa hàng loạt', 'danger');
        }
    }

    // Quản lý sinh viên tham gia ca thi (sử dụng StudentModalManager)
    let studentModalManager = null;
    async function openManageStudentsModal(scheduleId) {
        if (!studentModalManager) {
            studentModalManager = new StudentModalManager();
        }
        await studentModalManager.open(scheduleId);
    }

    // Quản lý giám thị ca thi (sử dụng SupervisorModalManager)
    let supervisorModalManager = null;
    async function openManageSupervisorsModal(scheduleId) {
        if (!supervisorModalManager) {
            supervisorModalManager = new SupervisorModalManager();
        }
        await supervisorModalManager.open(scheduleId);
    }

    function initializeImportExamScheduleModal(modalInstance) {
        // ... (Code import excel giữ nguyên như cũ để tiết kiệm không gian)
        const form = document.getElementById('importExcelForm');
        if (!form || form.dataset.initialized === 'true') return;
        const fileInput = form.querySelector('#excel_file');
        const tokenInput = form.querySelector('#import_token');
        const headingRowInput = form.querySelector('#import_heading_row');
        const mappingSection = form.querySelector('#mappingSection');
        const headingsPreview = form.querySelector('#headingsPreview');
        const headingsList = form.querySelector('#headingsList');
        const submitButton = form.querySelector('#btnImportExcel');
        const buttonText = submitButton ? submitButton.querySelector('.btn-text') : null;
        const mappingSelects = Array.from(form.querySelectorAll('.column-mapping'));
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        mappingSelects.forEach((select) => {
            select.dataset.defaultOptions = select.innerHTML;
            select.disabled = true;
            select.required = false;
        });
        const resetMappingUI = () => {
            form.dataset.step = 'preview';
            tokenInput.value = '';
            headingRowInput.value = '';
            headingsList.innerHTML = '';
            headingsPreview.classList.add('d-none');
            mappingSection.classList.add('d-none');
            mappingSelects.forEach((select) => {
                select.innerHTML = select.dataset.defaultOptions;
                select.value = '';
                select.disabled = true;
                select.required = false;
            });
            if (buttonText) buttonText.textContent = buttonText.dataset.textPreview || 'Tiếp tục';
        };
        const populateHeadings = (headings) => {
            const sanitize = window.escapeHtml ? window.escapeHtml.bind(window) : (value) => value;
            headingsList.innerHTML = headings.map((heading) => `<span class="badge bg-light text-dark border">${sanitize(heading)}</span>`).join('');
            mappingSelects.forEach((select) => {
                const defaultHtml = select.dataset.defaultOptions;
                const tempContainer = document.createElement('div');
                tempContainer.innerHTML = defaultHtml;
                select.innerHTML = '';
                Array.from(tempContainer.children).forEach((child) => select.appendChild(child));
                headings.forEach((heading) => {
                    const option = document.createElement('option');
                    option.value = heading;
                    option.textContent = heading;
                    select.appendChild(option);
                });
                select.disabled = false;
                if (select.dataset.required === 'true') select.required = true;
            });
            headingsPreview.classList.remove('d-none');
            mappingSection.classList.remove('d-none');
        };
        const handlePreview = async () => {
            if (!fileInput.files.length) throw new Error('Vui lòng chọn file Excel trước khi tiếp tục.');
            const previewData = new FormData();
            previewData.append('excel_file', fileInput.files[0]);
            const response = await fetch('/api/exam-schedules/import/preview', {
                method: 'POST', body: previewData, headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || 'Không thể đọc tiêu đề cột');
            tokenInput.value = result.token;
            headingRowInput.value = result.heading_row || '';
            populateHeadings(result.headings || []);
            form.dataset.step = 'mapping';
            if (buttonText) buttonText.textContent = buttonText.dataset.textImport || 'Import';
            showToast('Thông báo', 'Vui lòng map các cột trước khi import.', 'info');
        };
        const handleImport = async () => {
            if (!tokenInput.value) throw new Error('Vui lòng tải lại file trước khi import.');
            const mapping = {};
            mappingSelects.forEach((select) => { mapping[select.dataset.field] = select.value; });
            const response = await fetch('/api/exam-schedules/import', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ token: tokenInput.value, heading_row: headingRowInput.value ? Number(headingRowInput.value) : undefined, mapping }),
            });
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || 'Import thất bại');
            modalInstance.hide();
            showToast('Thành công', result.message || 'Đã import danh sách lịch thi.', 'success');
            await fetchExamSchedules(1, '', '');
            resetMappingUI();
        };
        form.addEventListener('change', (event) => { if (event.target === fileInput) resetMappingUI(); });
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            toggleButtonLoading(submitButton, true);
            try {
                if (form.dataset.step === 'mapping') await handleImport();
                else await handlePreview();
            } catch (error) { showToast('Lỗi', error.message || 'Có lỗi xảy ra khi import', 'danger'); } finally { toggleButtonLoading(submitButton, false); }
        });
        form.dataset.initialized = 'true';
        resetMappingUI();
    }

    async function setupEventListeners() {
        searchInput.addEventListener('keyup', debounce(() => {
            fetchExamSchedules(1, searchInput.value.trim(), dateFilter.value);
        }, DEBOUNCE_DELAY));

        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            fetchExamSchedules(1, searchInput.value.trim(), dateFilter.value);
        });

        dateFilter.addEventListener('change', () => {
            fetchExamSchedules(1, searchInput.value.trim(), dateFilter.value);
        });

        if (importExcelBtn) {
            importExcelBtn.addEventListener('click', async () => {
                importExcelModal = await loadModal('/exam-schedules/modals/import', 'importExcelModal');
                if (!importExcelModal) return;
                importExcelModal.show();
                initializeImportExamScheduleModal(importExcelModal);
            });
        }

        if (btnAddExamSchedule) {
            btnAddExamSchedule.addEventListener('click', (e) => {
                e.preventDefault();
                console.log('btnAddExamSchedule clicked');
                console.log('examScheduleFormManager:', examScheduleFormManager);
                if (examScheduleFormManager) {
                    examScheduleFormManager.openAddModal();
                } else {
                    console.error('examScheduleFormManager chưa được khởi tạo');
                }
            });
        } else {
            console.error('Không tìm thấy nút btnAddExamSchedule');
        }

        tableBody.addEventListener('click', async function (event) {
            const target = event.target.closest('[data-action]');
            if (!target) return;

            // XÓA DÒNG: event.preventDefault(); ở đây đi

            const action = target.dataset.action;
            const scheduleId = target.dataset.schedule_id;

            switch (action) {
                case 'edit-schedule':
                    event.preventDefault();
                    if (scheduleId && examScheduleFormManager) {
                        examScheduleFormManager.openEditModal(scheduleId);
                    }
                    break;
                case 'view-attendance':
                    event.preventDefault();
                    window.location.href = `/exam-schedules/show/${scheduleId}`;
                    break;
                case 'manage-students':
                    event.preventDefault();
                    if (scheduleId) openManageStudentsModal(scheduleId);
                    break;
                case 'manage-supervisors':
                    event.preventDefault();
                    if (scheduleId) openManageSupervisorsModal(scheduleId);
                    break;
                case 'delete-schedule':
                    event.preventDefault();
                    if (scheduleId) deleteSchedule(scheduleId);
                    break;
                case 'toggle-select':
                    const checkbox = event.target.closest('.schedule-checkbox');
                    if (checkbox) toggleScheduleSelection(checkbox.value, checkbox.checked);
                    break;
            }
        });

        paginationContainer.addEventListener('click', function (event) {
            event.preventDefault();
            const target = event.target.closest('a.page-link');
            if (target) {
                const page = parseInt(target.dataset.page, 10);
                if (!Number.isNaN(page) && page !== currentPage) {
                    fetchExamSchedules(page, currentQuery, currentDate);
                }
            }
        });

        btnBulkDelete.addEventListener('click', bulkDeleteSchedules);
        selectAllCheckbox.addEventListener('change', toggleSelectAll);

        document.addEventListener('examSchedulesUpdated', () => {
            fetchExamSchedules(currentPage, currentQuery, currentDate);
        });
    }

    function setupExportHandlers() {
        // ... (Export handler giữ nguyên)
        const btnExportSelected = document.getElementById('btnExportSelected');
        const btnExportOptions = document.getElementById('btnExportOptions');
        const exportSelectedCount = document.getElementById('exportSelectedCount');
        import('/js/admin/exam-export.js').then(module => {
            const { exportMultipleExams, showExportOptionsModal } = module;
            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('schedule-checkbox') || e.target.id === 'selectAll') {
                    setTimeout(() => {
                        const count = selectedSchedules.size;
                        if (exportSelectedCount) exportSelectedCount.textContent = count;
                        if (btnExportSelected) {
                            if (count > 0) btnExportSelected.classList.remove('d-none');
                            else btnExportSelected.classList.add('d-none');
                        }
                    }, 10);
                }
            });
            if (btnExportSelected) {
                btnExportSelected.addEventListener('click', (e) => {
                    e.preventDefault();
                    const selectedIds = Array.from(selectedSchedules);
                    if (selectedIds.length > 0) exportMultipleExams(selectedIds);
                });
            }
            if (btnExportOptions) {
                btnExportOptions.addEventListener('click', (e) => {
                    e.preventDefault();
                    const selectedIds = Array.from(selectedSchedules);
                    showExportOptionsModal(selectedIds);
                });
            }
        }).catch(err => { console.error('Failed to load export module:', err); });
    }

    function updateURL(page, query, date) {
        const url = new URL(window.location);
        url.searchParams.set('page', page);
        if (query) url.searchParams.set('q', query); else url.searchParams.delete('q');
        if (date) url.searchParams.set('date', date); else url.searchParams.delete('date');
        window.history.replaceState({}, '', url);
    }

    function getURLParams() {
        const urlParams = new URLSearchParams(window.location.search);
        return {
            page: parseInt(urlParams.get('page')) || 1,
            query: urlParams.get('q') || '',
            date: urlParams.get('date') || ''
        };
    }

    function init() {
        // Initialize form manager
        console.log('Init function called');
        console.log('ExamScheduleFormManager available:', typeof ExamScheduleFormManager !== 'undefined');

        if (typeof ExamScheduleFormManager !== 'undefined') {
            examScheduleFormManager = new ExamScheduleFormManager();
            examScheduleFormManager.init();
            console.log('examScheduleFormManager initialized:', examScheduleFormManager);
        } else {
            console.error('ExamScheduleFormManager class không tồn tại');
        }

        setupEventListeners();
        setupExportHandlers();
        const { page, query, date } = getURLParams();
        if (searchInput) searchInput.value = query;
        if (dateFilter) dateFilter.value = date;
        fetchExamSchedules(page, query, date);
    }

    init();

    // --- Attendance record integration (ĐÃ SỬA: Thêm Pagination) ---
    // If current page is an attendance detail page (path like /exam-schedules/show/{id})
    if (window.location.pathname.startsWith('/exam-schedules/show/')) {
        (function attendanceModule() {
            const pathParts = window.location.pathname.split('/');
            const examScheduleId = pathParts[pathParts.length - 1];
            const API_BASE_URL = `/api/exam-schedules/${examScheduleId}`;

            // State
            let currentPage = 1;
            // Nested sort state between full_name and class_code
            let primarySortBy = '';
            let primarySortDir = 'asc';
            const sortDirMap = {
                full_name: 'asc',
                class_code: 'asc'
            };

            // DOM Elements
            const examSessionCode = document.getElementById('exam-session-code');
            const examSubjectCode = document.getElementById('exam-subject-code');
            const examSubjectName = document.getElementById('exam-subject-name');
            const examDate = document.getElementById('exam-date');
            const examTime = document.getElementById('exam-time');
            const examRoom = document.getElementById('exam-room');
            const totalStudents = document.getElementById('total-students');
            const presentCount = document.getElementById('present-count');
            const pendingCount = document.getElementById('pending-count');
            const absentCount = document.getElementById('absent-count');
            const attendanceTableBody = document.getElementById('attendance-table-body');
            const btnRefresh = document.getElementById('btnRefresh');
            const btnExportExcel = document.getElementById('btnExportExcel');

            // Pagination Elements (Trong trang show.blade.php)
            const paginationStart = document.getElementById('attendance-pagination-start');
            const paginationEnd = document.getElementById('attendance-pagination-end');
            const paginationTotal = document.getElementById('attendance-pagination-total');
            const paginationContainer = document.getElementById('attendance-pagination-container');

            // Sortable headers
            const attendanceTable = document.getElementById('attendance-table');
            const sortableHeaders = attendanceTable ? attendanceTable.querySelectorAll('th[data-sort]') : [];

            function attendance_updateSortIndicators() {
                if (!attendanceTable) return;
                const indicators = attendanceTable.querySelectorAll('[data-sort-indicator]');
                indicators.forEach(el => {
                    const key = el.getAttribute('data-sort-indicator');
                    if (!key) return;
                    if (!primarySortBy) {
                        el.textContent = '▲';
                        return;
                    }
                    const dir = sortDirMap[key] || 'asc';
                    el.textContent = dir === 'asc' ? '▲' : '▼';
                });
            }


            function attendance_setSort(nextSortBy) {
                if (!nextSortBy) return;
                const currentDir = sortDirMap[nextSortBy] || 'asc';
                sortDirMap[nextSortBy] = currentDir === 'asc' ? 'desc' : 'asc';

                primarySortBy = nextSortBy;
                primarySortDir = sortDirMap[nextSortBy] || 'asc';

                currentPage = 1;
                attendance_updateSortIndicators();
                attendance_loadAttendanceData(1);
            }

            function attendance_formatDate(dateStr) {
                if (!dateStr) return '';
                const parts = dateStr.split('-');
                if (parts.length === 3) return `${parts[2]}-${parts[1]}-${parts[0]}`;
                return dateStr;
            }

            function attendance_formatTime(timeStr) {
                if (!timeStr) return '';
                return timeStr.substring(0, 5);
            }

            function attendance_getStatusBadge(rekognitionResult) {
                const statusMap = {
                    'match': { class: 'badge bg-success', text: 'Có mặt' },
                    'not_match': { class: 'badge bg-danger', text: 'Vắng mặt' },
                    'unknown': { class: 'badge bg-warning', text: 'Không xác định' },
                    null: { class: 'badge bg-secondary', text: '-' },
                    undefined: { class: 'badge bg-secondary', text: '-' }
                };
                const statusInfo = statusMap[rekognitionResult] || statusMap[null];
                return `<span class="${statusInfo.class}">${statusInfo.text}</span>`;
            }

            function attendance_formatAttendanceTime(timeStr) {
                if (!timeStr) return '-';
                try {
                    const date = new Date(timeStr);
                    return date.toLocaleString('vi-VN');
                } catch (e) { return timeStr; }
            }

            function attendance_escapeHtml(unsafe) {
                if (!unsafe) return '';
                return unsafe.toString().replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/\"/g, "&quot;").replace(/'/g, "&#039;");
            }

            // Hàm Render Pagination cho trang chi tiết
            function attendance_renderPagination(paginationData) {
                if (!paginationContainer) return;
                const { current_page, last_page, from, to, total } = paginationData;

                if (paginationStart) paginationStart.textContent = from || 0;
                if (paginationEnd) paginationEnd.textContent = to || 0;
                if (paginationTotal) paginationTotal.textContent = total || 0;

                let html = '';
                // Prev
                html += `<li class="page-item ${current_page === 1 ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-page="${current_page - 1}" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>`;
                // Numbers
                const range = 2;
                for (let i = 1; i <= last_page; i++) {
                    if (i === 1 || i === last_page || (i >= current_page - range && i <= current_page + range)) {
                        html += `<li class="page-item ${i === current_page ? 'active' : ''}">
                                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                                </li>`;
                    } else if (i === current_page - range - 1 || i === current_page + range + 1) {
                        html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                    }
                }
                // Next
                html += `<li class="page-item ${current_page === last_page ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-page="${current_page + 1}" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>`;
                paginationContainer.innerHTML = html;
            }

            async function attendance_loadAttendanceData(page = 1) {
                try {
                    currentPage = page;
                    if (!attendanceTableBody) return;
                    attendanceTableBody.innerHTML = '<tr><td colspan="6" class="text-center">Đang tải dữ liệu...</td></tr>';

                    const secondarySortBy = primarySortBy
                        ? (primarySortBy === 'full_name' ? 'class_code' : 'full_name')
                        : '';
                    const secondarySortDir = secondarySortBy ? (sortDirMap[secondarySortBy] || 'asc') : 'asc';

                    const url = `${API_BASE_URL}?page=${page}`
                        + (primarySortBy ? `&sort_by=${encodeURIComponent(primarySortBy)}` : '')
                        + (primarySortBy ? `&sort_dir=${encodeURIComponent(primarySortDir)}` : '')
                        + (primarySortBy ? `&sort_by2=${encodeURIComponent(secondarySortBy)}` : '')
                        + (primarySortBy ? `&sort_dir2=${encodeURIComponent(secondarySortDir)}` : '');
                    const response = await fetch(url);

                    if (!response.ok) {
                        if (response.status === 404) throw new Error('Không tìm thấy ca thi với ID: ' + examScheduleId);
                        throw new Error(`Lỗi HTTP: ${response.status}`);
                    }

                    const result = await response.json();
                    if (!result.success) throw new Error(result.message || 'Không thể tải dữ liệu');

                    const { exam, stats, students: studentsPaginator } = result.data;
                    const studentsList = studentsPaginator.data;
                    if (examSessionCode) examSessionCode.textContent = exam.session_code || exam.id || '-';
                    if (examSubjectCode) examSubjectCode.textContent = exam.subject_code || '-';
                    if (examSubjectName) examSubjectName.textContent = exam.subject_name || '-';
                    if (examDate) examDate.textContent = attendance_formatDate(exam.exam_date);
                    if (examTime) examTime.textContent = attendance_formatTime(exam.exam_time);
                    const examDuration = document.getElementById('exam-duration');
                    if (examDuration) examDuration.textContent = formatDuration(exam.duration);
                    if (examRoom) examRoom.textContent = exam.room || '-';
                    if (totalStudents) totalStudents.textContent = stats.total_students || 0;
                    if (presentCount) presentCount.textContent = stats.present || 0;
                    if (pendingCount) pendingCount.textContent = stats.pending || 0;
                    if (absentCount) absentCount.textContent = stats.absent || 0;
                    if (!studentsList || studentsList.length === 0) {
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
                        attendance_renderPagination(studentsPaginator);
                        return;
                    }

                    const from = studentsPaginator.from || 1;
                    const rowsHtml = studentsList.map((student, index) => `
                        <tr>
                            <td class="text-center">${from + index}</td>
                            <td>${attendance_escapeHtml(student.student_code || '')}</td>
                            <td>${attendance_escapeHtml(student.full_name || '')}</td>
                            <td>${attendance_escapeHtml(student.class_code || '')}</td>
                            <td>${attendance_formatAttendanceTime(student.attendance_time)}</td>
                            <td class="text-center">${attendance_getStatusBadge(student.rekognition_result)}</td>
                        </tr>
                    `).join('');

                    attendanceTableBody.innerHTML = rowsHtml;

                    attendance_updateSortIndicators();

                    // Render Pagination
                    attendance_renderPagination(studentsPaginator);

                } catch (error) {
                    console.error('Error loading attendance data:', error);
                    if (attendanceTableBody) {
                        attendanceTableBody.innerHTML = `<tr><td colspan="6" class="text-center text-danger"><i class="icon-alert-circle"></i> ${error.message}</td></tr>`;
                    }
                }
            }

            async function attendance_exportToExcel(e) {
                try {
                    if (e && typeof e.preventDefault === 'function') e.preventDefault();
                    const examId = window.location.pathname.split('/').pop();
                    const secondarySortBy = primarySortBy
                        ? (primarySortBy === 'full_name' ? 'class_code' : 'full_name')
                        : '';
                    const secondarySortDir = secondarySortBy ? (sortDirMap[secondarySortBy] || 'asc') : 'asc';

                    const url = `/exam-schedules/${examId}/export`
                        + (primarySortBy ? `?sort_by=${encodeURIComponent(primarySortBy)}` : '')
                        + (primarySortBy ? `&sort_dir=${encodeURIComponent(primarySortDir)}` : '')
                        + (primarySortBy ? `&sort_by2=${encodeURIComponent(secondarySortBy)}` : '')
                        + (primarySortBy ? `&sort_dir2=${encodeURIComponent(secondarySortDir)}` : '');

                    window.location.href = url;
                } catch (error) {
                    console.error('Error exporting Excel:', error);
                    swal('Lỗi', 'Lỗi khi xuất file Excel', 'error');
                }
            }

            if (btnRefresh) btnRefresh.addEventListener('click', () => attendance_loadAttendanceData(currentPage));
            if (btnExportExcel) btnExportExcel.addEventListener('click', attendance_exportToExcel);

            // Click-to-sort headers
            if (sortableHeaders && sortableHeaders.length > 0) {
                sortableHeaders.forEach(th => {
                    th.addEventListener('click', () => {
                        const key = th.getAttribute('data-sort');
                        attendance_setSort(key);
                    });
                });
            }

            // Ensure arrows are visible on first render
            attendance_updateSortIndicators();

            // Xử lý sự kiện click phân trang
            if (paginationContainer) {
                paginationContainer.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = e.target.closest('.page-link');
                    if (target) {
                        const page = parseInt(target.dataset.page);
                        if (page && !isNaN(page) && page !== currentPage) {
                            attendance_loadAttendanceData(page);
                        }
                    }
                });
            }

            // Initial load
            attendance_loadAttendanceData(1);
        })();
    }
});