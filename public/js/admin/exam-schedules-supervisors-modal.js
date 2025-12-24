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
        this.lookupDebounceTimer = null;
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
            addBtn: modalElement.querySelector('#btnAddSupervisor'),
            lookupSection: modalElement.querySelector('#lecturerLookupSection'),
            lookupStatus: modalElement.querySelector('#lecturerLookupStatus'),
            lookupList: modalElement.querySelector('#lecturerLookupList')
        };
    }

    setupEventListeners() {
        // Enter để thêm giám thị
        if (this.dom.searchInput) {
            this.dom.searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.addSupervisor();
                }
            });

            this.dom.searchInput.addEventListener('input', (e) => {
                const q = (e.target?.value || '').trim();
                this.searchLecturersDebounced(q);
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

    searchLecturersDebounced(query) {
        if (this.lookupDebounceTimer) {
            clearTimeout(this.lookupDebounceTimer);
        }

        this.lookupDebounceTimer = setTimeout(() => {
            this.searchLecturers(query);
        }, 250);
    }

    async searchLecturers(query) {
        if (!this.dom.lookupSection || !this.dom.lookupList || !this.dom.lookupStatus) return;

        if (!query) {
            this.dom.lookupSection.classList.add('d-none');
            this.dom.lookupStatus.textContent = '';
            this.dom.lookupList.innerHTML = '';
            return;
        }

        this.dom.lookupSection.classList.remove('d-none');
        this.dom.lookupStatus.textContent = 'Đang tìm...';
        this.dom.lookupList.innerHTML = '';

        try {
            const result = await apiFetch(`/api/lecturers?q=${encodeURIComponent(query)}&limit=10`);
            const rows = result?.success ? (result?.data?.data || []) : [];

            if (!rows || rows.length === 0) {
                this.dom.lookupStatus.textContent = 'Không tìm thấy giảng viên phù hợp.';
                this.dom.lookupList.innerHTML = '';
                return;
            }

            this.dom.lookupStatus.textContent = `Tìm thấy ${rows.length} kết quả`;
            this.dom.lookupList.innerHTML = rows
                .map((lecturer) => {
                    const code = lecturer?.lecturer_code || '';
                    const name = lecturer?.full_name || '';
                    const email = lecturer?.email || '';

                    const title = [code, name].filter(Boolean).join(' - ');
                    const subtitle = email ? `Email: ${email}` : '';

                    return `
                        <button type="button" class="list-group-item list-group-item-action" data-action="pick-lecturer" data-lecturer-code="${escapeHtml(code)}">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold">${escapeHtml(title)}</div>
                                    ${subtitle ? `<div class="small text-muted">${escapeHtml(subtitle)}</div>` : ''}
                                </div>
                                <span class="badge bg-primary">Chọn</span>
                            </div>
                        </button>
                    `;
                })
                .join('');

            this.dom.lookupList.querySelectorAll('[data-action="pick-lecturer"]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const code = btn.dataset.lecturerCode || '';
                    this.addSupervisorByCode(code);
                });
            });
        } catch (error) {
            this.dom.lookupStatus.textContent = 'Không thể tra cứu danh sách giảng viên.';
            this.dom.lookupList.innerHTML = '';
        }
    }

    async addSupervisorByCode(lecturerCodeRaw) {
        const lecturerCode = (lecturerCodeRaw || '').trim();

        if (!lecturerCode) {
            showToast('Lỗi', 'Vui lòng nhập mã giảng viên', 'danger');
            return;
        }

        const previousInputDisabled = !!this.dom.searchInput?.disabled;
        if (this.dom.searchInput) this.dom.searchInput.disabled = true;

        try {
            const result = await apiFetch(`/api/exam-schedules/${this.scheduleId}/supervisors`, {
                method: 'POST',
                body: JSON.stringify({ lecturer_code: lecturerCode })
            });

            // Ẩn lookup list sau khi thao tác (dù thành công hay không)
            if (this.dom.lookupSection && this.dom.lookupStatus && this.dom.lookupList) {
                this.dom.lookupSection.classList.add('d-none');
                this.dom.lookupStatus.textContent = '';
                this.dom.lookupList.innerHTML = '';
            }

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
        } finally {
            if (this.dom.searchInput) this.dom.searchInput.disabled = previousInputDisabled;
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
            <tr class="align-middle">
                <td class="text-center align-middle">${index + 1}</td>
                <td class="text-center align-middle">${escapeHtml(supervisor.lecturer_code)}</td>
                <td class="align-middle">${escapeHtml(supervisor.full_name || '')}</td>
                <td class="text-center align-middle">
                    <button class="btn btn-lg btn-danger" 
                            data-action="remove-supervisor" 
                               data-supervisor-id="${escapeHtml(supervisor.lecturer_code)}"
                            title="Xóa giám thị">
                        <i class="icon-trash-2"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    async addSupervisor() {
        const lecturerCode = this.dom.searchInput?.value.trim();
        await this.addSupervisorByCode(lecturerCode);
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
