<?php

namespace App\Http\Controllers;

use App\Models\ExamSchedule;
use Illuminate\Http\Request;

class LecturerExamSchedulesController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $lecturer = $user?->lecturer;

        if (!$user || $user->role !== 'lecturer') {
            abort(403);
        }

        if (!$lecturer) {
            return redirect()->route('attendance')->with('error', 'Tài khoản chưa được liên kết với giảng viên.');
        }

        $examSchedules = ExamSchedule::query()
            ->with(['subject', 'supervisors.lecturer'])
            ->whereHas('supervisors', function ($query) use ($lecturer) {
                $query->where('lecturer_code', $lecturer->lecturer_code);
            })
            ->orderBy('exam_date')
            ->orderBy('exam_time')
            ->paginate(10);

        return view('lecturer.exam-schedules.index', [
            'examSchedules' => $examSchedules,
        ]);
    }
}
