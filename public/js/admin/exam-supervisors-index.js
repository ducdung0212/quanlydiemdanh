document.addEventListener('DOMContentLoaded', function () {
    class ExamSupervisorManager extends BaseResourceManager {
        constructor() {
            super({
                apiBaseUrl: '/api/exam-supervisors',
                resourceName: 'giám thị',
                resourceIdKey: 'id',
                // The API expects an `ids` array in the request body for bulk delete
                bulkDeleteKey: 'ids',
                tableBodyId: 'exam-supervisors-table-body',
                checkboxClass: 'supervisor-checkbox',
                addTriggerSelector: null, // Không có nút thêm
                importConfig: {
                    modalUrl: '/exam-supervisors/modals/import',
                    previewUrl: '/api/exam-supervisors/import/preview',
                    importUrl: '/api/exam-supervisors/import',
                    validateMapping: null // Không cần validation đặc biệt
                }
            });
        }

        // --- Helpers ---
        formatDate(dateString) {
            try {
                const date = new Date(dateString);
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = date.getFullYear();
                return `${day}/${month}/${year}`;
            } catch (e) { return dateString; }
        }
        formatTime(timeString) {
            try {
                if (timeString.includes(':')) {
                    const parts = timeString.split(':');
                    return `${parts[0]}:${parts[1]}`;
                }
                return timeString;
            } catch (e) { return timeString; }
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
                tb.innerHTML = `<tr><td colspan="10" class="text-center">${q ? 'Không tìm thấy giám thị nào' : 'Không có dữ liệu'}</td></tr>`;
                return;
            }

            const { data: supervisors, from } = pd;
            const rowsHtml = supervisors.map((supervisor, index) => {
                const isChecked = this.state.selectedItems.has(supervisor.id.toString()) ? 'checked' : '';
                
                const examSchedule = supervisor.exam_schedule || {};
                const subject = examSchedule.subject || {};
                const subjectName = subject.name || subject.subject_name || 'N/A';
                const examDate = examSchedule.exam_date ? this.formatDate(examSchedule.exam_date) : 'N/A';
                const examTime = examSchedule.exam_time ? this.formatTime(examSchedule.exam_time) : 'N/A';
                const room = examSchedule.room || 'N/A';
                
                return `
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" class="supervisor-checkbox" value="${escapeHtml(supervisor.id)}" ${isChecked} style="cursor: pointer;" data-action="toggle-select">
                        </td>
                        <td class="text-center">${from + index}</td>
                        <td>${escapeHtml(supervisor.exam_schedule_id || '')}</td>
                        <td>${escapeHtml(subjectName)}</td>
                        <td>${examDate}</td>
                        <td>${examTime}</td>
                        <td>${escapeHtml(room)}</td>
                        <td>${escapeHtml(supervisor.lecturer_code || '')}</td>
                        <td>${escapeHtml(supervisor.lecturer_name || '')}</td>
                        <td>
                            <div class="list-icon-function">
                                <a href="#" data-action="edit-supervisor" data-id="${escapeHtml(supervisor.id)}" title="Sửa">
                                    <div class="item text-primary me-2"><i class="icon-edit-2"></i></div>
                                </a>
                                <a href="#" data-action="delete-supervisor" data-id="${escapeHtml(supervisor.id)}" title="Xóa">
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
                    case 'edit-supervisor':
                        event.preventDefault();
                        if (id) {
                            this.modals.edit = await loadModal(`/exam-supervisors/modals/edit/${id}`, 'editSupervisorModal');
                            if (!this.modals.edit) return;
                            this.modals.edit.show();
                            this.initializeEditForm(this.modals.edit, id);
                        }
                        break;
                    case 'delete-supervisor':
                        event.preventDefault();
                        if (id) this.deleteItem(id);
                        break;
                    case 'toggle-select':
                        const checkbox = event.target.closest('.supervisor-checkbox');
                        if (checkbox) this.toggleSelection(checkbox.value, checkbox.checked);
                        break;
                }
            });
            
            document.addEventListener('resourceUpdated', (e) => {
                const isEdit = e.detail?.isEdit || false;
                this.fetchData(isEdit ? this.state.currentPage : 1, this.state.currentQuery);
            });
        }

        initializeEditForm(modalInstance, id) {
            const form = document.getElementById('editSupervisorForm');
            if (!form || form.dataset.initialized === 'true') return;

            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleFormSubmit(form, async () => {
                    const lecturerSelect = document.getElementById('editLecturerCode');
                    const body = { lecturer_code: lecturerSelect.value };
                    return await apiFetch(`${this.options.apiBaseUrl}/${id}`, {
                        method: 'PUT',
                        body: JSON.stringify(body),
                    });
                }, modalInstance, 'resourceUpdated', { isEdit: true })
                .then((res) => {
                    if (res && res.success) showToast('Thành công', res.message || 'Cập nhật thành công', 'success');
                }).catch(() => {});
            });

            form.dataset.initialized = 'true';
        }
    }

    const manager = new ExamSupervisorManager();
    manager.init();
});