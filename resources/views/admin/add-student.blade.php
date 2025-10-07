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
                                    <h3>Quản lý Sinh Viên</h3>
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
                                            <div class="text-tiny">Quản lý Sinh Viên</div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="wg-box">
                                    <div class="flex items-center justify-between gap10 flex-wrap">
                                        <div class="wg-filter flex-grow">
                                            <form class="form-search" id="searchForm">
                                                <fieldset class="name">
                                                    <input type="text" placeholder="Tìm kiếm sinh viên..." class="" name="name"
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
                                            <a class="tf-button style-1 w208" href="#" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                                                <i class="icon-plus"></i>Thêm mới
                                            </a>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th style="width: 60px">STT</th>
                                                    <th>Mã SV</th>
                                                    <th>Tên</th>
                                                    <th>Lớp</th>
                                                    <th>Khoa</th>
                                                    <th>SĐT</th>
                                                    <th>Giới tính</th>
                                                    <th>Link Ảnh</th>
                                                    <th style="width: 120px">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="students-table-body">
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

    <!-- Modal Thêm sinh viên -->
    <div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStudentModalLabel">Thêm sinh viên mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addStudentForm">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="student_code" class="form-label">Mã Sinh Viên *</label>
                                <input type="text" class="form-control" id="student_code" name="student_code" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="full_name" class="form-label">Họ và Tên *</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="class" class="form-label">Lớp *</label>
                                <input type="text" class="form-control" id="class" name="class" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="faculty" class="form-label">Khoa *</label>
                                <input type="text" class="form-control" id="faculty" name="faculty" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="phone" class="form-label">Số Điện Thoại *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="gender" class="form-label">Giới Tính *</label>
                                <select class="form-control" id="gender" name="gender" required>
                                    <option value="">Chọn giới tính</option>
                                    <option value="Nam">Nam</option>
                                    <option value="Nữ">Nữ</option>
                                    <option value="Khác">Khác</option>
                                </select>
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
                    <button type="button" class="btn btn-primary" id="btnAddStudent">Thêm</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Sửa sinh viên -->
    <div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editStudentModalLabel">Sửa thông tin sinh viên</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editStudentForm">
                        <input type="hidden" id="editStudentId">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="editStudentCode" class="form-label">Mã Sinh Viên *</label>
                                <input type="text" class="form-control" id="editStudentCode" name="student_code" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="editFullName" class="form-label">Họ và Tên *</label>
                                <input type="text" class="form-control" id="editFullName" name="full_name" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="editClass" class="form-label">Lớp *</label>
                                <input type="text" class="form-control" id="editClass" name="class" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="editFaculty" class="form-label">Khoa *</label>
                                <input type="text" class="form-control" id="editFaculty" name="faculty" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="editPhone" class="form-label">Số Điện Thoại *</label>
                                <input type="tel" class="form-control" id="editPhone" name="phone" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="editGender" class="form-label">Giới Tính *</label>
                                <select class="form-control" id="editGender" name="gender" required>
                                    <option value="Nam">Nam</option>
                                    <option value="Nữ">Nữ</option>
                                    <option value="Khác">Khác</option>
                                </select>
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
                    <button type="button" class="btn btn-primary" id="btnEditStudent">Lưu thay đổi</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Import Excel -->
    <div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importExcelModalLabel" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importExcelModalLabel">Import danh sách sinh viên từ Excel</h5>
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
                                    <li>Mã Sinh Viên</li>
                                    <li>Họ và Tên</li>
                                    <li>Lớp</li>
                                    <li>Khoa</li>
                                    <li>Số Điện Thoại</li>
                                    <li>Giới Tính</li>
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

   <link href="{{ asset('css/student_css.css') }}" rel="stylesheet">

    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap-select.min.js') }}"></script>   
    <script src="{{ asset('js/apexcharts/apexcharts.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>

    <script>
        // Dữ liệu mẫu để demo giao diện
        const sampleStudents = [
            {
                id: 1,
                student_code: "SV001",
                full_name: "Nguyễn Văn A",
                class: "CNTT01",
                faculty: "Công nghệ Thông tin",
                phone: "0123456789",
                gender: "Nam",
                avatar_url: "https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop&crop=face"
            },
            {
                id: 2,
                student_code: "SV002",
                full_name: "Trần Thị B",
                class: "KT02",
                faculty: "Kế Toán",
                phone: "0987654321",
                gender: "Nữ",
                avatar_url: "https://images.unsplash.com/photo-1494790108755-2616b612b786?w=150&h=150&fit=crop&crop=face"
            },
            {
                id: 3,
                student_code: "SV003",
                full_name: "Lê Văn C",
                class: "QT03",
                faculty: "Quản Trị Kinh Doanh",
                phone: "0369852147",
                gender: "Nam",
                avatar_url: ""
            },
            {
                id: 4,
                student_code: "SV004",
                full_name: "Phạm Thị D",
                class: "CNTT01",
                faculty: "Công nghệ Thông tin",
                phone: "0912345678",
                gender: "Nữ",
                avatar_url: "https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&h=150&fit=crop&crop=face"
            },
            {
                id: 5,
                student_code: "SV005",
                full_name: "Hoàng Văn E",
                class: "KT02",
                faculty: "Kế Toán",
                phone: "0945678123",
                gender: "Nam",
                avatar_url: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&h=150&fit=crop&crop=face"
            }
        ];

        let currentStudents = [...sampleStudents];

        // Load danh sách students
        function listStudents(students = currentStudents) {
            const tbody = document.getElementById('students-table-body');
            tbody.innerHTML = '';

            if (students.length > 0) {
                students.forEach((student, index) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="text-center">${index + 1}</td>
                        <td>${student.student_code}</td>
                        <td>${student.full_name}</td>
                        <td>${student.class}</td>
                        <td>${student.faculty}</td>
                        <td>${student.phone}</td>
                        <td>${student.gender}</td>
                        <td>
                            ${student.avatar_url ? 
                                `<a href="${student.avatar_url}" target="_blank" class="avatar-link">Xem ảnh</a>` : 
                                '<span class="text-muted">Không có</span>'
                            }
                        </td>
                        <td>
                            <div class="list-icon-function">
                                <a href="#" class="edit-student" data-id="${student.id}" 
                                   data-code="${student.student_code}" 
                                   data-name="${student.full_name}" 
                                   data-class="${student.class}" 
                                   data-faculty="${student.faculty}" 
                                   data-phone="${student.phone}" 
                                   data-gender="${student.gender}" 
                                   data-avatar="${student.avatar_url}" 
                                   data-bs-toggle="modal" data-bs-target="#editStudentModal">
                                    <div class="item edit">
                                        <i class="icon-edit-3"></i>
                                    </div>
                                </a>
                                <a href="#" class="delete-student" data-id="${student.id}">
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
                tbody.innerHTML = '<tr><td colspan="9" class="text-center">Không có dữ liệu</td></tr>';
            }
        }

        // Gắn sự kiện cho các nút
        function attachEventListeners() {
            // Gắn sự kiện cho nút delete
            document.querySelectorAll('.delete-student').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = parseInt(this.dataset.id);
                    if (confirm('Bạn có chắc chắn muốn xóa sinh viên này?')) {
                        deleteStudent(id);
                    }
                });
            });

            // Gắn sự kiện cho nút edit
            document.querySelectorAll('.edit-student').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = parseInt(this.dataset.id);
                    const studentCode = this.dataset.code;
                    const fullName = this.dataset.name;
                    const className = this.dataset.class;
                    const faculty = this.dataset.faculty;
                    const phone = this.dataset.phone;
                    const gender = this.dataset.gender;
                    const avatarUrl = this.dataset.avatar;
                    
                    document.getElementById('editStudentId').value = id;
                    document.getElementById('editStudentCode').value = studentCode;
                    document.getElementById('editFullName').value = fullName;
                    document.getElementById('editClass').value = className;
                    document.getElementById('editFaculty').value = faculty;
                    document.getElementById('editPhone').value = phone;
                    document.getElementById('editGender').value = gender;
                    document.getElementById('editAvatarUrl').value = avatarUrl || '';
                });
            });
        }

        // Thêm student mới
        document.getElementById('btnAddStudent').addEventListener('click', function() {
            const studentCode = document.getElementById('student_code').value;
            const fullName = document.getElementById('full_name').value;
            const className = document.getElementById('class').value;
            const faculty = document.getElementById('faculty').value;
            const phone = document.getElementById('phone').value;
            const gender = document.getElementById('gender').value;
            const avatarUrl = document.getElementById('avatar_url').value;

            if (!studentCode || !fullName || !className || !faculty || !phone || !gender) {
                alert('Vui lòng điền đầy đủ thông tin bắt buộc!');
                return;
            }

            // Tạo student mới
            const newStudent = {
                id: Date.now(), // ID tạm thời
                student_code: studentCode,
                full_name: fullName,
                class: className,
                faculty: faculty,
                phone: phone,
                gender: gender,
                avatar_url: avatarUrl || ''
            };

            // Thêm vào danh sách
            currentStudents.push(newStudent);

            // Đóng modal và reset form
            bootstrap.Modal.getInstance(document.getElementById('addStudentModal')).hide();
            document.getElementById('addStudentForm').reset();
            
            // Load lại dữ liệu
            listStudents();
            
            alert('Thêm sinh viên thành công! (DEMO)');
        });

        // Cập nhật student
        document.getElementById('btnEditStudent').addEventListener('click', function() {
            const id = parseInt(document.getElementById('editStudentId').value);
            const studentCode = document.getElementById('editStudentCode').value;
            const fullName = document.getElementById('editFullName').value;
            const className = document.getElementById('editClass').value;
            const faculty = document.getElementById('editFaculty').value;
            const phone = document.getElementById('editPhone').value;
            const gender = document.getElementById('editGender').value;
            const avatarUrl = document.getElementById('editAvatarUrl').value;

            if (!studentCode || !fullName || !className || !faculty || !phone || !gender) {
                alert('Vui lòng điền đầy đủ thông tin bắt buộc!');
                return;
            }

            // Tìm và cập nhật student
            const studentIndex = currentStudents.findIndex(student => student.id === id);
            if (studentIndex !== -1) {
                currentStudents[studentIndex] = {
                    ...currentStudents[studentIndex],
                    student_code: studentCode,
                    full_name: fullName,
                    class: className,
                    faculty: faculty,
                    phone: phone,
                    gender: gender,
                    avatar_url: avatarUrl || ''
                };
            }

            // Đóng modal
            bootstrap.Modal.getInstance(document.getElementById('editStudentModal')).hide();
            
            // Load lại dữ liệu
            listStudents();
            
            alert('Cập nhật sinh viên thành công! (DEMO)');
        });

        // Xóa student
        function deleteStudent(id) {
            // Xóa khỏi danh sách
            currentStudents = currentStudents.filter(student => student.id !== id);
            
            // Load lại dữ liệu
            listStudents();
            
            alert('Xóa sinh viên thành công! (DEMO)');
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

            // Demo thêm 2 sinh viên từ file Excel
            const importedStudents = [
                {
                    id: Date.now() + 1,
                    student_code: "SV006",
                    full_name: "Đỗ Thị F",
                    class: "NN01",
                    faculty: "Ngoại Ngữ",
                    phone: "0978123456",
                    gender: "Nữ",
                    avatar_url: ""
                },
                {
                    id: Date.now() + 2,
                    student_code: "SV007",
                    full_name: "Vũ Văn G",
                    class: "CNTT02",
                    faculty: "Công nghệ Thông tin",
                    phone: "0934567890",
                    gender: "Nam",
                    avatar_url: "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&h=150&fit=crop&crop=face"
                }
            ];

            // Thêm vào danh sách
            currentStudents = [...currentStudents, ...importedStudents];

            // Đóng modal và reset form
            bootstrap.Modal.getInstance(document.getElementById('importExcelModal')).hide();
            document.getElementById('importExcelForm').reset();
            
            // Load lại dữ liệu
            listStudents();
            
            alert(`Import thành công ${importedStudents.length} sinh viên từ file Excel! (DEMO)`);
        });

        // Tìm kiếm
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const searchValue = document.getElementById('searchInput').value.toLowerCase().trim();
            
            if (searchValue) {
                const filteredStudents = currentStudents.filter(student => 
                    student.student_code.toLowerCase().includes(searchValue) ||
                    student.full_name.toLowerCase().includes(searchValue) ||
                    student.class.toLowerCase().includes(searchValue) ||
                    student.faculty.toLowerCase().includes(searchValue) ||
                    student.phone.includes(searchValue)
                );
                listStudents(filteredStudents);
            } else {
                listStudents();
            }
        });

        // Reset tìm kiếm
        document.getElementById('searchInput').addEventListener('input', function() {
            if (this.value.trim() === '') {
                listStudents();
            }
        });

        // Load dữ liệu khi trang được tải
        document.addEventListener('DOMContentLoaded', function() {
            listStudents();
        });
    </script>
</body>
</html>