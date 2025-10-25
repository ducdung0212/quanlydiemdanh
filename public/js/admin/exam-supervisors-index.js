document.addEventListener('DOMContentLoaded', function () {
    // --- Configuration ---
    const API_BASE_URL = '/api/exam-supervisors';
    const DEBOUNCE_DELAY = 300;

    // --- DOM Elements ---
    const searchInput = document.getElementById('searchInput');
    const searchForm = document.getElementById('searchForm');
    const tableBody = document.getElementById('exam-supervisors-table-body');
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
    let paginationData = null;
    let isLoading = false;
    const selectedSupervisors = new Set();
    let importExcelModal;

    // --- Core Application Logic ---

    async function fetchExamSupervisors(page = 1, query = '') {
        if (isLoading) return;
        isLoading = true;
        tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Đang tải...</td></tr>';

        try {
            const url = `${API_BASE_URL}?page=${page}&q=${encodeURIComponent(query)}`;
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
            console.error('Failed to fetch exam supervisors:', error);
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
            tableBody.innerHTML = `<tr><td colspan="6" class="text-center">${currentQuery ? 'Không tìm thấy giám thị nào' : 'Không có dữ liệu'}</td></tr>`;
            return;
        }

        const { data: supervisors, from } = paginationData;
        const rowsHtml = supervisors.map((supervisor, index) => {
            const isChecked = selectedSupervisors.has(supervisor.id) ? 'checked' : '';
            return `
                <tr>
                    <td>
                        <input type="checkbox" class="supervisor-checkbox" value="${escapeHtml(supervisor.id)}" ${isChecked} style="cursor: pointer;" data-action="toggle-select">
                    </td>
                    <td >${from + index}</td>
                    <td >${escapeHtml(supervisor.exam_schedule_id || '')}</td>
                    <td >${escapeHtml(supervisor.lecturer_code || '')}</td>
                    <td >${escapeHtml(supervisor.lecturer_name || '')}</td>
                    <td >
                        <div class="list-icon-function">
                            <a href="#" data-action="delete-supervisor" data-supervisor_id="${escapeHtml(supervisor.id)}" title="Xóa">
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
        const count = selectedSupervisors.size;
        selectedCountSpan.textContent = count;
        if (count > 0) {
            btnBulkDelete.classList.remove('d-none');
        } else {
            btnBulkDelete.classList.add('d-none');
        }
    }

    function toggleSupervisorSelection(supervisorId, isSelected) {
        if (isSelected) {
            selectedSupervisors.add(supervisorId);
        } else {
            selectedSupervisors.delete(supervisorId);
        }
        updateBulkDeleteButton();
        updateSelectAllCheckbox();
    }

    function updateSelectAllCheckbox() {
        const checkboxes = tableBody.querySelectorAll('.supervisor-checkbox');
        if (checkboxes.length === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
            return;
        }
        const allSelected = Array.from(checkboxes).every(cb => selectedSupervisors.has(cb.value));
        const someSelected = Array.from(checkboxes).some(cb => selectedSupervisors.has(cb.value));

        selectAllCheckbox.checked = allSelected;
        selectAllCheckbox.indeterminate = !allSelected && someSelected;
    }

    function toggleSelectAll(event) {
        const isChecked = event.target.checked;
        const checkboxes = tableBody.querySelectorAll('.supervisor-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = isChecked;
            toggleSupervisorSelection(checkbox.value, isChecked);
        });
    }

    async function deleteSupervisor(supervisorId) {
        if (!confirm('Bạn có chắc chắn muốn xóa giám thị này?')) return;

        try {
            const result = await apiFetch(`${API_BASE_URL}/${supervisorId}`, { method: 'DELETE' });
            if (result.success) {
                showToast('Thành công', result.message, 'success');
                selectedSupervisors.delete(supervisorId);
                if (tableBody.querySelectorAll('tr').length === 1 && currentPage > 1) {
                    await fetchExamSupervisors(currentPage - 1, currentQuery);
                } else {
                    await fetchExamSupervisors(currentPage, currentQuery);
                }
            } else {
                showToast('Lỗi', result.message, 'danger');
            }
        } catch (error) {
            showToast('Lỗi', error.message || 'Không thể xóa giám thị', 'danger');
        }
    }

    async function bulkDeleteSupervisors() {
        const count = selectedSupervisors.size;
        if (count === 0) return;

        if (!confirm(`Bạn có chắc chắn muốn xóa ${count} giám thị đã chọn?`)) return;

        const supervisorIds = Array.from(selectedSupervisors);

        try {
            const result = await apiFetch(`${API_BASE_URL}/bulk-delete`, {
                method: 'POST',
                body: JSON.stringify({ supervisor_ids: supervisorIds })
            });

            if (result.success) {
                showToast('Thành công', result.message, 'success');
                selectedSupervisors.clear();
                await fetchExamSupervisors(1, '', '');
            } else {
                showToast('Lỗi', result.message, 'danger');
            }
        } catch (error) {
            showToast('Lỗi', error.message || 'Không thể xóa hàng loạt', 'danger');
        }
    }

    function initializeImportExamSupervisorModal(modalInstance) {
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

            const response = await fetch('/api/exam-supervisors/import/preview', {
                method: 'POST',
                body: previewData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                }
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Không thể đọc tiêu đề cột');
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

            const response = await fetch('/api/exam-supervisors/import', {
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

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Import thất bại');
            }

            modalInstance.hide();
            showToast('Thành công', result.message || 'Đã import danh sách giám thị.', 'success');
            await fetchExamSupervisors(1, '');
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
            fetchExamSupervisors(1, searchInput.value.trim());
        }, DEBOUNCE_DELAY));

        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            fetchExamSupervisors(1, searchInput.value.trim());
        });

        // session filter removed

        if (importExcelBtn) {
            importExcelBtn.addEventListener('click', async () => {
                importExcelModal = await loadModal('/exam-supervisors/modals/import', 'importExcelModal');
                if (!importExcelModal) return;
                importExcelModal.show();
                initializeImportExamSupervisorModal(importExcelModal);
            });
        }

        tableBody.addEventListener('click', async function (event) {
            const target = event.target.closest('[data-action]');
            if (!target) return;

            // Prevent default anchor behavior to avoid # in URL
            event.preventDefault();

            const action = target.dataset.action;
            const supervisorId = target.dataset.supervisor_id;

            switch (action) {
                case 'delete-supervisor':
                    if (supervisorId) deleteSupervisor(supervisorId);
                    break;
                case 'toggle-select':
                    const checkbox = event.target.closest('.supervisor-checkbox');
                    if (checkbox) toggleSupervisorSelection(checkbox.value, checkbox.checked);
                    break;
            }
        });

        paginationContainer.addEventListener('click', function (event) {
            event.preventDefault();
            const target = event.target.closest('a.page-link');
            if (target) {
                const page = parseInt(target.dataset.page, 10);
                if (!Number.isNaN(page) && page !== currentPage) {
                    fetchExamSupervisors(page, currentQuery);
                }
            }
        });

        btnBulkDelete.addEventListener('click', bulkDeleteSupervisors);
        selectAllCheckbox.addEventListener('change', toggleSelectAll);

        document.addEventListener('examSupervisorsUpdated', () => {
            fetchExamSupervisors(currentPage, currentQuery);
        });
    }

    function updateURL(page, query) {
        const url = new URL(window.location);
        url.searchParams.set('page', page);
        if (query) {
            url.searchParams.set('q', query);
        } else {
            url.searchParams.delete('q');
        }
        window.history.replaceState({}, '', url);
    }

    function getURLParams() {
        const urlParams = new URLSearchParams(window.location.search);
        return {
            page: parseInt(urlParams.get('page')) || 1,
            query: urlParams.get('q') || ''
        };
    }

    function init() {
        setupEventListeners();
        const { page, query } = getURLParams();
        if (searchInput) {
            searchInput.value = query;
        }
        fetchExamSupervisors(page, query);
    }

    init();
});