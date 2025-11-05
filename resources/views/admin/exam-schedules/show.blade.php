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
    <!-- Thông tin ca thi -->
    <div class="wg-box mb-27">
        <h5 class="section-title">Thông tin ca thi</h5>
        <div class="exam-info-grid">
            <div class="exam-info-item">
                <strong>Mã ca thi</strong>
                <span id="exam-session-code">-</span>
            </div>
            <div class="exam-info-item">
                <strong>Mã môn học</strong>
                <span id="exam-subject-code">-</span>
            </div>
            <div class="exam-info-item">
                <strong>Tên môn học</strong>
                <span id="exam-subject-name">-</span>
            </div>
            <div class="exam-info-item">
                <strong>Ngày thi</strong>
                <span id="exam-date">-</span>
            </div>
            <div class="exam-info-item">
                <strong>Giờ thi</strong>
                <span id="exam-time">-</span>
            </div>
            <div class="exam-info-item">
                <strong>Thời lượng</strong>
                <span id="exam-duration">-</span>
            </div>
            <div class="exam-info-item">
                <strong>Phòng thi</strong>
                <span id="exam-room">-</span>
            </div>
        </div>
    </div>

    <!-- Thống kê điểm danh -->
    <div class="stats-grid mb-27">
        <div class="stat-card">
            <div class="stat-number" id="total-students">0</div>
            <div class="stat-label">Tổng sinh viên</div>
        </div>
        <div class="stat-card stat-present">
            <div class="stat-number" id="present-count">0</div>
            <div class="stat-label">Có mặt</div>
        </div>
        <div class="stat-card stat-pending">
            <div class="stat-number" id="pending-count">0</div>
            <div class="stat-label">Chưa điểm danh</div>
        </div>
        <div class="stat-card stat-absent">
            <div class="stat-number" id="absent-count">0</div>
            <div class="stat-label">Vắng mặt</div>
        </div>
    </div>

    <!-- Danh sách sinh viên điểm danh -->
    <div class="wg-box">
        <div class="flex items-center justify-between gap10 flex-wrap mb-20">
            <h5 class="section-title" style="margin: 0;">Danh sách sinh viên điểm danh</h5>
            <button class="tf-button style-1 w208" id="btnExportExcel">
                <i class="icon-download"></i> Xuất Excel
            </button>
        </div>
        
        <div class="table-responsive">
            <table id="attendance-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th style="width: 60px">STT</th>
                        <th>Mã SV</th>
                        <th>Họ và tên</th>
                        <th>Lớp</th>
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
                Hiển thị <span id="attendance-pagination-start">0</span>-<span id="attendance-pagination-end">0</span> của <span id="attendance-pagination-total">0</span> sinh viên
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