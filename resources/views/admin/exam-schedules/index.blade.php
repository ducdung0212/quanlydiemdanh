@extends('layouts.app')

@section('content')
    <div class="flex items-center flex-wrap justify-between gap20 mb-27">
        <h3>Quản lý Lịch Thi</h3>
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
                <div class="text-tiny">Quản lý Lịch Thi</div>
            </li>
        </ul>
    </div>

    <div class="wg-box">
        <div class="flex items-center justify-between gap10 flex-wrap">
            <div class="wg-filter flex-grow">
                <form class="form-search" id="searchForm">
                    <fieldset class="name">
                        <input type="text" placeholder="Tìm kiếm theo môn học..." class="" name="q"
                            tabindex="2" value="" aria-required="true" id="searchInput">
                    </fieldset>
                    <div class="button-submit">
                        <button class="" type="submit"><i class="icon-search"></i></button>
                    </div>
                </form>
            </div>
            <div class="d-flex gap10 align-items-center">
                <select class="form-select" id="dateFilter" style="width: 220px; font-size: 14px; padding: 10px 12px;">
                    <option value="">Tất cả ngày thi</option>
                    @php
                        $examDates = \App\Models\ExamSchedule::select('exam_date')
                            ->distinct()
                            ->orderBy('exam_date', 'desc')
                            ->get();
                    @endphp
                    @foreach ($examDates as $date)
                        @php
                            try {
                                if ($date->exam_date instanceof \Carbon\Carbon) {
                                    $valueDate = $date->exam_date->format('Y-m-d');
                                    $formattedDate = $date->exam_date->format('d-m-Y');
                                } else {
                                    $dateString = (string) $date->exam_date;
                                    // Nếu có cả giờ, chỉ lấy phần ngày
                                    if (strpos($dateString, ' ') !== false) {
                                        $dateString = explode(' ', $dateString)[0];
                                    }
                                    // Không ép timezone, chỉ format lại
                                    $dt = \Carbon\Carbon::parse($dateString);
                                    $valueDate = $dt->format('Y-m-d');
                                    $formattedDate = $dt->format('d-m-Y');
                                }
                            } catch (\Exception $e) {
                                $dateString = (string) $date->exam_date;
                                $valueDate = preg_match('/^(\d{4}-\d{2}-\d{2})/', $dateString, $m)
                                    ? $m[1]
                                    : $dateString;
                                $formattedDate = $valueDate;
                            }
                        @endphp
                        <option value="{{ $valueDate }}">{{ $formattedDate }}</option>
                    @endforeach
                </select>

                <a class="tf-button style-3 w208 d-none" href="#" id="btnExportSelected">
                    <i class="icon-download"></i>Export(<span id="exportSelectedCount">0</span>)
                </a>
                <a class="tf-button style-3 w208" href="#" id="btnExportOptions">
                    <i class="icon-calendar"></i>Export theo ngày
                </a>
                <button class="d-none w208" id="btnBulkDelete"
                    style="padding: 12px 20px; border-radius: 10px; font-size: 14px; height: 46px; background-color: #dc3545; color: white; border: none; cursor: pointer; font-weight: 500; transition: all 0.3s;">
                    <i class="icon-trash-2"></i> Xóa đã chọn (<span id="selectedCount">0</span>)
                </button>
                <a class="tf-button style-1 w208" href="#" id="btnAddExamSchedule">
                    <i class="icon-plus"></i>Thêm Ca Thi
                </a>
                <a class="tf-button style-2 w208" href="#" id="importExcelBtn">
                    <i class="icon-upload"></i>Import Excel
                </a>
            </div>
        </div>


        <div class="alert alert-info mt-3 mb-4" role="alert"
            style="font-size: 15px; padding: 16px 20px; border-radius: 10px; background-color: #e8f4ff; border: 1px solid #b3d9ff;">
            <div class="d-flex align-items-center">

                <div>
                    <i class="icon-info" style="font-size: 20px; margin-right: 12px; color: #2377FC;"></i>
                    <strong style="font-size: 15px; color: #2377FC;">HƯỚNG DẪN:</strong>
                    <hr>
                    <span style="color: #333; font-weight: 500;">
                        <ul style="line-height: 1;">
                            <li style="margin-bottom: 10px;">
                                <i class="icon-clipboard" style="color: #2377FC;"></i> Chi tiết ca thi tương ứng.
                            </li>
                            <li style="margin-bottom: 10px;">
                                <i class="icon-users" style="color: #2377FC;"></i> Quản lý thí sinh dự thi
                            </li>
                            <li style="margin-bottom: 10px;">
                                <i class="icon-user-check" style="color: #2377FC;"></i> Quản lý giám thị coi thi
                            </li>
                        </ul>
                    </span>
                </div>
            </div>
        </div>

        <div class="table-responsive mt-3">
            <table id="exam-schedules-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th style="width: 50px">
                            <input type="checkbox" id="selectAll" style="cursor: pointer;">
                        </th>
                        <th style="width: 60px">STT</th>
                        <th>Mã Ca Thi</th>
                        <th>Mã Môn Học</th>
                        <th>Tên Môn Học</th>
                        <th>Ngày Thi</th>
                        <th>Giờ Thi</th>
                        <th>Thời lượng</th>
                        <th>Phòng</th>
                        <th style="width: 210px">Action</th>
                    </tr>
                </thead>
                <tbody id="exam-schedules-table-body">
                </tbody>
            </table>
        </div>

        <div class="divider"></div>
        <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
            <div class="text-tiny text-secondary">
                Hiển thị <span id="pagination-start">0</span>-<span id="pagination-end">0</span> của <span
                    id="pagination-total">0</span> lịch thi
            </div>
            <div class="pagination-controls">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-end mb-0" id="pagination-container">
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Modals will be loaded here -->
    <div id="modal-container"></div>

    <!-- Form Modal -->
    @include('admin.exam-schedules.form-modal')
@endsection

@push('scripts')
    <script>
        // Test function
        function testModal() {
            console.log('Test button clicked');
            const modalElement = document.getElementById('examScheduleFormModal');
            console.log('Modal element:', modalElement);
            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                console.log('Bootstrap modal created:', modal);
                modal.show();
            } else {
                console.error('Modal element not found');
            }
        }
    </script>
    <script src="{{ asset('js/admin/exam-schedules-form.js') }}"></script>
    <script src="{{ asset('js/admin/exam-schedules-students-modal.js') }}"></script>
    <script src="{{ asset('js/admin/exam-schedules-supervisors-modal.js') }}"></script>
    <script src="{{ asset('js/admin/exam-schedules-index.js') }}"></script>
@endpush
