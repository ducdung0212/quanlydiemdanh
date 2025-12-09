document.addEventListener('DOMContentLoaded', function () {
    class SubjectManager extends BaseResourceManager {
        constructor() {
            super({
                apiBaseUrl: '/api/subjects',
                resourceName: 'môn học',
                resourceIdKey: 'subject_code',
                bulkDeleteKey: 'subject_codes',
                tableBodyId: 'subjects-table-body',
                checkboxClass: 'subject-checkbox',
                addTriggerSelector: '[data-bs-target="#addSubjectModal"]',
                importConfig: {
                    modalUrl: '/subjects/modals/import',
                    previewUrl: '/api/subjects/import/preview',
                    importUrl: '/api/subjects/import',
                    validateMapping: (mapping) => {
                        if (!mapping.subject_code) throw new Error('Vui lòng chọn cột cho Mã môn học.');
                        if (!mapping.name) throw new Error('Vui lòng chọn cột cho Tên môn học.');
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
                tb.innerHTML = `<tr><td colspan="6" class="text-center">${q ? 'Không tìm thấy môn học nào' : 'Không có dữ liệu'}</td></tr>`;
                return;
            }

            const { data: subjects, from } = pd;
            const rowsHtml = subjects.map((subject, index) => {
                const isChecked = this.state.selectedItems.has(subject.subject_code) ? 'checked' : '';
                return `
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" class="subject-checkbox" value="${escapeHtml(subject.subject_code)}" ${isChecked} style="cursor: pointer;" data-action="toggle-select">
                        </td>
                        <td class="text-center">${from + index}</td>
                        <td>${escapeHtml(subject.subject_code)}</td>
                        <td>${escapeHtml(subject.name)}</td>
                        <td class="text-center">${subject.credit || ''}</td>
                        <td>
                            <div class="list-icon-function">
                                <a href="#" data-action="edit-subject" data-id="${escapeHtml(subject.subject_code)}">
                                    <div class="item edit"><i class="icon-edit-3"></i></div>
                                </a>
                                <a href="#" data-action="delete-subject" data-id="${escapeHtml(subject.subject_code)}">
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
            super.setupEventListeners(); // Gắn sự kiện chung (search, import, pagination...)

            // Gắn sự kiện riêng cho table
            this.dom.tableBody.addEventListener('click', async (event) => {
                const target = event.target.closest('[data-action]');
                if (!target) return;
                
                const action = target.dataset.action;
                const id = target.dataset.id; // Dùng 'id' chung

                switch (action) {
                    case 'edit-subject':
                        event.preventDefault();
                        this.modals.edit = await loadModal(`/subjects/modals/edit/${encodeURIComponent(id)}`, 'editSubjectModal');
                        if (this.modals.edit) {
                            this.modals.edit.show();
                            this.initializeEditForm(this.modals.edit, id);
                        }
                        break;
                    case 'delete-subject':
                        event.preventDefault();
                        if (id) this.deleteItem(id); // Gọi hàm của lớp cha
                        break;
                    case 'toggle-select':
                        const checkbox = event.target.closest('.subject-checkbox');
                        if (checkbox) this.toggleSelection(checkbox.value, checkbox.checked); // Gọi hàm của lớp cha
                        break;
                }
            });

            // Gắn sự kiện cho nút "Thêm mới"
            if (this.dom.addTrigger) {
                this.dom.addTrigger.addEventListener('click', async (e) => {
                    e.preventDefault();
                    this.modals.add = await loadModal('/subjects/modals/create', 'addSubjectModal');
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
            const form = document.getElementById('addSubjectForm');
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
            const form = document.getElementById('editSubjectForm');
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

    const manager = new SubjectManager();
    manager.init(); // Gọi init từ lớp cha
});