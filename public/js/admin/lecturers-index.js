document.addEventListener('DOMContentLoaded', function () {
    const API_BASE_URL = '/api/lecturers';
    const ITEMS_PER_PAGE = 10;
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
        } catch (error) {
            showToast('Lỗi', error.message || 'Không thể mở modal.', 'danger');
        }
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
