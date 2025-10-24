document.addEventListener('DOMContentLoaded', function() {
    const examScheduleId = window.location.pathname.split('/').pop();
    const API_URL = `/api/exam-schedules/${examScheduleId}`;
    
    // DOM Elements
    const examSessionCode = document.getElementById('exam-session-code');
    const examSubjectCode = document.getElementById('exam-subject-code');
    const examSubjectName = document.getElementById('exam-subject-name');
    const examDate = document.getElementById('exam-date');
    const examTime = document.getElementById('exam-time');
    const examRoom = document.getElementById('exam-room');
    const totalStudents = document.getElementById('total-students');
    const presentCount = document.getElementById('present-count');
    const lateCount = document.getElementById('late-count');
    const absentCount = document.getElementById('absent-count');
    const attendanceTableBody = document.getElementById('attendance-table-body');
    const btnRefresh = document.getElementById('btnRefresh');
    const btnExportExcel = document.getElementById('btnExportExcel');

    // Format date from YYYY-MM-DD to DD-MM-YYYY
    function formatDate(dateStr) {
        if (!dateStr) return '';
        const parts = dateStr.split('-');
        if (parts.length === 3) {
            return `${parts[2]}-${parts[1]}-${parts[0]}`;
        }
        return dateStr;
    }

    // Format time from HH:MM:SS to HH:MM
    function formatTime(timeStr) {
        if (!timeStr) return '';
        return timeStr.substring(0, 5);
    }

    // Get status badge HTML
    function getStatusBadge(status) {
        const statusMap = {
            'present': { class: 'badge bg-success', text: 'Có mặt' },
            'late': { class: 'badge bg-warning', text: 'Đi muộn' },
            'absent': { class: 'badge bg-danger', text: 'Vắng mặt' }
        };
        
        const statusInfo = statusMap[status] || statusMap['absent'];
        return `<span class="${statusInfo.class}">${statusInfo.text}</span>`;
    }

    // Format attendance time
    function formatAttendanceTime(timeStr) {
        if (!timeStr) return '-';
        try {
            const date = new Date(timeStr);
            return date.toLocaleString('vi-VN');
        } catch (e) {
            return timeStr;
        }
    }

    // Escape HTML to prevent XSS
    function escapeHtml(unsafe) {
        if (!unsafe) return '';
        return unsafe
            .toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Load attendance data
    async function loadAttendanceData() {
        try {
            attendanceTableBody.innerHTML = '<tr><td colspan="6" class="text-center">Đang tải dữ liệu...</td></tr>';
            
            const response = await fetch(API_URL);
            
            if (!response.ok) {
                if (response.status === 404) {
                    throw new Error('Không tìm thấy ca thi với ID: ' + examScheduleId);
                }
                throw new Error(`Lỗi HTTP: ${response.status}`);
            }

            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'Không thể tải dữ liệu');
            }
            
            const { exam, stats, students } = result.data;
            
            // Update exam info
            examSessionCode.textContent = exam.session_code || exam.id || '-';
            examSubjectCode.textContent = exam.subject_code || '-';
            examSubjectName.textContent = exam.subject_name || '-';
            examDate.textContent = formatDate(exam.exam_date);
            examTime.textContent = formatTime(exam.exam_time);
            examRoom.textContent = exam.room || '-';
            
            // Update stats
            totalStudents.textContent = stats.total_students || 0;
            presentCount.textContent = stats.present || 0;
            lateCount.textContent = stats.late || 0;
            absentCount.textContent = stats.absent || 0;
            
            // Update students table
            if (!students || students.length === 0) {
                attendanceTableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="text-muted">
                                <i class="icon-info" style="font-size: 24px; opacity: 0.5;"></i>
                                <div class="mt-2">Không có dữ liệu điểm danh cho ca thi này</div>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }
            
            const rowsHtml = students.map((student, index) => `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td>${escapeHtml(student.student_code || '')}</td>
                    <td>${escapeHtml(student.full_name || '')}</td>
                    <td>${escapeHtml(student.class_code || '')}</td>
                    <td>${formatAttendanceTime(student.attendance_time)}</td>
                    <td class="text-center">${getStatusBadge(student.status)}</td>
                </tr>
            `).join('');
            
            attendanceTableBody.innerHTML = rowsHtml;
            
        } catch (error) {
            console.error('Error loading attendance data:', error);
            
            if (error.message.includes('Không tìm thấy ca thi')) {
                attendanceTableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="text-warning">
                                <i class="icon-alert-triangle" style="font-size: 24px;"></i>
                                <div class="mt-2">${error.message}</div>
                                <small class="text-muted d-block mt-1">Vui lòng kiểm tra lại ID ca thi</small>
                            </div>
                        </td>
                    </tr>
                `;
            } else {
                attendanceTableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center text-danger">
                            <i class="icon-alert-circle"></i> ${error.message}
                        </td>
                    </tr>
                `;
            }
            
            // Reset all data on error
            examSessionCode.textContent = '-';
            examSubjectCode.textContent = '-';
            examSubjectName.textContent = '-';
            examDate.textContent = '-';
            examTime.textContent = '-';
            examRoom.textContent = '-';
            totalStudents.textContent = '0';
            presentCount.textContent = '0';
            lateCount.textContent = '0';
            absentCount.textContent = '0';
        }
    }

    // Export to Excel
    async function exportToExcel() {
        try {
            alert('Tính năng xuất Excel đang được phát triển');
        } catch (error) {
            console.error('Error exporting Excel:', error);
            alert('Lỗi khi xuất file Excel');
        }
    }

    // Event listeners
    btnRefresh.addEventListener('click', loadAttendanceData);
    btnExportExcel.addEventListener('click', exportToExcel);

    // Initialize
    loadAttendanceData();
});