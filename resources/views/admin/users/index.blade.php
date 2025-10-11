@extends('layouts_main.app')

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
                        <th style="width: 60px">STT</th>
                        <th>Tên</th>
                        <th>Email</th>
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
                Hiển thị <span id="pagination-start">0</span>-<span id="pagination-end">0</span> của <span id="pagination-total">0</span> tài khoản
            </div>
            <div class="pagination-controls">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-end mb-0" id="pagination-container">
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Modals will be loaded here -->
    <div id="modal-container"></div>
@endsection

@push('scripts')
    <style>
        .is-invalid {
            border-color: #dc3545 !important;
        }
        .invalid-feedback {
            display: none;
            width: 100%;
            margin-top: .25rem;
            font-size: .875em;
            color: #dc3545;
        }
        .is-invalid ~ .invalid-feedback {
            display: block;
        }
    </style>
    
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- Configuration ---
        const API_BASE_URL = '/api/users';
        const ITEMS_PER_PAGE = 10;
        const DEBOUNCE_DELAY = 200;

        // --- DOM Elements ---
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
        
        // --- State ---
        let currentPage = 1;
        let currentQuery = '';
        let paginationData = null;
        let isLoading = false;
        let selectedUsers = new Set();
        let addUserModal, editUserModal;
        
        // --- Utility Functions ---
        async function apiFetch(url, options = {}) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const defaultHeaders = { 'Accept': 'application/json' };
            if (csrfToken) defaultHeaders['X-CSRF-TOKEN'] = csrfToken;

            if (!(options.body instanceof FormData)) {
                defaultHeaders['Content-Type'] = 'application/json';
            }

            const config = { ...options, headers: { ...defaultHeaders, ...options.headers } };
            
            if (config.body && typeof config.body === 'object' && !(config.body instanceof FormData)) {
                config.body = JSON.stringify(config.body);
            }

            const response = await fetch(url, config);

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: response.statusText, errors: {} }));
                errorData.statusCode = response.status;
                throw errorData;
            }
            return response.json();
        }

        function toggleButtonLoading(button, isLoading) {
            if (!button) return;
            const btnText = button.querySelector('.btn-text');
            const spinner = button.querySelector('.spinner-border');
            button.disabled = isLoading;
            if (btnText) btnText.classList.toggle('d-none', isLoading);
            if (spinner) spinner.classList.toggle('d-none', !isLoading);
        }

        function clearValidationErrors(form) {
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
        }

        function displayValidationErrors(form, errors) {
            clearValidationErrors(form);
            for (const [field, messages] of Object.entries(errors)) {
                const fieldName = field.split('.')[0];
                const input = form.querySelector(`[name="${fieldName}"]`) || form.querySelector(`[name="${fieldName}[]"]`);
                if (input) {
                    input.classList.add('is-invalid');
                    let feedback = input.nextElementSibling;
                    if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                        feedback = input.closest('.mb-3, .mb-4')?.querySelector('.invalid-feedback');
                    }
                    if (feedback) {
                        feedback.textContent = messages.join(' ');
                    }
                }
            }
        }

        function showToast(title, message, type = 'info') {
            let toastContainer = document.getElementById('toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'toast-container';
                toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
                document.body.appendChild(toastContainer);
            }
            
            const toastId = 'toast-' + Date.now();
            const toastHtml = `
                <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header bg-${type} text-white">
                        <strong class="me-auto">${title}</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">${message}</div>
                </div>`;
            toastContainer.insertAdjacentHTML('beforeend', toastHtml);
            const toastEl = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastEl, { delay: 5000 });
            toast.show();
            toastEl.addEventListener('hidden.bs.toast', e => e.currentTarget.remove());
        }

        // Expose functions globally for modals
        window.apiFetch = apiFetch;
        window.toggleButtonLoading = toggleButtonLoading;
        window.clearValidationErrors = clearValidationErrors;
        window.displayValidationErrors = displayValidationErrors;
        window.showToast = showToast;

        async function loadModal(url, modalId) {
            try {
                const response = await fetch(url);
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const html = await response.text();
                
                const existingModal = document.querySelector('.modal');
                if (existingModal) {
                    const modalInstance = bootstrap.Modal.getInstance(existingModal);
                    if (modalInstance) modalInstance.hide();
                    existingModal.parentElement.remove();
                }

                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                document.body.appendChild(tempDiv);
                
                const modalElement = document.getElementById(modalId);
                if (!modalElement) {
                    console.error(`Modal with id ${modalId} not found in loaded content.`);
                    return null;
                }
                
                const modal = new bootstrap.Modal(modalElement);
                modalElement.addEventListener('hidden.bs.modal', () => tempDiv.remove());
                return modal;
            } catch (error) {
                console.error('Failed to load modal:', error);
                showToast('Lỗi', 'Không thể tải cửa sổ làm việc.', 'danger');
                return null;
            }
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

        // --- Core Application Logic ---
        async function fetchUsers(page = 1, query = '') {
            if (isLoading) return;
            isLoading = true;
            tableBody.innerHTML = `<tr><td colspan="5" class="text-center">Đang tải...</td></tr>`;

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
                console.error("Failed to fetch users:", error);
                tableBody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">Lỗi khi tải dữ liệu.</td></tr>`;
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
                tableBody.innerHTML = `<tr><td colspan="5" class="text-center">${currentQuery ? 'Không tìm thấy tài khoản nào' : 'Không có dữ liệu'}</td></tr>`;
                return;
            }

            const { data: users, from } = paginationData;
            const rowsHtml = users.map((user, index) => {
                const isChecked = selectedUsers.has(user.id) ? 'checked' : '';
                return `
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" class="user-checkbox" value="${user.id}" ${isChecked} style="cursor: pointer;" data-action="toggle-select">
                        </td>
                        <td class="text-center">${from + index}</td>
                        <td>${escapeHtml(user.name)}</td>
                        <td>${escapeHtml(user.email)}</td>
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
            if (!paginationData || paginationData.last_page <= 1) {
                paginationContainer.innerHTML = '';
                paginationInfo.start.textContent = paginationData?.from || 0;
                paginationInfo.end.textContent = paginationData?.to || 0;
                paginationInfo.total.textContent = paginationData?.total || 0;
                return;
            }

            const { current_page, last_page, from, to, total } = paginationData;
            paginationInfo.start.textContent = from;
            paginationInfo.end.textContent = to;
            paginationInfo.total.textContent = total;

            let paginationHtml = '';
            const delta = 2;
            const left = current_page - delta;
            const right = current_page + delta + 1;
            let range = [];
            let rangeWithDots = [];
            let l;

            for (let i = 1; i <= last_page; i++) {
                if (i == 1 || i == last_page || (i >= left && i < right)) {
                    range.push(i);
                }
            }

            for (let i of range) {
                if (l) {
                    if (i - l === 2) rangeWithDots.push(l + 1);
                    else if (i - l !== 1) rangeWithDots.push('...');
                }
                rangeWithDots.push(i);
                l = i;
            }

            paginationHtml += `<li class="page-item ${current_page === 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${current_page - 1}">Previous</a></li>`;
            rangeWithDots.forEach(page => {
                if (page === '...') {
                    paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                } else {
                    paginationHtml += `<li class="page-item ${page === current_page ? 'active' : ''}"><a class="page-link" href="#" data-page="${page}">${page}</a></li>`;
                }
            });
            paginationHtml += `<li class="page-item ${current_page === last_page ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${current_page + 1}">Next</a></li>`;
            paginationContainer.innerHTML = paginationHtml;
        }

        function updateURL(page, query) {
            const url = new URL(window.location);
            url.searchParams.set('page', page);
            url.searchParams.set('q', query);
            window.history.pushState({}, '', url);
        }

        function updateBulkDeleteButton() {
            const count = selectedUsers.size;
            selectedCountSpan.textContent = count;
            btnBulkDelete.classList.toggle('d-none', count === 0);
        }

        function toggleUserSelection(userId, isSelected) {
            userId = parseInt(userId);
            if (isSelected) selectedUsers.add(userId);
            else selectedUsers.delete(userId);
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
            const allSelected = Array.from(checkboxes).every(cb => selectedUsers.has(parseInt(cb.value)));
            const someSelected = Array.from(checkboxes).some(cb => selectedUsers.has(parseInt(cb.value)));
            selectAllCheckbox.checked = allSelected;
            selectAllCheckbox.indeterminate = !allSelected && someSelected;
        }

        function toggleSelectAll(event) {
            const isChecked = event.target.checked;
            tableBody.querySelectorAll('.user-checkbox').forEach(checkbox => {
                checkbox.checked = isChecked;
                toggleUserSelection(checkbox.value, isChecked);
            });
        }

        async function deleteUser(userId) {
            if (!confirm(`Bạn có chắc chắn muốn xóa tài khoản này?`)) return;
            try {
                const result = await apiFetch(`${API_BASE_URL}/${userId}`, { method: 'DELETE' });
                if (result.success) {
                    showToast('Thành công', result.message, 'success');
                    selectedUsers.delete(parseInt(userId));
                    if (tableBody.querySelectorAll('tr').length === 1 && currentPage > 1) {
                        await fetchUsers(currentPage - 1, currentQuery);
                    } else {
                        await fetchUsers(currentPage, currentQuery);
                    }
                } else {
                    showToast('Lỗi', result.message, 'danger');
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
                    body: JSON.stringify({ user_ids: userIds })
                });
                if (result.success) {
                    showToast('Thành công', result.message, 'success');
                    selectedUsers.clear();
                    await fetchUsers(1, '');
                } else {
                    showToast('Lỗi', result.message, 'danger');
                }
            } catch (error) {
                showToast('Lỗi', error.message || 'Không thể xóa hàng loạt', 'danger');
            }
        }

        // --- Event Handlers ---
        async function setupEventListeners() {
            searchInput.addEventListener('keyup', debounce(() => fetchUsers(1, searchInput.value.trim()), DEBOUNCE_DELAY));
            searchForm.addEventListener('submit', (e) => {
                e.preventDefault();
                fetchUsers(1, searchInput.value.trim());
            });

            document.querySelector('[data-bs-target="#addUserModal"]').addEventListener('click', async (e) => {
                e.preventDefault();
                addUserModal = await loadModal('/users/modals/create', 'addUserModal');
                if (addUserModal) {
                    addUserModal.show();
                    // Setup form submit handler for add user modal
                    const addUserForm = document.getElementById('addUserForm');
                    if (addUserForm) {
                        addUserForm.addEventListener('submit', async function(event) {
                            event.preventDefault();
                            const button = addUserForm.querySelector('button[type="submit"]');
                            toggleButtonLoading(button, true);
                            clearValidationErrors(addUserForm);

                            const formData = new FormData(addUserForm);
                            const data = Object.fromEntries(formData.entries());

                            try {
                                const result = await apiFetch('/api/users', {
                                    method: 'POST',
                                    body: data
                                });

                                if (result.success) {
                                    addUserModal.hide();
                                    showToast('Thành công', result.message, 'success');
                                    document.dispatchEvent(new CustomEvent('usersUpdated'));
                                }
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
                    }
                }
            });

            tableBody.addEventListener('click', async function (event) {
                const target = event.target.closest('[data-action]');
                if (!target) return;
                const action = target.dataset.action;
                const id = target.dataset.id;

                switch (action) {
                    case 'edit-user':
                        editUserModal = await loadModal(`/users/modals/edit/${id}`, 'editUserModal');
                        if (editUserModal) {
                            editUserModal.show();
                            // Setup form submit handler for edit user modal
                            const editUserForm = document.getElementById('editUserForm');
                            const userId = document.getElementById('editUserId').value;
                            if (editUserForm) {
                                editUserForm.addEventListener('submit', async function(event) {
                                    event.preventDefault();
                                    const button = editUserForm.querySelector('button[type="submit"]');
                                    toggleButtonLoading(button, true);
                                    clearValidationErrors(editUserForm);

                                    const formData = new FormData(editUserForm);
                                    const data = Object.fromEntries(formData.entries());

                                    // Remove password fields if empty
                                    if (!data.password && !data.password_confirmation) {
                                        delete data.password;
                                        delete data.password_confirmation;
                                    }

                                    try {
                                        const result = await apiFetch(`/api/users/${userId}`, {
                                            method: 'PUT',
                                            body: data
                                        });

                                        if (result.success) {
                                            editUserModal.hide();
                                            showToast('Thành công', result.message, 'success');
                                            document.dispatchEvent(new CustomEvent('usersUpdated', { detail: { isEdit: true } }));
                                        }
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
                            }
                        }
                        break;
                    case 'delete-user':
                        if (id) deleteUser(id);
                        break;
                    case 'toggle-select':
                        const checkbox = event.target.closest('.user-checkbox');
                        if(checkbox) toggleUserSelection(checkbox.value, checkbox.checked);
                        break;
                }
            });

            paginationContainer.addEventListener('click', function (event) {
                event.preventDefault();
                const target = event.target.closest('a.page-link');
                if (target && !target.parentElement.classList.contains('disabled')) {
                    const page = parseInt(target.dataset.page, 10);
                    if (!isNaN(page) && page !== currentPage) fetchUsers(page, currentQuery);
                }
            });

            btnBulkDelete.addEventListener('click', bulkDeleteUsers);
            selectAllCheckbox.addEventListener('change', toggleSelectAll);

            document.addEventListener('usersUpdated', (e) => {
                const isEdit = e.detail?.isEdit || false;
                fetchUsers(isEdit ? currentPage : 1, currentQuery);
            });
        }

        // --- Initialization ---
        function init() {
            setupEventListeners();
            const urlParams = new URLSearchParams(window.location.search);
            const page = parseInt(urlParams.get('page'), 10) || 1;
            const query = urlParams.get('q') || '';
            searchInput.value = query;
            fetchUsers(page, query);
        }

        init();
    });
    </script>
@endpush
