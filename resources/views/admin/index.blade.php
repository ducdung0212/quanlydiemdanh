@extends('layouts_main.app')

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

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="wg-box">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-tiny text-secondary">Tổng sinh viên</div>
                        <h4 class="mb-0" id="totalStudents">0</h4>
                    </div>
                    <div class="icon-box">
                        <i class="icon-user" style="font-size: 2rem; color: #2377FC;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="wg-box">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-tiny text-secondary">Tổng giảng viên</div>
                        <h4 class="mb-0" id="totalTeachers">0</h4>
                    </div>
                    <div class="icon-box">
                        <i class="icon-users" style="font-size: 2rem; color: #52c41a;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="wg-box">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-tiny text-secondary">Điểm danh hôm nay</div>
                        <h4 class="mb-0" id="todayAttendance">0</h4>
                    </div>
                    <div class="icon-box">
                        <i class="icon-check-circle" style="font-size: 2rem; color: #13c2c2;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Activities --}}
    <div class="row">
        <div class="col-md-8">
            <div class="wg-box">
                <div class="flex items-center justify-between mb-4">
                    <h5 class="mb-0">Hoạt động gần đây</h5>
                    <a href="#" class="text-tiny">Xem tất cả</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Thời gian</th>
                                <th>Hoạt động</th>
                                <th>Người thực hiện</th>
                            </tr>
                        </thead>
                        <tbody id="recentActivities">
                            <tr>
                                <td colspan="3" class="text-center text-secondary">Đang tải dữ liệu...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

