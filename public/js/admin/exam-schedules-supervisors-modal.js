/**
 * QUẢN LÝ MODAL GIÁM THỊ TRONG CA THI
 * Xử lý việc thêm/xóa giám thị vào ca thi
 */
class SupervisorModalManager {
    constructor() {
        this.modal = null;
        this.scheduleId = null;
        this.supervisors = [];
        this.dom = {};
    }

    async open(scheduleId) {
        this.scheduleId = scheduleId;

        try {
            this.modal = await loadModal(
                `/exam-schedules/${scheduleId}/modals/supervisors`,
                'manageSupervisorsModal'
            );

            if (this.modal) {
                this.initializeElements();
                this.setupEventListeners();
                await this.loadSupervisors();
                this.modal.show();
            }
        } catch (error) {
            showToast('Lỗi', 'Không thể tải modal quản lý giám thị', 'danger');
        }
    }

    initializeElements() {
        const modalElement = document.getElementById('manageSupervisorsModal');
        if (!modalElement) return;

        this.dom = {
            modal: modalElement,
            tableBody: modalElement.querySelector('#supervisors-list-body'),
            searchInput: modalElement.querySelector('#searchSupervisor'),
            addBtn: modalElement.querySelector('#btnAddSupervisor')
        };
    }

    setupEventListeners() {
        // Thêm giám thị
        if (this.dom.addBtn) {
            this.dom.addBtn.addEventListener('click', () => this.addSupervisor());
        }

        // Enter để thêm giám thị
        if (this.dom.searchInput) {
            this.dom.searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.addSupervisor();
                }
            });
        }

        // Xóa giám thị
        if (this.dom.modal) {
            this.dom.modal.addEventListener('click', async (e) => {
                const removeBtn = e.target.closest('[data-action="remove-supervisor"]');
                if (removeBtn) {
                    await this.removeSupervisor(removeBtn.dataset.supervisorId);
                }
            });
        }
    }

    async loadSupervisors() {
        try {
            const result = await apiFetch(`/api/exam-schedules/${this.scheduleId}/supervisors`);
            if (result.success && result.data) {
                this.supervisors = result.data;
                this.renderSupervisors();
            }
        } catch (error) {
            showToast('Lỗi', 'Không thể tải danh sách giám thị', 'danger');
            this.supervisors = [];
            this.renderSupervisors();
        }
    }

    renderSupervisors() {
        if (!this.dom.tableBody) return;

        if (!this.supervisors || this.supervisors.length === 0) {
            this.dom.tableBody.innerHTML = '<tr><td colspan="4" class="text-center">Chưa có giám thị nào</td></tr>';
            return;
        }

        this.dom.tableBody.innerHTML = this.supervisors.map((supervisor, index) => `
            <tr>
                <td class="text-center">${index + 1}</td>
                <td>${escapeHtml(supervisor.lecturer_code)}</td>
                <td>${escapeHtml(supervisor.full_name || '')}</td>
                <td class="text-center">
                    <button class="btn btn-lg btn-danger" 
                            data-action="remove-supervisor" 
                            data-supervisor-id="${supervisor.lecturerCode}"
                            title="Xóa giám thị">
                        <i class="icon-trash-2"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    async addSupervisor() {
        const lecturerCode = this.dom.searchInput?.value.trim();

        if (!lecturerCode) {
            showToast('Lỗi', 'Vui lòng nhập mã giảng viên', 'danger');
            return;
        }

        try {
            const result = await apiFetch(`/api/exam-schedules/${this.scheduleId}/supervisors`, {
                method: 'POST',
                body: JSON.stringify({ lecturer_code: lecturerCode })
            });

            if (result.success) {
                showToast('Thành công', result.message, 'success');
                if (this.dom.searchInput) {
                    this.dom.searchInput.value = '';
                }
                await this.loadSupervisors();
            } else {
                showToast('Lỗi', result.message, 'danger');
            }
        } catch (error) {
            showToast('Lỗi', error.message || 'Không thể thêm giám thị', 'danger');
        }
    }

    async removeSupervisor(supervisorId) {
        if (!confirm('Bạn có chắc muốn xóa giám thị này khỏi ca thi?')) {
            return;
        }

        try {
            const result = await apiFetch(
                `/api/exam-schedules/${this.scheduleId}/supervisors/${supervisorId}`,
                { method: 'DELETE' }
            );

            if (result.success) {
                showToast('Thành công', result.message, 'success');
                await this.loadSupervisors();
            } else {
                showToast('Lỗi', result.message, 'danger');
            }
        } catch (error) {
            showToast('Lỗi', error.message || 'Không thể xóa giám thị', 'danger');
        }
    }
}
