document.addEventListener('DOMContentLoaded', function () {
    const API_BASE_URL = '/api/lecturers';
    const DEBOUNCE_DELAY = 300;

    const searchInput = document.getElementById('searchInput');
    const searchForm = document.getElementById('searchForm');
    const tableBody = document.getElementById('lecturers-table-body');
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
    const addLecturerTrigger = document.querySelector('[data-bs-target="#addLecturerModal"]');

    if (!tableBody || !paginationContainer) {
        console.error('Lecturer table markup is missing required elements.');
        return;
    }

    let currentPage = 1;
    let currentQuery = '';
    let paginationData = null;
    let isLoading = false;
    const selectedLecturers = new Set();
    let addLecturerModal;
    let editLecturerModal;
    let viewLecturerModal;
    let importExcelModal;

    async function fetchLecturers(page = 1, query = '') {
        if (isLoading) {
            return;
        }
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
            console.error('Failed to fetch lecturers:', error);
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
        if (!paginationData || !Array.isArray(paginationData.data) || paginationData.data.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="6" class="text-center">${currentQuery ? 'Không tìm thấy giảng viên nào' : 'Không có dữ liệu'}</td></tr>`;
            return;
        }

        const { data: lecturers, from } = paginationData;
        const rowsHtml = lecturers.map((lecturer, index) => {
            const isChecked = selectedLecturers.has(lecturer.lecturer_code) ? 'checked' : '';
            return `
                <tr>
                    <td class="text-center">
                        <input type="checkbox" class="lecturer-checkbox" value="${escapeHtml(lecturer.lecturer_code)}" ${isChecked} style="cursor: pointer;" data-action="toggle-select">
                    </td>
                    <td class="text-center">${from + index}</td>
                    <td>${escapeHtml(lecturer.lecturer_code)}</td>
                    <td>${escapeHtml(lecturer.full_name || '')}</td>
                    <td>${escapeHtml(lecturer.faculty_code || '')}</td>
                    <td>
                        <div class="list-icon-function">
                            <a href="#" data-action="view-lecturer" data-lecturer_code="${escapeHtml(lecturer.lecturer_code)}">
                                <div class="item view"><i class="icon-eye"></i></div>
                            </a>
                            <a href="#" data-action="edit-lecturer" data-lecturer_code="${escapeHtml(lecturer.lecturer_code)}">
                                <div class="item edit"><i class="icon-edit-3"></i></div>
                            </a>
                            <a href="#" data-action="delete-lecturer" data-lecturer_code="${escapeHtml(lecturer.lecturer_code)}">
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
        if (!btnBulkDelete || !selectedCountSpan) {
            return;
        }

        const count = selectedLecturers.size;
        selectedCountSpan.textContent = count;
        if (count > 0) {
            btnBulkDelete.classList.remove('d-none');
        } else {
            btnBulkDelete.classList.add('d-none');
        }
    }

    function toggleLecturerSelection(lecturerCode, isSelected) {
        if (!lecturerCode) {
            return;
        }

        if (isSelected) {
            selectedLecturers.add(lecturerCode);
        } else {
            selectedLecturers.delete(lecturerCode);
        }
        updateBulkDeleteButton();
        updateSelectAllCheckbox();
    }

    function updateSelectAllCheckbox() {
        if (!selectAllCheckbox) {
            return;
        }

        const checkboxes = tableBody.querySelectorAll('.lecturer-checkbox');
        if (checkboxes.length === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
            return;
        }

        const allSelected = Array.from(checkboxes).every((checkbox) => selectedLecturers.has(checkbox.value));
        const someSelected = Array.from(checkboxes).some((checkbox) => selectedLecturers.has(checkbox.value));

        selectAllCheckbox.checked = allSelected;
        selectAllCheckbox.indeterminate = !allSelected && someSelected;
    }

    function toggleSelectAll(event) {
        const isChecked = event.target.checked;
        const checkboxes = tableBody.querySelectorAll('.lecturer-checkbox');
        checkboxes.forEach((checkbox) => {
            checkbox.checked = isChecked;
            toggleLecturerSelection(checkbox.value, isChecked);
        });
    }

    async function deleteLecturer(lecturerCode) {
        if (!lecturerCode) {
            return;
        }

        if (!confirm(`Bạn có chắc chắn muốn xóa giảng viên có mã ${lecturerCode}?`)) {
            return;
        }

        try {
            const result = await apiFetch(`${API_BASE_URL}/${encodeURIComponent(lecturerCode)}`, { method: 'DELETE' });
            if (result.success) {
                showToast('Thành công', result.message || 'Đã xóa giảng viên.', 'success');
                selectedLecturers.delete(lecturerCode);
                if (tableBody.querySelectorAll('tr').length === 1 && currentPage > 1) {
                    await fetchLecturers(currentPage - 1, currentQuery);
                } else {
                    await fetchLecturers(currentPage, currentQuery);
                }
            } else {
                showToast('Lỗi', result.message || 'Không thể xóa giảng viên.', 'danger');
            }
        } catch (error) {
            showToast('Lỗi', error.message || 'Không thể xóa giảng viên.', 'danger');
        }
    }

    async function bulkDeleteLecturers() {
        const count = selectedLecturers.size;
        if (count === 0) {
            return;
        }

        if (!confirm(`Bạn có chắc chắn muốn xóa ${count} giảng viên đã chọn?`)) {
            return;
        }

        const lecturerCodes = Array.from(selectedLecturers);

        try {
            const result = await apiFetch(`${API_BASE_URL}/bulk-delete`, {
                method: 'POST',
                body: JSON.stringify({ lecturer_codes: lecturerCodes })
            });

            if (result.success) {
                showToast('Thành công', result.message || 'Đã xóa giảng viên đã chọn.', 'success');
                selectedLecturers.clear();
                await fetchLecturers(1, '');
            } else {
                showToast('Lỗi', result.message || 'Không thể xóa hàng loạt.', 'danger');
            }
        } catch (error) {
            showToast('Lỗi', error.message || 'Không thể xóa hàng loạt.', 'danger');
        }
    }

    function handleTableClick(event) {
        const actionElement = event.target.closest('[data-action]');
        if (!actionElement) {
            return;
        }

        const action = actionElement.dataset.action;
        const lecturerCode = actionElement.dataset.lecturer_code;

        switch (action) {
            case 'view-lecturer':
                if (lecturerCode) {
                    openLecturerModal('view', lecturerCode);
                }
                break;
            case 'edit-lecturer':
                if (lecturerCode) {
                    openLecturerModal('edit', lecturerCode);
                }
                break;
            case 'delete-lecturer':
                deleteLecturer(lecturerCode);
                break;
            default:
                break;
        }
    }

    function handleTableChange(event) {
        const target = event.target;
        if (target.matches('.lecturer-checkbox')) {
            toggleLecturerSelection(target.value, target.checked);
        }
    }

    async function openLecturerModal(type, lecturerCode = null) {
        let modalId;
        let modalUrl;

        if (type === 'add') {
            modalId = 'addLecturerModal';
            modalUrl = '/lecturers/modals/create';
        } else if (type === 'edit') {
            modalId = 'editLecturerModal';
            modalUrl = `/lecturers/${encodeURIComponent(lecturerCode)}/modals/edit`;
        } else if (type === 'view') {
            modalId = 'viewLecturerModal';
            modalUrl = `/lecturers/${encodeURIComponent(lecturerCode)}/modals/view`;
        } else {
            return;
        }

        try {
            const modal = await loadModal(modalUrl, modalId);
            if (!modal) {
                return;
            }

            if (type === 'add') {
                addLecturerModal = modal;
            } else if (type === 'edit') {
                editLecturerModal = modal;
            } else {
                viewLecturerModal = modal;
            }

            modal.show();

            if (type === 'add') {
                initializeAddLecturerForm(modal);
            } else if (type === 'edit') {
                initializeEditLecturerForm(modal, lecturerCode);
            }
        } catch (error) {
            showToast('Lỗi', error.message || 'Không thể mở modal.', 'danger');
        }
    }

    function initializeAddLecturerForm(modalInstance) {
        const form = document.getElementById('addLecturerForm');
        if (!form || form.dataset.initialized === 'true') {
            return;
        }

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const submitButton = form.querySelector('#btnAddLecturer') || form.querySelector('button[type="submit"]');
            toggleButtonLoading(submitButton, true);
            clearValidationErrors(form);

            try {
                const formData = new FormData(form);
                const result = await apiFetch(API_BASE_URL, {
                    method: 'POST',
                    body: formData
                });

                if (!result.success) {
                    showToast('Lỗi', result.message || 'Không thể thêm giảng viên.', 'danger');
                    return;
                }

                modalInstance.hide();
                showToast('Thành công', result.message || 'Đã thêm giảng viên.', 'success');
                document.dispatchEvent(new CustomEvent('lecturersUpdated'));
            } catch (error) {
                if (error.statusCode === 422 && error.errors) {
                    displayValidationErrors(form, error.errors);
                } else {
                    showToast('Lỗi', error.message || 'Không thể thêm giảng viên.', 'danger');
                }
            } finally {
                toggleButtonLoading(submitButton, false);
            }
        });

        form.dataset.initialized = 'true';
    }

    function initializeEditLecturerForm(modalInstance, lecturerCode) {
        const form = document.getElementById('editLecturerForm');
        if (!form || form.dataset.initialized === 'true') {
            return;
        }

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const submitButton = form.querySelector('#btnEditLecturer') || form.querySelector('button[type="submit"]');
            toggleButtonLoading(submitButton, true);
            clearValidationErrors(form);

            try {
                const formData = new FormData(form);
                formData.append('_method', 'PUT');

                const result = await apiFetch(`${API_BASE_URL}/${encodeURIComponent(lecturerCode)}`, {
                    method: 'POST',
                    body: formData
                });

                if (!result.success) {
                    showToast('Lỗi', result.message || 'Không thể cập nhật giảng viên.', 'danger');
                    return;
                }

                modalInstance.hide();
                showToast('Thành công', result.message || 'Đã cập nhật giảng viên.', 'success');
                document.dispatchEvent(new CustomEvent('lecturersUpdated', { detail: { isEdit: true } }));
            } catch (error) {
                if (error.statusCode === 422 && error.errors) {
                    displayValidationErrors(form, error.errors);
                } else {
                    showToast('Lỗi', error.message || 'Không thể cập nhật giảng viên.', 'danger');
                }
            } finally {
                toggleButtonLoading(submitButton, false);
            }
        });

        form.dataset.initialized = 'true';
    }

    function initializeImportLecturerModal(modalInstance) {
        const form = document.getElementById('importLecturerForm');
        if (!form || form.dataset.initialized === 'true') {
            return;
        }

        const fileInput = form.querySelector('#excel_file');
        const tokenInput = form.querySelector('#import_token');
        const headingRowInput = form.querySelector('#import_heading_row');
        const mappingSection = form.querySelector('#mappingSection');
        const headingsPreview = form.querySelector('#headingsPreview');
        const headingsList = form.querySelector('#headingsList');
        const submitButton = form.querySelector('#btnImportLecturer');
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

            const response = await fetch('/api/lecturers/import/preview', {
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

            if (!mapping.lecturer_code) {
                throw new Error('Vui lòng chọn cột cho Mã giảng viên.');
            }

            const response = await fetch('/api/lecturers/import', {
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
            showToast('Thành công', result.message || 'Đã import danh sách giảng viên.', 'success');
            await fetchLecturers(1, '');
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

    function setupEventListeners() {
        if (searchInput) {
            searchInput.addEventListener('keyup', debounce(() => {
                fetchLecturers(1, searchInput.value.trim());
            }, DEBOUNCE_DELAY));
        }

        if (searchForm) {
            searchForm.addEventListener('submit', (event) => {
                event.preventDefault();
                fetchLecturers(1, searchInput ? searchInput.value.trim() : '');
            });
        }

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', toggleSelectAll);
        }

        tableBody.addEventListener('change', handleTableChange);
        tableBody.addEventListener('click', handleTableClick);

        if (btnBulkDelete) {
            btnBulkDelete.addEventListener('click', bulkDeleteLecturers);
        }

        if (importExcelBtn) {
            importExcelBtn.addEventListener('click', async () => {
                try {
                    importExcelModal = await loadModal('/lecturers/modals/import', 'importLecturerModal');
                    if (importExcelModal) {
                        importExcelModal.show();
                        initializeImportLecturerModal(importExcelModal);
                    }
                } catch (error) {
                    showToast('Lỗi', error.message || 'Không thể tải modal import.', 'danger');
                }
            });
        }

        if (addLecturerTrigger) {
            addLecturerTrigger.addEventListener('click', (event) => {
                event.preventDefault();
                openLecturerModal('add');
            });
        }

        document.addEventListener('lecturersUpdated', (event) => {
            const isEdit = event.detail?.isEdit || false;
            const targetPage = isEdit ? currentPage : 1;
            fetchLecturers(targetPage, currentQuery);
        });

        paginationContainer.addEventListener('click', (event) => {
            const pageLink = event.target.closest('[data-page]');
            if (!pageLink) {
                return;
            }

            event.preventDefault();
            const targetPage = Number(pageLink.dataset.page);
            if (!Number.isNaN(targetPage)) {
                fetchLecturers(targetPage, currentQuery);
            }
        });
    }

    setupEventListeners();
    fetchLecturers();
});
