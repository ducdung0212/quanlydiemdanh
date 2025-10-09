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
                                            <button class="btn btn-outline-primary" id="btnExport" style="padding: 8px 16px; border-radius: 8px;">
                                                <i class="icon-download"></i> Export CSV
                                            </button>
                                            <select class="form-select" id="perPageSelect" style="width: auto; padding: 8px 12px; border-radius: 8px;">
                                                <option value="5">5 / trang</option>
                                                <option value="10">10 / trang</option>
                                                <option value="20">20 / trang</option>
                                                <option value="50">50 / trang</option>
                                            </select>
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

    <!-- Add Account Modal -->
    <div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountModalLabel" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);">
                <div class="modal-header" style="border-bottom: 1px solid #eee; padding: 20px 25px;">
                    <h5 class="modal-title" id="addAccountModalLabel" style="font-size: 1.3rem; font-weight: 600;">Thêm tài khoản mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding: 25px;">
                    <form id="addAccountForm">
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
                    </form>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #eee; padding: 20px 25px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" 
                            style="border-radius: 8px; padding: 10px 20px; border: none;">Hủy</button>
                    <button type="button" class="btn btn-primary" id="btnAddAccount"
                            style="border-radius: 8px; padding: 10px 20px; background-color: #2377FC; border: none;">
                        <span class="btn-text">Thêm</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Account Modal -->
    <div class="modal fade" id="editAccountModal" tabindex="-1" aria-labelledby="editAccountModalLabel" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);">
                <div class="modal-header" style="border-bottom: 1px solid #eee; padding: 20px 25px;">
                    <h5 class="modal-title" id="editAccountModalLabel" style="font-size: 1.3rem; font-weight: 600;">Sửa tài khoản</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding: 25px;">
                    <form id="editAccountForm">
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
                    </form>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #eee; padding: 20px 25px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            style="border-radius: 8px; padding: 10px 20px; border: none;">Hủy</button>
                    <button type="button" class="btn btn-primary" id="btnEditAccount"
                            style="border-radius: 8px; padding: 10px 20px; background-color: #2377FC; border: none;">
                        <span class="btn-text">Lưu thay đổi</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <link href="{{ asset('css/user_css.css') }}" rel="stylesheet">

    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap-select.min.js') }}"></script>   
    <script src="{{ asset('js/apexcharts/apexcharts.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>
    <script>
        const apiBase = '/api/users';
        
        let currentPage = 1;
        let currentQuery = '';
        let itemsPerPage = 5;
        let paginationData = null;
        let isLoading = false;
        let selectedUsers = new Set();

        // Utility function for debouncing
        const debounce = (func, delay) => {
            let timeout;
            return function(...args) {
                const context = this;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), delay);
            };
        };

        // Show/hide loading button state
        function toggleButtonLoading(button, loading) {
            const btnText = button.querySelector('.btn-text');
            const spinner = button.querySelector('.spinner-border');
            
            if (loading) {
                button.disabled = true;
                btnText.classList.add('d-none');
                spinner.classList.remove('d-none');
            } else {
                button.disabled = false;
                btnText.classList.remove('d-none');
                spinner.classList.add('d-none');
            }
        }

        // Fetches users from the API with server-side pagination
        async function fetchUsers(page = 1, query = '', perPage = itemsPerPage) {
            if (isLoading) return;
            
            isLoading = true;
            const tbody = document.getElementById('users-table-body');
            tbody.innerHTML = '<tr><td colspan="4" class="text-center">Đang tải...</td></tr>';
            
            try {
                const url = `${apiBase}?page=${page}&limit=${perPage}&q=${encodeURIComponent(query)}`;
                const response = await fetch(url);
                
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                
                const result = await response.json();
                
                if (result.success && result.data) {
                    paginationData = result.data;
                    currentPage = paginationData.current_page;
                    currentQuery = query;
                    itemsPerPage = perPage;
                    updateURL(currentPage, currentQuery, itemsPerPage);
                    renderUI();
                } else {
                    throw new Error('Invalid response format');
                }
            } catch (error) {
                console.error("Failed to fetch users:", error);
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Lỗi khi tải dữ liệu.</td></tr>';
                paginationData = null;
                renderUI();
            } finally {
                isLoading = false;
            }
        }

        // Renders the table and pagination based on API response
        function renderUI() {
            const tbody = document.getElementById('users-table-body');
            const paginationContainer = document.getElementById('pagination-container');
            const paginationStart = document.getElementById('pagination-start');
            const paginationEnd = document.getElementById('pagination-end');
            const paginationTotal = document.getElementById('pagination-total');

            tbody.innerHTML = '';
            paginationContainer.innerHTML = '';

            if (!paginationData || !paginationData.data) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center">Không có dữ liệu</td></tr>';
                paginationStart.textContent = '0';
                paginationEnd.textContent = '0';
                paginationTotal.textContent = '0';
                return;
            }

            const users = paginationData.data;
            const from = paginationData.from || 0;
            const to = paginationData.to || 0;
            const total = paginationData.total || 0;
            const lastPage = paginationData.last_page || 1;

            // Update pagination info
            paginationStart.textContent = from;
            paginationEnd.textContent = to;
            paginationTotal.textContent = total;

            // Render table rows
            if (users.length > 0) {
                users.forEach((user, index) => {
                    const tr = document.createElement('tr');
                    const isChecked = selectedUsers.has(user.id) ? 'checked' : '';
                    tr.innerHTML = `
                        <td class="text-center">
                            <input type="checkbox" class="user-checkbox" value="${user.id}" ${isChecked} style="cursor: pointer;">
                        </td>
                        <td class="text-center">${from + index}</td>
                        <td>${escapeHtml(user.name)}</td>
                        <td>${escapeHtml(user.email)}</td>
                        <td>
                            <div class="list-icon-function">
                                <a href="#" class="edit-user" data-id="${user.id}" data-name="${escapeHtml(user.name)}" data-email="${escapeHtml(user.email)}" data-bs-toggle="modal" data-bs-target="#editAccountModal">
                                    <div class="item edit"><i class="icon-edit-3"></i></div>
                                </a>
                                <a href="#" class="delete-user" data-id="${user.id}">
                                    <div class="item text-danger delete"><i class="icon-trash-2"></i></div>
                                </a>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center">Không tìm thấy tài khoản nào</td></tr>';
            }

            // Render pagination controls
            if (lastPage > 1) {
                const prevDisabled = currentPage === 1 ? 'disabled' : '';
                paginationContainer.innerHTML += `
                    <li class="page-item ${prevDisabled}">
                        <a class="page-link" href="#" data-page="${currentPage - 1}">
                            <i class="icon-chevron-left"></i>
                        </a>
                    </li>
                `;

                let startPage = Math.max(1, currentPage - 3);
                let endPage = Math.min(lastPage, currentPage + 3);

                if (currentPage <= 4) {
                    endPage = Math.min(7, lastPage);
                }
                if (currentPage >= lastPage - 3) {
                    startPage = Math.max(1, lastPage - 6);
                }

                if (startPage > 1) {
                    paginationContainer.innerHTML += `
                        <li class="page-item">
                            <a class="page-link" href="#" data-page="1">1</a>
                        </li>
                    `;
                    if (startPage > 2) {
                        paginationContainer.innerHTML += `
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        `;
                    }
                }

                for (let i = startPage; i <= endPage; i++) {
                    const active = i === currentPage ? 'active' : '';
                    paginationContainer.innerHTML += `
                        <li class="page-item ${active}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>
                    `;
                }

                if (endPage < lastPage) {
                    if (endPage < lastPage - 1) {
                        paginationContainer.innerHTML += `
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        `;
                    }
                    paginationContainer.innerHTML += `
                        <li class="page-item">
                            <a class="page-link" href="#" data-page="${lastPage}">${lastPage}</a>
                        </li>
                    `;
                }

                const nextDisabled = currentPage === lastPage ? 'disabled' : '';
                paginationContainer.innerHTML += `
                    <li class="page-item ${nextDisabled}">
                        <a class="page-link" href="#" data-page="${currentPage + 1}">
                            <i class="icon-chevron-right"></i>
                        </a>
                    </li>
                `;
            }

            attachActionEvents();
        }

        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }

        // Attach events for edit, delete, and pagination
        function attachActionEvents() {
            // Checkbox selection
            document.querySelectorAll('.user-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', (e) => {
                    const userId = parseInt(e.target.value);
                    if (e.target.checked) {
                        selectedUsers.add(userId);
                    } else {
                        selectedUsers.delete(userId);
                    }
                    updateBulkDeleteButton();
                    updateSelectAllCheckbox();
                });
            });

            // Select all checkbox
            const selectAllCheckbox = document.getElementById('selectAll');
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', (e) => {
                    const checkboxes = document.querySelectorAll('.user-checkbox');
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = e.target.checked;
                        const userId = parseInt(checkbox.value);
                        if (e.target.checked) {
                            selectedUsers.add(userId);
                        } else {
                            selectedUsers.delete(userId);
                        }
                    });
                    updateBulkDeleteButton();
                });
            }

            // Edit user
            document.querySelectorAll('.edit-user').forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    const { id, name, email } = e.currentTarget.dataset;
                    document.getElementById('editUserId').value = id;
                    document.getElementById('editUsername').value = name;
                    document.getElementById('editEmail').value = email;
                    document.getElementById('editPassword').value = '';
                    document.getElementById('editConfirmPassword').value = '';
                });
            });

            document.querySelectorAll('.delete-user').forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (confirm('Bạn có chắc chắn muốn xóa tài khoản này?')) {
                        const id = e.currentTarget.dataset.id;
                        deleteUser(id);
                    }
                });
            });

            document.querySelectorAll('#pagination-container .page-link').forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (e.currentTarget.parentElement.classList.contains('disabled')) return;
                    const page = parseInt(e.currentTarget.dataset.page);
                    if (page && page !== currentPage) {
                        fetchUsers(page, currentQuery, itemsPerPage);
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                });
            });
        }

        // Update bulk delete button visibility and count
        function updateBulkDeleteButton() {
            const btnBulkDelete = document.getElementById('btnBulkDelete');
            const selectedCount = document.getElementById('selectedCount');
            
            if (selectedUsers.size > 0) {
                btnBulkDelete.classList.remove('d-none');
                selectedCount.textContent = selectedUsers.size;
            } else {
                btnBulkDelete.classList.add('d-none');
            }
        }

        // Update select all checkbox state
        function updateSelectAllCheckbox() {
            const selectAllCheckbox = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.user-checkbox');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            const someChecked = Array.from(checkboxes).some(cb => cb.checked);
            
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = someChecked && !allChecked;
            }
        }

        // Handle Add User
        document.getElementById('btnAddAccount').addEventListener('click', async () => {
            const button = document.getElementById('btnAddAccount');
            const form = document.getElementById('addAccountForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            if (!data.name || !data.email || !data.password) {
                return alert('Vui lòng điền đầy đủ thông tin.');
            }
            if (data.password !== data.password_confirmation) {
                return alert('Mật khẩu xác nhận không khớp.');
            }

            toggleButtonLoading(button, true);

            try {
                const response = await fetch(apiBase, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'Accept': 'application/json' 
                    },
                    body: JSON.stringify(data)
                });
                
                if (response.ok) {
                    alert('Thêm tài khoản thành công!');
                    bootstrap.Modal.getInstance(document.getElementById('addAccountModal')).hide();
                    form.reset();
                    fetchUsers(1, currentQuery, itemsPerPage);
                } else {
                    const error = await response.json();
                    alert('Lỗi: ' + (error.message || 'Không thể thêm tài khoản.'));
                }
            } catch (error) {
                console.error("Add user failed:", error);
                alert('Có lỗi xảy ra, vui lòng thử lại.');
            } finally {
                toggleButtonLoading(button, false);
            }
        });

        // Handle Edit User
        document.getElementById('btnEditAccount').addEventListener('click', async () => {
            const button = document.getElementById('btnEditAccount');
            const id = document.getElementById('editUserId').value;
            const form = document.getElementById('editAccountForm');
            const formData = new FormData(form);
            let data = Object.fromEntries(formData.entries());

            if (!data.name || !data.email) {
                return alert('Tên và Email không được để trống.');
            }
            if (data.password && data.password !== data.password_confirmation) {
                return alert('Mật khẩu xác nhận không khớp.');
            }
            if (!data.password) {
                delete data.password;
                delete data.password_confirmation;
            }

            toggleButtonLoading(button, true);

            try {
                const response = await fetch(`${apiBase}/${id}`, {
                    method: 'PUT',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'Accept': 'application/json' 
                    },
                    body: JSON.stringify(data)
                });

                if (response.ok) {
                    alert('Cập nhật thành công!');
                    bootstrap.Modal.getInstance(document.getElementById('editAccountModal')).hide();
                    fetchUsers(currentPage, currentQuery, itemsPerPage);
                } else {
                    const error = await response.json();
                    alert('Lỗi: ' + (error.message || 'Không thể cập nhật.'));
                }
            } catch (error) {
                console.error("Update user failed:", error);
                alert('Có lỗi xảy ra, vui lòng thử lại.');
            } finally {
                toggleButtonLoading(button, false);
            }
        });

        // Handle Delete User
        async function deleteUser(id) {
            try {
                const response = await fetch(`${apiBase}/${id}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json' }
                });

                if (response.ok) {
                    alert('Xóa tài khoản thành công!');
                    
                    if (paginationData.data.length === 1 && currentPage > 1) {
                        fetchUsers(currentPage - 1, currentQuery, itemsPerPage);
                    } else {
                        fetchUsers(currentPage, currentQuery, itemsPerPage);
                    }
                } else {
                    const error = await response.json();
                    alert('Lỗi: ' + (error.message || 'Không thể xóa tài khoản.'));
                }
            } catch (error) {
                console.error("Delete user failed:", error);
                alert('Có lỗi xảy ra, vui lòng thử lại.');
            }
        }

        // Handle items per page change
        document.getElementById('perPageSelect').addEventListener('change', (e) => {
            const perPage = parseInt(e.target.value);
            fetchUsers(1, currentQuery, perPage);
        });

        // Export to CSV
        document.getElementById('btnExport').addEventListener('click', async () => {
            try {
                const url = `${apiBase}?limit=10000&q=${encodeURIComponent(currentQuery)}`;
                const response = await fetch(url);
                const result = await response.json();
                
                if (result.success && result.data && result.data.data) {
                    const users = result.data.data;
                    let csv = 'STT,Tên,Email,Ngày tạo\n';
                    
                    users.forEach((user, index) => {
                        const createdAt = user.created_at ? new Date(user.created_at).toLocaleDateString('vi-VN') : '';
                        csv += `${index + 1},"${user.name}","${user.email}","${createdAt}"\n`;
                    });
                    
                    const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
                    const link = document.createElement('a');
                    const url = URL.createObjectURL(blob);
                    link.setAttribute('href', url);
                    link.setAttribute('download', `users_${new Date().getTime()}.csv`);
                    link.style.visibility = 'hidden';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    
                    alert('Export thành công!');
                } else {
                    alert('Không thể export dữ liệu.');
                }
            } catch (error) {
                console.error('Export failed:', error);
                alert('Có lỗi xảy ra khi export.');
            }
        });

        // Search handler
        const handleSearch = debounce((query) => {
            fetchUsers(1, query, itemsPerPage);
        }, 300);

        document.getElementById('searchInput').addEventListener('input', (e) => {
            handleSearch(e.target.value);
        });

        document.getElementById('searchForm').addEventListener('submit', (e) => {
            e.preventDefault();
            const query = document.getElementById('searchInput').value;
            fetchUsers(1, query, itemsPerPage);
        });

        // URL State Management
        function updateURL(page, query, perPage) {
            const url = new URL(window.location);
            url.searchParams.set('page', page);
            url.searchParams.set('per_page', perPage);
            if (query) {
                url.searchParams.set('q', query);
            } else {
                url.searchParams.delete('q');
            }
            window.history.pushState({}, '', url);
        }

        function getURLParams() {
            const url = new URL(window.location);
            return {
                page: parseInt(url.searchParams.get('page')) || 1,
                query: url.searchParams.get('q') || '',
                perPage: parseInt(url.searchParams.get('per_page')) || 5
            };
        }

        // Initial load from URL params
        document.addEventListener('DOMContentLoaded', () => {
            const params = getURLParams();
            itemsPerPage = params.perPage;
            document.getElementById('perPageSelect').value = params.perPage;
            document.getElementById('searchInput').value = params.query;
            fetchUsers(params.page, params.query, params.perPage);
        });

        // Handle browser back/forward buttons
        window.addEventListener('popstate', () => {
            const params = getURLParams();
            itemsPerPage = params.perPage;
            document.getElementById('perPageSelect').value = params.perPage;
            document.getElementById('searchInput').value = params.query;
            fetchUsers(params.page, params.query, params.perPage);
        });
    </script>
</body>