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
@endsection

