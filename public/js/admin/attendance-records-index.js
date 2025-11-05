document.addEventListener('DOMContentLoaded', function () {
    // --- Configuration ---
    const API_BASE_URL = '/api/attendance-records';
    const DEBOUNCE_DELAY = 300;

    // --- DOM Elements ---
    const searchInput = document.getElementById('searchInput');
    const searchForm = document.getElementById('searchForm');
    const sessionFilter = document.getElementById('sessionFilter');
    const tableBody = document.getElementById('attendance-records-table-body');
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

    // --- State ---
    let currentPage = 1;
    let currentQuery = '';
    let currentSession = '';
    let paginationData = null;
    let isLoading = false;
    const selectedRecords = new Set();
    let importExcelModal;

    // --- Core Application Logic ---

    async function fetchAttendanceRecords(page = 1, query = '', session = '') {
        if (isLoading) return;
        isLoading = true;
        tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Đang tải...</td></tr>';

        try {
            const url = `${API_BASE_URL}?page=${page}&q=${encodeURIComponent(query)}&session=${encodeURIComponent(session)}`;
            const result = await apiFetch(url);

            if (result.success && result.data) {
                paginationData = result.data;
                currentPage = paginationData.current_page;
                currentQuery = query;
                currentSession = session;
                updateURL(currentPage, currentQuery, session);
                render();
            } else {
                throw new Error('Invalid API response format');
            }
        } catch (error) {
            console.error('Failed to fetch attendance records:', error);
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Lỗi khi tải dữ liệu.</td></tr>';
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
            tableBody.innerHTML = `<tr><td colspan="6" class="text-center">${currentQuery || currentSession ? 'Không tìm thấy sinh viên nào' : 'Không có dữ liệu'}</td></tr>`;
            return;
        }

        const { data: records, from } = paginationData;
        const rowsHtml = records.map((record, index) => {
            const isChecked = selectedRecords.has(record.id) ? 'checked' : '';
            return `
                <tr>
                    <td style="text-align: center;">
                        <input type="checkbox" class="record-checkbox" value="${escapeHtml(record.id)}" ${isChecked} style="cursor: pointer;" data-action="toggle-select">
                    </td>
                    <td style="text-align: center;">${from + index}</td>
                    <td style="text-align: left;">${escapeHtml(record.exam_schedule_id || '')}</td>
                    <td style="text-align: left;">${escapeHtml(record.student_code || '')}</td>
                    <td style="text-align: left;">${escapeHtml(record.student_name || '')}</td>
                    <td style="text-align: center;">
                        <div class="list-icon-function">
                            <a href="#" data-action="delete-record" data-record_id="${escapeHtml(record.id)}" title="Xóa">
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
        if (!paginationData) {
            paginationContainer.innerHTML = '';
            updatePaginationInfo(paginationInfo, paginationData);
            return;
        }

        paginationContainer.innerHTML = renderPaginationHTML(paginationData);
        updatePaginationInfo(paginationInfo, paginationData);
    }

    function updateBulkDeleteButton() {
        const count = selectedRecords.size;
        selectedCountSpan.textContent = count;
        if (count > 0) {
            btnBulkDelete.classList.remove('d-none');
        } else {
            btnBulkDelete.classList.add('d-none');
        }
    }

    function toggleRecordSelection(recordId, isSelected) {
        if (isSelected) {
            selectedRecords.add(recordId);
        } else {
            selectedRecords.delete(recordId);
        }
        updateBulkDeleteButton();
        updateSelectAllCheckbox();
    }

    function updateSelectAllCheckbox() {
        const checkboxes = tableBody.querySelectorAll('.record-checkbox');
        if (checkboxes.length === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
            return;
        }
        const allSelected = Array.from(checkboxes).every(cb => selectedRecords.has(cb.value));
        const someSelected = Array.from(checkboxes).some(cb => selectedRecords.has(cb.value));

        selectAllCheckbox.checked = allSelected;
        selectAllCheckbox.indeterminate = !allSelected && someSelected;
    }

    function toggleSelectAll(event) {
        const isChecked = event.target.checked;
        const checkboxes = tableBody.querySelectorAll('.record-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = isChecked;
            toggleRecordSelection(checkbox.value, isChecked);
        });
    }

    async function deleteRecord(recordId) {
        if (!confirm('Bạn có chắc chắn muốn xóa sinh viên này?')) return;

        try {
            const result = await apiFetch(`${API_BASE_URL}/${recordId}`, { method: 'DELETE' });
            if (result.success) {
                showToast('Thành công', result.message, 'success');
                selectedRecords.delete(recordId);
                if (tableBody.querySelectorAll('tr').length === 1 && currentPage > 1) {
                    await fetchAttendanceRecords(currentPage - 1, currentQuery, currentSession);
                } else {
                    await fetchAttendanceRecords(currentPage, currentQuery, currentSession);
                }
            } else {
                showToast('Lỗi', result.message, 'danger');
            }
        } catch (error) {
            showToast('Lỗi', error.message || 'Không thể xóa sinh viên', 'danger');
        }
    }

    async function bulkDeleteRecords() {
        const count = selectedRecords.size;
        if (count === 0) return;

        if (!confirm(`Bạn có chắc chắn muốn xóa ${count} sinh viên đã chọn?`)) return;

        const recordIds = Array.from(selectedRecords);

        try {
            const result = await apiFetch(`${API_BASE_URL}/bulk-delete`, {
                method: 'POST',
                body: JSON.stringify({ record_ids: recordIds })
            });

            if (result.success) {
                showToast('Thành công', result.message, 'success');
                selectedRecords.clear();
                await fetchAttendanceRecords(1, '', '');
            } else {
                showToast('Lỗi', result.message, 'danger');
            }
        } catch (error) {
            showToast('Lỗi', error.message || 'Không thể xóa hàng loạt', 'danger');
        }
    }

    function initializeImportAttendanceModal(modalInstance) {
        const form = document.getElementById('importExcelForm');
        if (!form || form.dataset.initialized === 'true') {
            return;
        }

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
            if (buttonText) {
                buttonText.textContent = buttonText.dataset.textPreview || 'Tiếp tục';
            }
            // Clear any previous import errors shown in the modal
            const errorBox = form.querySelector('#importErrors');
            if (errorBox) {
                errorBox.innerHTML = '';
                errorBox.classList.add('d-none');
            }
        };

        const showImportErrors = (messages) => {
            const errorBoxId = 'importErrors';
            let errorBox = form.querySelector('#' + errorBoxId);
            if (!errorBox) {
                errorBox = document.createElement('div');
                errorBox.id = errorBoxId;
                errorBox.className = 'alert alert-danger mt-2';
                errorBox.style.whiteSpace = 'pre-wrap';
                form.insertBefore(errorBox, form.firstChild);
            }

            if (typeof messages === 'string') {
                errorBox.textContent = messages;
            } else if (Array.isArray(messages)) {
                errorBox.innerHTML = messages.map(m => escapeHtml(m)).join('<br>');
            } else if (typeof messages === 'object' && messages !== null) {
                // Laravel-style errors: { errors: { field: [..] }, message: '...' }
                if (messages.errors) {
                    const lines = [];
                    Object.keys(messages.errors).forEach((field) => {
                        messages.errors[field].forEach((msg) => lines.push(msg));
                    });
                    errorBox.innerHTML = lines.map(l => escapeHtml(l)).join('<br>');
                } else if (messages.message) {
                    errorBox.textContent = messages.message;
                } else {
                    errorBox.textContent = JSON.stringify(messages);
                }
            } else {
                errorBox.textContent = String(messages);
            }

            errorBox.classList.remove('d-none');
        };

        const populateHeadings = (headings) => {
            const sanitize = window.escapeHtml ? window.escapeHtml.bind(window) : (value) => value;
            headingsList.innerHTML = headings.map((heading) => `
                <span class="badge bg-light text-dark border">${sanitize(heading)}</span>
            `).join('');

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
                if (select.dataset.required === 'true') {
                    select.required = true;
                }
            });

            headingsPreview.classList.remove('d-none');
            mappingSection.classList.remove('d-none');
        };

        const handlePreview = async () => {
            if (!fileInput.files.length) {
                throw new Error('Vui lòng chọn file Excel trước khi tiếp tục.');
            }

            const previewData = new FormData();
            previewData.append('excel_file', fileInput.files[0]);

            const response = await fetch('/api/attendance-records/import/preview', {
                method: 'POST',
                body: previewData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                }
            });

            const result = await response.json();

            if (!response.ok) {
                // Show validation/import errors inline in the modal
                showImportErrors(result);
                return;
            }

            if (!result.success) {
                showImportErrors(result.message || 'Không thể đọc tiêu đề cột');
                return;
            }

            tokenInput.value = result.token;
            headingRowInput.value = result.heading_row || '';
            populateHeadings(result.headings || []);
            form.dataset.step = 'mapping';

            if (buttonText) {
                buttonText.textContent = buttonText.dataset.textImport || 'Import';
            }

            showToast('Thông báo', 'Vui lòng map các cột trước khi import.', 'info');
        };

        const handleImport = async () => {
            if (!tokenInput.value) {
                throw new Error('Vui lòng tải lại file trước khi import.');
            }

            const mapping = {};
            mappingSelects.forEach((select) => {
                mapping[select.dataset.field] = select.value;
            });

            const response = await fetch('/api/attendance-records/import', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    token: tokenInput.value,
                    heading_row: headingRowInput.value ? Number(headingRowInput.value) : undefined,
                    mapping,
                }),
            });

            const result = await response.json();

            if (!response.ok) {
                // Show detailed errors inline and also toast
                showImportErrors(result);
                showToast('Lỗi', result.message || 'Import thất bại', 'danger');
                return;
            }

            if (!result.success) {
                showImportErrors(result.message || 'Import thất bại');
                showToast('Lỗi', result.message || 'Import thất bại', 'danger');
                return;
            }

            modalInstance.hide();
            showToast('Thành công', result.message || 'Đã import danh sách sinh viên.', 'success');
            await fetchAttendanceRecords(1, '', '');
            resetMappingUI();
        };

        form.addEventListener('change', (event) => {
            if (event.target === fileInput) {
                resetMappingUI();
            }
        });

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            toggleButtonLoading(submitButton, true);

            try {
                if (form.dataset.step === 'mapping') {
                    await handleImport();
                } else {
                    await handlePreview();
                }
            } catch (error) {
                showToast('Lỗi', error.message || 'Có lỗi xảy ra khi import', 'danger');
            } finally {
                toggleButtonLoading(submitButton, false);
            }
        });

        form.dataset.initialized = 'true';
        resetMappingUI();
    }

    async function setupEventListeners() {
        searchInput.addEventListener('keyup', debounce(() => {
            fetchAttendanceRecords(1, searchInput.value.trim(), sessionFilter.value);
        }, DEBOUNCE_DELAY));

        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            fetchAttendanceRecords(1, searchInput.value.trim(), sessionFilter.value);
        });

        sessionFilter.addEventListener('change', () => {
            fetchAttendanceRecords(1, searchInput.value.trim(), sessionFilter.value);
        });

        if (importExcelBtn) {
            importExcelBtn.addEventListener('click', async () => {
                importExcelModal = await loadModal('/attendance-records/modals/import', 'importExcelModal');
                if (!importExcelModal) return;
                importExcelModal.show();
                initializeImportAttendanceModal(importExcelModal);
            });
        }

        tableBody.addEventListener('click', async function (event) {
            const target = event.target.closest('[data-action]');
            if (!target) return;

            // Prevent default anchor behavior to avoid # in URL
            event.preventDefault();

            const action = target.dataset.action;
            const recordId = target.dataset.record_id;

            switch (action) {
                case 'delete-record':
                    if (recordId) deleteRecord(recordId);
                    break;
                case 'toggle-select':
                    const checkbox = event.target.closest('.record-checkbox');
                    if (checkbox) toggleRecordSelection(checkbox.value, checkbox.checked);
                    break;
            }
        });

        paginationContainer.addEventListener('click', function (event) {
            event.preventDefault();
            const target = event.target.closest('a.page-link');
            if (target) {
                const page = parseInt(target.dataset.page, 10);
                if (!Number.isNaN(page) && page !== currentPage) {
                    fetchAttendanceRecords(page, currentQuery, currentSession);
                }
            }
        });

        btnBulkDelete.addEventListener('click', bulkDeleteRecords);
        selectAllCheckbox.addEventListener('change', toggleSelectAll);

        document.addEventListener('attendanceRecordsUpdated', () => {
            fetchAttendanceRecords(currentPage, currentQuery, currentSession);
        });
    }

    function updateURL(page, query, session) {
        const url = new URL(window.location);
        url.searchParams.set('page', page);
        if (query) {
            url.searchParams.set('q', query);
        } else {
            url.searchParams.delete('q');
        }
        if (session) {
            url.searchParams.set('session', session);
        } else {
            url.searchParams.delete('session');
        }
        window.history.replaceState({}, '', url);
    }

    function getURLParams() {
        const urlParams = new URLSearchParams(window.location.search);
        return {
            page: parseInt(urlParams.get('page')) || 1,
            query: urlParams.get('q') || '',
            session: urlParams.get('session') || ''
        };
    }

    function init() {
        setupEventListeners();
        const { page, query, session } = getURLParams();
        if (searchInput) {
            searchInput.value = query;
        }
        if (sessionFilter) {
            sessionFilter.value = session;
        }
        fetchAttendanceRecords(page, query, session);
    }

    init();
});