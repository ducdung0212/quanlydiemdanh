document.addEventListener('DOMContentLoaded', () => {
    const API_BASE_URL = '/api/users';
    const ITEMS_PER_PAGE = 10;
    const DEBOUNCE_DELAY = 200;

    const searchInput = document.getElementById('searchInput');
    const searchForm = document.getElementById('searchForm');
    const tableBody = document.getElementById('users-table-body');
    const paginationContainer = document.getElementById('pagination-container');
    const paginationInfo = {
        start: document.getElementById('pagination-start'),
        end: document.getElementById('pagination-end'),
        total: document.getElementById('pagination-total')
    };
    const btnBulkDelete = document.getElementById('btnBulkDelete');
    const selectedCountSpan = document.getElementById('selectedCount');
    const selectAllCheckbox = document.getElementById('selectAll');
    const addUserTrigger = document.querySelector('[data-bs-target="#addUserModal"]');

    let currentPage = 1;
    let currentQuery = '';
    let paginationData = null;
    let isLoading = false;
    const selectedUsers = new Set();
    let addUserModalInstance;
    let editUserModalInstance;

    const escape = window.escapeHtml || ((value) => value);

    async function fetchUsers(page = 1, query = '') {
        if (isLoading) return;
        isLoading = true;
        tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Đang tải...</td></tr>';

        try {
            const url = `${API_BASE_URL}?page=${page}&limit=${ITEMS_PER_PAGE}&q=${encodeURIComponent(query)}`;
            const result = await apiFetch(url);

            if (!result.success || !result.data) {
                throw new Error('Invalid API response format');
            }

            paginationData = result.data;
            currentPage = paginationData.current_page;
            currentQuery = query;
            updateURL(currentPage, currentQuery);
            render();
        } catch (error) {
            console.error('Failed to fetch users:', error);
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
            tableBody.innerHTML = `<tr><td colspan="6" class="text-center">${currentQuery ? 'Không tìm thấy tài khoản nào' : 'Không có dữ liệu'}</td></tr>`;
            return;
        }

        const { data: users, from } = paginationData;
        const rowsHtml = users.map((user, index) => {
            const isChecked = selectedUsers.has(user.id) ? 'checked' : '';
            const roleLabel = user.role ? user.role.charAt(0).toUpperCase() + user.role.slice(1) : 'N/A';
            return `
                <tr>
                    <td class="text-center">
                        <input type="checkbox" class="user-checkbox" value="${user.id}" ${isChecked} style="cursor: pointer;" data-action="toggle-select">
                    </td>
                    <td class="text-center">${from + index}</td>
                    <td>${escape(user.name || '')}</td>
                    <td>${escape(user.email || '')}</td>
                    <td>${escape(roleLabel)}</td>
                    <td>
                        <div class="list-icon-function">
                            <a href="#" data-action="edit-user" data-id="${user.id}">
                                <div class="item edit"><i class="icon-edit-3"></i></div>
                            </a>
                            <a href="#" data-action="delete-user" data-id="${user.id}">
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
        const count = selectedUsers.size;
        selectedCountSpan.textContent = count;
        btnBulkDelete.classList.toggle('d-none', count === 0);
    }

    function toggleUserSelection(userId, isSelected) {
        const id = Number.parseInt(userId, 10);
        if (Number.isNaN(id)) return;

        if (isSelected) {
            selectedUsers.add(id);
        } else {
            selectedUsers.delete(id);
        }

        updateBulkDeleteButton();
        updateSelectAllCheckbox();
    }

    function updateSelectAllCheckbox() {
        const checkboxes = tableBody.querySelectorAll('.user-checkbox');
        if (checkboxes.length === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
            return;
        }

        const allSelected = Array.from(checkboxes).every((checkbox) => selectedUsers.has(Number.parseInt(checkbox.value, 10)));
        const someSelected = Array.from(checkboxes).some((checkbox) => selectedUsers.has(Number.parseInt(checkbox.value, 10)));

        selectAllCheckbox.checked = allSelected;
        selectAllCheckbox.indeterminate = !allSelected && someSelected;
    }

    function toggleSelectAll(event) {
        const isChecked = event.target.checked;
        tableBody.querySelectorAll('.user-checkbox').forEach((checkbox) => {
            checkbox.checked = isChecked;
            toggleUserSelection(checkbox.value, isChecked);
        });
    }

    async function deleteUser(userId) {
        if (!confirm('Bạn có chắc chắn muốn xóa tài khoản này?')) return;

        try {
            const result = await apiFetch(`${API_BASE_URL}/${userId}`, { method: 'DELETE' });
            if (!result.success) {
                showToast('Lỗi', result.message || 'Không thể xóa tài khoản', 'danger');
                return;
            }

            showToast('Thành công', result.message, 'success');
            selectedUsers.delete(Number.parseInt(userId, 10));
            const remainingRows = tableBody.querySelectorAll('tr').length;
            if (remainingRows === 1 && currentPage > 1) {
                await fetchUsers(currentPage - 1, currentQuery);
            } else {
                await fetchUsers(currentPage, currentQuery);
            }
        } catch (error) {
            showToast('Lỗi', error.message || 'Không thể xóa tài khoản', 'danger');
        }
    }

    async function bulkDeleteUsers() {
        const count = selectedUsers.size;
        if (count === 0) return;

        if (!confirm(`Bạn có chắc chắn muốn xóa ${count} tài khoản đã chọn?`)) return;

        const userIds = Array.from(selectedUsers);

        try {
            const result = await apiFetch(`${API_BASE_URL}/bulk-delete`, {
                method: 'POST',
                body: { user_ids: userIds }
            });

            if (!result.success) {
                showToast('Lỗi', result.message || 'Không thể xóa hàng loạt', 'danger');
                return;
            }

            showToast('Thành công', result.message, 'success');
            selectedUsers.clear();
            await fetchUsers(1, '');
        } catch (error) {
            showToast('Lỗi', error.message || 'Không thể xóa hàng loạt', 'danger');
        }
    }

    function initializeCreateUserForm() {
        const addUserForm = document.getElementById('addUserForm');
        const modalElement = document.getElementById('addUserModal');

        if (!addUserForm || !modalElement || addUserForm.dataset.initialized === 'true') {
            return;
        }

        addUserForm.dataset.initialized = 'true';

        addUserForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const button = addUserForm.querySelector('button[type="submit"]');
            toggleButtonLoading(button, true);
            clearValidationErrors(addUserForm);

            const formData = Object.fromEntries(new FormData(addUserForm).entries());

            try {
                const result = await apiFetch(API_BASE_URL, {
                    method: 'POST',
                    body: formData
                });

                if (!result.success) {
                    showToast('Lỗi', result.message || 'Không thể thêm tài khoản', 'danger');
                    return;
                }

                const modalInstance = bootstrap.Modal.getInstance(modalElement);
                if (modalInstance) modalInstance.hide();

                showToast('Thành công', result.message, 'success');
                document.dispatchEvent(new CustomEvent('usersUpdated'));
                addUserForm.reset();
            } catch (error) {
                if (error.statusCode === 422 && error.errors) {
                    displayValidationErrors(addUserForm, error.errors);
                } else {
                    showToast('Lỗi', error.message || 'Không thể thêm tài khoản', 'danger');
                }
            } finally {
                toggleButtonLoading(button, false);
            }
        });

        modalElement.addEventListener('hidden.bs.modal', () => {
            addUserForm.reset();
            clearValidationErrors(addUserForm);
        });
    }

    function initializeEditUserForm() {
        const editUserForm = document.getElementById('editUserForm');
        const modalElement = document.getElementById('editUserModal');
        const userId = document.getElementById('editUserId')?.value;

        if (!editUserForm || !modalElement || !userId || editUserForm.dataset.initialized === 'true') {
            return;
        }

        editUserForm.dataset.initialized = 'true';

        editUserForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const button = editUserForm.querySelector('button[type="submit"]');
            toggleButtonLoading(button, true);
            clearValidationErrors(editUserForm);

            const formEntries = Object.fromEntries(new FormData(editUserForm).entries());

            if (!formEntries.password && !formEntries.password_confirmation) {
                delete formEntries.password;
                delete formEntries.password_confirmation;
            }

            try {
                const result = await apiFetch(`${API_BASE_URL}/${userId}`, {
                    method: 'PUT',
                    body: formEntries
                });

                if (!result.success) {
                    showToast('Lỗi', result.message || 'Không thể cập nhật tài khoản', 'danger');
                    return;
                }

                const modalInstance = bootstrap.Modal.getInstance(modalElement);
                if (modalInstance) modalInstance.hide();

                showToast('Thành công', result.message, 'success');
                document.dispatchEvent(new CustomEvent('usersUpdated', { detail: { isEdit: true } }));
            } catch (error) {
                if (error.statusCode === 422 && error.errors) {
                    displayValidationErrors(editUserForm, error.errors);
                } else {
                    showToast('Lỗi', error.message || 'Không thể cập nhật tài khoản', 'danger');
                }
            } finally {
                toggleButtonLoading(button, false);
            }
        });

        modalElement.addEventListener('hidden.bs.modal', () => {
            clearValidationErrors(editUserForm);
        });
    }

    function setupEventListeners() {
        if (searchInput) {
            searchInput.addEventListener('keyup', debounce(() => fetchUsers(1, searchInput.value.trim()), DEBOUNCE_DELAY));
        }

        if (searchForm) {
            searchForm.addEventListener('submit', (event) => {
                event.preventDefault();
                fetchUsers(1, searchInput.value.trim());
            });
        }

        if (addUserTrigger) {
            addUserTrigger.addEventListener('click', async (event) => {
                event.preventDefault();
                addUserModalInstance = await loadModal('/users/modals/create', 'addUserModal');
                if (!addUserModalInstance) return;

                addUserModalInstance.show();
                initializeCreateUserForm();
            });
        }

        tableBody.addEventListener('click', async (event) => {
            const actionElement = event.target.closest('[data-action]');
            if (!actionElement) return;

            const action = actionElement.dataset.action;
            const userId = actionElement.dataset.id;

            switch (action) {
                case 'edit-user':
                    editUserModalInstance = await loadModal(`/users/modals/edit/${userId}`, 'editUserModal');
                    if (!editUserModalInstance) return;

                    editUserModalInstance.show();
                    initializeEditUserForm();
                    break;
                case 'delete-user':
                    if (userId) deleteUser(userId);
                    break;
                case 'toggle-select':
                    {
                        const checkbox = event.target.closest('.user-checkbox');
                        if (checkbox) toggleUserSelection(checkbox.value, checkbox.checked);
                    }
                    break;
                default:
                    break;
            }
        });

        paginationContainer.addEventListener('click', (event) => {
            event.preventDefault();
            const target = event.target.closest('a.page-link');
            if (!target || target.parentElement.classList.contains('disabled')) return;

            const page = Number.parseInt(target.dataset.page, 10);
            if (!Number.isNaN(page) && page !== currentPage) {
                fetchUsers(page, currentQuery);
            }
        });

        btnBulkDelete.addEventListener('click', bulkDeleteUsers);
        selectAllCheckbox.addEventListener('change', toggleSelectAll);

        document.addEventListener('usersUpdated', (event) => {
            const isEdit = event.detail?.isEdit || false;
            fetchUsers(isEdit ? currentPage : 1, currentQuery);
        });
    }

    function init() {
        setupEventListeners();
        const { page, query } = getURLParams();
        if (searchInput) {
            searchInput.value = query;
        }
        fetchUsers(page, query);
    }

    init();
});
