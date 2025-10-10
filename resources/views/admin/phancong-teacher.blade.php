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
                                    <h3>Phân Công Giảng Viên</h3>
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
                                            <div class="text-tiny">Phân Công Giảng Viên</div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="wg-box">
                                    <div class="flex items-center justify-between gap10 flex-wrap">
                                        <div class="wg-filter flex-grow">
                                            <form class="form-search" id="searchForm">
                                                <fieldset class="name">
                                                    <input type="text" placeholder="Tìm kiếm phân công..." class="" name="name"
                                                        tabindex="2" value="" aria-required="true" id="searchInput">
                                                </fieldset>
                                                <div class="button-submit">
                                                    <button class="" type="submit"><i class="icon-search"></i></button>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="flex items-center gap10">
                                            <a class="tf-button style-1 w208" href="#" data-bs-toggle="modal" data-bs-target="#addAssignmentModal">
                                                <i class="icon-plus"></i>Phân công mới
                                            </a>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th style="width: 60px">STT</th>
                                                    <th>Mã Giảng Viên</th>
                                                    <th>Tên Giảng Viên</th>
                                                    <th>Lớp Phụ Trách</th>
                                                    <th>Môn Học</th>
                                                    <th>Học Kỳ</th>
                                                    <th>Năm Học</th>
                                                    <th style="width: 150px">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="assignments-table-body">
                                                <!-- Dữ liệu sẽ được load từ API -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="divider"></div>
                                    <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                                        <div class="text-tiny text-secondary">
                                            Hiển thị <span id="pagination-start">1</span>-<span id="pagination-end">5</span> của <span id="pagination-total">5</span> phân công
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

    <!-- Modal Phân công giảng viên -->
    <div class="modal fade" id="addAssignmentModal" tabindex="-1" aria-labelledby="addAssignmentModalLabel" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAssignmentModalLabel">Phân công giảng viên</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addAssignmentForm">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="teacher_id" class="form-label">Giảng Viên *</label>
                                <select class="form-control" id="teacher_id" name="teacher_id" required>
                                    <option value="">Chọn giảng viên</option>
                                    <!-- Options sẽ được load từ danh sách giảng viên -->
                                </select>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="class_id" class="form-label">Lớp Phụ Trách *</label>
                                <select class="form-control" id="class_id" name="class_id" required>
                                    <option value="">Chọn lớp</option>
                                    <!-- Options sẽ được load từ danh sách lớp -->
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="subject" class="form-label">Môn Học *</label>
                                <input type="text" class="form-control" id="subject" name="subject" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="semester" class="form-label">Học Kỳ *</label>
                                <select class="form-control" id="semester" name="semester" required>
                                    <option value="">Chọn học kỳ</option>
                                    <option value="1">Học kỳ 1</option>
                                    <option value="2">Học kỳ 2</option>
                                    <option value="3">Học kỳ Hè</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="school_year" class="form-label">Năm Học *</label>
                                <input type="text" class="form-control" id="school_year" name="school_year" placeholder="2024-2025" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="start_date" class="form-label">Ngày Bắt Đầu</label>
                                <input type="date" class="form-control" id="start_date" name="start_date">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="form-label">Ghi Chú</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Ghi chú về phân công..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" id="btnAddAssignment">Phân công</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Sửa phân công -->
    <div class="modal fade" id="editAssignmentModal" tabindex="-1" aria-labelledby="editAssignmentModalLabel" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAssignmentModalLabel">Sửa phân công giảng viên</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editAssignmentForm">
                        <input type="hidden" id="editAssignmentId">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="editTeacherId" class="form-label">Giảng Viên *</label>
                                <select class="form-control" id="editTeacherId" name="teacher_id" required>
                                    <option value="">Chọn giảng viên</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="editClassId" class="form-label">Lớp Phụ Trách *</label>
                                <select class="form-control" id="editClassId" name="class_id" required>
                                    <option value="">Chọn lớp</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="editSubject" class="form-label">Môn Học *</label>
                                <input type="text" class="form-control" id="editSubject" name="subject" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="editSemester" class="form-label">Học Kỳ *</label>
                                <select class="form-control" id="editSemester" name="semester" required>
                                    <option value="">Chọn học kỳ</option>
                                    <option value="1">Học kỳ 1</option>
                                    <option value="2">Học kỳ 2</option>
                                    <option value="3">Học kỳ Hè</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="editSchoolYear" class="form-label">Năm Học *</label>
                                <input type="text" class="form-control" id="editSchoolYear" name="school_year" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="editStartDate" class="form-label">Ngày Bắt Đầu</label>
                                <input type="date" class="form-control" id="editStartDate" name="start_date">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="editNotes" class="form-label">Ghi Chú</label>
                            <textarea class="form-control" id="editNotes" name="notes" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" id="btnEditAssignment">Lưu thay đổi</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Xem chi tiết phân công -->
    <div class="modal fade" id="viewAssignmentModal" tabindex="-1" aria-labelledby="viewAssignmentModalLabel" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewAssignmentModalLabel">Thông tin chi tiết phân công</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Mã Giảng Viên:</strong>
                            <div id="viewTeacherCode" class="text-primary"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Tên Giảng Viên:</strong>
                            <div id="viewTeacherName"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Lớp Phụ Trách:</strong>
                            <div id="viewClassName"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Môn Học:</strong>
                            <div id="viewSubject"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Học Kỳ:</strong>
                            <div id="viewSemester"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Năm Học:</strong>
                            <div id="viewSchoolYear"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Ngày Bắt Đầu:</strong>
                            <div id="viewStartDate"></div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <strong>Ghi Chú:</strong>
                            <div id="viewNotes" class="border p-2 rounded bg-light"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <link href="{{ asset('css/phancong_css.css') }}" rel="stylesheet">

    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap-select.min.js') }}"></script>   
    <script src="{{ asset('js/apexcharts/apexcharts.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>

    <script>
        // Biến phân trang
        let currentPage = 1;
        const itemsPerPage = 5;

        // Dữ liệu mẫu
        const sampleTeachers = [
            { id: 1, teacher_code: "GV001", full_name: "TS. Nguyễn Văn A" },
            { id: 2, teacher_code: "GV002", full_name: "ThS. Trần Thị B" },
            { id: 3, teacher_code: "GV003", full_name: "TS. Lê Văn C" },
            { id: 4, teacher_code: "GV004", full_name: "ThS. Phạm Thị D" },
            { id: 5, teacher_code: "GV005", full_name: "TS. Hoàng Văn E" }
        ];

        const sampleClasses = [
            { id: 1, class_name: "CNTT01", faculty: "CNTT" },
            { id: 2, class_name: "CNTT02", faculty: "CNTT" },
            { id: 3, class_name: "KT01", faculty: "KT" },
            { id: 4, class_name: "KT02", faculty: "KT" },
            { id: 5, class_name: "QTKD01", faculty: "QTKD" }
        ];

        const sampleAssignments = [
            {
                id: 1,
                teacher_id: 1,
                teacher_code: "GV001",
                teacher_name: "TS. Nguyễn Văn A",
                class_id: 1,
                class_name: "CNTT01",
                subject: "Lập trình Web",
                semester: "1",
                semester_name: "Học kỳ 1",
                school_year: "2024-2025",
                start_date: "2024-09-01",
                notes: "Phụ trách môn Lập trình Web cho lớp CNTT01"
            },
            {
                id: 2,
                teacher_id: 2,
                teacher_code: "GV002",
                teacher_name: "ThS. Trần Thị B",
                class_id: 3,
                class_name: "KT01",
                subject: "Kế toán tài chính",
                semester: "1",
                semester_name: "Học kỳ 1",
                school_year: "2024-2025",
                start_date: "2024-09-01",
                notes: ""
            },
            {
                id: 3,
                teacher_id: 3,
                teacher_code: "GV003",
                teacher_name: "TS. Lê Văn C",
                class_id: 2,
                class_name: "CNTT02",
                subject: "Cơ sở dữ liệu",
                semester: "2",
                semester_name: "Học kỳ 2",
                school_year: "2024-2025",
                start_date: "2025-02-01",
                notes: "Giảng dạy môn Cơ sở dữ liệu nâng cao"
            }
        ];

        let currentAssignments = [...sampleAssignments];

        // Hàm phân trang
        function setupPagination(assignments = currentAssignments) {
            const totalPages = Math.ceil(assignments.length / itemsPerPage);
            const paginationContainer = document.getElementById('pagination-container');
            const paginationStart = document.getElementById('pagination-start');
            const paginationEnd = document.getElementById('pagination-end');
            const paginationTotal = document.getElementById('pagination-total');
            
            // Cập nhật thông tin hiển thị
            const startIndex = (currentPage - 1) * itemsPerPage + 1;
            const endIndex = Math.min(currentPage * itemsPerPage, assignments.length);
            
            paginationStart.textContent = startIndex;
            paginationEnd.textContent = endIndex;
            paginationTotal.textContent = assignments.length;
            
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
            attachPaginationEvents(assignments);
        }

        // Gắn sự kiện cho phân trang
        function attachPaginationEvents(assignments = currentAssignments) {
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
                        const paginatedAssignments = assignments.slice(startIndex, endIndex);
                        
                        listAssignments(paginatedAssignments);
                        setupPagination(assignments);
                    }
                });
            });
        }

        // Hiển thị danh sách phân công
        function listAssignments(assignments = currentAssignments) {
            const tbody = document.getElementById('assignments-table-body');
            tbody.innerHTML = '';

            // Nếu không có assignments được truyền vào, sử dụng phân trang
            if (assignments === currentAssignments) {
                const startIndex = (currentPage - 1) * itemsPerPage;
                const endIndex = startIndex + itemsPerPage;
                assignments = currentAssignments.slice(startIndex, endIndex);
            }

            if (assignments.length > 0) {
                const globalIndex = (currentPage - 1) * itemsPerPage;
                
                assignments.forEach((assignment, index) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="text-center">${globalIndex + index + 1}</td>
                        <td>${assignment.teacher_code}</td>
                        <td>${assignment.teacher_name}</td>
                        <td>${assignment.class_name}</td>
                        <td>${assignment.subject}</td>
                        <td>${assignment.semester_name}</td>
                        <td>${assignment.school_year}</td>
                        <td>
                            <div class="list-icon-function">
                                <a href="#" class="view-assignment" data-id="${assignment.id}" 
                                   data-teacher-code="${assignment.teacher_code}" 
                                   data-teacher-name="${assignment.teacher_name}" 
                                   data-class-name="${assignment.class_name}" 
                                   data-subject="${assignment.subject}" 
                                   data-semester="${assignment.semester}" 
                                   data-semester-name="${assignment.semester_name}" 
                                   data-school-year="${assignment.school_year}" 
                                   data-start-date="${assignment.start_date}" 
                                   data-notes="${assignment.notes}" 
                                   data-bs-toggle="modal" data-bs-target="#viewAssignmentModal">
                                    <div class="item view">
                                        <i class="icon-eye"></i>
                                    </div>
                                </a>
                                <a href="#" class="edit-assignment" data-id="${assignment.id}" 
                                   data-teacher-id="${assignment.teacher_id}" 
                                   data-class-id="${assignment.class_id}" 
                                   data-subject="${assignment.subject}" 
                                   data-semester="${assignment.semester}" 
                                   data-school-year="${assignment.school_year}" 
                                   data-start-date="${assignment.start_date}" 
                                   data-notes="${assignment.notes}" 
                                   data-bs-toggle="modal" data-bs-target="#editAssignmentModal">
                                    <div class="item edit">
                                        <i class="icon-edit-3"></i>
                                    </div>
                                </a>
                                <a href="#" class="delete-assignment" data-id="${assignment.id}">
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
                tbody.innerHTML = '<tr><td colspan="8" class="text-center">Không có dữ liệu</td></tr>';
            }
        }

        // Tải danh sách giảng viên và lớp vào select
        function loadSelectOptions() {
            const teacherSelect = document.getElementById('teacher_id');
            const classSelect = document.getElementById('class_id');
            const editTeacherSelect = document.getElementById('editTeacherId');
            const editClassSelect = document.getElementById('editClassId');

            // Tải giảng viên
            sampleTeachers.forEach(teacher => {
                const option = document.createElement('option');
                option.value = teacher.id;
                option.textContent = `${teacher.teacher_code} - ${teacher.full_name}`;
                teacherSelect.appendChild(option);

                const editOption = option.cloneNode(true);
                editTeacherSelect.appendChild(editOption);
            });

            // Tải lớp
            sampleClasses.forEach(classItem => {
                const option = document.createElement('option');
                option.value = classItem.id;
                option.textContent = `${classItem.class_name} (${classItem.faculty})`;
                classSelect.appendChild(option);

                const editOption = option.cloneNode(true);
                editClassSelect.appendChild(editOption);
            });
        }

        // Gắn sự kiện cho các nút
        function attachEventListeners() {
            // Gắn sự kiện cho nút delete
            document.querySelectorAll('.delete-assignment').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = parseInt(this.dataset.id);
                    if (confirm('Bạn có chắc chắn muốn xóa phân công này?')) {
                        deleteAssignment(id);
                    }
                });
            });

            // Gắn sự kiện cho nút edit
            document.querySelectorAll('.edit-assignment').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = parseInt(this.dataset.id);
                    const teacherId = this.dataset.teacherId;
                    const classId = this.dataset.classId;
                    const subject = this.dataset.subject;
                    const semester = this.dataset.semester;
                    const schoolYear = this.dataset.schoolYear;
                    const startDate = this.dataset.startDate;
                    const notes = this.dataset.notes;
                    
                    document.getElementById('editAssignmentId').value = id;
                    document.getElementById('editTeacherId').value = teacherId;
                    document.getElementById('editClassId').value = classId;
                    document.getElementById('editSubject').value = subject;
                    document.getElementById('editSemester').value = semester;
                    document.getElementById('editSchoolYear').value = schoolYear;
                    document.getElementById('editStartDate').value = startDate;
                    document.getElementById('editNotes').value = notes || '';
                });
            });

            // Gắn sự kiện cho nút view
            document.querySelectorAll('.view-assignment').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const teacherCode = this.dataset.teacherCode;
                    const teacherName = this.dataset.teacherName;
                    const className = this.dataset.className;
                    const subject = this.dataset.subject;
                    const semesterName = this.dataset.semesterName;
                    const schoolYear = this.dataset.schoolYear;
                    const startDate = this.dataset.startDate;
                    const notes = this.dataset.notes;
                    
                    document.getElementById('viewTeacherCode').textContent = teacherCode;
                    document.getElementById('viewTeacherName').textContent = teacherName;
                    document.getElementById('viewClassName').textContent = className;
                    document.getElementById('viewSubject').textContent = subject;
                    document.getElementById('viewSemester').textContent = semesterName;
                    document.getElementById('viewSchoolYear').textContent = schoolYear;
                    document.getElementById('viewStartDate').textContent = startDate ? new Date(startDate).toLocaleDateString('vi-VN') : 'Chưa xác định';
                    document.getElementById('viewNotes').textContent = notes || 'Không có ghi chú';
                });
            });
        }

        // Thêm phân công mới
        document.getElementById('btnAddAssignment').addEventListener('click', function() {
            const teacherId = document.getElementById('teacher_id').value;
            const classId = document.getElementById('class_id').value;
            const subject = document.getElementById('subject').value;
            const semester = document.getElementById('semester').value;
            const schoolYear = document.getElementById('school_year').value;
            const startDate = document.getElementById('start_date').value;
            const notes = document.getElementById('notes').value;

            if (!teacherId || !classId || !subject || !semester || !schoolYear) {
                alert('Vui lòng điền đầy đủ thông tin bắt buộc!');
                return;
            }

            // Tìm thông tin giảng viên và lớp
            const teacher = sampleTeachers.find(t => t.id === parseInt(teacherId));
            const classItem = sampleClasses.find(c => c.id === parseInt(classId));
            const semesterName = semester === '1' ? 'Học kỳ 1' : semester === '2' ? 'Học kỳ 2' : 'Học kỳ Hè';

            // Tạo phân công mới
            const newAssignment = {
                id: Date.now(),
                teacher_id: parseInt(teacherId),
                teacher_code: teacher.teacher_code,
                teacher_name: teacher.full_name,
                class_id: parseInt(classId),
                class_name: classItem.class_name,
                subject: subject,
                semester: semester,
                semester_name: semesterName,
                school_year: schoolYear,
                start_date: startDate,
                notes: notes
            };

            // Thêm vào danh sách
            currentAssignments.push(newAssignment);

            // Đóng modal và reset form
            bootstrap.Modal.getInstance(document.getElementById('addAssignmentModal')).hide();
            document.getElementById('addAssignmentForm').reset();
            
            // Load lại dữ liệu
            refreshAfterDataChange();
            
            alert('Phân công giảng viên thành công! (DEMO)');
        });

        // Cập nhật phân công
        document.getElementById('btnEditAssignment').addEventListener('click', function() {
            const id = parseInt(document.getElementById('editAssignmentId').value);
            const teacherId = document.getElementById('editTeacherId').value;
            const classId = document.getElementById('editClassId').value;
            const subject = document.getElementById('editSubject').value;
            const semester = document.getElementById('editSemester').value;
            const schoolYear = document.getElementById('editSchoolYear').value;
            const startDate = document.getElementById('editStartDate').value;
            const notes = document.getElementById('editNotes').value;

            if (!teacherId || !classId || !subject || !semester || !schoolYear) {
                alert('Vui lòng điền đầy đủ thông tin bắt buộc!');
                return;
            }

            // Tìm thông tin giảng viên và lớp
            const teacher = sampleTeachers.find(t => t.id === parseInt(teacherId));
            const classItem = sampleClasses.find(c => c.id === parseInt(classId));
            const semesterName = semester === '1' ? 'Học kỳ 1' : semester === '2' ? 'Học kỳ 2' : 'Học kỳ Hè';

            // Tìm và cập nhật phân công
            const assignmentIndex = currentAssignments.findIndex(assignment => assignment.id === id);
            if (assignmentIndex !== -1) {
                currentAssignments[assignmentIndex] = {
                    ...currentAssignments[assignmentIndex],
                    teacher_id: parseInt(teacherId),
                    teacher_code: teacher.teacher_code,
                    teacher_name: teacher.full_name,
                    class_id: parseInt(classId),
                    class_name: classItem.class_name,
                    subject: subject,
                    semester: semester,
                    semester_name: semesterName,
                    school_year: schoolYear,
                    start_date: startDate,
                    notes: notes
                };
            }

            // Đóng modal
            bootstrap.Modal.getInstance(document.getElementById('editAssignmentModal')).hide();
            
            // Load lại dữ liệu
            refreshAfterDataChange();
            
            alert('Cập nhật phân công thành công! (DEMO)');
        });

        // Xóa phân công
        function deleteAssignment(id) {
            // Xóa khỏi danh sách
            currentAssignments = currentAssignments.filter(assignment => assignment.id !== id);
            
            // Load lại dữ liệu
            refreshAfterDataChange();
            
            alert('Xóa phân công thành công! (DEMO)');
        }

        // Cập nhật các hàm thêm, sửa, xóa để refresh phân trang
        function refreshAfterDataChange() {
            currentPage = 1; // Reset về trang 1 sau khi thay đổi dữ liệu
            listAssignments();
            setupPagination();
        }

        // Tìm kiếm
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const searchValue = document.getElementById('searchInput').value.toLowerCase().trim();
            
            if (searchValue) {
                const filteredAssignments = currentAssignments.filter(assignment => 
                    assignment.teacher_code.toLowerCase().includes(searchValue) ||
                    assignment.teacher_name.toLowerCase().includes(searchValue) ||
                    assignment.class_name.toLowerCase().includes(searchValue) ||
                    assignment.subject.toLowerCase().includes(searchValue) ||
                    assignment.school_year.includes(searchValue)
                );
                currentPage = 1; // Reset về trang 1 khi tìm kiếm
                listAssignments(filteredAssignments);
                setupPagination(filteredAssignments);
            } else {
                currentPage = 1; // Reset về trang 1 khi xóa tìm kiếm
                listAssignments();
                setupPagination();
            }
        });

        // Reset tìm kiếm
        document.getElementById('searchInput').addEventListener('input', function() {
            if (this.value.trim() === '') {
                currentPage = 1; // Reset về trang 1 khi xóa tìm kiếm
                listAssignments();
                setupPagination();
            }
        });

        // Load dữ liệu khi trang được tải
        document.addEventListener('DOMContentLoaded', function() {
            loadSelectOptions();
            listAssignments();
            setupPagination();
        });
    </script>
</body>
</html>