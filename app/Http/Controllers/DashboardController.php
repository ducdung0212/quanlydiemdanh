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

        // Face registration statistics
        $totalStudents = Student::count();
        $studentsWithPhotos = Student::has('photos')->count();
        $studentsWithoutPhotos = $totalStudents - $studentsWithPhotos;

        return response()->json([
            'ongoingExams' => $examsData,
            'faceRegistrationStats' => [
                'total' => $totalStudents,
                'registered' => $studentsWithPhotos,
                'unregistered' => $studentsWithoutPhotos,
                'registered_percentage' => $totalStudents > 0 ? round(($studentsWithPhotos / $totalStudents) * 100, 1) : 0,
            ],
        ]);
    }

    public function faceRegistrationStudents(Request $request)
    {
        $status = $request->query('status', 'all'); // all, registered, unregistered
        $search = $request->query('q', '');
        $limit = $request->query('limit', 20);

        $query = Student::with('photos');

        // Filter by status
        if ($status === 'registered') {
            $query->has('photos');
        } elseif ($status === 'unregistered') {
            $query->doesntHave('photos');
        }

        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('student_code', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%")
                  ->orWhere('class_code', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('student_code')->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => $students->items(),
            'pagination' => [
                'current_page' => $students->currentPage(),
                'last_page' => $students->lastPage(),
                'per_page' => $students->perPage(),
                'total' => $students->total(),
                'from' => $students->firstItem(),
                'to' => $students->lastItem(),
            ],
        ]);
    }
}