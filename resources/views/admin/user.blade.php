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
                                        <!-- Phân trang -->
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

        // Load danh sách users
        async function listUsers() {
            try {
                const res = await fetch(apiBase);
                const json = await res.json();
                const tbody = document.getElementById('users-table-body');
                tbody.innerHTML = '';

                if (json.success && json.data && json.data.data && json.data.data.length > 0) {
                    json.data.data.forEach((user, index) => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td class="text-center">${index + 1}</td>
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
                                const id = e.currentTarget.dataset.id;
                                await deleteUser(id);
                            }
                        });
                    });

                    // Gắn sự kiện cho nút edit
                    document.querySelectorAll('.edit-user').forEach(btn => {
                        btn.addEventListener('click', (e) => {
                            e.preventDefault();
                            const id = e.currentTarget.dataset.id;
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
                const res = await fetch(apiBase, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ name, email, password })
                });

                const json = await res.json();

                if (json.success) {
                    alert('Thêm tài khoản thành công!');
                    bootstrap.Modal.getInstance(document.getElementById('addAccountModal')).hide();
                    document.getElementById('addAccountForm').reset();
                    listUsers();
                } else {
                    alert('Lỗi: ' + (json.message || 'Không thể thêm tài khoản'));
                }
            } catch (error) {
                console.error('Error adding user:', error);
                alert('Có lỗi xảy ra khi thêm tài khoản!');
            }
        });

        // Cập nhật user
        document.getElementById('btnEditAccount').addEventListener('click', async () => {
            const id = document.getElementById('editUserId').value;
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
                const data = { name, email };
                if (password) {
                    data.password = password;
                }

                const res = await fetch(`${apiBase}/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const json = await res.json();

                if (json.success) {
                    alert('Cập nhật tài khoản thành công!');
                    bootstrap.Modal.getInstance(document.getElementById('editAccountModal')).hide();
                    listUsers();
                } else {
                    alert('Lỗi: ' + (json.message || 'Không thể cập nhật tài khoản'));
                }
            } catch (error) {
                console.error('Error updating user:', error);
                alert('Có lỗi xảy ra khi cập nhật tài khoản!');
            }
        });

        // Xóa user
        async function deleteUser(id) {
            try {
                const res = await fetch(`${apiBase}/${id}`, {
                    method: 'DELETE'
                });

                const json = await res.json();

                if (json.success) {
                    alert('Xóa tài khoản thành công!');
                    listUsers();
                } else {
                    alert('Lỗi: ' + (json.message || 'Không thể xóa tài khoản'));
                }
            } catch (error) {
                console.error('Error deleting user:', error);
                alert('Có lỗi xảy ra khi xóa tài khoản!');
            }
        }

        // Load dữ liệu khi trang được tải
        document.addEventListener('DOMContentLoaded', function() {
            listUsers();
        });

        // Tìm kiếm (có thể mở rộng thêm)
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const searchValue = document.getElementById('searchInput').value.toLowerCase();
            // Implement search logic here if needed
            console.log('Searching for:', searchValue);
        });
    </script>
</body>
</html>