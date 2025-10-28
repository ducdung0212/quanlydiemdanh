document.addEventListener('DOMContentLoaded', function () {
    // DOM Elements
    const searchInput = document.getElementById('searchInput');
    const filterDate = document.getElementById('filterDate');
    const btnClearFilter = document.getElementById('btnClearFilter');
    const tableBody = document.getElementById('exam-schedule-table-body');
    const pagination = document.getElementById('pagination');
    const showingInfo = document.getElementById('showing-info');

    // State
    let currentPage = 1;
    let totalPages = 1;
    let searchTimeout = null;

    // ==================== FETCH DATA ====================

    async function fetchMySchedule(page = 1) {
        try {
            const params = new URLSearchParams({
                page: page,
                limit: 10
            });

            const searchValue = searchInput.value.trim();
            if (searchValue) {
                params.append('q', searchValue);
            }

            const dateValue = filterDate.value;
            if (dateValue) {
                params.append('date', dateValue);
            }

            tableBody.innerHTML = '<tr><td colspan="8" class="text-center">Đang tải dữ liệu...</td></tr>';

            const response = await fetch(`/api/exam-schedules/my/schedule?${params.toString()}`);
            const result = await response.json();

            if (!result.success) {
                throw new Error(result.message || 'Không thể tải dữ liệu');
            }

            const { data, current_page, last_page, total, from, to } = result.data;

            currentPage = current_page;
            totalPages = last_page;

            renderTable(data);
            renderPagination();
            updateShowingInfo(from, to, total);

        } catch (error) {
            console.error('Error fetching schedule:', error);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center text-danger">
                        Lỗi: ${error.message}
                    </td>
                </tr>
            `;
            showToast('Lỗi', error.message, 'error');
        }
    }

    // ==================== RENDER ====================

    function renderTable(schedules) {
        if (!schedules || schedules.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="8" class="text-center">Không có lịch coi thi nào</td></tr>';
            return;
        }

        tableBody.innerHTML = schedules.map(schedule => {
            const supervisorNames = schedule.supervisors 
                ? schedule.supervisors.map(s => s.lecturer_name || s.lecturer_code).join(', ')
                : '-';

            return `
                <tr>
                    <td class="text-center">${escapeHtml(schedule.session_code || schedule.id)}</td>
                    <td>
                        <div><strong>${escapeHtml(schedule.subject_code)}</strong></div>
                        <div class="text-muted small">${escapeHtml(schedule.subject_name || '-')}</div>
                    </td>
                    <td class="text-center">${formatDate(schedule.exam_date)}</td>
                    <td class="text-center">${formatTime(schedule.exam_time)}</td>
                    <td class="text-center">${escapeHtml(schedule.room)}</td>
                    <td class="text-center">${schedule.total_students || 0}</td>
                    <td><small>${escapeHtml(supervisorNames)}</small></td>
                    <td class="text-center">
                        <a href="/attendance?exam_id=${schedule.id}" class="btn btn-sm btn-primary" title="Điểm danh">
                            <i class="icon-camera"></i> Điểm danh
                        </a>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function renderPagination() {
        if (totalPages <= 1) {
            pagination.innerHTML = '';
            return;
        }

        let paginationHTML = '';

        // Previous button
        paginationHTML += `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}">
                    <i class="icon-arrow-left"></i>
                </a>
            </li>
        `;

        // Page numbers
        const maxVisible = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
        let endPage = Math.min(totalPages, startPage + maxVisible - 1);

        if (endPage - startPage < maxVisible - 1) {
            startPage = Math.max(1, endPage - maxVisible + 1);
        }

        if (startPage > 1) {
            paginationHTML += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
            if (startPage > 2) {
                paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            paginationHTML += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
            paginationHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`;
        }

        // Next button
        paginationHTML += `
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}">
                    <i class="icon-arrow-right"></i>
                </a>
            </li>
        `;

        pagination.innerHTML = paginationHTML;
    }

    function updateShowingInfo(from, to, total) {
        if (total === 0) {
            showingInfo.textContent = 'Không có kết quả';
        } else {
            showingInfo.textContent = `Đang hiển thị ${from} - ${to} trong tổng số ${total} kết quả`;
        }
    }

    // ==================== HELPER FUNCTIONS ====================

    function formatDate(dateStr) {
        if (!dateStr) return '';
        try {
            const date = new Date(dateStr);
            if (!isNaN(date.getTime())) {
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = date.getFullYear();
                return `${day}/${month}/${year}`;
            }
        } catch (e) {
            console.error('Error parsing date:', e);
        }
        return dateStr;
    }

    function formatTime(timeStr) {
        if (!timeStr) return '';
        return timeStr.substring(0, 5);
    }

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

    // ==================== EVENT LISTENERS ====================

    // Search with debounce
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            fetchMySchedule(currentPage);
        }, 500);
    });

    // Date filter
    filterDate.addEventListener('change', function () {
        currentPage = 1;
        fetchMySchedule(currentPage);
    });

    // Clear filter
    btnClearFilter.addEventListener('click', function () {
        searchInput.value = '';
        filterDate.value = '';
        currentPage = 1;
        fetchMySchedule(currentPage);
    });

    // Pagination clicks
    pagination.addEventListener('click', function (e) {
        e.preventDefault();
        if (e.target.tagName === 'A' || e.target.closest('a')) {
            const link = e.target.closest('a');
            const page = parseInt(link.getAttribute('data-page'));
            if (page && page !== currentPage && page >= 1 && page <= totalPages) {
                fetchMySchedule(page);
            }
        }
    });

    // ==================== INITIALIZATION ====================

    fetchMySchedule(currentPage);
});
