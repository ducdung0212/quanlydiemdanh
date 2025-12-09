<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Lecturer;
use App\Models\AttendanceRecord;
use App\Models\ExamSchedule;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Chỉ cần return view, dữ liệu sẽ được load qua AJAX
        return view('admin.index');
    }

    public function stats()
    {
        // Giữ lại API endpoint cho các trường hợp cần thiết
        $now = now();
        $ongoingExams = ExamSchedule::where('exam_date', $now->toDateString())
            ->whereRaw('? BETWEEN exam_time AND ADDTIME(exam_time, SEC_TO_TIME(duration*60))', [$now->format('H:i:s')])
            ->get();

        $examsData = $ongoingExams->map(function ($exam) {
            return [
                'id' => $exam->id,
                'subject_name' => $exam->subject_name,
                'exam_date' => $exam->exam_date->format('d/m/Y'),
                'exam_time' => $exam->exam_time,
                'room' => $exam->room,
                'registered_count' => $exam->registered_count,
                'attended_count' => $exam->attended_count,
                'attendance_rate' => $exam->attendance_rate,
            ];
        });

        return response()->json([
            'ongoingExams' => $examsData,
        ]);
    }
}