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
                                                    <input type="text" placeholder="Tìm kiếm tài khoản..." class="" name="name"
                                                        tabindex="2" value="" aria-required="true" id="searchInput">
                                                </fieldset>
                                                <div class="button-submit">
                                                    <button class="" type="submit"><i class="icon-search"></i></button>
                                                </div>
                                            </form>
                                        </div>
                                        <a class="tf-button style-1 w208" href="#" data-bs-toggle="modal" data-bs-target="#addAccountModal">
                                            <i class="icon-plus"></i>Thêm mới
                                        </a>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th style="width: 60px">STT</th>
                                                    <th>Tên</th>
                                                    <th>Email</th>
                                                    <th style="width: 120px">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="users-table-body">
                                                <!-- Dữ liệu sẽ được load từ API -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="divider"></div>
                                    <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
    <div class="text-tiny text-secondary">
        Hiển thị <span id="pagination-start">1</span>-<span id="pagination-end">5</span> của <span id="pagination-total">5</span> tài khoản
    </div>
    <div class="pagination-controls">
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-end mb-0" id="pagination-container">
                <!-- Phân trang sẽ được tạo tự động bằng JavaScript -->
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

    <!-- Modal Thêm tài khoản -->
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
                            <input type="text" class="form-control" id="username" name="username" required 
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
                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required
                                   style="border-radius: 8px; padding: 12px; border: 1px solid #ddd;">
                        </div>
                    </form>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #eee; padding: 20px 25px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" 
                            style="border-radius: 8px; padding: 10px 20px; border: none;">Hủy</button>
                    <button type="button" class="btn btn-primary" id="btnAddAccount"
                            style="border-radius: 8px; padding: 10px 20px; background-color: #2377FC; border: none;">Thêm</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Sửa tài khoản -->
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
                            <input type="text" class="form-control" id="editUsername" name="username" required
                                   style="border-radius: 8px; padding: 12px; border: 1px solid #ddd;">
                        </div>
                        <div class="mb-4">
                            <label for="editEmail" class="form-label" style="font-weight: 500;">Email</label>
                            <input type="email" class="form-control" id="editEmail" name="email" required
                                   style="border-radius: 8px; padding: 12px; border: 1px solid #ddd;">
                        </div>
                        <div class="mb-4">
                            <label for="editPassword" class="form-label" style="font-weight: 500;">Mật khẩu mới(Để trống nếu không muốn thay đổi mật khẩu)</label>
                            <input type="password" class="form-control" id="editPassword" name="password"
                                   style="border-radius: 8px; padding: 12px; border: 1px solid #ddd;">
                        </div>
                        <div class="mb-4">
                            <label for="editConfirmPassword" class="form-label" style="font-weight: 500;">Xác nhận mật khẩu mới</label>
                            <input type="password" class="form-control" id="editConfirmPassword" name="confirmPassword"
                                   style="border-radius: 8px; padding: 12px; border: 1px solid #ddd;">
                        </div>
                    </form>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #eee; padding: 20px 25px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            style="border-radius: 8px; padding: 10px 20px; border: none;">Hủy</button>
                    <button type="button" class="btn btn-primary" id="btnEditAccount"
                            style="border-radius: 8px; padding: 10px 20px; background-color: #2377FC; border: none;">Lưu thay đổi</button>
                </div>
            </div>
        </div>
    </div>

   <link href="{{ asset('css/user_css.css') }}" rel="stylesheet">

<style>
    /* CHỈ GIỮ LẠI CÁC STYLE CẦN THIẾT NẾU CÓ */
    .loading {
        text-align: center;
        padding: 20px;
    }
