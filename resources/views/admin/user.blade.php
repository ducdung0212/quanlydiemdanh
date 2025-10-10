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
                                            <a class="tf-button style-1 w208" href="#" data-bs-toggle="modal" data-bs-target="#addAccountModal">
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
                            </div>
                        </div>

                        @include('layouts_main.footer')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountModalLabel" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);">
                <div class="modal-header" style="border-bottom: 1px solid #eee; padding: 20px 25px;">
                    <h5 class="modal-title" id="addAccountModalLabel" style="font-size: 1.3rem; font-weight: 600;">Thêm tài khoản mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addAccountForm">
                    <div class="modal-body" style="padding: 25px;">
                        <div class="mb-4">
                            <label for="username" class="form-label" style="font-weight: 500;">Tên</label>
                            <input type="text" class="form-control" id="username" name="name" required 
                                   style="border-radius: 8px; padding: 12px; border: 1px solid #ddd;">
                        </div>
                        <div class="mb-4">
                            <label for="email" class="form-label" style="font-weight: 500;">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required
                                   style="border-radius: 8px; padding: 12px; border: 1px solid #ddd;">
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label" style="font-weight: 500;">Mật khẩu</label>
                            <input type="password" class="form-control" id="password" name="password" required
                                   style="border-radius: 8px; padding: 12px; border: 1px solid #ddd;">
                        </div>
                        <div class="mb-4">
                            <label for="confirmPassword" class="form-label" style="font-weight: 500;">Xác nhận mật khẩu</label>
                            <input type="password" class="form-control" id="confirmPassword" name="password_confirmation" required
                                   style="border-radius: 8px; padding: 12px; border: 1px solid #ddd;">
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid #eee; padding: 20px 25px;">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" 
                                style="border-radius: 8px; padding: 10px 20px; border: none;">Hủy</button>
                        <button type="submit" class="btn btn-primary" id="btnAddAccount"
                                style="border-radius: 8px; padding: 10px 20px; background-color: #2377FC; border: none;">
                            <span class="btn-text">Thêm</span>
                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editAccountModal" tabindex="-1" aria-labelledby="editAccountModalLabel" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);">
                <div class="modal-header" style="border-bottom: 1px solid #eee; padding: 20px 25px;">
                    <h5 class="modal-title" id="editAccountModalLabel" style="font-size: 1.3rem; font-weight: 600;">Sửa tài khoản</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editAccountForm">
                    <div class="modal-body" style="padding: 25px;">
                        <input type="hidden" id="editUserId">
                        <div class="mb-4">
                            <label for="editUsername" class="form-label" style="font-weight: 500;">Tên</label>
                            <input type="text" class="form-control" id="editUsername" name="name" required
                                   style="border-radius: 8px; padding: 12px; border: 1px solid #ddd;">
                        </div>
                        <div class="mb-4">
                            <label for="editEmail" class="form-label" style="font-weight: 500;">Email</label>
                            <input type="email" class="form-control" id="editEmail" name="email" required
                                   style="border-radius: 8px; padding: 12px; border: 1px solid #ddd;">
                        </div>
                        <div class="mb-4">
                            <label for="editPassword" class="form-label" style="font-weight: 500;">Mật khẩu mới (Để trống nếu không đổi)</label>
                            <input type="password" class="form-control" id="editPassword" name="password"
                                   style="border-radius: 8px; padding: 12px; border: 1px solid #ddd;">
                        </div>
                        <div class="mb-4">
                            <label for="editConfirmPassword" class="form-label" style="font-weight: 500;">Xác nhận mật khẩu mới</label>
                            <input type="password" class="form-control" id="editConfirmPassword" name="password_confirmation"
                                   style="border-radius: 8px; padding: 12px; border: 1px solid #ddd;">
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid #eee; padding: 20px 25px;">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                                style="border-radius: 8px; padding: 10px 20px; border: none;">Hủy</button>
                        <button type="submit" class="btn btn-primary" id="btnEditAccount"
                                style="border-radius: 8px; padding: 10px 20px; background-color: #2377FC; border: none;">
                            <span class="btn-text">Lưu thay đổi</span>
                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <link href="{{ asset('css/common_admin.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap-select.min.js') }}"></script>   
    <script src="{{ asset('js/apexcharts/apexcharts.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- Configuration ---
        const API_BASE_URL = '/api/users';
        const ITEMS_PER_PAGE = 10;
        const DEBOUNCE_DELAY = 300;

        // --- DOM Elements ---
        const wrapper = document.getElementById('wrapper');
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
        
        // --- Modals ---
        const addAccountModal = new bootstrap.Modal(document.getElementById('addAccountModal'));
        const editAccountModal = new bootstrap.Modal(document.getElementById('editAccountModal'));
        const addAccountForm = document.getElementById('addAccountForm');
        const editAccountForm = document.getElementById('editAccountForm');

        // --- State ---
        let currentPage = 1;
        let currentQuery = '';
        let paginationData = null;
        let isLoading = false;
        let selectedUsers = new Set();
        
        // --- Utility Functions ---

        /**
         * Generic API fetch function with error handling and CSRF token.
         * @param {string} url - The URL to fetch.
         * @param {object} options - Fetch options (method, headers, body).
         * @returns {Promise<any>} - The JSON response.
         */
        async function apiFetch(url, options = {}) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            const defaultHeaders = {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            };

            if (options.body) {
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
                render(); // Render empty state
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
                                <a href="#" data-action="edit-user" data-id="${user.id}" data-name="${escapeHtml(user.name)}" data-email="${escapeHtml(user.email)}">
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

            // Simplified pagination logic (can be expanded)
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
                const { action, id } = actionTarget.dataset;

                switch (action) {
                    case 'edit-user':
                        handleEditClick(actionTarget);
                        break;
                    case 'delete-user':
                        handleDeleteClick(id);
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
                    fetchUsers(page, currentQuery);
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }
        });

        function handleEditClick(target) {
            const { id, name, email } = target.dataset;
            editAccountForm.querySelector('#editUserId').value = id;
            editAccountForm.querySelector('#editUsername').value = name;
            editAccountForm.querySelector('#editEmail').value = email;
            editAccountForm.reset(); // Also clears passwords
            editAccountModal.show();
        }

        function handleDeleteClick(id) {
            if (confirm('Bạn có chắc chắn muốn xóa tài khoản này?')) {
                deleteUser(id);
            }
        }
        
        function handleCheckboxChange(checkbox) {
            const userId = parseInt(checkbox.value);
            if (checkbox.checked) {
                selectedUsers.add(userId);
            } else {
                selectedUsers.delete(userId);
            }
            updateBulkDeleteButton();
            updateSelectAllCheckbox();
        }

        selectAllCheckbox.addEventListener('change', (e) => {
            const isChecked = e.target.checked;
            document.querySelectorAll('.user-checkbox').forEach(checkbox => {
                checkbox.checked = isChecked;
                const userId = parseInt(checkbox.value);
                if (isChecked) {
                    selectedUsers.add(userId);
                } else {
                    selectedUsers.delete(userId);
                }
            });
            updateBulkDeleteButton();
        });

        // --- Update UI State Functions ---
        
        function updateBulkDeleteButton() {
            const count = selectedUsers.size;
            selectedCountSpan.textContent = count;
            btnBulkDelete.classList.toggle('d-none', count === 0);
        }

        function updateSelectAllCheckbox() {
            const checkboxes = document.querySelectorAll('.user-checkbox');
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
        
        addAccountForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const button = e.currentTarget.querySelector('#btnAddAccount');
            const formData = new FormData(addAccountForm);
            const data = Object.fromEntries(formData.entries());

            if (data.password !== data.password_confirmation) {
                return alert('Mật khẩu xác nhận không khớp.');
            }
            
            toggleButtonLoading(button, true);
            try {
                await apiFetch(API_BASE_URL, {
                    method: 'POST',
                    body: JSON.stringify(data),
                });
                alert('Thêm tài khoản thành công!');
                addAccountModal.hide();
                addAccountForm.reset();
                fetchUsers(1, '');
            } catch (error) {
                alert(`Lỗi: ${error.message}`);
            } finally {
                toggleButtonLoading(button, false);
            }
        });

        editAccountForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const button = e.currentTarget.querySelector('#btnEditAccount');
            const id = document.getElementById('editUserId').value;
            const formData = new FormData(editAccountForm);
            let data = Object.fromEntries(formData.entries());

            if (data.password && data.password !== data.password_confirmation) {
                return alert('Mật khẩu xác nhận không khớp.');
            }
            if (!data.password) {
                delete data.password;
                delete data.password_confirmation;
            }

            toggleButtonLoading(button, true);
            try {
                await apiFetch(`${API_BASE_URL}/${id}`, {
                    method: 'PUT',
                    body: JSON.stringify(data)
                });
                alert('Cập nhật thành công!');
                editAccountModal.hide();
                fetchUsers(currentPage, currentQuery);
            } catch (error) {
                alert(`Lỗi: ${error.message}`);
            } finally {
                toggleButtonLoading(button, false);
            }
        });
        
        async function deleteUser(id) {
            try {
                await apiFetch(`${API_BASE_URL}/${id}`, { method: 'DELETE' });
                alert('Xóa tài khoản thành công!');
                selectedUsers.delete(parseInt(id)); // Remove from selection
                // If the last item on a page is deleted, go to the previous page
                const isLastItemOnPage = paginationData.data.length === 1 && currentPage > 1;
                fetchUsers(isLastItemOnPage ? currentPage - 1 : currentPage, currentQuery);
            } catch (error) {
                alert(`Lỗi: ${error.message}`);
            }
        }

        btnBulkDelete.addEventListener('click', async () => {
            if (selectedUsers.size === 0) return;
            if (!confirm(`Bạn có chắc chắn muốn xóa ${selectedUsers.size} tài khoản đã chọn?`)) return;

            try {
                await apiFetch(`${API_BASE_URL}/bulk-delete`, {
                    method: 'POST',
                    body: JSON.stringify({ ids: Array.from(selectedUsers) })
                });
                alert('Xóa các tài khoản đã chọn thành công!');
                selectedUsers.clear();
                fetchUsers(1, ''); // Reset to first page after bulk action
            } catch (error) {
                 alert(`Lỗi: ${error.message}`);
            }
        });
        
        // --- Search ---
        const handleSearch = debounce(query => fetchUsers(1, query), DEBOUNCE_DELAY);
        searchInput.addEventListener('input', e => handleSearch(e.target.value));
        searchForm.addEventListener('submit', e => {
            e.preventDefault();
            fetchUsers(1, searchInput.value);
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
            fetchUsers(state.page, state.query);
        });

        // --- Initial Load ---
        const initialParams = getURLParams();
        searchInput.value = initialParams.query;
        fetchUsers(initialParams.page, initialParams.query);
    });
    </script>
</body>