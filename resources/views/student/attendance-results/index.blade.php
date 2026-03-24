@extends('layouts.app')

@section('content')
    <div class="flex items-center flex-wrap justify-between gap20 mb-27">
        <h3>Kết quả điểm danh của tôi</h3>
    </div>

    <div class="wg-box">
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Môn học</th>
                        <th>Ngày thi</th>
                        <th>Giờ thi</th>
                        <th>Phòng</th>
                        <th>Kết quả</th>
                        <th>Thời gian điểm danh</th>
                    </tr>
                </thead>
                <tbody id="student-attendance-results-body">
                    <tr>
                        <td colspan="6" class="text-center">Đang tải dữ liệu...</td>
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
            const tableBody = document.getElementById('student-attendance-results-body');
            const paginationContainer = document.getElementById('pagination-container');
            const paginationInfo = document.getElementById('pagination-info');
            const perPage = 10;
            let currentPage = 1;

            const statusLabel = {
                match: 'Có mặt',
                not_match: 'Không khớp',
                unknown: 'Không xác định',
                null: 'Chưa điểm danh',
            };

            async function loadAttendanceResults(page = 1) {
                try {
                    const response = await fetch(
                        `/api/student/attendance-results?page=${page}&limit=${perPage}`, {
                            headers: {
                                'Accept': 'application/json',
                            },
                        });

                    const payload = await response.json();

                    if (!response.ok || !payload.success) {
                        throw new Error(payload.message || 'Không thể tải kết quả điểm danh.');
                    }

                    const data = payload.data || {};
                    const rows = data.data || [];
                    currentPage = data.current_page || 1;
                    const lastPage = data.last_page || 1;
                    const total = data.total || 0;

                    if (!rows.length) {
                        tableBody.innerHTML =
                            '<tr><td colspan="6" class="text-center">Bạn chưa có dữ liệu điểm danh.</td></tr>';
                        paginationContainer.innerHTML = '';
                        paginationInfo.innerHTML = '';
                        return;
                    }

                    tableBody.innerHTML = rows.map((item) => {
                        const exam = item.exam_schedule || {};
                        const subjectName = exam.subject?.name || '';
                        const resultKey = item.rekognition_result === null ? 'null' : item
                            .rekognition_result;

                        return `
                            <tr>
                                <td>${subjectName}</td>
                                <td>${exam.exam_date || ''}</td>
                                <td>${exam.exam_time || ''}</td>
                                <td>${exam.room || ''}</td>
                                <td>${statusLabel[resultKey] || resultKey || ''}</td>
                                <td>${item.attendance_time || ''}</td>
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
                                loadAttendanceResults(page);
                            }
                        });
                    });
                } catch (error) {
                    tableBody.innerHTML =
                        `<tr><td colspan="6" class="text-center text-danger">${error.message}</td></tr>`;
                }
            }

            loadAttendanceResults(1);
        });
    </script>
@endpush