</style>

    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap-select.min.js') }}"></script>   
    <script src="{{ asset('js/apexcharts/apexcharts.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>

    <script>
        const apiBase = '/api/users';

        // Biến phân trang
        let currentPage = 1;
        const itemsPerPage = 5;

        // Dữ liệu mẫu để demo giao diện - THÊM LẠI DỮ LIỆU MẪU
        const sampleUsers = [
            {
                id: 1,
                name: "Nguyễn Văn Admin",
                email: "admin@example.com"
            },
            {
                id: 2,
                name: "Trần Thị User",
                email: "user@example.com"
            },
            {
                id: 3,
                name: "Lê Văn Moderator",
                email: "moderator@example.com"
            },
            {
                id: 4,
                name: "Phạm Thị Editor",
                email: "editor@example.com"
            },
            {
                id: 5,
                name: "Hoàng Văn Viewer",
                email: "viewer@example.com"
            },
            {
                id: 6,
                name: "Đỗ Thị Manager",
                email: "manager@example.com"
            },
            {
                id: 7,
                name: "Vũ Văn Tester",
                email: "tester@example.com"
            }
        ];

        let currentUsers = [...sampleUsers];

        // Hàm phân trang
        function setupPagination(users = currentUsers) {
            const totalPages = Math.ceil(users.length / itemsPerPage);
            const paginationContainer = document.getElementById('pagination-container');
            const paginationStart = document.getElementById('pagination-start');
            const paginationEnd = document.getElementById('pagination-end');
            const paginationTotal = document.getElementById('pagination-total');
            
            // Cập nhật thông tin hiển thị
            const startIndex = (currentPage - 1) * itemsPerPage + 1;
            const endIndex = Math.min(currentPage * itemsPerPage, users.length);
            
            paginationStart.textContent = startIndex;
            paginationEnd.textContent = endIndex;
            paginationTotal.textContent = users.length;
            
            // Xóa phân trang cũ
            paginationContainer.innerHTML = '';
            
            // Nút Previous
            const prevItem = document.createElement('li');
            prevItem.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
            prevItem.innerHTML = `
                <a class="page-link" href="#" data-page="${currentPage - 1}">
                    <i class="icon-chevron-left"></i>
                </a>
            `;
            paginationContainer.appendChild(prevItem);
            
            // Các nút trang
            const maxVisiblePages = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
            let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
            
            // Điều chỉnh nếu không đủ số trang hiển thị
            if (endPage - startPage + 1 < maxVisiblePages) {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }
            
            // Nút trang đầu
            if (startPage > 1) {
                const firstPageItem = document.createElement('li');
                firstPageItem.className = 'page-item';
                firstPageItem.innerHTML = `
                    <a class="page-link" href="#" data-page="1">1</a>
                `;
                paginationContainer.appendChild(firstPageItem);
                
                if (startPage > 2) {
                    const ellipsisItem = document.createElement('li');
                    ellipsisItem.className = 'page-item disabled';
                    ellipsisItem.innerHTML = `<span class="page-link">...</span>`;
                    paginationContainer.appendChild(ellipsisItem);
                }
            }
            
            // Các trang số
            for (let i = startPage; i <= endPage; i++) {
                const pageItem = document.createElement('li');
                pageItem.className = `page-item ${i === currentPage ? 'active' : ''}`;
                pageItem.innerHTML = `
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                `;
                paginationContainer.appendChild(pageItem);
            }
            
            // Nút trang cuối
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    const ellipsisItem = document.createElement('li');
                    ellipsisItem.className = 'page-item disabled';
                    ellipsisItem.innerHTML = `<span class="page-link">...</span>`;
                    paginationContainer.appendChild(ellipsisItem);
                }
                
                const lastPageItem = document.createElement('li');
                lastPageItem.className = 'page-item';
                lastPageItem.innerHTML = `
                    <a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a>
                `;
                paginationContainer.appendChild(lastPageItem);
            }
            
            // Nút Next
            const nextItem = document.createElement('li');
            nextItem.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
            nextItem.innerHTML = `
                <a class="page-link" href="#" data-page="${currentPage + 1}">
                    <i class="icon-chevron-right"></i>
                </a>
            `;
            paginationContainer.appendChild(nextItem);
            
            // Gắn sự kiện cho các nút phân trang
            attachPaginationEvents(users);
        }

        // Gắn sự kiện cho phân trang
        function attachPaginationEvents(users = currentUsers) {
            document.querySelectorAll('.page-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (this.closest('.page-item').classList.contains('disabled')) {
                        return;
                    }
                    
                    const page = parseInt(this.dataset.page);
                    if (page && page !== currentPage) {
                        currentPage = page;
                        const startIndex = (currentPage - 1) * itemsPerPage;
                        const endIndex = startIndex + itemsPerPage;
                        const paginatedUsers = users.slice(startIndex, endIndex);
                        
                        listUsers(paginatedUsers);
                        setupPagination(users);
                    }
                });
            });
        }

        // Load danh sách users
        async function listUsers(users = currentUsers) {
            try {
                // SỬA LẠI: Sử dụng dữ liệu mẫu thay vì API để demo
                const tbody = document.getElementById('users-table-body');
                tbody.innerHTML = '';

                // Nếu không có users được truyền vào, sử dụng phân trang
                if (users === currentUsers) {
                    const startIndex = (currentPage - 1) * itemsPerPage;
                    const endIndex = startIndex + itemsPerPage;
                    users = currentUsers.slice(startIndex, endIndex);
                }

                const globalIndex = (currentPage - 1) * itemsPerPage;
                
                if (users.length > 0) {
                    users.forEach((user, index) => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td class="text-center">${globalIndex + index + 1}</td>
                            <td>${user.name}</td>
                            <td>${user.email}</td>
                            <td>
                                <div class="list-icon-function">
                                    <a href="#" class="edit-user" data-id="${user.id}" data-name="${user.name}" data-email="${user.email}" data-bs-toggle="modal" data-bs-target="#editAccountModal">
                                        <div class="item edit">
                                            <i class="icon-edit-3"></i>
                                        </div>
                                    </a>
                                    <a href="#" class="delete-user" data-id="${user.id}">
                                        <div class="item text-danger delete">
                                            <i class="icon-trash-2"></i>
                                        </div>
                                    </a>
                                </div>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });

                    // Gắn sự kiện cho nút delete
                    document.querySelectorAll('.delete-user').forEach(btn => {
                        btn.addEventListener('click', async (e) => {
                            e.preventDefault();
                            if (confirm('Bạn có chắc chắn muốn xóa tài khoản này?')) {
                                const id = parseInt(e.currentTarget.dataset.id);
                                await deleteUser(id);
                            }
                        });
                    });

                    // Gắn sự kiện cho nút edit
                    document.querySelectorAll('.edit-user').forEach(btn => {
                        btn.addEventListener('click', (e) => {
                            e.preventDefault();
                            const id = parseInt(e.currentTarget.dataset.id);
                            const name = e.currentTarget.dataset.name;
                            const email = e.currentTarget.dataset.email;
                            
                            document.getElementById('editUserId').value = id;
                            document.getElementById('editUsername').value = name;
                            document.getElementById('editEmail').value = email;
                            document.getElementById('editPassword').value = '';
                            document.getElementById('editConfirmPassword').value = '';
                        });
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center">Không có dữ liệu</td></tr>';
                }

                // Cập nhật phân trang
                setupPagination(currentUsers);
            } catch (error) {
                console.error('Error loading users:', error);
                document.getElementById('users-table-body').innerHTML = '<tr><td colspan="4" class="text-center text-danger">Lỗi khi tải dữ liệu</td></tr>';
            }
        }

        // Thêm user mới
        document.getElementById('btnAddAccount').addEventListener('click', async () => {
            const name = document.getElementById('username').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (!name || !email || !password) {
                alert('Vui lòng điền đầy đủ thông tin!');
                return;
            }

            if (password !== confirmPassword) {
                alert('Mật khẩu xác nhận không khớp!');
                return;
            }

            try {
                // SỬA LẠI: Thêm vào dữ liệu mẫu thay vì gọi API
                const newUser = {
                    id: Date.now(), // ID tạm thời
                    name: name,
                    email: email
                };

                // Thêm vào danh sách
                currentUsers.push(newUser);

                alert('Thêm tài khoản thành công! (DEMO)');
                bootstrap.Modal.getInstance(document.getElementById('addAccountModal')).hide();
                document.getElementById('addAccountForm').reset();
                refreshAfterDataChange();
                
            } catch (error) {
                console.error('Error adding user:', error);
                alert('Có lỗi xảy ra khi thêm tài khoản!');
            }
        });

        // Cập nhật user
        document.getElementById('btnEditAccount').addEventListener('click', async () => {
            const id = parseInt(document.getElementById('editUserId').value);
            const name = document.getElementById('editUsername').value;
            const email = document.getElementById('editEmail').value;
            const password = document.getElementById('editPassword').value;
            const confirmPassword = document.getElementById('editConfirmPassword').value;

            if (!name || !email) {
                alert('Vui lòng điền đầy đủ thông tin!');
                return;
            }

            if (password && password !== confirmPassword) {
                alert('Mật khẩu xác nhận không khớp!');
                return;
            }

            try {
                // SỬA LẠI: Cập nhật dữ liệu mẫu thay vì gọi API
                const userIndex = currentUsers.findIndex(user => user.id === id);
                if (userIndex !== -1) {
                    currentUsers[userIndex] = {
                        ...currentUsers[userIndex],
                        name: name,
                        email: email
                    };
                }

                alert('Cập nhật tài khoản thành công! (DEMO)');
                bootstrap.Modal.getInstance(document.getElementById('editAccountModal')).hide();
                refreshAfterDataChange();
                
            } catch (error) {
                console.error('Error updating user:', error);
                alert('Có lỗi xảy ra khi cập nhật tài khoản!');
            }
        });

        // Xóa user
        async function deleteUser(id) {
            try {
                // SỬA LẠI: Xóa khỏi dữ liệu mẫu thay vì gọi API
                currentUsers = currentUsers.filter(user => user.id !== id);

                alert('Xóa tài khoản thành công! (DEMO)');
                refreshAfterDataChange();
                
            } catch (error) {
                console.error('Error deleting user:', error);
                alert('Có lỗi xảy ra khi xóa tài khoản!');
            }
        }

        // Refresh sau khi thay đổi dữ liệu
        function refreshAfterDataChange() {
            currentPage = 1; // Reset về trang 1 sau khi thay đổi dữ liệu
            listUsers();
        }

        // Tìm kiếm
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const searchValue = document.getElementById('searchInput').value.toLowerCase().trim();
            
            if (searchValue) {
                const filteredUsers = currentUsers.filter(user => 
                    user.name.toLowerCase().includes(searchValue) ||
                    user.email.toLowerCase().includes(searchValue)
                );
                currentPage = 1; // Reset về trang 1 khi tìm kiếm
                listUsers(filteredUsers);
                setupPagination(filteredUsers);
            } else {
                currentPage = 1; // Reset về trang 1 khi xóa tìm kiếm
                listUsers();
                setupPagination();
            }
        });

        // Reset tìm kiếm
        document.getElementById('searchInput').addEventListener('input', function() {
            if (this.value.trim() === '') {
                currentPage = 1; // Reset về trang 1 khi xóa tìm kiếm
                listUsers();
                setupPagination();
            }
        });

        // Load dữ liệu khi trang được tải
        document.addEventListener('DOMContentLoaded', function() {
            listUsers();
        });
    </script>
</body>
</html>