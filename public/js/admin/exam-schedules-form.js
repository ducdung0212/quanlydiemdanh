/**
 * QUẢN LÝ FORM THÊM/SỬA CA THI
 */
class ExamScheduleFormManager {
    constructor() {
        this.modal = null;
        this.form = null;
        this.isEditMode = false;
        this.currentId = null;
    }

    init() {
        const modalElement = document.getElementById('examScheduleFormModal');
        if (!modalElement) {
            console.error('Không tìm thấy modal examScheduleFormModal');
            return;
        }

        this.modal = new bootstrap.Modal(modalElement);
        this.form = document.getElementById('examScheduleForm');

        if (!this.form) {
            console.error('Không tìm thấy form examScheduleForm');
            return;
        }

        this.setupEventListeners();
        console.log('ExamScheduleFormManager initialized');
    }

    setupEventListeners() {
        // Submit form
        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleSubmit();
        });

        // Reset form khi đóng modal
        document.getElementById('examScheduleFormModal').addEventListener('hidden.bs.modal', () => {
            this.resetForm();
        });
    }

    openAddModal() {
        console.log('openAddModal called');
        this.isEditMode = false;
        this.currentId = null;
        document.getElementById('examScheduleFormModalLabel').textContent = 'Thêm Ca Thi';
        document.getElementById('btnSubmitExamSchedule').querySelector('.btn-text').textContent = 'Thêm mới';
        this.resetForm();
        this.modal.show();
    }

    async openEditModal(examScheduleId) {
        this.isEditMode = true;
        this.currentId = examScheduleId;
        document.getElementById('examScheduleFormModalLabel').textContent = 'Sửa Ca Thi';
        document.getElementById('btnSubmitExamSchedule').querySelector('.btn-text').textContent = 'Cập nhật';

        try {
            // Load dữ liệu ca thi
            const result = await apiFetch(`/api/exam-schedules/${examScheduleId}`);
            if (result.success && result.data) {
                this.fillForm(result.data.exam);
                this.modal.show();
            }
        } catch (error) {
            showToast('Lỗi', 'Không thể tải thông tin ca thi', 'danger');
        }
    }

    fillForm(data) {
        document.getElementById('examScheduleId').value = data.id || '';
        document.getElementById('subject_code').value = data.subject_code || '';
        document.getElementById('room').value = data.room || '';
        document.getElementById('exam_date').value = data.exam_date || '';

        // Format time từ HH:mm:ss hoặc HH:mm
        let timeValue = data.exam_time || '';
        if (timeValue && !timeValue.includes(':')) {
            timeValue = timeValue + ':00';
        } else if (timeValue && timeValue.split(':').length === 2) {
            timeValue = timeValue + ':00';
        }
        document.getElementById('exam_time').value = timeValue;

        document.getElementById('duration').value = data.duration || 90;
        document.getElementById('note').value = data.note || '';
    }

    async handleSubmit() {
        const submitBtn = document.getElementById('btnSubmitExamSchedule');
        const btnText = submitBtn.querySelector('.btn-text');
        const spinner = submitBtn.querySelector('.spinner-border');


        this.clearErrors();

        // Get form data
        let examTime = document.getElementById('exam_time').value;



        if (examTime && examTime.split(':').length === 2) {
            examTime = examTime + ':00';
        }


        const formData = {
            subject_code: document.getElementById('subject_code').value,
            exam_date: document.getElementById('exam_date').value,
            exam_time: examTime,
            duration: parseInt(document.getElementById('duration').value),
            room: document.getElementById('room').value,
            note: document.getElementById('note').value || null,
        };

        console.log('Form data to submit:', formData);
        submitBtn.disabled = true;
        btnText.classList.add('d-none');
        spinner.classList.remove('d-none');

        try {
            let result;
            if (this.isEditMode) {
                result = await apiFetch(`/api/exam-schedules/${this.currentId}`, {
                    method: 'PUT',
                    body: JSON.stringify(formData)
                });
            } else {
                result = await apiFetch('/api/exam-schedules', {
                    method: 'POST',
                    body: JSON.stringify(formData)
                });
            }

            if (result.success) {
                showToast('Thành công', result.message, 'success');
                this.modal.hide();
                document.dispatchEvent(new CustomEvent('examSchedulesUpdated'));
            } else {
                if (result.errors) {
                    this.showErrors(result.errors);
                } else {
                    showToast('Lỗi', result.message || 'Có lỗi xảy ra', 'danger');
                }
            }
        } catch (error) {
            console.error('Error submitting form:', error);
            if (error.errors) {
                this.showErrors(error.errors);
            } else {
                showToast('Lỗi', error.message || 'Không thể lưu ca thi', 'danger');
            }
        } finally {
            submitBtn.disabled = false;
            btnText.classList.remove('d-none');
            spinner.classList.add('d-none');
        }
    }

    showErrors(errors) {
        Object.keys(errors).forEach(field => {
            const input = document.getElementById(field);
            const errorDiv = document.getElementById(`error_${field}`);

            if (input && errorDiv) {
                input.classList.add('is-invalid');
                errorDiv.textContent = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
            }
        });
    }

    clearErrors() {
        const inputs = this.form.querySelectorAll('.form-control, .form-select');
        inputs.forEach(input => {
            input.classList.remove('is-invalid');
        });

        const errorDivs = this.form.querySelectorAll('.invalid-feedback');
        errorDivs.forEach(div => {
            div.textContent = '';
        });
    }

    resetForm() {
        this.form.reset();
        this.clearErrors();
        this.currentId = null;
        this.isEditMode = false;
        document.getElementById('examScheduleId').value = '';
        document.getElementById('duration').value = 90;
    }
}

// Export để sử dụng trong exam-schedules-index.js
window.ExamScheduleFormManager = ExamScheduleFormManager;
