@extends('layouts.app')

@section('content')
    <div class="flex items-center flex-wrap justify-between gap20 mb-27">
        <h3>Lịch thi của tôi</h3>
    </div>

    <div class="wg-box">
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Môn học</th>
                        <th>Mã môn</th>
                        <th>Ngày thi</th>
                        <th>Giờ thi</th>
                        <th>Phòng</th>
                    </tr>
                </thead>
                <tbody id="student-exam-schedules-body">
                    <tr>
                        <td colspan="5" class="text-center">Đang tải dữ liệu...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center" id="pagination-container">
            </ul>
        </nav>
        <div class="text-center text-muted fs-3" id="pagination-info"></div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', async function() {
            const tableBody = document.getElementById('student-exam-schedules-body');
            const paginationContainer = document.getElementById('pagination-container');
            const paginationInfo = document.getElementById('pagination-info');
            const perPage = 10;
            let currentPage = 1;

            async function loadExamSchedules(page = 1) {
                try {
                    const response = await fetch(
                        `/api/student/exam-schedules?page=${page}&limit=${perPage}`, {
                            headers: {
                                'Accept': 'application/json',
                            },
                        });

                    const payload = await response.json();

                    if (!response.ok || !payload.success) {
                        throw new Error(payload.message || 'Không thể tải lịch thi.');
                    }

                    const data = payload.data || {};
                    const rows = data.data || [];
                    currentPage = data.current_page || 1;
                    const lastPage = data.last_page || 1;
                    const total = data.total || 0;

                    if (!rows.length) {
                        tableBody.innerHTML =
                            '<tr><td colspan="5" class="text-center">Bạn chưa có lịch thi nào.</td></tr>';
                        paginationContainer.innerHTML = '';
                        paginationInfo.innerHTML = '';
                        return;
                    }

                    tableBody.innerHTML = rows.map((item) => {
                        const subjectName = item.subject?.name || '';
                        const examDate = item.exam_date || '';
                        const examTime = item.exam_time || '';
                        const room = item.room || '';
                        return `
                            <tr>
                                <td>${subjectName}</td>
                                <td>${item.subject_code || ''}</td>
                                <td>${examDate}</td>
                                <td>${examTime}</td>
                                <td>${room}</td>
                            </tr>
                        `;
                    }).join('');

                    // Update pagination info
                    const start = (currentPage - 1) * perPage + 1;
                    const end = Math.min(currentPage * perPage, total);
                    paginationInfo.textContent =
                        `Hiển thị ${start} đến ${end} trong tổng số ${total} bản ghi`;

                    // Generate pagination links
                    let paginationHTML = '';

                    if (currentPage > 1) {
                        paginationHTML +=
                            `<li class="page-item"><a class="page-link" href="#" data-page="1">Đầu tiên</a></li>`;
                        paginationHTML +=
                            `<li class="page-item"><a class="page-link" href="#" data-page="${currentPage - 1}">Trước</a></li>`;
                    }

                    for (let i = Math.max(1, currentPage - 2); i <= Math.min(lastPage, currentPage +
                        2); i++) {
                        if (i === currentPage) {
                            paginationHTML +=
                                `<li class="page-item active"><span class="page-link">${i}</span></li>`;
                        } else {
                            paginationHTML +=
                                `<li class="page-item"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                        }
                    }

                    if (currentPage < lastPage) {
                        paginationHTML +=
                            `<li class="page-item"><a class="page-link" href="#" data-page="${currentPage + 1}">Sau</a></li>`;
                        paginationHTML +=
                            `<li class="page-item"><a class="page-link" href="#" data-page="${lastPage}">Cuối cùng</a></li>`;
                    }

                    paginationContainer.innerHTML = paginationHTML;

                    // Add event listeners to pagination links
                    document.querySelectorAll('#pagination-container a').forEach(link => {
                        link.addEventListener('click', (e) => {
                            e.preventDefault();
                            const page = parseInt(link.getAttribute('data-page'));
                            if (page !== currentPage) {
                                loadExamSchedules(page);
                            }
                        });
                    });
                } catch (error) {
                    tableBody.innerHTML =
                        `<tr><td colspan="5" class="text-center text-danger">${error.message}</td></tr>`;
                }
            }

            loadExamSchedules(1);
        });
    </script>
@endpush
