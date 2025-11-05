<?php

namespace App\Http\Controllers;

use App\Imports\ExamSchedulesImport;
use App\Models\AttendanceRecord;
use App\Models\ExamSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class ExamSchedulesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $q = request()->query('q');
        $date = request()->query('date');
        $limit = (int) request()->query('limit', 10);
        $user = auth()->user();

        $examSchedules = ExamSchedule::query()
            ->with('subject')
            ->latest();

        // Nếu là lecturer, chỉ xem ca thi được phân công
        if ($user && $user->role === 'lecturer') {
            $lecturer = $user->lecturer;
            
            if (!$lecturer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tài khoản chưa được liên kết với giảng viên. Vui lòng liên hệ quản trị viên.',
                ], 400);
            }

            $examSchedules->whereHas('supervisors', function ($query) use ($lecturer) {
                $query->where('lecturer_code', $lecturer->lecturer_code);
            });
        }

        if ($q) {
            $examSchedules->where(function ($query) use ($q) {
                $query->where('id', 'like', "%{$q}%")
                    ->orWhere('subject_code', 'like', "%{$q}%")
                    ->orWhere('exam_date', 'like', "%{$q}%")
                    ->orWhere('exam_time', 'like', "%{$q}%")
                    ->orWhere('room', 'like', "%{$q}%")
                    ->orWhereHas('subject', function ($subjectQuery) use ($q) {
                        $subjectQuery->where('name', 'like', "%{$q}%");
                    });
            });
        }

        if ($date) {
            try {
                $normalizedDate = Carbon::parse($date)->toDateString();
                $examSchedules->whereDate('exam_date', $normalizedDate);
            } catch (Throwable $e) {
                // If the provided date cannot be parsed, ignore the filter.
            }
        }

        return response()->json([
            'success' => true,
            'data' => $examSchedules->paginate($limit),
            'message' => 'List Exam Schedules',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }
    //
    // Thêm method này vào controller
public function exportAttendance($id)
{
    $examSchedule = ExamSchedule::with('subject')->find($id);
    
    if (!$examSchedule) {
        return response()->json([
            'success' => false,
            'message' => 'Exam Schedule not found',
        ], 404);
    }

    // Tạm thời trả về thông báo
    return response()->json([
        'success' => true,
        'message' => 'Tính năng xuất Excel đang được phát triển',
    ]);
}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = auth()->user();
        $examSchedule = ExamSchedule::with('subject')->find($id);

        if (!$examSchedule) {
            return response()->json([
                'success' => false,
                'message' => 'Exam Schedule not found',
            ], 404);
        }

        // Nếu là lecturer, kiểm tra quyền truy cập và thời gian
        if ($user && $user->role === 'lecturer') {
            $lecturer = $user->lecturer;
            
            if (!$lecturer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tài khoản chưa được liên kết với giảng viên. Vui lòng liên hệ quản trị viên.',
                ], 400);
            }

            // Kiểm tra quyền truy cập
            $hasAccess = $examSchedule->supervisors()
                ->where('lecturer_code', $lecturer->lecturer_code)
                ->exists();
            
            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền xem ca thi này',
                ], 403);
            }

            // Kiểm tra thời gian: Chỉ cho phép tải thông tin khi đã đến 30 phút trước giờ thi
            try {
                $examDate = \Carbon\Carbon::parse($examSchedule->exam_date)->startOfDay();
                $examTime = \Carbon\Carbon::parse($examSchedule->exam_time);
                $examDateTime = $examDate->setTimeFromTimeString($examTime->format('H:i:s'));
                $now = \Carbon\Carbon::now();
                $thirtyMinutesBefore = $examDateTime->copy()->subMinutes(30);
                
                if ($now->isBefore($thirtyMinutesBefore)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Chưa đến giờ thi. Vui lòng quay lại sau ' . $thirtyMinutesBefore->format('H:i d/m/Y'),
                    ], 403);
                }
            } catch (\Exception $e) {
                // Nếu không parse được thời gian, cho phép tiếp tục
            }
        }

        $attendanceRecords = AttendanceRecord::with('student')
            ->where('exam_schedule_id', $examSchedule->id)
            ->get();

        // Tính thời gian kết thúc ca thi (exam_date + exam_time + duration)
        $examEndTime = null;
        try {
            $examDateTime = \Carbon\Carbon::parse($examSchedule->exam_date . ' ' . $examSchedule->exam_time);
            $examEndTime = $examDateTime->addMinutes($examSchedule->duration ?? 0);
        } catch (\Exception $e) {
            // Nếu không parse được, để null
        }

        $now = \Carbon\Carbon::now();
        $isExamEnded = $examEndTime && $now->isAfter($examEndTime);

        $students = [];
        $present = 0;
        $absent = 0;
        $pending = 0; // Số sinh viên chưa điểm danh

        // Lặp qua TẤT CẢ các bản ghi điểm danh 
        foreach ($attendanceRecords as $record) {
            $student = $record->student;
            $status = 'pending'; // Mặc định: chưa điểm danh
            $attendanceTime = null;

            // Kiểm tra rekognition_result
            if ($record->rekognition_result === 'match') {
                // Đã điểm danh thành công
                $status = 'present';
                $attendanceTime = optional($record->attendance_time)?->toDateTimeString();
                $present++;
            } elseif ($record->rekognition_result === null) {
                // Chưa điểm danh
                if ($isExamEnded) {
                    // Nếu ca thi đã kết thúc → Vắng mặt
                    $status = 'absent';
                    $absent++;
                } else {
                    // Ca thi chưa kết thúc → Chưa điểm danh
                    $status = 'pending';
                    $pending++;
                }
            } else {
                // rekognition_result khác (no_match, error, ...) → Vắng mặt
                $status = 'absent';
                $absent++;
            }

            $students[] = [
                'id' => $record->id,
                'student_code' => $record->student_code,
                'full_name' => $student->full_name ?? null,
                'class_code' => $student->class_code ?? null,
                'attendance_time' => $attendanceTime,
                'status' => $status,
            ];
        }

        $total = count($attendanceRecords);

        return response()->json([
            'success' => true,
            'data' => [
                'exam' => [
                    'id' => $examSchedule->id,
                    'session_code' => $examSchedule->session_code ?? $examSchedule->id,
                    'subject_code' => $examSchedule->subject_code,
                    'subject_name' => optional($examSchedule->subject)->name,
                    'exam_date' => optional($examSchedule->exam_date)?->toDateString() ?? (string) $examSchedule->exam_date,
                    'exam_time' => optional($examSchedule->exam_time)?->format('H:i:s') ?? (string) $examSchedule->exam_time,
                    'duration' => $examSchedule->duration,
                    'room' => $examSchedule->room,
                    'note' => $examSchedule->note,
                    'registered_count' => $examSchedule->registered_count,
                    'attended_count' => $examSchedule->attended_count,
                    'attendance_rate' => $examSchedule->attendance_rate,
                ],
              'stats' => [
                    'total_students' => $total,
                    'present' => $present,
                    'pending' => $pending, // Số sinh viên chưa điểm danh
                    'absent' => $absent,
                ],
                'students' => $students,
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $examSchedule = ExamSchedule::find($id);

        if (!$examSchedule) {
            return response()->json([
                'success' => false,
                'message' => 'Exam Schedule not found',
            ], 404);
        }

        $examSchedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Exam Schedule deleted successfully',
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'Không có lịch thi nào được chọn',
            ], 400);
        }

        ExamSchedule::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa ' . count($ids) . ' lịch thi thành công.',
        ]);
    }

    public function previewImport(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls',
        ]);

        try {
            $file = $request->file('excel_file');
            $storedPath = $file->store('imports/tmp');
            $fullPath = Storage::path($storedPath);

            $reader = IOFactory::createReaderForFile($fullPath);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($fullPath);
            $sheet = $spreadsheet->getActiveSheet();

            $highestColumn = $sheet->getHighestDataColumn();
            $highestRow = min($sheet->getHighestDataRow(), 50);

            $rows = [];
            for ($rowIndex = 1; $rowIndex <= $highestRow; $rowIndex++) {
                $range = sprintf('A%d:%s%d', $rowIndex, $highestColumn, $rowIndex);
                $rowValues = $sheet->rangeToArray($range, null, true, true, false);
                if (!empty($rowValues)) {
                    $rows[$rowIndex] = $rowValues[0];
                }
            }

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            $expectedKeys = [
                'subject',
                'subject-code',
                'subject_code',
                'ma-mon',
                'ma-hoc-phan',
                'exam-date',
                'exam-time',
                'exam_date',
                'exam_time',
                'duration',
                'thoi-gian',
                'thoi-luong',
                'room',
                'phong',
                'ghi-chu',
                'note',
            ];

            $visibleHeadings = [];
            $visibleHeadingRow = null;
            $fallbackHeadings = [];
            $fallbackHeadingRow = null;

            foreach ($rows as $rowNumber => $row) {
                if (!is_array($row)) {
                    continue;
                }

                $values = array_map(static fn ($value) => trim((string) $value), array_values($row));
                $nonEmpty = array_values(array_filter($values, static fn ($value) => $value !== ''));

                if (empty($nonEmpty)) {
                    continue;
                }

                $normalized = array_map(static function ($value) {
                    $ascii = Str::lower(Str::ascii($value));
                    $slug = preg_replace('/[^a-z0-9]+/i', '-', $ascii);
                    return trim((string) $slug, '-');
                }, $nonEmpty);

                if (array_intersect($normalized, $expectedKeys)) {
                    $visibleHeadings = $nonEmpty;
                    $visibleHeadingRow = $rowNumber;
                    break;
                }

                if (empty($fallbackHeadings)) {
                    $fallbackHeadings = $nonEmpty;
                    $fallbackHeadingRow = $rowNumber;
                }
            }

            if (empty($visibleHeadings)) {
                $visibleHeadings = $fallbackHeadings;
                $visibleHeadingRow = $fallbackHeadingRow;
            }

            if (empty($visibleHeadings)) {
                Storage::delete($storedPath);

                return response()->json([
                    'success' => false,
                    'message' => 'Không thể tìm thấy hàng tiêu đề trong file. Vui lòng kiểm tra lại định dạng.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'token' => $storedPath,
                'headings' => $visibleHeadings,
                'heading_row' => $visibleHeadingRow,
            ]);
        } catch (Throwable $e) {
            if (isset($storedPath)) {
                Storage::delete($storedPath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Không thể đọc file: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function import(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'heading_row' => 'nullable|integer|min:1',
            'mapping' => 'required|array',
            'mapping.subject_code' => 'required|string',
            'mapping.exam_date' => 'required|string',
            'mapping.exam_time' => 'required|string',
            'mapping.duration' => 'required|string',
            'mapping.room' => 'required|string',
            'mapping.note' => 'nullable|string',
        ]);

        $filePath = $validated['token'];
        $headingRow = (int) ($validated['heading_row'] ?? 1);

        if (!Storage::exists($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'File tạm không tồn tại hoặc đã hết hạn.',
            ], 410);
        }

        try {
            Excel::import(
                new ExamSchedulesImport($validated['mapping'], $headingRow),
                Storage::path($filePath)
            );

            return response()->json([
                'success' => true,
                'message' => 'Import lịch thi thành công.',
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã có lỗi xảy ra trong quá trình import: ' . $e->getMessage(),
            ], 500);
        } finally {
            Storage::delete($filePath);
        }
    }

    /**
     * Get exam schedule for current lecturer (my schedule).
     */
    public function mySchedule()
    {
        $user = auth()->user();

        if (!$user || $user->role !== 'lecturer') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ giảng viên mới có thể xem lịch coi thi',
            ], 403);
        }

        $q = request()->query('q');
        $date = request()->query('date');
        $limit = (int) request()->query('limit', 10);

        $examSchedules = ExamSchedule::query()
            ->with(['subject', 'supervisors.lecturer'])
            ->whereHas('supervisors', function ($query) use ($user) {
                $query->where('lecturer_code', $user->lecturer_code);
            })
            ->orderBy('exam_date')
            ->orderBy('exam_time');

        if ($q) {
            $examSchedules->where(function ($query) use ($q) {
                $query->where('id', 'like', "%{$q}%")
                    ->orWhere('subject_code', 'like', "%{$q}%")
                    ->orWhere('room', 'like', "%{$q}%")
                    ->orWhereHas('subject', function ($subjectQuery) use ($q) {
                        $subjectQuery->where('name', 'like', "%{$q}%");
                    });
            });
        }

        if ($date) {
            try {
                $normalizedDate = Carbon::parse($date)->toDateString();
                $examSchedules->whereDate('exam_date', $normalizedDate);
            } catch (Throwable $e) {
                // If the provided date cannot be parsed, ignore the filter.
            }
        }

        return response()->json([
            'success' => true,
            'data' => $examSchedules->paginate($limit),
            'message' => 'Lịch coi thi của bạn',
        ]);
    }

    /**
     * Get current active exam schedule for lecturer (đến giờ thi).
     * Trả về ca thi đang diễn ra hoặc sắp diễn ra trong vòng 30 phút.
     */
    public function currentExam()
    {
        $user = auth()->user();

        if (!$user || $user->role !== 'lecturer') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ giảng viên mới có thể xem ca thi hiện tại',
            ], 403);
        }

        if (!$user->lecturer_code) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản chưa được liên kết với giảng viên',
            ], 400);
        }

        $now = \Carbon\Carbon::now();
        
        // Log để debug
        \Log::info('Current Exam Request', [
            'user_id' => $user->id,
            'lecturer_code' => $user->lecturer_code,
            'now' => $now->toDateTimeString(),
        ]);
        
        // Tìm ca thi của giảng viên:
        // - Đang diễn ra: exam_date + exam_time <= now < exam_date + exam_time + duration
        // - Hoặc sắp diễn ra trong 30 phút: exam_date + exam_time trong khoảng (now, now + 30 phút)
        $examSchedules = ExamSchedule::query()
            ->with(['subject', 'supervisors.lecturer'])
            ->whereHas('supervisors', function ($query) use ($user) {
                $query->where('lecturer_code', $user->lecturer_code);
            })
            ->whereDate('exam_date', $now->toDateString())
            ->get();

        \Log::info('Found exam schedules today', [
            'count' => $examSchedules->count(),
            'exams' => $examSchedules->map(function($e) {
                return [
                    'id' => $e->id,
                    'subject' => $e->subject_code,
                    'date' => $e->exam_date,
                    'time' => $e->exam_time,
                    'duration' => $e->duration,
                ];
            }),
        ]);

        $examSchedule = $examSchedules->filter(function ($exam) use ($now) {
                try {
                    $examDateTime = \Carbon\Carbon::parse($exam->exam_date . ' ' . $exam->exam_time);
                    $examEndTime = $examDateTime->copy()->addMinutes($exam->duration ?? 90);
                    
                    // Ca thi đang diễn ra
                    if ($now->between($examDateTime, $examEndTime)) {
                        \Log::info('Exam is ongoing', ['exam_id' => $exam->id]);
                        return true;
                    }
                    
                    // Ca thi sắp diễn ra trong 30 phút
                    $thirtyMinutesLater = $now->copy()->addMinutes(30);
                    if ($examDateTime->between($now, $thirtyMinutesLater)) {
                        \Log::info('Exam starting soon', ['exam_id' => $exam->id]);
                        return true;
                    }
                    
                    return false;
                } catch (\Exception $e) {
                    \Log::error('Error filtering exam', ['exam_id' => $exam->id, 'error' => $e->getMessage()]);
                    return false;
                }
            })
            ->sortBy(function ($exam) {
                return $exam->exam_date . ' ' . $exam->exam_time;
            })
            ->first();

        if (!$examSchedule) {
            return response()->json([
                'success' => false,
                'message' => 'Không có ca thi nào đang diễn ra hoặc sắp diễn ra',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $examSchedule,
            'message' => 'Ca thi hiện tại',
        ]);
    }
}
