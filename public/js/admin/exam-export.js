// Export functionality for exam schedules

/**
 * Export một ca thi đơn lẻ
 */
export function exportSingleExam(examScheduleId) {
    if (!examScheduleId) {
        swal('Lỗi', 'Không tìm thấy thông tin ca thi', 'error');
        return;
    }

    // Sử dụng route web đã có sẵn
    window.location.href = `/exam-schedules/${examScheduleId}/export`;
}

/**
 * Export nhiều ca thi đã chọn
 */
export async function exportMultipleExams(examScheduleIds) {
    if (!examScheduleIds || examScheduleIds.length === 0) {
        swal('Thông báo', 'Vui lòng chọn ít nhất một ca thi để export', 'warning');
        return;
    }

    // Hiển thị loading
    swal({
        title: 'Đang xuất file...',
        text: `Đang xuất ${examScheduleIds.length} ca thi`,
        icon: 'info',
        buttons: false,
        closeOnClickOutside: false,
        closeOnEsc: false
    });

    try {
        const response = await fetch('/api/exam-schedules/export/multiple', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                exam_schedule_ids: examScheduleIds
            })
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || 'Lỗi khi xuất file');
        }

        // Download file
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;

        // Lấy tên file từ header hoặc tạo tên mặc định
        const contentDisposition = response.headers.get('content-disposition');
        let fileName = 'Danh_Sach_Ca_Thi.xlsx';
        if (contentDisposition) {
            const fileNameMatch = contentDisposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
            if (fileNameMatch && fileNameMatch[1]) {
                fileName = fileNameMatch[1].replace(/['"]/g, '');
            }
        }

        a.download = fileName;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);

        swal('Thành công', `Đã xuất ${examScheduleIds.length} ca thi`, 'success');

    } catch (error) {
        console.error('Export error:', error);
        swal('Lỗi', 'Có lỗi xảy ra khi xuất file: ' + error.message, 'error');
    }
}

/**
 * Export tất cả ca thi trong một ngày
 */
export async function exportExamsByDate(date) {
    if (!date) {
        swal('Thông báo', 'Vui lòng chọn ngày để export', 'warning');
        return;
    }

    // Hiển thị loading
    swal({
        title: 'Đang xuất file...',
        text: 'Đang xuất danh sách ca thi trong ngày',
        icon: 'info',
        buttons: false,
        closeOnClickOutside: false,
        closeOnEsc: false
    });

    try {
        const response = await fetch('/api/exam-schedules/export/by-date', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                date: date
            })
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || 'Lỗi khi xuất file');
        }

        // Download file
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;

        const contentDisposition = response.headers.get('content-disposition');
        let fileName = `Ca_Thi_${date}.xlsx`;
        if (contentDisposition) {
            const fileNameMatch = contentDisposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
            if (fileNameMatch && fileNameMatch[1]) {
                fileName = fileNameMatch[1].replace(/['"]/g, '');
            }
        }

        a.download = fileName;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);

        swal('Thành công', 'Đã xuất danh sách ca thi', 'success');

    } catch (error) {
        console.error('Export error:', error);
        swal('Lỗi', error.message, 'error');
    }
}

/**
 * Hiển thị modal chọn cách export
 */
export function showExportOptionsModal(selectedIds = []) {
    console.log('showExportOptionsModal called');

    // Load modal if not exists
    if (!document.getElementById('exportByDateModal')) {
        console.log('Loading modal from server...');
        fetch('/exam-schedules/modals/export')
            .then(response => {
                console.log('Modal response received:', response.status);
                return response.text();
            })
            .then(html => {
                console.log('Modal HTML loaded, appending to DOM');
                const container = document.getElementById('modal-container') || document.body;
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html.trim();

                // Kiểm tra có element không
                if (tempDiv.firstElementChild) {
                    container.appendChild(tempDiv.firstElementChild);
                    console.log('Modal appended successfully');
                } else {
                    console.error('No valid HTML element found in response');
                    throw new Error('Invalid modal HTML');
                }

                setupExportByDateModal(selectedIds);
                showModal();
            })
            .catch(err => {
                console.error('Failed to load export modal:', err);
                swal('Lỗi', 'Không thể tải modal export', 'error');
            });
    } else {
        console.log('Modal already exists, reusing');
        setupExportByDateModal(selectedIds);
        showModal();
    }

    function showModal() {
        console.log('Showing modal...');
        const modalElement = document.getElementById('exportByDateModal');
        if (!modalElement) {
            console.error('Modal element not found!');
            return;
        }
        const modal = new bootstrap.Modal(modalElement);
        const dateInput = document.getElementById('export_date');
        if (dateInput) {
            dateInput.value = new Date().toISOString().split('T')[0];
        }
        modal.show();
        console.log('Modal shown');
    }
} function setupExportByDateModal(selectedIds) {
    const form = document.getElementById('exportByDateForm');

    // Remove old event listeners
    const newForm = form.cloneNode(true);
    form.parentNode.replaceChild(newForm, form);

    document.getElementById('exportByDateForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        const date = document.getElementById('export_date').value;
        if (!date) {
            swal('Thông báo', 'Vui lòng chọn ngày', 'warning');
            return;
        }

        const btn = document.getElementById('btnExportByDateSubmit');
        const btnText = btn.querySelector('.btn-text');
        const spinner = btn.querySelector('.spinner-border');

        btn.disabled = true;
        spinner.classList.remove('d-none');

        try {
            const response = await fetch('/api/exam-schedules/export/by-date', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ date: date })
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.message || 'Lỗi khi xuất file');
            }

            // Download file
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;

            const contentDisposition = response.headers.get('content-disposition');
            let fileName = `Ca_Thi_${date}.xlsx`;
            if (contentDisposition) {
                const fileNameMatch = contentDisposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
                if (fileNameMatch && fileNameMatch[1]) {
                    fileName = fileNameMatch[1].replace(/['"]/g, '');
                }
            }

            a.download = fileName;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);

            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('exportByDateModal'));
            if (modal) modal.hide();

            swal('Thành công', 'Đã xuất danh sách ca thi', 'success');

        } catch (error) {
            console.error('Export error:', error);
            swal('Lỗi', error.message, 'error');
        } finally {
            btn.disabled = false;
            spinner.classList.add('d-none');
        }
    });
}
