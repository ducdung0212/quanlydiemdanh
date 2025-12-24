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

    extractStudentCodes(rawInput) {
        if (!rawInput || typeof rawInput !== 'string') return [];

        const matches = rawInput.match(/[A-Za-z0-9_-]+/g) || [];
        const unique = [];
        const seen = new Set();

        for (const token of matches) {
            const code = token.trim();
            if (!code || !/\d/.test(code)) continue;
            const normalized = code.toUpperCase();
            if (seen.has(normalized)) continue;
            seen.add(normalized);
            unique.push(normalized);
        }

        return unique;
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

        // Import Excel button
        const importBtn = this.dom.modal?.querySelector('#btnImportStudentsExcel');
        if (importBtn) {
            importBtn.addEventListener('click', () => this.openImportModal());
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
        const rawInput = this.dom.searchInput?.value ?? '';
        const studentCodes = this.extractStudentCodes(rawInput);

        if (studentCodes.length === 0) {
            showToast('Lỗi', 'Vui lòng nhập mã sinh viên', 'danger');
            return;
        }

        const previousBtnDisabled = !!this.dom.addBtn?.disabled;
        const previousInputDisabled = !!this.dom.searchInput?.disabled;
        if (this.dom.addBtn) this.dom.addBtn.disabled = true;
        if (this.dom.searchInput) this.dom.searchInput.disabled = true;

        let addedCount = 0;
        const failed = [];

        try {
            for (const studentCode of studentCodes) {
                try {
                    const result = await apiFetch(`/api/exam-schedules/${this.scheduleId}/students`, {
                        method: 'POST',
                        body: JSON.stringify({ student_code: studentCode })
                    });

                    if (result && result.success) {
                        addedCount++;
                    } else {
                        failed.push(studentCode);
                    }
                } catch (e) {
                    failed.push(studentCode);
                }
            }

            if (this.dom.searchInput) {
                this.dom.searchInput.value = '';
            }

            if (addedCount > 0) {
                await this.loadStudents();
            }

            if (failed.length === 0) {
                const msg = studentCodes.length === 1
                    ? 'Đã thêm sinh viên vào ca thi'
                    : `Đã thêm ${addedCount}/${studentCodes.length} sinh viên`;
                showToast('Thành công', msg, 'success');
                return;
            }

            const previewFailed = failed.slice(0, 5).join(', ');
            const more = failed.length > 5 ? ` (+${failed.length - 5})` : '';
            showToast(
                'Hoàn tất',
                `Đã thêm ${addedCount}/${studentCodes.length}. Không thêm được: ${previewFailed}${more}`,
                addedCount > 0 ? 'warning' : 'danger'
            );
        } finally {
            if (this.dom.addBtn) this.dom.addBtn.disabled = previousBtnDisabled;
            if (this.dom.searchInput) this.dom.searchInput.disabled = previousInputDisabled;
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

    async openImportModal() {
        try {
            // Load modal import
            const importModal = await loadModal(
                `/exam-schedules/${this.scheduleId}/modals/import-students`,
                'importStudentsExcelModal'
            );

            if (importModal) {
                this.setupImportModalHandlers();
                importModal.show();
            }
        } catch (error) {
            showToast('Lỗi', 'Không thể tải modal import', 'danger');
        }
    }

    setupImportModalHandlers() {
        const form = document.getElementById('importStudentsExcelForm');
        const fileInput = document.getElementById('students_excel_file');
        const btnSubmit = document.getElementById('btnImportStudentsSubmit');
        const btnText = btnSubmit?.querySelector('.btn-text');
        const spinner = btnSubmit?.querySelector('.spinner-border');

        if (!form || !fileInput || !btnSubmit) return;

        // Reset form khi mở modal
        form.reset();
        document.getElementById('studentsHeadingsPreview')?.classList.add('d-none');
        document.getElementById('studentsMappingSection')?.classList.add('d-none');
        form.dataset.isPreviewMode = 'true';
        if (btnText) btnText.textContent = btnText.dataset.textPreview;

        // Xử lý khi chọn file mới
        fileInput.addEventListener('change', () => {
            document.getElementById('studentsHeadingsPreview')?.classList.add('d-none');
            document.getElementById('studentsMappingSection')?.classList.add('d-none');
            form.dataset.isPreviewMode = 'true';
            if (btnText) btnText.textContent = btnText.dataset.textPreview;
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const isPreviewMode = form.dataset.isPreviewMode === 'true';

            if (isPreviewMode) {
                // Bước 1: Preview - lấy tiêu đề cột
                await this.handlePreviewImport(form, fileInput, btnSubmit, spinner, btnText);
            } else {
                // Bước 2: Import thực tế
                await this.handleImportStudents(form, btnSubmit, spinner, btnText);
            }
        });
    }

    async handlePreviewImport(form, fileInput, btnSubmit, spinner, btnText) {
        const file = fileInput.files[0];
        if (!file) {
            showToast('Lỗi', 'Vui lòng chọn file Excel', 'danger');
            return;
        }

        const formData = new FormData();
        formData.append('excel_file', file);

        btnSubmit.disabled = true;
        spinner?.classList.remove('d-none');

        try {
            const response = await fetch(`/api/exam-schedules/${this.scheduleId}/students/import/preview`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            });

            const result = await response.json();

            if (result.success) {
                // Lưu token và heading row
                document.getElementById('import_students_token').value = result.token;
                document.getElementById('import_students_heading_row').value = result.heading_row;

                // Hiển thị các heading
                this.displayHeadings(result.headings);

                // Populate mapping selects
                this.populateMappingSelects(result.headings);

                // Hiển thị phần mapping
                document.getElementById('studentsMappingSection')?.classList.remove('d-none');

                // Đổi chế độ sang import
                if (btnText) btnText.textContent = btnText.dataset.textImport;

                // Đánh dấu là không còn preview mode
                form.dataset.isPreviewMode = 'false';

                showToast('Thành công', 'Đã tải tiêu đề cột. Vui lòng ghép cột với trường dữ liệu.', 'success');
            } else {
                showToast('Lỗi', result.message || 'Không thể đọc file', 'danger');
            }
        } catch (error) {
            showToast('Lỗi', error.message || 'Lỗi khi tải file', 'danger');
        } finally {
            btnSubmit.disabled = false;
            spinner?.classList.add('d-none');
        }
    }

    async handleImportStudents(form, btnSubmit, spinner, btnText) {
        const token = document.getElementById('import_students_token')?.value;
        const headingRow = document.getElementById('import_students_heading_row')?.value;

        if (!token) {
            showToast('Lỗi', 'Token không hợp lệ', 'danger');
            return;
        }

        // Thu thập mapping
        const mapping = {};
        const mappingSelects = document.querySelectorAll('.column-mapping-students');

        for (const select of mappingSelects) {
            const field = select.dataset.field;
            const required = select.dataset.required === 'true';
            const value = select.value;

            if (required && !value) {
                showToast('Lỗi', `Vui lòng chọn cột cho trường "${select.previousElementSibling?.textContent || field}"`, 'danger');
                return;
            }

            if (value) {
                mapping[field] = value;
            }
        }

        btnSubmit.disabled = true;
        spinner?.classList.remove('d-none');

        try {
            const result = await apiFetch(`/api/exam-schedules/${this.scheduleId}/students/import`, {
                method: 'POST',
                body: JSON.stringify({
                    token,
                    heading_row: parseInt(headingRow),
                    mapping
                })
            });

            if (result.success) {
                showToast('Thành công', result.message, 'success');

                // Hiển thị thông tin chi tiết nếu có
                if (result.data) {
                    const { added_count, skipped_count } = result.data;
                    console.log('Import result:', result.data);

                    if (skipped_count > 0 && result.data.skipped) {
                        console.warn('Skipped students:', result.data.skipped);
                    }
                }

                // Đóng modal import
                const importModalEl = document.getElementById('importStudentsExcelModal');
                const importModal = bootstrap.Modal.getInstance(importModalEl);
                if (importModal) {
                    importModal.hide();
                }

                // Reload danh sách sinh viên
                await this.loadStudents();
            } else {
                showToast('Lỗi', result.message || 'Import thất bại', 'danger');
            }
        } catch (error) {
            showToast('Lỗi', error.message || 'Lỗi khi import', 'danger');
        } finally {
            btnSubmit.disabled = false;
            spinner?.classList.add('d-none');
        }
    }

    displayHeadings(headings) {
        const headingsList = document.getElementById('studentsHeadingsList');
        const headingsPreview = document.getElementById('studentsHeadingsPreview');

        if (!headingsList || !headingsPreview) return;

        headingsList.innerHTML = headings.map(h =>
            `<span class="badge bg-secondary">${escapeHtml(h)}</span>`
        ).join('');

        headingsPreview.classList.remove('d-none');
    }

    populateMappingSelects(headings) {
        const selects = document.querySelectorAll('.column-mapping-students');

        selects.forEach(select => {
            // Clear existing options except first one
            while (select.options.length > 1) {
                select.remove(1);
            }

            // Add heading options
            headings.forEach(heading => {
                const option = document.createElement('option');
                option.value = this.normalizeColumnKey(heading);
                option.textContent = heading;
                select.appendChild(option);
            });

            // Auto-select matching column
            const field = select.dataset.field;
            const normalizedField = this.normalizeColumnKey(field);

            for (let i = 0; i < select.options.length; i++) {
                if (select.options[i].value === normalizedField) {
                    select.selectedIndex = i;
                    break;
                }
            }
        });
    }

    normalizeColumnKey(column) {
        if (!column) return '';
        return column
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '');
    }
}
