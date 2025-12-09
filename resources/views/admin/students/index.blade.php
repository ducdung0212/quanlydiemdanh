@extends('layouts.app')

@section('content')
    <div class="flex items-center flex-wrap justify-between gap20 mb-27">
        <h3>Quản lý Sinh Viên</h3>
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
                <div class="text-tiny">Quản lý Sinh Viên</div>
            </li>
        </ul>
    </div>

    <div class="wg-box">
        <div class="flex items-center justify-between gap10 flex-wrap">
            <div class="wg-filter flex-grow">
                <form class="form-search" id="searchForm">
                    <fieldset class="name">
                        <input type="text" placeholder="Tìm kiếm sinh viên..." class="" name="q"
                            tabindex="2" value="" aria-required="true" id="searchInput">
                    </fieldset>
                    <div class="button-submit">
                        <button class="" type="submit"><i class="icon-search"></i></button>
                    </div>
                </form>
            </div>
            <div class="d-flex gap10 align-items-center">
                <button class="btn btn-danger d-none" id="btnBulkDelete" style="padding: 8px 16px; border-radius: 8px;">
                    <i class="icon-trash-2"></i> Xóa đã chọn (<span id="selectedCount">0</span>)
                </button>
                <a class="tf-button style-2 w208" href="#" id="importExcelBtn">
                    <i class="icon-upload"></i>Import Excel
                </a>
                <a class="tf-button style-1 w208" href="#" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                    <i class="icon-plus"></i>Thêm mới
                </a>
            </div>
        </div>
        <div class="table-responsive">
            <table id="students-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th style="width: 50px">
                            <input type="checkbox" id="selectAll" style="cursor: pointer;">
                        </th>
                        <th style="width: 60px">STT</th>
                        <th>Mã SV</th>
                        <th>Tên</th>
                        <th>Lớp</th>
                        <th style="width: 150px">Action</th>
                    </tr>
                </thead>
                <tbody id="students-table-body">
                </tbody>
            </table>
        </div>

        <div class="divider"></div>
        <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
            <div class="text-tiny text-secondary">
                Hiển thị <span id="pagination-start">0</span>-<span id="pagination-end">0</span> của <span
                    id="pagination-total">0</span> sinh viên
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
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/students-index.js') }}"></script>
@endpush
