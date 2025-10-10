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
                                    <h3>Quản lý Giảng Viên</h3>
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
                                            <div class="text-tiny">Quản lý Giảng Viên</div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="wg-box">
                                    <div class="flex items-center justify-between gap10 flex-wrap">
                                        <div class="wg-filter flex-grow">
                                            <form class="form-search" id="searchForm">
                                                <fieldset class="name">
                                                    <input type="text" placeholder="Tìm kiếm giảng viên..." class="" name="name"
                                                        tabindex="2" value="" aria-required="true" id="searchInput">
                                                </fieldset>
                                                <div class="button-submit">
                                                    <button class="" type="submit"><i class="icon-search"></i></button>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="flex items-center gap10">
                                            <a class="tf-button style-2 w208" href="#" id="importExcelBtn">
                                                <i class="icon-upload"></i>Import Excel
                                            </a>
                                            <a class="tf-button style-1 w208" href="#" data-bs-toggle="modal" data-bs-target="#addTeacherModal">
                                                <i class="icon-plus"></i>Thêm mới
                                            </a>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th style="width: 60px">STT</th>
                                                    <th>Mã GV</th>
                                                    <th>Tên</th>
                                                    <th>Mã khoa</th>
                                                    <th style="width: 150px">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="teachers-table-body">
                                                <!-- Dữ liệu sẽ được load từ API -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="divider"></div>
                                   <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
    <div class="text-tiny text-secondary">
        Hiển thị <span id="pagination-start">1</span>-<span id="pagination-end">5</span> của <span id="pagination-total">5</span> giảng viên
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

    <!-- Modal Thêm giảng viên -->
    <div class="modal fade" id="addTeacherModal" tabindex="-1" aria-labelledby="addTeacherModalLabel" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addTeacherModalLabel">Thêm giảng viên mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addTeacherForm">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="teacher_code" class="form-label">Mã Giảng Viên *</label>
                                <input type="text" class="form-control" id="teacher_code" name="teacher_code" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="full_name" class="form-label">Họ và Tên *</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="faculty" class="form-label">Mã khoa *</label>
                                <input type="text" class="form-control" id="faculty" name="faculty" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="position" class="form-label">Chức vụ *</label>
                                <input type="text" class="form-control" id="position" name="position" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="phone" class="form-label">Số Điện Thoại *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="avatar_url" class="form-label">Link Ảnh đại diện</label>
                            <input type="url" class="form-control" id="avatar_url" name="avatar_url" placeholder="https://example.com/image.jpg">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" id="btnAddTeacher">Thêm</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Sửa giảng viên -->
    <div class="modal fade" id="editTeacherModal" tabindex="-1" aria-labelledby="editTeacherModalLabel" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTeacherModalLabel">Sửa thông tin giảng viên</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editTeacherForm">
                        <input type="hidden" id="editTeacherId">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="editTeacherCode" class="form-label">Mã Giảng Viên *</label>
                                <input type="text" class="form-control" id="editTeacherCode" name="teacher_code" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="editFullName" class="form-label">Họ và Tên *</label>
                                <input type="text" class="form-control" id="editFullName" name="full_name" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="editFaculty" class="form-label">Mã khoa *</label>
                                <input type="text" class="form-control" id="editFaculty" name="faculty" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="editPosition" class="form-label">Chức vụ *</label>
                                <input type="text" class="form-control" id="editPosition" name="position" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="editPhone" class="form-label">Số Điện Thoại *</label>
                                <input type="tel" class="form-control" id="editPhone" name="phone" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="editEmail" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="editEmail" name="email" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="editAvatarUrl" class="form-label">Link Ảnh đại diện</label>
                            <input type="url" class="form-control" id="editAvatarUrl" name="avatar_url" placeholder="https://example.com/image.jpg">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" id="btnEditTeacher">Lưu thay đổi</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Xem chi tiết giảng viên -->
    <div class="modal fade" id="viewTeacherModal" tabindex="-1" aria-labelledby="viewTeacherModalLabel" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewTeacherModalLabel">Thông tin chi tiết giảng viên</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-3 text-center">
                            <div id="teacherAvatarContainer">
                                <img id="teacherAvatar" src="" alt="Ảnh đại diện" class="img-fluid rounded" style="max-height: 200px;">
                                <div id="noAvatar" class="text-muted mt-2" style="display: none;">Không có ảnh</div>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <strong>Mã Giảng Viên:</strong>
                                    <div id="viewTeacherCode" class="text-primary"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Họ và Tên:</strong>
                                    <div id="viewFullName"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Mã khoa:</strong>
                                    <div id="viewFaculty"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Chức vụ:</strong>
                                    <div id="viewPosition"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Số Điện Thoại:</strong>
                                    <div id="viewPhone"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Email:</strong>
                                    <div id="viewEmail"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Import Excel -->
    <div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importExcelModalLabel" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importExcelModalLabel">Import danh sách giảng viên từ Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="importExcelForm" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label for="excel_file" class="form-label">Chọn file Excel</label>
                            <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx,.xls" required>
                            <div class="form-text">Chỉ chấp nhận file Excel (.xlsx, .xls)</div>
                        </div>
                        <div class="mb-3">
                            <div class="form-text">
                                <strong>Lưu ý:</strong> File Excel cần có các cột sau:
                                <ul>
                                    <li>Mã Giảng Viên</li>
                                    <li>Họ và Tên</li>
                                    <li>Mã khoa</li>
                                    <li>Chức vụ</li>
                                    <li>Số Điện Thoại</li>
                                    <li>Email</li>
                                    <li>Link Ảnh (tuỳ chọn)</li>
                                </ul>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" id="btnImportExcel">Import</button>
                </div>
            </div>
        </div>
    </div>

   <link href="{{ asset('css/teacher_css.css') }}" rel="stylesheet">

    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap-select.min.js') }}"></script>   
    <script src="{{ asset('js/apexcharts/apexcharts.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>

    <script>
        // Biến phân trang
        let currentPage = 1;
        const itemsPerPage = 5;

        // Hàm phân trang
        function setupPagination(teachers = currentTeachers) {
            const totalPages = Math.ceil(teachers.length / itemsPerPage);
            const paginationContainer = document.getElementById('pagination-container');
            const paginationStart = document.getElementById('pagination-start');
            const paginationEnd = document.getElementById('pagination-end');
            const paginationTotal = document.getElementById('pagination-total');
            
            // Cập nhật thông tin hiển thị
            const startIndex = (currentPage - 1) * itemsPerPage + 1;
            const endIndex = Math.min(currentPage * itemsPerPage, teachers.length);
            
            paginationStart.textContent = startIndex;
            paginationEnd.textContent = endIndex;
            paginationTotal.textContent = teachers.length;
            
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
            attachPaginationEvents(teachers);
        }

        // Gắn sự kiện cho phân trang
        function attachPaginationEvents(teachers = currentTeachers) {
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
                        const paginatedTeachers = teachers.slice(startIndex, endIndex);
                        
                        listTeachers(paginatedTeachers);
                        setupPagination(teachers);
                    }
                });
            });
        }

        // Cập nhật hàm listTeachers để hỗ trợ phân trang
        function listTeachers(teachers = currentTeachers) {
            const tbody = document.getElementById('teachers-table-body');
            tbody.innerHTML = '';

            // Nếu không có teachers được truyền vào, sử dụng phân trang
            if (teachers === currentTeachers) {
                const startIndex = (currentPage - 1) * itemsPerPage;
                const endIndex = startIndex + itemsPerPage;
                teachers = currentTeachers.slice(startIndex, endIndex);
            }

            if (teachers.length > 0) {
                const globalIndex = (currentPage - 1) * itemsPerPage;
                
                teachers.forEach((teacher, index) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="text-center">${globalIndex + index + 1}</td>
                        <td>${teacher.teacher_code}</td>
                        <td>${teacher.full_name}</td>
                        <td>${teacher.faculty}</td>
                        <td>
                            <div class="list-icon-function">
                                <a href="#" class="view-teacher" data-id="${teacher.id}" 
                                   data-code="${teacher.teacher_code}" 
                                   data-name="${teacher.full_name}" 
                                   data-faculty="${teacher.faculty}" 
                                   data-position="${teacher.position}" 
                                   data-phone="${teacher.phone}" 
                                   data-email="${teacher.email}" 
                                   data-avatar="${teacher.avatar_url}" 
                                   data-bs-toggle="modal" data-bs-target="#viewTeacherModal">
                                    <div class="item view">
                                        <i class="icon-eye"></i>
                                    </div>
                                </a>
                                <a href="#" class="edit-teacher" data-id="${teacher.id}" 
                                   data-code="${teacher.teacher_code}" 
                                   data-name="${teacher.full_name}" 
                                   data-faculty="${teacher.faculty}" 
                                   data-position="${teacher.position}" 
                                   data-phone="${teacher.phone}" 
                                   data-email="${teacher.email}" 
                                   data-avatar="${teacher.avatar_url}" 
                                   data-bs-toggle="modal" data-bs-target="#editTeacherModal">
                                    <div class="item edit">
                                        <i class="icon-edit-3"></i>
                                    </div>
                                </a>
                                <a href="#" class="delete-teacher" data-id="${teacher.id}">
                                    <div class="item text-danger delete">
                                        <i class="icon-trash-2"></i>
                                    </div>
                                </a>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });

                attachEventListeners();
            } else {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center">Không có dữ liệu</td></tr>';
            }
        }

        // Cập nhật hàm tìm kiếm để reset về trang 1
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const searchValue = document.getElementById('searchInput').value.toLowerCase().trim();
            
            if (searchValue) {
                const filteredTeachers = currentTeachers.filter(teacher => 
                    teacher.teacher_code.toLowerCase().includes(searchValue) ||
                    teacher.full_name.toLowerCase().includes(searchValue) ||
                    teacher.faculty.toLowerCase().includes(searchValue) ||
                    teacher.position.toLowerCase().includes(searchValue) ||
                    teacher.phone.includes(searchValue) ||
                    teacher.email.toLowerCase().includes(searchValue)
                );
                currentPage = 1; // Reset về trang 1 khi tìm kiếm
                listTeachers(filteredTeachers);
                setupPagination(filteredTeachers);
            } else {
                currentPage = 1; // Reset về trang 1 khi xóa tìm kiếm
                listTeachers();
                setupPagination();
            }
        });

        // Cập nhật hàm reset tìm kiếm
        document.getElementById('searchInput').addEventListener('input', function() {
            if (this.value.trim() === '') {
                currentPage = 1; // Reset về trang 1 khi xóa tìm kiếm
                listTeachers();
                setupPagination();
            }
        });

        // Cập nhật các hàm thêm, sửa, xóa để refresh phân trang
        function refreshAfterDataChange() {
            currentPage = 1; // Reset về trang 1 sau khi thay đổi dữ liệu
            listTeachers();
            setupPagination();
        }

        // Dữ liệu mẫu để demo giao diện
        const sampleTeachers = [
            {
                id: 1,
                teacher_code: "GV001",
                full_name: "TS. Nguyễn Văn A",
                faculty: "CNTT",
                position: "Trưởng khoa",
                phone: "0123456789",
                email: "nguyenvana@example.com",
                avatar_url: "https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop&crop=face"
            },
            {
                id: 2,
                teacher_code: "GV002",
                full_name: "ThS. Trần Thị B",
                faculty: "KT",
                position: "Phó trưởng khoa",
                phone: "0987654321",
                email: "tranthib@example.com",
                avatar_url: "https://images.unsplash.com/photo-1494790108755-2616b612b786?w=150&h=150&fit=crop&crop=face"
            },
            {
                id: 3,
                teacher_code: "GV003",
                full_name: "TS. Lê Văn C",
                faculty: "QTKD",
                position: "Giảng viên chính",
                phone: "0369852147",
                email: "levanc@example.com",
                avatar_url: ""
            },
            {
                id: 4,
                teacher_code: "GV004",
                full_name: "ThS. Phạm Thị D",
                faculty: "CNTT",
                position: "Giảng viên",
                phone: "0912345678",
                email: "phamthid@example.com",
                avatar_url: "https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&h=150&fit=crop&crop=face"
            },
            {
                id: 5,
                teacher_code: "GV005",
                full_name: "TS. Hoàng Văn E",
                faculty: "KT",
                position: "Trưởng bộ môn",
                phone: "0945678123",
                email: "hoangvane@example.com",
                avatar_url: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&h=150&fit=crop&crop=face"
            }
        ];

        let currentTeachers = [...sampleTeachers];

        // Gắn sự kiện cho các nút
        function attachEventListeners() {
            // Gắn sự kiện cho nút delete
            document.querySelectorAll('.delete-teacher').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = parseInt(this.dataset.id);
                    if (confirm('Bạn có chắc chắn muốn xóa giảng viên này?')) {
                        deleteTeacher(id);
                    }
                });
            });

            // Gắn sự kiện cho nút edit
            document.querySelectorAll('.edit-teacher').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = parseInt(this.dataset.id);
                    const teacherCode = this.dataset.code;
                    const fullName = this.dataset.name;
                    const faculty = this.dataset.faculty;
                    const position = this.dataset.position;
                    const phone = this.dataset.phone;
                    const email = this.dataset.email;
                    const avatarUrl = this.dataset.avatar;
                    
                    document.getElementById('editTeacherId').value = id;
                    document.getElementById('editTeacherCode').value = teacherCode;
                    document.getElementById('editFullName').value = fullName;
                    document.getElementById('editFaculty').value = faculty;
                    document.getElementById('editPosition').value = position;
                    document.getElementById('editPhone').value = phone;
                    document.getElementById('editEmail').value = email;
                    document.getElementById('editAvatarUrl').value = avatarUrl || '';
                });
            });

            // Gắn sự kiện cho nút view
            document.querySelectorAll('.view-teacher').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = parseInt(this.dataset.id);
                    const teacherCode = this.dataset.code;
                    const fullName = this.dataset.name;
                    const faculty = this.dataset.faculty;
                    const position = this.dataset.position;
                    const phone = this.dataset.phone;
                    const email = this.dataset.email;
                    const avatarUrl = this.dataset.avatar;
                    
                    document.getElementById('viewTeacherCode').textContent = teacherCode;
                    document.getElementById('viewFullName').textContent = fullName;
                    document.getElementById('viewFaculty').textContent = faculty;
                    document.getElementById('viewPosition').textContent = position;
                    document.getElementById('viewPhone').textContent = phone;
                    document.getElementById('viewEmail').textContent = email;
                    
                    // Xử lý hiển thị ảnh
                    const avatarImg = document.getElementById('teacherAvatar');
                    const noAvatarDiv = document.getElementById('noAvatar');
                    
                    if (avatarUrl) {
                        avatarImg.src = avatarUrl;
                        avatarImg.style.display = 'block';
                        noAvatarDiv.style.display = 'none';
                    } else {
                        avatarImg.style.display = 'none';
                        noAvatarDiv.style.display = 'block';
                    }
                });
            });
        }

        // Thêm teacher mới
        document.getElementById('btnAddTeacher').addEventListener('click', function() {
            const teacherCode = document.getElementById('teacher_code').value;
            const fullName = document.getElementById('full_name').value;
            const faculty = document.getElementById('faculty').value;
            const position = document.getElementById('position').value;
            const phone = document.getElementById('phone').value;
            const email = document.getElementById('email').value;
            const avatarUrl = document.getElementById('avatar_url').value;

            if (!teacherCode || !fullName || !faculty || !position || !phone || !email) {
                alert('Vui lòng điền đầy đủ thông tin bắt buộc!');
                return;
            }

            // Tạo teacher mới
            const newTeacher = {
                id: Date.now(), // ID tạm thời
                teacher_code: teacherCode,
                full_name: fullName,
                faculty: faculty,
                position: position,
                phone: phone,
                email: email,
                avatar_url: avatarUrl || ''
            };

            // Thêm vào danh sách
            currentTeachers.push(newTeacher);

            // Đóng modal và reset form
            bootstrap.Modal.getInstance(document.getElementById('addTeacherModal')).hide();
            document.getElementById('addTeacherForm').reset();
            
            // Load lại dữ liệu
            refreshAfterDataChange();
            
            alert('Thêm giảng viên thành công! (DEMO)');
        });

        // Cập nhật teacher
        document.getElementById('btnEditTeacher').addEventListener('click', function() {
            const id = parseInt(document.getElementById('editTeacherId').value);
            const teacherCode = document.getElementById('editTeacherCode').value;
            const fullName = document.getElementById('editFullName').value;
            const faculty = document.getElementById('editFaculty').value;
            const position = document.getElementById('editPosition').value;
            const phone = document.getElementById('editPhone').value;
            const email = document.getElementById('editEmail').value;
            const avatarUrl = document.getElementById('editAvatarUrl').value;

            if (!teacherCode || !fullName || !faculty || !position || !phone || !email) {
                alert('Vui lòng điền đầy đủ thông tin bắt buộc!');
                return;
            }

            // Tìm và cập nhật teacher
            const teacherIndex = currentTeachers.findIndex(teacher => teacher.id === id);
            if (teacherIndex !== -1) {
                currentTeachers[teacherIndex] = {
                    ...currentTeachers[teacherIndex],
                    teacher_code: teacherCode,
                    full_name: fullName,
                    faculty: faculty,
                    position: position,
                    phone: phone,
                    email: email,
                    avatar_url: avatarUrl || ''
                };
            }

            // Đóng modal
            bootstrap.Modal.getInstance(document.getElementById('editTeacherModal')).hide();
            
            // Load lại dữ liệu
            refreshAfterDataChange();
            
            alert('Cập nhật giảng viên thành công! (DEMO)');
        });

        // Xóa teacher
        function deleteTeacher(id) {
            // Xóa khỏi danh sách
            currentTeachers = currentTeachers.filter(teacher => teacher.id !== id);
            
            // Load lại dữ liệu
            refreshAfterDataChange();
            
            alert('Xóa giảng viên thành công! (DEMO)');
        }

        // Import Excel
        document.getElementById('importExcelBtn').addEventListener('click', function() {
            const importModal = new bootstrap.Modal(document.getElementById('importExcelModal'));
            importModal.show();
        });

        document.getElementById('btnImportExcel').addEventListener('click', function() {
            const fileInput = document.getElementById('excel_file');
            const file = fileInput.files[0];

            if (!file) {
                alert('Vui lòng chọn file Excel!');
                return;
            }

            // Demo thêm 2 giảng viên từ file Excel
            const importedTeachers = [
                {
                    id: Date.now() + 1,
                    teacher_code: "GV006",
                    full_name: "TS. Đỗ Thị F",
                    faculty: "NN",
                    position: "Giảng viên",
                    phone: "0978123456",
                    email: "dothif@example.com",
                    avatar_url: ""
                },
                {
                    id: Date.now() + 2,
                    teacher_code: "GV007",
                    full_name: "ThS. Vũ Văn G",
                    faculty: "CNTT",
                    position: "Giảng viên",
                    phone: "0934567890",
                    email: "vuvang@example.com",
                    avatar_url: "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&h=150&fit=crop&crop=face"
                }
            ];

            // Thêm vào danh sách
            currentTeachers = [...currentTeachers, ...importedTeachers];

            // Đóng modal và reset form
            bootstrap.Modal.getInstance(document.getElementById('importExcelModal')).hide();
            document.getElementById('importExcelForm').reset();
            
            // Load lại dữ liệu
            refreshAfterDataChange();
            
            alert(`Import thành công ${importedTeachers.length} giảng viên từ file Excel! (DEMO)`);
        });

        // Reset tìm kiếm
        document.getElementById('searchInput').addEventListener('input', function() {
            if (this.value.trim() === '') {
                listTeachers();
            }
        });

        // Load dữ liệu khi trang được tải
        document.addEventListener('DOMContentLoaded', function() {
            listTeachers();
            setupPagination();
        });
    </script>
</body>
</html>