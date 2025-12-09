document.addEventListener('DOMContentLoaded', function () {
    class StudentManager extends BaseResourceManager {
        constructor() {
            // 1. Cấu hình các thông tin riêng của Student
            super({
                apiBaseUrl: '/api/students',
                resourceName: 'sinh viên',
                resourceIdKey: 'student_code',
                bulkDeleteKey: 'student_codes',
                tableBodyId: 'students-table-body',
                checkboxClass: 'student-checkbox',
                modalPrefix: 'Student', // dùng cho data-bs-target="#addStudentModal"
                importConfig: {
                    modalUrl: '/students/modals/import',
                    previewUrl: '/api/students/import/preview',
                    importUrl: '/api/students/import',
                    requiredField: 'student_code' // Validation
                }
            });
        }

        /**
         * 2. (BẮT BUỘC) Định nghĩa cách render bảng
         */
        renderTable() {
            const tb = this.dom.tableBody;
            const pd = this.state.paginationData;
            const q = this.state.currentQuery;
            if (!tb) return;

            if (!pd || !pd.data || pd.data.length === 0) {
                tb.innerHTML = `<tr><td colspan="6" class="text-center">${q ? 'Không tìm thấy sinh viên nào' : 'Không có dữ liệu'}</td></tr>`;
                return;
            }

            const { data: students, from } = pd;
            const rowsHtml = students.map((student, index) => {
                const isChecked = this.state.selectedItems.has(student.student_code) ? 'checked' : '';
                return `
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" class="student-checkbox" value="${escapeHtml(student.student_code)}" ${isChecked} style="cursor: pointer;" data-action="toggle-select">
                        </td>
                        <td class="text-center">${from + index}</td>
                        <td>${escapeHtml(student.student_code)}</td>
                        <td>${escapeHtml(student.full_name)}</td>
                        <td>${escapeHtml(student.class_code || '')}</td>
                        <td>
                            <div class="list-icon-function">
                                <a href="#" data-action="view-student" data-id="${escapeHtml(student.student_code)}"><div class="item view"><i class="icon-eye"></i></div></a>
                                <a href="#" data-action="edit-student" data-id="${escapeHtml(student.student_code)}"><div class="item edit"><i class="icon-edit-3"></i></div></a>
                                <a href="#" data-action="delete-student" data-id="${escapeHtml(student.student_code)}"><div class="item text-danger delete"><i class="icon-trash-2"></i></div></a>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
            tb.innerHTML = rowsHtml;
        }

        /**
         * 3. (BẮT BUỘC) Gắn thêm các sự kiện riêng
         */
        setupEventListeners() {
            super.setupEventListeners(); // Gọi hàm của lớp cha (để gắn sự kiện search, pagination, import...)

            // Gắn sự kiện click riêng cho table body (view, edit, delete)
            this.dom.tableBody.addEventListener('click', async (event) => {
                const target = event.target.closest('[data-action]');
                if (!target) return;
                
                const action = target.dataset.action;
                const id = target.dataset.id; // Dùng 'id' chung

                switch (action) {
                    case 'view-student':
                        event.preventDefault();
                        this.modals.view = await loadModal(`/students/modals/view/${id}`, 'viewStudentModal');
                        if (this.modals.view) this.modals.view.show();
                        break;
                    case 'edit-student':
                        event.preventDefault();
                        this.modals.edit = await loadModal(`/students/modals/edit/${encodeURIComponent(id)}`, 'editStudentModal');
                        if (this.modals.edit) {
                            this.modals.edit.show();
                            this.initializeEditForm(this.modals.edit, id);
                        }
                        break;
                    case 'delete-student':
                        event.preventDefault();
                        if (id) this.deleteItem(id); // Gọi hàm của lớp cha
                        break;
                    case 'toggle-select':
                        const checkbox = event.target.closest('.student-checkbox');
                        if (checkbox) this.toggleSelection(checkbox.value, checkbox.checked); // Gọi hàm của lớp cha
                        break;
                }
            });

            // Gắn sự kiện cho nút "Thêm mới"
            if (this.dom.addTrigger) {
                this.dom.addTrigger.addEventListener('click', async (e) => {
                    e.preventDefault();
                    this.modals.add = await loadModal('/students/modals/create', 'addStudentModal');
                    if (this.modals.add) {
                        this.modals.add.show();
                        this.initializeAddForm(this.modals.add);
                    }
                });
            }
            
            // Lắng nghe sự kiện tùy chỉnh để reload
            document.addEventListener('resourceUpdated', (e) => {
                const isEdit = e.detail?.isEdit || false;
                this.fetchData(isEdit ? this.state.currentPage : 1, this.state.currentQuery);
            });
        }

        /**
         * 4. (BẮT BUỘC) Định nghĩa logic cho form Thêm
         */
        initializeAddForm(modalInstance) {
            const form = document.getElementById('addStudentForm');
            if (!form || form.dataset.initialized === 'true') return;

            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleFormSubmit(form, async () => {
                    const formData = new FormData(form);
                    return await apiFetch(this.options.apiBaseUrl, { method: 'POST', body: formData });
                }, modalInstance, 'resourceUpdated', null)
                .then((res) => {
                    if (res && res.success) showToast('Thành công', res.message || 'Đã thêm', 'success');
                }).catch(() => {});
            });
            form.dataset.initialized = 'true';
        }

        /**
         * 5. (BẮT BUỘC) Định nghĩa logic cho form Sửa
         */
        initializeEditForm(modalInstance, id) {
            const form = document.getElementById('editStudentForm');
            if (!form || form.dataset.initialized === 'true') return;

            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleFormSubmit(form, async () => {
                    const formData = new FormData(form);
                    formData.append('_method', 'PUT');
                    return await apiFetch(`${this.options.apiBaseUrl}/${encodeURIComponent(id)}`, { method: 'POST', body: formData });
                }, modalInstance, 'resourceUpdated', { isEdit: true })
                .then((res) => {
                    if (res && res.success) showToast('Thành công', res.message || 'Đã cập nhật', 'success');
                }).catch(() => {});
            });
            form.dataset.initialized = 'true';
        }
    }

    // Khởi tạo
    const manager = new StudentManager();
    manager.init(); // Gọi init từ lớp cha
});