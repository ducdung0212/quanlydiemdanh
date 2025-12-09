@extends('layouts.app')

@section('content')
    <div class="flex items-center flex-wrap justify-between gap20 mb-27">
        <h3>Dashboard</h3>
        <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
            <li>
                <a href="{{ route('dashboard') }}">
                    <div class="text-tiny">Dashboard</div>
                </a>
            </li>
        </ul>
    </div>

    {{-- Face Registration Statistics --}}
    <div class="wg-box mb-27">
        <div class="flex items-center justify-between mb-20">
            <h5>Thống kê đăng ký khuôn mặt</h5>
        </div>

        <style>
            .face-stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
                margin-bottom: 20px;
            }

            .face-stat-item {
                display: flex;
                flex-direction: column;
                gap: 8px;
            }

            .face-stat-item strong {
                font-size: 15px !important;
                font-weight: 600 !important;
                color: #6c757d !important;
            }

            .face-stat-item span {
                font-size: 18px !important;
                font-weight: 600 !important;
                color: #2c3e50 !important;
                padding: 12px 16px;
                background: #f8f9fa;
                border-radius: 8px;
                border-left: 3px solid #2377FC;
            }

            .modal-xxl {
                max-width: 1300px;
            }

            .form-select-xl {
                padding: 1rem 3rem 1rem 1.5rem;
                font-size: 1.5rem;
                border-radius: 0.5rem;
                background-size: 20px 20px;
                /* Tăng kích thước icon dropdown */
            }
        </style>

        <div class="face-stats-grid" id="faceStatsContainer">
            <div class="face-stat-item">
                <strong>Tổng sinh viên</strong>
                <span id="totalStudentsCount">-</span>
            </div>
            <div class="face-stat-item">
                <strong>Đã đăng ký</strong>
                <span id="registeredCount" style="border-left-color: #28a745;">-</span>
            </div>
            <div class="face-stat-item">
                <strong>Chưa đăng ký</strong>
                <span id="unregisteredCount" style="border-left-color: #dc3545;">-</span>
            </div>
            <div class="face-stat-item">
                <strong>Tỷ lệ hoàn thành</strong>
                <span id="registeredPercentage" style="border-left-color: #17a2b8;">-</span>
            </div>
        </div>

        <div class="text-center mt-3">
            <button type="button" class="tf-button style-1" id="btnViewFaceDetails">
                <i class="icon-list"></i> Xem danh sách chi tiết
            </button>
        </div>
    </div>

    {{-- Ongoing Exams Section --}}
    <div class="wg-box">
        <div class="flex items-center justify-between mb-20">
            <h5>Ca thi đang diễn ra</h5>
            <div class="text-tiny text-secondary">
                <i class="icon-clock"></i> Cập nhật: <span id="lastUpdate">{{ now()->format('H:i - d/m/Y') }}</span>
            </div>
        </div>

        {{-- Search Box --}}
        <div class="search-box-wrapper mb-20">
            <div class="search-input-group">
                <i class="icon-search"></i>
                <input type="text" id="searchInput" class="search-input"
                    placeholder="Tìm kiếm theo phòng thi, tên môn học, mã môn học..." autocomplete="off">
                <button type="button" id="clearSearch" class="clear-search" style="display: none;" title="Xóa tìm kiếm">
                    <i class="icon-x"></i>
                </button>
                <div id="searchLoading" class="search-loading" style="display: none;">
                    <div class="spinner"></div>
                </div>
            </div>
            <div id="searchResultInfo" class="search-result-info" style="display: none;">
                <span class="text-tiny"></span>
            </div>
        </div>

        {{-- Exams Container --}}
        <div id="ongoingExamsContainer">
            <div class="text-center py-5" id="loadingState">
                <div class="spinner-large mb-3"></div>
                <p class="text-secondary">Đang tải dữ liệu...</p>
            </div>
        </div>
    </div>

    {{-- Modal: Face Registration Students List --}}
    <div class="modal fade" id="faceRegistrationModal" tabindex="-1">
        <div class="modal-dialog modal-xxl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Danh sách sinh viên đăng ký khuôn mặt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 d-flex gap-2 align-items-center">
                        <input type="text" id="faceStudentSearch" class="form-control-lg" placeholder="Tìm kiếm..."
                            style="max-width: 400px;">
                        <select id="faceStatusFilter" class="form-select form-select-xl" style="max-width: 200px;">
                            <option value="all">Tất cả</option>
                            <option value="registered">Đã đăng ký</option>
                            <option value="unregistered">Chưa đăng ký</option>
                        </select>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th style="width: 60px">STT</th>
                                    <th>Mã SV</th>
                                    <th>Họ tên</th>
                                    <th>Lớp</th>
                                    <th style="width: 180px">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody id="faceStudentTableBody">
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted small">
                            Hiển thị <span id="faceStudentFrom">0</span>-<span id="faceStudentTo">0</span> của <span
                                id="faceStudentTotal">0</span> sinh viên
                        </div>
                        <nav>
                            <ul class="pagination mb-0" id="faceStudentPagination"></ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('js/admin/dashboard.js') }}"></script>
@endpush
