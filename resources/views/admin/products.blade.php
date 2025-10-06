GIT<body class="body">
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
                                            <form class="form-search">
                                                <fieldset class="name">
                                                    <input type="text" placeholder="Tìm kiếm tài khoản..." class="" name="name"
                                                        tabindex="2" value="" aria-required="true" required="">
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
                                            <tbody>
                                                <tr>
                                                    <td class="text-center">1</td>
                                                    <td>Dung</td>
                                                    <td>password123</td>                           
                                                    <td>
                                                        <div class="list-icon-function">
                                                            <a href="#" data-bs-toggle="modal" data-bs-target="#editAccountModal">
                                                                <div class="item edit">
                                                                    <i class="icon-edit-3"></i>
                                                                </div>
                                                            </a>
                                                            <form action="#" method="POST">
                                                                <div class="item text-danger delete">
                                                                    <i class="icon-trash-2"></i>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-center">2</td>
                                                    <td>user456</td>
                                                    <td>pass457</td>
                                                    <td>
                                                        <div class="list-icon-function">
                                                            <a href="#" data-bs-toggle="modal" data-bs-target="#editAccountModal">
                                                                <div class="item edit">
                                                                    <i class="icon-edit-3"></i>
                                                                </div>
                                                            </a>
                                                            <form action="#" method="POST">
                                                                <div class="item text-danger delete">
                                                                    <i class="icon-trash-2"></i>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
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
                <button type="button" class="btn btn-primary"
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
                    <div class="mb-4">
                        <label for="editUsername" class="form-label" style="font-weight: 500;">Tài khoản</label>
                        <input type="text" class="form-control" id="editUsername" name="username" value="admin123" required
                               style="border-radius: 8px; padding: 12px; border: 1px solid #ddd;">
                    </div>
                    <div class="mb-4">
                        <label for="editUsername" class="form-label" style="font-weight: 500;">Email</label>
                        <input type="text" class="form-control" id="editUsername" name="username" value="admin123" required
                               style="border-radius: 8px; padding: 12px; border: 1px solid #ddd;">
                    </div>
                    <div class="mb-4">
                        <label for="editPassword" class="form-label" style="font-weight: 500;">Mật khẩu mới</label>
                        <input type="password" class="form-control" id="editPassword" name="password"
                               style="border-radius: 8px; padding: 12px; border: 1px solid #ddd;">
                        <div class="form-text" style="margin-top: 5px;">Để trống nếu không muốn thay đổi mật khẩu</div>
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
                <button type="button" class="btn btn-primary"
                        style="border-radius: 8px; padding: 10px 20px; background-color: #2377FC; border: none;">Lưu thay đổi</button>
            </div>
        </div>
    </div>
</div>

<style>

</style>

    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap-select.min.js') }}"></script>   
    <script src="{{ asset('js/apexcharts/apexcharts.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>
</body>
</html>