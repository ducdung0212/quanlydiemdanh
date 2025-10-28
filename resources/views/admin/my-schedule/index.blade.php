@extends('layouts.app')

@section('title', 'Lịch Coi Thi Của Tôi')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="icon-calendar me-2"></i>Lịch Coi Thi Của Tôi
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Search and Filter -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="icon-magnifier"></i></span>
                                <input type="text" class="form-control" id="searchInput" 
                                       placeholder="Tìm theo mã môn, tên môn, phòng...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <input type="date" class="form-control" id="filterDate" 
                                   placeholder="Lọc theo ngày">
                        </div>
                        <div class="col-md-5 text-end">
                            <button type="button" class="btn btn-secondary" id="btnClearFilter">
                                <i class="icon-close"></i> Xóa bộ lọc
                            </button>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 100px;">Mã ca thi</th>
                                    <th>Môn học</th>
                                    <th style="width: 120px;">Ngày thi</th>
                                    <th style="width: 100px;">Giờ thi</th>
                                    <th style="width: 100px;">Phòng</th>
                                    <th style="width: 120px;">Số SV</th>
                                    <th style="width: 120px;">Giám thị</th>
                                    <th style="width: 150px;">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody id="exam-schedule-table-body">
                                <tr>
                                    <td colspan="8" class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div id="showing-info">Đang hiển thị 0 kết quả</div>
                        <nav>
                            <ul class="pagination mb-0" id="pagination">
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/admin/my-schedule-index.js') }}"></script>
@endpush
