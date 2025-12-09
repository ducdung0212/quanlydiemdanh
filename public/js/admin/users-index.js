document.addEventListener('DOMContentLoaded', () => {
    class UserManager extends BaseResourceManager {
        constructor() {
            super({
                apiBaseUrl: '/api/users',
                resourceName: 'tài khoản',
                resourceIdKey: 'id',
                bulkDeleteKey: 'user_ids',
                tableBodyId: 'users-table-body',
                checkboxClass: 'user-checkbox',
                addTriggerSelector: '[data-bs-target="#addUserModal"]',
                importConfig: null // Users không có import
            });
            
            this.escape = window.escapeHtml || ((value) => value);
        }

        /**
         * @override
         */
        renderTable() {
            const tb = this.dom.tableBody;
            const pd = this.state.paginationData;
            const q = this.state.currentQuery;
            if (!tb) return;

            if (!pd || !Array.isArray(pd.data) || pd.data.length === 0) {
                tb.innerHTML = `<tr><td colspan="6" class="text-center">${q ? 'Không tìm thấy tài khoản nào' : 'Không có dữ liệu'}</td></tr>`;
                return;
            }

            const { data: users, from } = pd;
            const rowsHtml = users.map((user, index) => {
                const isChecked = this.state.selectedItems.has(user.id.toString()) ? 'checked' : '';
                const roleLabel = user.role ? user.role.charAt(0).toUpperCase() + user.role.slice(1) : 'N/A';
                return `
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" class="user-checkbox" value="${user.id}" ${isChecked} style="cursor: pointer;" data-action="toggle-select">
                        </td>
                        <td class="text-center">${from + index}</td> <td>${this.escape(user.name || '')}</td>
                        <td>${this.escape(user.email || '')}</td>
                        <td>${this.escape(roleLabel)}</td>
                        <td>
                            <div class="list-icon-function">
                                <a href="#" data-action="edit-user" data-id="${user.id}">
                                    <div class="item edit"><i class="icon-edit-3"></i></div>
                                </a>
                                <a href="#" data-action="delete-user" data-id="${user.id}">
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
                    case 'edit-user':
                        event.preventDefault();
                        this.modals.edit = await loadModal(`/users/modals/edit/${id}`, 'editUserModal');
                        if (this.modals.edit) {
                            this.modals.edit.show();
                            this.initializeEditForm(this.modals.edit, id);
                        }
                        break;
                    case 'delete-user':
                        event.preventDefault();
                        if (id) this.deleteItem(id);
                        break;
                    case 'toggle-select':
                        const checkbox = event.target.closest('.user-checkbox');
                        if (checkbox) this.toggleSelection(checkbox.value, checkbox.checked);
                        break;
                }
            });

            // Gắn sự kiện cho nút "Thêm mới"
            if (this.dom.addTrigger) {
                this.dom.addTrigger.addEventListener('click', async (e) => {
                    e.preventDefault();
                    this.modals.add = await loadModal('/users/modals/create', 'addUserModal');
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
            const form = document.getElementById('addUserForm');
            if (!form || form.dataset.initialized === 'true') return;

            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleFormSubmit(form, async () => {
                    const formData = Object.fromEntries(new FormData(form).entries());
                    return await apiFetch(this.options.apiBaseUrl, { method: 'POST', body: formData });
                }, modalInstance, 'resourceUpdated', null)
                .then((res) => {
                    if (res && res.success) {
                        showToast('Thành công', res.message || 'Đã thêm', 'success');
                        form.reset();
                    }
                }).catch(() => {});
            });
            
            const modalElement = modalInstance._element || modalInstance;
            modalElement.addEventListener('hidden.bs.modal', () => {
                form.reset();
                clearValidationErrors(form);
            });

            form.dataset.initialized = 'true';
        }

        initializeEditForm(modalInstance, id) {
            const form = document.getElementById('editUserForm');
            if (!form || form.dataset.initialized === 'true') return;

            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleFormSubmit(form, async () => {
                    const formEntries = Object.fromEntries(new FormData(form).entries());
                    // Xử lý logic password riêng
                    if (!formEntries.password && !formEntries.password_confirmation) {
                        delete formEntries.password;
                        delete formEntries.password_confirmation;
                    }
                    return await apiFetch(`${this.options.apiBaseUrl}/${id}`, { method: 'PUT', body: formEntries });
                }, modalInstance, 'resourceUpdated', { isEdit: true })
                .then((res) => {
                    if (res && res.success) showToast('Thành công', res.message || 'Đã cập nhật', 'success');
                }).catch(() => {});
            });
            
            const modalElement = modalInstance._element || modalInstance;
            modalElement.addEventListener('hidden.bs.modal', () => {
                clearValidationErrors(form);
            });

            form.dataset.initialized = 'true';
        }
    }

    const manager = new UserManager();
    manager.init();
});