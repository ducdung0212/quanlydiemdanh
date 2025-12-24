@extends('layouts.app')

@section('content')
    <div class="flex items-center flex-wrap justify-between gap20 mb-27">
        <h3>Ca thi được phân công</h3>
    </div>

    <div class="wg-box">
        <div class="table-responsive mt-3">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th style="width: 60px">STT</th>
                        <th>Mã ca thi</th>
                        <th>Mã môn</th>
                        <th>Môn học</th>
                        <th>Ngày thi</th>
                        <th>Giờ thi</th>
                        <th>Phòng</th>
                        <th>Thời lượng</th>
                        <th>Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($examSchedules as $index => $exam)
                        <tr>
                            <td>{{ ($examSchedules->firstItem() ?? 0) + $index }}</td>
                            <td>{{ $exam->session_code ?? $exam->id }}</td>
                            <td>{{ $exam->subject_code ?? '' }}</td>
                            <td>{{ optional($exam->subject)->name ?? '' }}</td>
                            <td>
                                @php
                                    try {
                                        $date =
                                            $exam->exam_date instanceof \Carbon\Carbon
                                                ? $exam->exam_date
                                                : \Carbon\Carbon::parse((string) $exam->exam_date);
                                        echo e($date->format('d-m-Y'));
                                    } catch (\Throwable $e) {
                                        echo e((string) $exam->exam_date);
                                    }
                                @endphp
                            </td>
                            <td>
                                @php
                                    try {
                                        if ($exam->exam_time instanceof \Carbon\Carbon) {
                                            echo e($exam->exam_time->format('H:i:s'));
                                        } else {
                                            echo e((string) $exam->exam_time);
                                        }
                                    } catch (\Throwable $e) {
                                        echo e((string) $exam->exam_time);
                                    }
                                @endphp
                            </td>
                            <td>{{ $exam->room }}</td>
                            <td>{{ $exam->duration ?? '' }}</td>
                            <td>{{ $exam->note ?? '' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">
                                <div class="text-muted">Không có ca thi nào được phân công.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $examSchedules->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
