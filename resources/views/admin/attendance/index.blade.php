@extends('layouts.app')

@section('title', 'Điểm danh sinh viên')

@section('content')
<div class="flex items-center flex-wrap justify-between gap20 mb-27">
    <h3>Điểm danh sinh viên</h3>
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
            <div class="text-tiny">Điểm danh</div>
        </li>
    </ul>
</div>

<div class="attendance-container">
    <!-- Lựa chọn ca thi -->
    <div class="wg-box mb-27">
        <h5 class="section-title">Chọn ca thi để điểm danh</h5>
        <div class="row align-items-end">
            <div class="col-md-8">
                <div class="form-group">
                    <label for="examScheduleSelect" class="form-label">Chọn ca thi</label>
                    <select id="examScheduleSelect" class="form-control form-control-lg">
                        <option value="">-- Chọn ca thi --</option>
                        <!-- Options sẽ được load bằng JavaScript -->
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <button id="btnLoadExam" class="tf-button style-1 w-100 h-45">
                        <i class="icon-search"></i> Tải thông tin ca thi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Thông tin ca thi -->
    <div class="wg-box mb-27" id="examInfoSection" style="display: none;">
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
                <strong>Phòng thi</strong>
                <span id="exam-room">-</span>
            </div>
        </div>
    </div>

    <!-- Thống kê điểm danh -->
    <div class="stats-grid mb-27" id="statsSection" style="display: none;">
        <div class="stat-card">
            <div class="stat-number" id="total-students">0</div>
            <div class="stat-label">Tổng sinh viên</div>
        </div>
        <div class="stat-card stat-present">
            <div class="stat-number" id="present-count">0</div>
            <div class="stat-label">Có mặt</div>
        </div>
        <div class="stat-card stat-late">
            <div class="stat-number" id="late-count">0</div>
            <div class="stat-label">Đi muộn</div>
        </div>
        <div class="stat-card stat-absent">
            <div class="stat-number" id="absent-count">0</div>
            <div class="stat-label">Vắng mặt</div>
        </div>
    </div>

    <!-- Nút bắt đầu điểm danh -->
    <div class="text-center mb-27" id="startAttendanceSection" style="display: none;">
        <button class="tf-button style-1 w208" id="btnStartAttendance">
            <i class="icon-camera"></i> Bắt đầu điểm danh
        </button>
    </div>

    <!-- Danh sách sinh viên điểm danh -->
    <div class="wg-box" id="attendanceListSection" style="display: none;">
        <div class="flex items-center justify-between gap10 flex-wrap mb-20">
            <h5 class="section-title" style="margin: 0;">Danh sách sinh viên điểm danh</h5>
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
                            <div class="text-muted">Chọn ca thi để xem danh sách điểm danh</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal điểm danh bằng camera -->
<div class="modal fade" id="attendanceModal" tabindex="-1" aria-labelledby="attendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attendanceModalLabel">Điểm danh bằng camera</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="camera-container text-center">
                    <div id="cameraPreview" class="camera-preview mb-3">
                        <video id="video" autoplay playsinline class="w-100" style="max-height: 400px; background: #000;"></video>
                        <canvas id="canvas" class="d-none"></canvas>
                    </div>
                    <div id="capturedImage" class="captured-image mb-3 d-none">
                        <img id="photo" src="#" alt="Ảnh đã chụp" class="w-100" style="max-height: 400px;">
                    </div>
                    <div class="camera-controls">
                        <button id="btnCapture" class="tf-button style-1 me-2">
                            <i class="icon-camera"></i> Chụp ảnh
                        </button>
                        <button id="btnRetake" class="tf-button style-2 me-2 d-none">
                            <i class="icon-refresh-cw"></i> Chụp lại
                        </button>
                        <button id="btnSubmit" class="tf-button style-3 d-none">
                            <i class="icon-send"></i> Gửi điểm danh
                        </button>
                    </div>
                    <div id="attendanceResult" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="{{ asset('js/admin/attendance-index.js') }}"></script>
@endpush