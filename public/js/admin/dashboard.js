$(document).ready(function () {
    let searchTimeout = null;
    const searchInput = $('#searchInput');
    const clearButton = $('#clearSearch');
    const searchLoading = $('#searchLoading');
    const searchResultInfo = $('#searchResultInfo');
    const container = $('#ongoingExamsContainer');

    // Load initial data
    loadExams();

    // Search with debounce (300ms)
    searchInput.on('input', function () {
        const value = $(this).val().trim();

        // Show/hide clear button
        clearButton.toggle(value.length > 0);

        // Clear previous timeout
        clearTimeout(searchTimeout);

        // Set new timeout
        searchTimeout = setTimeout(function () {
            loadExams(value);
        }, 300);
    });

    // Clear search
    clearButton.on('click', function () {
        searchInput.val('').focus();
        clearButton.hide();
        searchResultInfo.hide();
        loadExams();
    });

    // Enter key to search immediately
    searchInput.on('keypress', function (e) {
        if (e.which === 13) {
            clearTimeout(searchTimeout);
            loadExams($(this).val().trim());
        }
    });

    function loadExams(searchQuery = '') {
        // Show loading
        searchLoading.show();

        $.ajax({
            url: '/dashboard/stats',
            method: 'GET',
            data: { search: searchQuery },
            success: function (response) {
                searchLoading.hide();
                renderExams(response.ongoingExams, searchQuery);
                updateFaceStats(response.faceRegistrationStats);
                updateLastUpdateTime();
            },
            error: function (xhr, status, error) {
                searchLoading.hide();
                console.error('Error loading exams:', error);
                container.html(`
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="icon-alert-circle" style="font-size: 48px; color: #dc3545;"></i>
                        </div>
                        <p class="text-danger mb-2">Lỗi khi tải dữ liệu</p>
                        <button onclick="location.reload()" class="tf-button style-1">
                            <i class="icon-refresh-cw"></i> Tải lại
                        </button>
                    </div>
                `);
            }
        });
    }

    function renderExams(exams, searchQuery) {
        // Show search result info
        if (searchQuery) {
            searchResultInfo.find('.text-tiny').html(
                `Tìm thấy <strong>${exams.length}</strong> kết quả cho "<strong>${escapeHtml(searchQuery)}</strong>"`
            );
            searchResultInfo.show();
        } else {
            searchResultInfo.hide();
        }

        // Empty state
        if (exams.length === 0) {
            const emptyMessage = searchQuery
                ? `<p class="text-secondary mb-0">Không tìm thấy ca thi nào</p>
                   <p class="text-tiny text-secondary">Thử tìm kiếm với từ khóa khác</p>`
                : `<p class="text-secondary mb-0">Không có ca thi nào đang diễn ra</p>
                   <p class="text-tiny text-secondary">Các ca thi sẽ được hiển thị khi bắt đầu</p>`;

            container.html(`
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="icon-calendar" style="font-size: 48px; color: #ccc;"></i>
                    </div>
                    ${emptyMessage}
                </div>
            `);
            return;
        }

        // Render exam cards
        let html = '<div class="row">';
        exams.forEach(function (exam) {
            const attendanceRate = parseFloat(exam.attendance_rate);
            const progressColor = attendanceRate >= 80 ? 'bg-success' : (attendanceRate >= 50 ? 'bg-warning' : 'bg-danger');
            const rateColor = attendanceRate >= 80 ? 'text-success' : (attendanceRate >= 50 ? 'text-warning' : 'text-danger');
            const absentCount = exam.registered_count - exam.attended_count;

            html += `
                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 mb-20 fade-in">
                    <div class="card-exam">
                        <div class="card-exam-header">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h6 class="mb-1">${escapeHtml(exam.subject_name)}</h6>
                                    ${exam.subject_code ? `<span class="subject-code">${escapeHtml(exam.subject_code)}</span>` : ''}
                                </div>
                                <span class="badge badge-success">Đang diễn ra</span>
                            </div>
                        </div>
                        
                        <div class="card-exam-body">
                            <div class="exam-info-item">
                                <i class="icon-calendar"></i>
                                <div>
                                    <div class="text-tiny text-secondary">Ngày thi</div>
                                    <div class="fw-6">${exam.exam_date}</div>
                                </div>
                            </div>

                            <div class="exam-info-item">
                                <i class="icon-clock"></i>
                                <div>
                                    <div class="text-tiny text-secondary">Giờ thi</div>
                                    <div class="fw-6">${exam.exam_time}</div>
                                </div>
                            </div>

                            <div class="exam-info-item">
                                <i class="icon-map-pin"></i>
                                <div>
                                    <div class="text-tiny text-secondary">Phòng thi</div>
                                    <div class="fw-6">${escapeHtml(exam.room)}</div>
                                </div>
                            </div>

                            <div class="divider"></div>

                            <div class="attendance-stats">
                                <div class="stat-item">
                                    <div class="stat-value text-primary">${exam.registered_count}</div>
                                    <div class="stat-label">Số lượng</div>
                                </div>
                                <div class="stat-divider"></div>
                                <div class="stat-item">
                                    <div class="stat-value text-success">${exam.attended_count}</div>
                                    <div class="stat-label">Đã điểm danh</div>
                                </div>
                                <div class="stat-divider"></div>
                                <div class="stat-item">
                                    <div class="stat-value text-warning">${absentCount}</div>
                                    <div class="stat-label">Vắng mặt</div>
                                </div>
                            </div>

                            <div class="progress-container mt-3">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-tiny text-secondary">Tỷ lệ điểm danh</span>
                                    <span class="fw-6 ${rateColor}">${attendanceRate.toFixed(1)}%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar ${progressColor}" 
                                         role="progressbar" 
                                         style="width: ${attendanceRate}%" 
                                         aria-valuenow="${attendanceRate}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-exam-footer">
                            <a href="/exam-schedules/show/${exam.id}" class="tf-button style-1 w-100">
                                <i class="icon-eye"></i> Xem chi tiết điểm danh
                            </a>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';

        container.html(html);
    }

    function updateLastUpdateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' }) +
            ' - ' +
            now.toLocaleDateString('vi-VN');
        $('#lastUpdate').text(timeString);
    }

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

    // Face Registration Stats
    function updateFaceStats(stats) {
        if (!stats) return;

        $('#totalStudentsCount').text(stats.total.toLocaleString());
        $('#registeredCount').text(stats.registered.toLocaleString());
        $('#unregisteredCount').text(stats.unregistered.toLocaleString());
        $('#registeredPercentage').text(stats.registered_percentage + '%');
    }

    // Face Registration Modal
    const faceModal = new bootstrap.Modal(document.getElementById('faceRegistrationModal'));
    let currentFacePage = 1;
    let currentFaceStatus = 'all';
    let currentFaceSearch = '';

    $('#btnViewFaceDetails').on('click', function () {
        faceModal.show();
        loadFaceStudents();
    });

    $('#faceStudentSearch').on('input', debounce(function () {
        currentFaceSearch = $(this).val().trim();
        currentFacePage = 1;
        loadFaceStudents();
    }, 300));

    $('#faceStatusFilter').on('change', function () {
        currentFaceStatus = $(this).val();
        currentFacePage = 1;
        loadFaceStudents();
    });

    function loadFaceStudents() {
        const tbody = $('#faceStudentTableBody');
        tbody.html('<tr><td colspan="5" class="text-center"><div class="spinner-border text-primary"></div></td></tr>');

        $.ajax({
            url: '/dashboard/face-registration-students',
            method: 'GET',
            data: {
                status: currentFaceStatus,
                q: currentFaceSearch,
                limit: 20,
                page: currentFacePage
            },
            success: function (response) {
                if (response.success) {
                    renderFaceStudents(response.data, response.pagination);
                }
            },
            error: function () {
                tbody.html('<tr><td colspan="5" class="text-center text-danger">Lỗi khi tải dữ liệu</td></tr>');
            }
        });
    }

    function renderFaceStudents(students, pagination) {
        const tbody = $('#faceStudentTableBody');

        if (students.length === 0) {
            tbody.html('<tr><td colspan="6" class="text-center text-muted">Không có dữ liệu</td></tr>');
            return;
        }

        let html = '';
        students.forEach((student, index) => {
            const stt = (pagination.current_page - 1) * pagination.per_page + index + 1;
            const hasPhotos = student.photos && student.photos.length > 0;
            const statusBadge = hasPhotos
                ? '<span class="badge bg-success"><i class="icon-check-circle"></i> Đã đăng ký</span>'
                : '<span class="badge bg-danger"><i class="icon-x-circle"></i> Chưa đăng ký</span>';
            const photoCount = hasPhotos ? student.photos.length : 0;

            html += `
                <tr>
                    <td class="text-center">${stt}</td>
                    <td>${escapeHtml(student.student_code)}</td>
                    <td>${escapeHtml(student.full_name)}</td>
                    <td>${escapeHtml(student.class_code || '-')}</td>
                    <td class="text-center">${statusBadge}</td>
                </tr>
            `;
        });

        tbody.html(html);
        $('#faceStudentFrom').text(pagination.from || 0);
        $('#faceStudentTo').text(pagination.to || 0);
        $('#faceStudentTotal').text(pagination.total);
        renderFacePagination(pagination);
    }

    function renderFacePagination(pagination) {
        const container = $('#faceStudentPagination');

        if (pagination.last_page <= 1) {
            container.empty();
            return;
        }

        let html = '';

        html += `<li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${pagination.current_page - 1}">«</a>
        </li>`;
        for (let i = 1; i <= pagination.last_page; i++) {
            if (i === 1 || i === pagination.last_page ||
                (i >= pagination.current_page - 2 && i <= pagination.current_page + 2)) {
                html += `<li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>`;
            } else if (i === pagination.current_page - 3 || i === pagination.current_page + 3) {
                html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }

        // Next
        html += `<li class="page-item ${pagination.current_page === pagination.last_page ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${pagination.current_page + 1}">»</a>
        </li>`;

        container.html(html);

        // Bind click events
        container.find('a.page-link').on('click', function (e) {
            e.preventDefault();
            const page = parseInt($(this).data('page'));
            if (page && page !== currentFacePage) {
                currentFacePage = page;
                loadFaceStudents();
            }
        });
    }

    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // Auto refresh every 30 seconds
    setInterval(function () {
        if (!searchInput.val().trim()) {
            loadExams();
        }
    }, 30000);
});