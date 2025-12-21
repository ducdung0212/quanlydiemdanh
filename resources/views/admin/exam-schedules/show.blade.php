@extends('layouts.app')

@section('title', 'Chi tiết điểm danh ca thi')

@section('content')
    <div class="flex items-center flex-wrap justify-between gap20 mb-27">
        <h3>Chi tiết điểm danh ca thi</h3>
        <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
            <li>
                <a href="{{ route('dashboard') }}">
                    <div class="text-tiny">Dashboard</div>
                </a>
            </li>
            <li>
                <i class="icon-chevron-right"></i>
            </li>
            <li>
                <a href="{{ route('exam-schedules') }}">
                    <div class="text-tiny">Quản lý Lịch Thi</div>
                </a>
            </li>
            <li>
                <i class="icon-chevron-right"></i>
            </li>
            <li>
                <div class="text-tiny">Chi tiết điểm danh</div>
            </li>
        </ul>
    </div>

    <div class="attendance-container">

        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="m-0 font-weight-bold text-primary">Thông tin ca thi</h5>
            </div>

            <div class="card-body p-3">
                <div class="row border-bottom py-3 align-items-center">
                    <div class="col-5 col-md-3 text-secondary fw-bold fs-5">
                        Mã ca thi
                    </div>
                    <div class="col-7 col-md-9 text-dark fs-4 fw-bold" id="exam-session-code">
                        -
                    </div>
                </div>

                <div class="row border-bottom py-3 align-items-center">
                    <div class="col-5 col-md-3 text-secondary fw-bold fs-5">
                        Mã môn học
                    </div>
                    <div class="col-7 col-md-9 text-dark fs-4 fw-bold" id="exam-subject-code">
                        -
                    </div>
                </div>

                <div class="row border-bottom py-3 align-items-center">
                    <div class="col-5 col-md-3 text-secondary fw-bold fs-5">
                        Tên môn học
                    </div>
                    <div class="col-7 col-md-9 text-dark fs-4 fw-bold" id="exam-subject-name">
                        -
                    </div>
                </div>

                <div class="row border-bottom py-3 align-items-center">
                    <div class="col-5 col-md-3 text-secondary fw-bold fs-5">
                        Ngày thi
                    </div>
                    <div class="col-7 col-md-9 text-dark fs-4 fw-bold" id="exam-date">
                        -
                    </div>
                </div>

                <div class="row border-bottom py-3 align-items-center">
                    <div class="col-5 col-md-3 text-secondary fw-bold fs-5">
                        Giờ thi
                    </div>
                    <div class="col-7 col-md-9 text-dark fs-4 fw-bold" id="exam-time">
                        -
                    </div>
                </div>

                <div class="row border-bottom py-3 align-items-center">
                    <div class="col-5 col-md-3 text-secondary fw-bold fs-5">
                        Thời lượng
                    </div>
                    <div class="col-7 col-md-9 text-dark fs-4 fw-bold" id="exam-duration">
                        -
                    </div>
                </div>

                <div class="row py-3 align-items-center">
                    <div class="col-5 col-md-3 text-secondary fw-bold fs-5">
                        Phòng thi
                    </div>
                    <div class="col-7 col-md-9 text-primary fs-3 fw-bold" id="exam-room">
                        -
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3 d-flex justify-content-center">

            <div class="col-6 col-md-3">
                <div
                    class="p-2 border rounded bg-white text-center h-100 shadow-sm d-flex flex-column justify-content-center">
                    <div class="fw-bolder mb-1 text-dark fs-2" id="total-students">0</div>
                    <div class="text-secondary fw-bold fs-6">Tổng sinh viên</div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div
                    class="p-2 border rounded bg-white text-center h-100 shadow-sm d-flex flex-column justify-content-center">
                    <div class="fw-bolder mb-1 text-success fs-2" id="present-count">0</div>
                    <div class="text-secondary fw-bold fs-6">Có mặt</div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div
                    class="p-2 border rounded bg-white text-center h-100 shadow-sm d-flex flex-column justify-content-center">
                    <div class="fw-bolder mb-1 text-warning fs-2" id="pending-count">0</div>
                    <div class="text-secondary fw-bold fs-6">Chưa điểm danh</div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div
                    class="p-2 border rounded bg-white text-center h-100 shadow-sm d-flex flex-column justify-content-center">
                    <div class="fw-bolder mb-1 text-danger fs-2" id="absent-count">0</div>
                    <div class="text-secondary fw-bold fs-6">Vắng mặt</div>
                </div>
            </div>

        </div>
    </div>

    <!-- Danh sách sinh viên điểm danh -->
    <div class="wg-box">
        <div class="flex items-center justify-between gap10 flex-wrap mb-20">
            <h5 class="section-title" style="margin: 0;">Danh sách sinh viên điểm danh</h5>
            <a href="/exam-schedules/{{ $id }}/export" class="tf-button style-1 w208" id="btnExportExcel">
                <i class="icon-download"></i> Xuất Excel
            </a>
        </div>

        <div class="table-responsive">
            <table id="attendance-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th style="width: 60px">STT</th>
                        <th>Mã SV</th>
                        <th data-sort="full_name" style="cursor: pointer; user-select: none;">
                            Họ và tên <span class="ms-1" data-sort-indicator="full_name">↕</span>
                        </th>
                        <th data-sort="class_code" style="cursor: pointer; user-select: none;">
                            Lớp <span class="ms-1" data-sort-indicator="class_code">↕</span>
                        </th>
                        <th>Thời gian điểm danh</th>
                        <th style="width: 150px">Trạng thái</th>
                    </tr>
                </thead>
                <tbody id="attendance-table-body">
                    <tr>
                        <td colspan="6" class="text-center">
                            <div class="loading-container">
                                <div class="loading-spinner"></div>
                                <div>Đang tải dữ liệu...</div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Phân trang -->
        <div class="divider"></div>
        <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
            <div class="text-tiny text-secondary">
                Hiển thị <span id="attendance-pagination-start">0</span>-<span id="attendance-pagination-end">0</span>
                của <span id="attendance-pagination-total">0</span> sinh viên
            </div>
            <div class="pagination-controls">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-end mb-0" id="attendance-pagination-container">
                    </ul>
                </nav>
            </div>
        </div>
    </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/exam-schedules-index.js') }}"></script>
@endpush
