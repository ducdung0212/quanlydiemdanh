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
        <div class="card mb-3 shadow-sm" id="examInfoSection" style="display: none;">
            <div class="card-header bg-white py-3">
                <h5 class="m-0 font-weight-bold text-primary">Thông tin ca thi</h5>
            </div>
            <div class="card-body p-3">

                <div class="row border-bottom py-3 align-items-center">
                    <div class="col-5 col-md-3 text-secondary fw-bold fs-4">
                        Mã ca thi:
                    </div>
                    <div class="col-7 col-md-9 text-dark fs-4 fw-bold" id="exam-session-code">
                        -
                    </div>
                </div>

                <div class="row border-bottom py-3 align-items-center">
                    <div class="col-5 col-md-3 text-secondary fw-bold fs-4">
                        Mã môn:
                    </div>
                    <div class="col-7 col-md-9 text-dark fs-4 fw-bold" id="exam-subject-code">
                        -
                    </div>
                </div>

                <div class="row border-bottom py-3 align-items-center">
                    <div class="col-5 col-md-3 text-secondary fw-bold fs-4">
                        Môn học:
                    </div>
                    <div class="col-7 col-md-9 text-dark fs-4 fw-bold" id="exam-subject-name">
                        -
                    </div>
                </div>

                <div class="row border-bottom py-3 align-items-center">
                    <div class="col-5 col-md-3 text-secondary fw-bold fs-4">
                        Ngày thi:
                    </div>
                    <div class="col-7 col-md-9 text-dark fs-4 fw-bold" id="exam-date">
                        -
                    </div>
                </div>

                <div class="row border-bottom py-3 align-items-center">
                    <div class="col-5 col-md-3 text-secondary fw-bold fs-4">
                        Giờ thi:
                    </div>
                    <div class="col-7 col-md-9 text-dark fs-4 fw-bold" id="exam-time">
                        -
                    </div>
                </div>

                <div class="row border-bottom py-3 align-items-center">
                    <div class="col-5 col-md-3 text-secondary fw-bold fs-4">
                        Thời lượng:
                    </div>
                    <div class="col-7 col-md-9 text-dark fs-4 fw-bold" id="exam-duration">
                        -
                    </div>
                </div>

                <div class="row py-3 align-items-center">
                    <div class="col-5 col-md-3 text-secondary fw-bold fs-4">
                        Phòng thi:
                    </div>
                    <div class="col-7 col-md-9 text-primary fs-4 fw-bold" id="exam-room">
                        -
                    </div>
                </div>

            </div>
        </div>

        <div class="row g-3 mb-3 d-flex justify-content-center" id="statsSection" style="display: none;">
            <div class="col-6 col-md-3">
                <div
                    class="p-2 border rounded bg-white text-center h-100 shadow-sm d-flex flex-column justify-content-center">
                    <div class="fw-bolder mb-1 text-dark fs-2" id="total-students">0</div>
                    <div class="text-secondary fw-bold fs-4">Tổng SV</div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div
                    class="p-2 border rounded bg-white text-center h-100 shadow-sm d-flex flex-column justify-content-center">
                    <div class="fw-bolder mb-1 text-success fs-2" id="present-count">0</div>
                    <div class="text-secondary fw-bold fs-4">Có mặt</div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div
                    class="p-2 border rounded bg-white text-center h-100 shadow-sm d-flex flex-column justify-content-center">
                    <div class="fw-bolder mb-1 text-warning fs-2" id="pending-count">0</div>
                    <div class="text-secondary fw-bold fs-4">Chưa ĐD</div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div
                    class="p-2 border rounded bg-white text-center h-100 shadow-sm d-flex flex-column justify-content-center">
                    <div class="fw-bolder mb-1 text-danger fs-2" id="absent-count">0</div>
                    <div class="text-secondary fw-bold fs-4">Vắng</div>
                </div>
            </div>
        </div>
    </div>
    <div class="text-center mb-27" id="startAttendanceSection" style="display: none;">
        <button class="tf-button style-1 w208" id="btnStartAttendance">
            <i class="icon-camera"></i> Bắt đầu điểm danh
        </button>
    </div>

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
                            <div class="text-muted">Chọn ca thi để xem danh sách điểm danh</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    </div>

    <div class="modal fade" id="attendanceModal" tabindex="-1" aria-labelledby="attendanceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="attendanceModalLabel">Điểm danh bằng camera</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="camera-container text-center">
                        <div id="cameraPreview" class="camera-preview mb-3" style="position:relative;">
                            <video id="video" autoplay playsinline class="w-100"
                                style="max-height: 400px; background: #000; position:relative; z-index:1; display:block; object-fit:contain;">
                            </video>
                            <canvas id="overlay"
                                style="position:absolute; top:0; left:0; width:100%; height:100%; pointer-events:none; z-index:2; display:block;"></canvas>
                            <canvas id="canvas" class="d-none"></canvas>
                        </div>

                        <div id="capturedImage" class="captured-image mb-3 d-none"
                            style="max-height: 400px; overflow-y: auto; background: #f0f0f0; padding: 10px; border-radius: 6px; display: flex; flex-wrap: wrap; gap: 10px; justify-content: center;">
                        </div>
                        <div class="camera-controls d-flex justify-content-center flex-nowrap">
                            <button id="btnCapture" class="tf-button style-1 me-2">
                                <i class="icon-camera"></i> Chụp ảnh
                            </button>
                            <button id="btnRetake" class="tf-button style-2 me-2 d-none">
                                <i class="icon-refresh-cw"></i> Chụp lại
                            </button>
                            <button id="btnSubmit" class="tf-button style-3 d-none">
                                <i class="icon-send"></i> Kiểm tra
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
    <script>
        // Truyền role của user vào JavaScript
        window.userRole = '{{ auth()->user()->role ?? 'guest' }}';
    </script>
    <script src="{{ asset('js/vendor/tf.min.js') }}"></script>
    <script src="{{ asset('js/vendor/blazeface.min.js') }}"></script>
    <script src="{{ asset('js/admin/attendance-index.js') }}"></script>
@endpush
