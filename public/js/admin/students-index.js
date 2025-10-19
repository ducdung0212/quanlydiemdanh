document.addEventListener('DOMContentLoaded', function () {
    // --- Configuration ---
    const API_BASE_URL = '/api/students';
    const DEBOUNCE_DELAY = 300;

    // --- DOM Elements ---
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
    const addStudentTrigger = document.querySelector('[data-bs-target="#addStudentModal"]');

    // --- State ---
    let currentPage = 1;
    let currentQuery = '';
    let paginationData = null;
    let isLoading = false;
    const selectedStudents = new Set();
    let addStudentModal;
    let editStudentModal;
    let viewStudentModal;
    let importExcelModal;

    // --- Core Application Logic ---

    async function fetchStudents(page = 1, query = '') {
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
            console.error('Failed to fetch students:', error);
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
            tableBody.innerHTML = `<tr><td colspan="6" class="text-center">${currentQuery ? 'Không tìm thấy sinh viên nào' : 'Không có dữ liệu'}</td></tr>`;
            return;
        }

        const { data: students, from } = paginationData;
        const rowsHtml = students.map((student, index) => {
            const isChecked = selectedStudents.has(student.student_code) ? 'checked' : '';
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
                            <a href="#" data-action="view-student" data-student_code="${escapeHtml(student.student_code)}">
                                <div class="item view"><i class="icon-eye"></i></div>
                            </a>
                            <a href="#" data-action="edit-student" data-student_code="${escapeHtml(student.student_code)}">
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
        if (!paginationData) {
            paginationContainer.innerHTML = '';
            updatePaginationInfo(paginationInfo, paginationData);
            return;
        }

        paginationContainer.innerHTML = renderPaginationHTML(paginationData);
        updatePaginationInfo(paginationInfo, paginationData);
    }

    function updateBulkDeleteButton() {
        const count = selectedStudents.size;
        selectedCountSpan.textContent = count;
        if (count > 0) {
            btnBulkDelete.classList.remove('d-none');
        } else {
            btnBulkDelete.classList.add('d-none');
        }
    }

    function toggleStudentSelection(studentCode, isSelected) {
        if (isSelected) {
            selectedStudents.add(studentCode);
        } else {
            selectedStudents.delete(studentCode);
        }
        updateBulkDeleteButton();
        updateSelectAllCheckbox();
    }

    function updateSelectAllCheckbox() {
        const checkboxes = tableBody.querySelectorAll('.student-checkbox');
        if (checkboxes.length === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
            return;
        }
        const allSelected = Array.from(checkboxes).every(cb => selectedStudents.has(cb.value));
        const someSelected = Array.from(checkboxes).some(cb => selectedStudents.has(cb.value));

        selectAllCheckbox.checked = allSelected;
        selectAllCheckbox.indeterminate = !allSelected && someSelected;
    }

    function toggleSelectAll(event) {
        const isChecked = event.target.checked;
        const checkboxes = tableBody.querySelectorAll('.student-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = isChecked;
            toggleStudentSelection(checkbox.value, isChecked);
        });
    }

    async function deleteStudent(studentCode) {
        if (!confirm(`Bạn có chắc chắn muốn xóa sinh viên có mã ${studentCode}?`)) return;

        try {
            const result = await apiFetch(`${API_BASE_URL}/${studentCode}`, { method: 'DELETE' });
            if (result.success) {
                showToast('Thành công', result.message, 'success');
                selectedStudents.delete(studentCode);
                if (tableBody.querySelectorAll('tr').length === 1 && currentPage > 1) {
                    await fetchStudents(currentPage - 1, currentQuery);
                } else {
                    await fetchStudents(currentPage, currentQuery);
                }
            } else {
                showToast('Lỗi', result.message, 'danger');
            }
        } catch (error) {
            showToast('Lỗi', error.message || 'Không thể xóa sinh viên', 'danger');
        }
    }

    async function bulkDeleteStudents() {
        const count = selectedStudents.size;
        if (count === 0) return;

        if (!confirm(`Bạn có chắc chắn muốn xóa ${count} sinh viên đã chọn?`)) return;

        const studentCodes = Array.from(selectedStudents);

        try {
            const result = await apiFetch(`${API_BASE_URL}/bulk-delete`, {
                method: 'POST',
                body: JSON.stringify({ student_codes: studentCodes })
            });

            if (result.success) {
                showToast('Thành công', result.message, 'success');
                selectedStudents.clear();
                await fetchStudents(1, '');
            } else {
                showToast('Lỗi', result.message, 'danger');
            }
        } catch (error) {
            showToast('Lỗi', error.message || 'Không thể xóa hàng loạt', 'danger');
        }
    }

    function initializeAddStudentForm(modalInstance) {
        const form = document.getElementById('addStudentForm');
        if (!form || form.dataset.initialized === 'true') {
            return;
        }

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const submitButton = form.querySelector('button[type="submit"]');
            toggleButtonLoading(submitButton, true);
            clearValidationErrors(form);

            try {
                const formData = new FormData(form);
                const result = await apiFetch(API_BASE_URL, {
                    method: 'POST',
                    body: formData
                });

                if (!result.success) {
                    showToast('Lỗi', result.message || 'Không thể thêm sinh viên', 'danger');
                    return;
                }

                modalInstance.hide();
                showToast('Thành công', result.message || 'Đã thêm sinh viên', 'success');
                document.dispatchEvent(new CustomEvent('studentsUpdated'));
            } catch (error) {
                if (error.statusCode === 422 && error.errors) {
                    displayValidationErrors(form, error.errors);
                } else {
                    showToast('Lỗi', error.message || 'Không thể thêm sinh viên', 'danger');
                }
            } finally {
                toggleButtonLoading(submitButton, false);
            }
        });

        form.dataset.initialized = 'true';
    }

    function initializeEditStudentForm(modalInstance, studentCode) {
        const form = document.getElementById('editStudentForm');
        if (!form || form.dataset.initialized === 'true') {
            return;
        }

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const submitButton = form.querySelector('button[type="submit"]');
            toggleButtonLoading(submitButton, true);
            clearValidationErrors(form);

            try {
                const formData = new FormData(form);
                formData.append('_method', 'PUT');

                const result = await apiFetch(`${API_BASE_URL}/${encodeURIComponent(studentCode)}`, {
                    method: 'POST',
                    body: formData
                });

                if (!result.success) {
                    showToast('Lỗi', result.message || 'Không thể cập nhật sinh viên', 'danger');
                    return;
                }

                modalInstance.hide();
                showToast('Thành công', result.message || 'Đã cập nhật sinh viên', 'success');
                document.dispatchEvent(new CustomEvent('studentsUpdated', { detail: { isEdit: true } }));
            } catch (error) {
                if (error.statusCode === 422 && error.errors) {
                    displayValidationErrors(form, error.errors);
                } else {
                    showToast('Lỗi', error.message || 'Không thể cập nhật sinh viên', 'danger');
                }
            } finally {
                toggleButtonLoading(submitButton, false);
            }
        });

        form.dataset.initialized = 'true';
    }

    function initializeImportStudentModal(modalInstance) {
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

            const response = await fetch('/api/students/import/preview', {
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

            if (!mapping.student_code) {
                throw new Error('Vui lòng chọn cột cho Mã sinh viên.');
            }

            const response = await fetch('/api/students/import', {
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
            showToast('Thành công', result.message || 'Đã import danh sách sinh viên.', 'success');
            await fetchStudents(1, '');
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
            fetchStudents(1, searchInput.value.trim());
        }, DEBOUNCE_DELAY));

        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            fetchStudents(1, searchInput.value.trim());
        });

        if (importExcelBtn) {
            importExcelBtn.addEventListener('click', async () => {
                importExcelModal = await loadModal('/students/modals/import', 'importExcelModal');
                if (!importExcelModal) return;
                importExcelModal.show();
                initializeImportStudentModal(importExcelModal);
            });
        }

        tableBody.addEventListener('click', async function (event) {
            const target = event.target.closest('[data-action]');
            if (!target) return;

            const action = target.dataset.action;
            const studentCode = target.dataset.student_code;

            switch (action) {
                case 'view-student':
                    viewStudentModal = await loadModal(`/students/modals/view/${studentCode}`, 'viewStudentModal');
                    if (viewStudentModal) viewStudentModal.show();
                    break;
                case 'edit-student':
                    editStudentModal = await loadModal(`/students/modals/edit/${encodeURIComponent(studentCode)}`, 'editStudentModal');
                    if (editStudentModal) {
                        editStudentModal.show();
                        initializeEditStudentForm(editStudentModal, studentCode);
                    }
                    break;
                case 'delete-student':
                    if (studentCode) deleteStudent(studentCode);
                    break;
                case 'toggle-select':
                    const checkbox = event.target.closest('.student-checkbox');
                    if (checkbox) toggleStudentSelection(checkbox.value, checkbox.checked);
                    break;
            }
        });

        if (addStudentTrigger) {
            addStudentTrigger.addEventListener('click', async (e) => {
                e.preventDefault();
                addStudentModal = await loadModal('/students/modals/create', 'addStudentModal');
                if (addStudentModal) {
                    addStudentModal.show();
                    initializeAddStudentForm(addStudentModal);
                }
            });
        }

        paginationContainer.addEventListener('click', function (event) {
            event.preventDefault();
            const target = event.target.closest('a.page-link');
            if (target) {
                const page = parseInt(target.dataset.page, 10);
                if (!Number.isNaN(page) && page !== currentPage) {
                    fetchStudents(page, currentQuery);
                }
            }
        });

        btnBulkDelete.addEventListener('click', bulkDeleteStudents);
        selectAllCheckbox.addEventListener('change', toggleSelectAll);

        document.addEventListener('studentsUpdated', (e) => {
            const isEdit = e.detail?.isEdit || false;
            fetchStudents(isEdit ? currentPage : 1, currentQuery);
        });
    }

    function init() {
        setupEventListeners();
        const { page, query } = getURLParams();
        if (searchInput) {
            searchInput.value = query;
        }
        fetchStudents(page, query);
    }

    init();
});
