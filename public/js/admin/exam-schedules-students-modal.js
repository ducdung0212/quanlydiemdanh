/**
 * QUẢN LÝ MODAL SINH VIÊN TRONG CA THI
 * Xử lý việc thêm/xóa sinh viên vào ca thi với phân trang 10 records/page
 */
class StudentModalManager {
    constructor() {
        this.modal = null;
        this.scheduleId = null;
        this.students = [];
        this.currentPage = 1;
        this.perPage = 10;
        this.dom = {};
    }

    async open(scheduleId) {
        this.scheduleId = scheduleId;

        try {
            this.modal = await loadModal(
                `/exam-schedules/${scheduleId}/modals/students`,
                'manageStudentsModal'
            );

            if (this.modal) {
                this.initializeElements();
                this.setupEventListeners();
                await this.loadStudents();
                this.modal.show();
            }
        } catch (error) {
            showToast('Lỗi', 'Không thể tải modal quản lý sinh viên', 'danger');
        }
    }

    initializeElements() {
        const modalElement = document.getElementById('manageStudentsModal');
        if (!modalElement) return;

        this.dom = {
            modal: modalElement,
            tableBody: modalElement.querySelector('#students-list-body'),
            searchInput: modalElement.querySelector('#searchStudent'),
            addBtn: modalElement.querySelector('#btnAddStudent'),
            pagination: modalElement.querySelector('#students-pagination')
        };
    }

    setupEventListeners() {
        // Thêm sinh viên
        if (this.dom.addBtn) {
            this.dom.addBtn.addEventListener('click', () => this.addStudent());
        }

        // Enter để thêm sinh viên
        if (this.dom.searchInput) {
            this.dom.searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.addStudent();
                }
            });
        }

        // Xóa sinh viên
        if (this.dom.modal) {
            this.dom.modal.addEventListener('click', async (e) => {
                const removeBtn = e.target.closest('[data-action="remove-student"]');
                if (removeBtn) {
                    await this.removeStudent(removeBtn.dataset.studentCode);
                }
            });
        }
    }

    async loadStudents() {
        try {
            const result = await apiFetch(`/api/exam-schedules/${this.scheduleId}/students`);
            if (result.success && result.data) {
                this.students = result.data;
                this.currentPage = 1; // Reset về trang 1
                this.renderStudents();
            }
        } catch (error) {
            showToast('Lỗi', 'Không thể tải danh sách sinh viên', 'danger');
            this.students = [];
            this.renderStudents();
        }
    }

    renderStudents() {
        if (!this.dom.tableBody) return;

        if (!this.students || this.students.length === 0) {
            this.dom.tableBody.innerHTML = '<tr><td colspan="5" class="text-center">Chưa có sinh viên nào</td></tr>';
            this.renderPagination();
            return;
        }

        // Phân trang
        const start = (this.currentPage - 1) * this.perPage;
        const end = start + this.perPage;
        const pageData = this.students.slice(start, end);

        this.dom.tableBody.innerHTML = pageData.map((student, index) => `
            <tr>
                <td class="text-center">${start + index + 1}</td>
                <td>${escapeHtml(student.student_code)}</td>
                <td>${escapeHtml(student.full_name || '')}</td>
                <td>${escapeHtml(student.class_code || '')}</td>
                <td class="text-center">
                   <button class="btn btn-lg btn-danger" 
                            data-action="remove-student" 
                            data-student-code="${escapeHtml(student.student_code)}"
                            title="Xóa sinh viên">
                        <i class="icon-trash-2"></i>
                    </button>
                </td>
            </tr>
        `).join('');

        this.renderPagination();
    }

    renderPagination() {
        if (!this.dom.pagination) return;

        const totalPages = Math.ceil(this.students.length / this.perPage);

        if (totalPages <= 1) {
            this.dom.pagination.innerHTML = '';
            return;
        }

        this.dom.pagination.innerHTML = `
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item ${this.currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="prev">&lt;</a>
                    </li>
                    ${Array.from({ length: totalPages }, (_, i) => i + 1).map(page => `
                        <li class="page-item ${page === this.currentPage ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${page}">${page}</a>
                        </li>
                    `).join('')}
                    <li class="page-item ${this.currentPage === totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="next">&gt;</a>
                    </li>
                </ul>
            </nav>
        `;

        // Event listeners cho pagination
        this.dom.pagination.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = e.target.dataset.page;

                if (page === 'prev' && this.currentPage > 1) {
                    this.currentPage--;
                } else if (page === 'next' && this.currentPage < totalPages) {
                    this.currentPage++;
                } else if (page && page !== 'prev' && page !== 'next') {
                    this.currentPage = parseInt(page);
                }

                this.renderStudents();
            });
        });
    }

    async addStudent() {
        const studentCode = this.dom.searchInput?.value.trim();

        if (!studentCode) {
            showToast('Lỗi', 'Vui lòng nhập mã sinh viên', 'danger');
            return;
        }

        try {
            const result = await apiFetch(`/api/exam-schedules/${this.scheduleId}/students`, {
                method: 'POST',
                body: JSON.stringify({ student_code: studentCode })
            });

            if (result.success) {
                showToast('Thành công', result.message, 'success');
                if (this.dom.searchInput) {
                    this.dom.searchInput.value = '';
                }
                await this.loadStudents();
            } else {
                showToast('Lỗi', result.message, 'danger');
            }
        } catch (error) {
            showToast('Lỗi', error.message || 'Không thể thêm sinh viên', 'danger');
        }
    }

    async removeStudent(studentCode) {
        if (!confirm('Bạn có chắc muốn xóa sinh viên này khỏi ca thi?')) {
            return;
        }

        try {
            const result = await apiFetch(
                `/api/exam-schedules/${this.scheduleId}/students/${studentCode}`,
                { method: 'DELETE' }
            );

            if (result.success) {
                showToast('Thành công', result.message, 'success');
                await this.loadStudents();
            } else {
                showToast('Lỗi', result.message, 'danger');
            }
        } catch (error) {
            showToast('Lỗi', error.message || 'Không thể xóa sinh viên', 'danger');
        }
    }
}
