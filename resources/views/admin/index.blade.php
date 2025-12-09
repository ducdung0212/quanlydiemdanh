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
                <input type="text" 
                       id="searchInput"
                       class="search-input" 
                       placeholder="Tìm kiếm theo phòng thi, tên môn học, mã môn học..." 
                       autocomplete="off">
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
@endsection
@push('scripts')
    <script src="{{ asset('js/admin/dashboard.js') }}"></script>
@endpush
