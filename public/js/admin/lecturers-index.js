document.addEventListener('DOMContentLoaded', function () {
    class LecturerManager extends BaseResourceManager {
        constructor() {
            super({
                apiBaseUrl: '/api/lecturers',
                resourceName: 'giảng viên',
                resourceIdKey: 'lecturer_code',
                bulkDeleteKey: 'lecturer_codes',
                tableBodyId: 'lecturers-table-body',
                checkboxClass: 'lecturer-checkbox',
                addTriggerSelector: '[data-bs-target="#addLecturerModal"]',
                importConfig: {
                    modalUrl: '/lecturers/modals/import',
                    previewUrl: '/api/lecturers/import/preview',
                    importUrl: '/api/lecturers/import',
                    validateMapping: (mapping) => {
                        if (!mapping.lecturer_code) throw new Error('Vui lòng chọn cột cho Mã giảng viên.');
                    }
                }
            });
        }

        /**
         * @override
         */
        renderTable() {
            const tb = this.dom.tableBody;
            const pd = this.state.paginationData;
            const q = this.state.currentQuery;
            if (!tb) return;

            if (!pd || !pd.data || pd.data.length === 0) {
                tb.innerHTML = `<tr><td colspan="6" class="text-center">${q ? 'Không tìm thấy giảng viên nào' : 'Không có dữ liệu'}</td></tr>`;
                return;
            }

            const { data: lecturers, from } = pd;
            const rowsHtml = lecturers.map((lecturer, index) => {
                const isChecked = this.state.selectedItems.has(lecturer.lecturer_code) ? 'checked' : '';
                return `
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" class="lecturer-checkbox" value="${escapeHtml(lecturer.lecturer_code)}" ${isChecked} style="cursor: pointer;" data-action="toggle-select">
                        </td>
                        <td class="text-center">${from + index}</td>
                        <td>${escapeHtml(lecturer.lecturer_code)}</td>
                        <td>${escapeHtml(lecturer.full_name || '')}</td>
                        <td>${escapeHtml(lecturer.faculty_code || '')}</td>
                        <td>
                            <div class="list-icon-function">
                                <a href="#" data-action="view-lecturer" data-id="${escapeHtml(lecturer.lecturer_code)}">
                                    <div class="item view"><i class="icon-eye"></i></div>
                                </a>
                                <a href="#" data-action="edit-lecturer" data-id="${escapeHtml(lecturer.lecturer_code)}">
                                    <div class="item edit"><i class="icon-edit-3"></i></div>
                                </a>
                                <a href="#" data-action="delete-lecturer" data-id="${escapeHtml(lecturer.lecturer_code)}">
                                    <div class="item text-danger delete"><i class="icon-trash-2"></i></div>
                                </a>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');

            tb.innerHTML = rowsHtml;
        }

        /**
         * @override
         */
        setupEventListeners() {
            super.setupEventListeners(); // Gắn sự kiện chung

            // Gắn sự kiện riêng cho table
            this.dom.tableBody.addEventListener('click', async (event) => {
                const target = event.target.closest('[data-action]');
                if (!target) return;
                
                const action = target.dataset.action;
                const id = target.dataset.id;

                switch (action) {
                    case 'view-lecturer':
                        event.preventDefault();
                        this.modals.view = await loadModal(`/lecturers/${encodeURIComponent(id)}/modals/view`, 'viewLecturerModal');
                        if (this.modals.view) this.modals.view.show();
                        break;
                    case 'edit-lecturer':
                        event.preventDefault();
                        this.modals.edit = await loadModal(`/lecturers/${encodeURIComponent(id)}/modals/edit`, 'editLecturerModal');
                        if (this.modals.edit) {
                            this.modals.edit.show();
                            this.initializeEditForm(this.modals.edit, id);
                        }
                        break;
                    case 'delete-lecturer':
                        event.preventDefault();
                        if (id) this.deleteItem(id);
                        break;
                    case 'toggle-select':
                        const checkbox = event.target.closest('.lecturer-checkbox');
                        if (checkbox) this.toggleSelection(checkbox.value, checkbox.checked);
                        break;
                }
            });

            // Gắn sự kiện cho nút "Thêm mới"
            if (this.dom.addTrigger) {
                this.dom.addTrigger.addEventListener('click', async (e) => {
                    e.preventDefault();
                    this.modals.add = await loadModal('/lecturers/modals/create', 'addLecturerModal');
                    if (this.modals.add) {
                        this.modals.add.show();
                        this.initializeAddForm(this.modals.add);
                    }
                });
            }
            
            document.addEventListener('resourceUpdated', (e) => {
                const isEdit = e.detail?.isEdit || false;
                this.fetchData(isEdit ? this.state.currentPage : 1, this.state.currentQuery);
            });
        }

        initializeAddForm(modalInstance) {
            const form = document.getElementById('addLecturerForm');
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

        initializeEditForm(modalInstance, id) {
            const form = document.getElementById('editLecturerForm');
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

    const manager = new LecturerManager();
    manager.init();
});