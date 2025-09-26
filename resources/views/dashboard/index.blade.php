@extends('layouts.master')

@section('title', 'Dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
    <h2>📊 Dashboard</h2>

    <div class="dashboard-cards">
        <div class="dashboard-card">
            <h3>{{ $totalStudents ?? 0 }}</h3>
            <p>Sinh viên</p>
        </div>
        <div class="dashboard-card">
            <h3>{{ $totalLecturers ?? 0 }}</h3>
            <p>Giảng viên</p>
        </div>
        <div class="dashboard-card">
            <h3>{{ $totalExams ?? 0 }}</h3>
            <p>Lịch thi</p>
        </div>
        <div class="dashboard-card">
            <h3>{{ $attendanceToday ?? 0 }}</h3>
            <p>Điểm danh hôm nay</p>
        </div>
    </div>
@endsection
