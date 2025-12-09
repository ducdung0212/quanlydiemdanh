<?php

namespace App\Http\Controllers;

use App\Exports\MultiExamSchedulesExport;
use App\Http\Requests\ExamScheduleRequest;
use App\Imports\ExamSchedulesImport;
use App\Models\AttendanceRecord;
use App\Models\ExamSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
                Log::info('Received date filter: ' . $date);
                $normalizedDate = Carbon::parse($date)->format('Y-m-d');
                Log::info('Normalized date: ' . $normalizedDate);
                $examSchedules->whereDate('exam_date', $normalizedDate);
            } catch (Throwable $e) {
                Log::info('Date parse error: ' . $e->getMessage());
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
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ExamScheduleRequest $request)
    {
        try {
            $validated = $request->validated();
            
            $examSchedule = ExamSchedule::create($validated);

            return response()->json([
                'success' => true,
                'data' => $examSchedule->load('subject'),
                'message' => 'Tạo ca thi thành công',
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating exam schedule: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo ca thi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export attendance cho 1 ca thi
     */
    public function exportAttendance($id)
    {
        $examSchedule = ExamSchedule::with(['subject', 'attendanceRecords.student'])->find($id);

        if (!$examSchedule) {
            return response()->json([
                'success' => false,
                'message' => 'Exam Schedule not found',
            ], 404);
        }

        try {
            $fileName = 'Ca_Thi_' . $examSchedule->subject_code . '_' . 
                        Carbon::parse($examSchedule->exam_date)->format('Y-m-d') . '.xlsx';

            return Excel::download(
                new MultiExamSchedulesExport(collect([$examSchedule])), 
                $fileName
            );
        } catch (\Exception $e) {
            Log::error('Export exam schedule error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xuất file Excel: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export attendance cho nhiều ca thi (theo điều kiện lọc)
     */
    public function exportMultipleAttendance(Request $request)
    {
        $request->validate([
            'exam_schedule_ids' => 'required|array',
            'exam_schedule_ids.*' => 'exists:exam_schedules,id',
        ]);

        $examScheduleIds = $request->exam_schedule_ids;
        
        $examSchedules = ExamSchedule::with(['subject', 'attendanceRecords.student'])
            ->whereIn('id', $examScheduleIds)
            ->orderBy('exam_date')
            ->orderBy('exam_time')
            ->get();

        if ($examSchedules->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy ca thi nào',
            ], 404);
        }

        try {
            $fileName = 'Danh_Sach_Ca_Thi_' . Carbon::now()->format('Y-m-d_His') . '.xlsx';

            return Excel::download(
                new MultiExamSchedulesExport($examSchedules), 
                $fileName
            );
        } catch (\Exception $e) {
            Log::error('Export multiple exam schedules error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xuất file Excel: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export tất cả ca thi theo ngày
     */
    public function exportByDate(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $date = $request->date;
        
        $examSchedules = ExamSchedule::with(['subject', 'attendanceRecords.student'])
            ->whereDate('exam_date', $date)
            ->orderBy('exam_time')
            ->get();

        if ($examSchedules->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Không có ca thi nào trong ngày này',
            ], 404);
        }

        try {
            $dateFormatted = Carbon::parse($date)->format('d-m-Y');
            $fileName = 'Ca_Thi_Ngay_' . $dateFormatted . '.xlsx';

            return Excel::download(
                new MultiExamSchedulesExport($examSchedules), 
                $fileName
            );
        } catch (\Exception $e) {
            Log::error('Export exam schedules by date error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xuất file Excel: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     * UPDATE: Đã tối ưu hóa và thêm phân trang
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
        }

        // 1. Tính toán thời gian & Trạng thái ca thi
        $examEndTime = null;
        $examStartTime = null;
        $canAttend = false;

        try {
            $examDate = \Carbon\Carbon::parse($examSchedule->exam_date)->format('Y-m-d');
            $examTime = is_string($examSchedule->exam_time) ? $examSchedule->exam_time : $examSchedule->exam_time->format('H:i:s');
            $examDateTimeString = $examDate . ' ' . $examTime;

            $examStartTime = \Carbon\Carbon::parse($examDateTimeString);
            $examEndTime = $examStartTime->copy()->addMinutes($examSchedule->duration ?? 0);
            $now = \Carbon\Carbon::now();

            // Cho phép điểm danh từ khi bắt đầu đến khi kết thúc ca thi
            $canAttend = $now->greaterThanOrEqualTo($examStartTime) && $now->lessThanOrEqualTo($examEndTime);
        } catch (\Exception $e) {
            Log::error('Error calculating attendance time', [
                'exam_id' => $examSchedule->id,
                'error' => $e->getMessage(),
            ]);
            $canAttend = false;
        }

        $now = \Carbon\Carbon::now();
        $isExamEnded = $examEndTime && $now->isAfter($examEndTime);

        // 2. Tính toán thống kê (Stats) trên TOÀN BỘ danh sách (không phân trang)
        // Đây là phần tối ưu: Dùng database count thay vì loop PHP
        $baseQuery = AttendanceRecord::where('exam_schedule_id', $examSchedule->id);
        
        $total = $baseQuery->count();
        $present = (clone $baseQuery)->where('rekognition_result', 'match')->count();
        
        if ($isExamEnded) {
            // Đã kết thúc: Những ai chưa match thì tính là vắng
            $absent = $total - $present;
            $pending = 0; 
        } else {
            // Chưa kết thúc: Null là pending
            $pending = (clone $baseQuery)->whereNull('rekognition_result')->count();
            // Absent là những người có kết quả nhưng không phải match (ví dụ: not_match)
            $absent = (clone $baseQuery)->whereNotNull('rekognition_result')->where('rekognition_result', '!=', 'match')->count();
        }

        // 3. Lấy dữ liệu sinh viên có phân trang (Pagination)
        $limit = request()->query('limit', 10); // Mặc định 10 dòng mỗi trang
        $paginatedRecords = AttendanceRecord::with('student')
            ->where('exam_schedule_id', $examSchedule->id)
            // Sắp xếp: Ưu tiên người mới điểm danh lên đầu, sau đó theo mã sinh viên
            ->orderByRaw('attendance_time DESC, student_code ASC') 
            ->paginate($limit);

        // Transform dữ liệu cho trang hiện tại
        $studentsData = $paginatedRecords->getCollection()->map(function ($record) {
            return [
                'id' => $record->id,
                'student_code' => $record->student_code,
                'full_name' => optional($record->student)->full_name,
                'class_code' => optional($record->student)->class_code,
                'attendance_time' => optional($record->attendance_time)?->toDateTimeString(),
                'rekognition_result' => $record->rekognition_result,
            ];
        });

        // Gán lại collection đã transform vào paginator
        $paginatedRecords->setCollection($studentsData);

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
                    'can_attend' => $canAttend,
                ],
                'stats' => [
                    'total_students' => $total,
                    'present' => $present,
                    'pending' => $pending,
                    'absent' => $absent,
                ],
                'students' => $paginatedRecords, // Trả về object phân trang đầy đủ
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
    public function update(ExamScheduleRequest $request, string $id)
    {
        try {
            $examSchedule = ExamSchedule::find($id);

            if (!$examSchedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy ca thi',
                ], 404);
            }

            $validated = $request->validated();
            $examSchedule->update($validated);

            return response()->json([
                'success' => true,
                'data' => $examSchedule->load('subject'),
                'message' => 'Cập nhật ca thi thành công',
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating exam schedule: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật ca thi: ' . $e->getMessage(),
            ], 500);
        }
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

                $values = array_map(static fn($value) => trim((string) $value), array_values($row));
                $nonEmpty = array_values(array_filter($values, static fn($value) => $value !== ''));

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

        // Lấy thông tin giảng viên qua relationship
        $lecturer = $user->lecturer;

        if (!$lecturer) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản chưa được liên kết với giảng viên',
            ], 400);
        }

        $q = request()->query('q');
        $date = request()->query('date');
        $limit = (int) request()->query('limit', 10);

        $examSchedules = ExamSchedule::query()
            ->with(['subject', 'supervisors.lecturer'])
            ->whereHas('supervisors', function ($query) use ($lecturer) {
                $query->where('lecturer_code', $lecturer->lecturer_code);
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
     * Lấy tất cả ca thi trong ngày của giảng viên
     */
    public function todayExams()
    {
        $user = auth()->user();

        if (!$user || $user->role !== 'lecturer') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ giảng viên mới có thể xem ca thi',
            ], 403);
        }

        // Lấy thông tin giảng viên qua relationship
        $lecturer = $user->lecturer;

        if (!$lecturer) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản chưa được liên kết với giảng viên',
            ], 400);
        }

        $now = \Carbon\Carbon::now();

        // Lấy TẤT CẢ ca thi của giảng viên trong ngày hôm nay
        $examSchedules = ExamSchedule::query()
            ->with(['subject', 'supervisors.lecturer'])
            ->whereHas('supervisors', function ($query) use ($lecturer) {
                $query->where('lecturer_code', $lecturer->lecturer_code);
            })
            ->whereDate('exam_date', $now->toDateString())
            ->orderBy('exam_time', 'asc')
            ->get();

        if ($examSchedules->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Hôm nay bạn không có ca thi nào',
                'data' => [],
            ], 200); // Trả về 200 OK nhưng message thông báo không có ca thi
        }

        // Format dữ liệu trả về
        $examsData = $examSchedules->map(function ($exam) use ($now) {
            $examDateTime = null;
            $examEndTime = null;
            $status = 'upcoming'; // upcoming, ongoing, finished

            try {
                $examDate = \Carbon\Carbon::parse($exam->exam_date)->format('Y-m-d');
                $examTime = is_string($exam->exam_time) ? $exam->exam_time : $exam->exam_time->format('H:i:s');
                $examDateTimeString = $examDate . ' ' . $examTime;

                $examDateTime = \Carbon\Carbon::parse($examDateTimeString);
                $examEndTime = $examDateTime->copy()->addMinutes($exam->duration ?? 90);

                if ($now->greaterThanOrEqualTo($examDateTime) && $now->lessThan($examEndTime)) {
                    $status = 'ongoing';
                } elseif ($now->greaterThanOrEqualTo($examEndTime)) {
                    $status = 'finished';
                }
            } catch (\Exception $e) {
                // Nếu không parse được thời gian
            }

            return [
                'id' => $exam->id,
                'session_code' => $exam->session_code ?? $exam->id,
                'subject_code' => $exam->subject_code,
                'subject_name' => optional($exam->subject)->name,
                'exam_date' => optional($exam->exam_date)?->toDateString() ?? (string) $exam->exam_date,
                'exam_time' => optional($exam->exam_time)?->format('H:i:s') ?? (string) $exam->exam_time,
                'duration' => $exam->duration,
                'room' => $exam->room,
                'status' => $status,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $examsData,
            'message' => 'Danh sách ca thi hôm nay',
            'count' => $examSchedules->count(),
        ]);
    }

    public function currentExam()
    {
        $user = auth()->user();

        if (!$user || $user->role !== 'lecturer') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ giảng viên mới có thể xem ca thi hiện tại',
            ], 403);
        }

        // Lấy thông tin giảng viên qua relationship
        $lecturer = $user->lecturer;

        if (!$lecturer) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản chưa được liên kết với giảng viên',
            ], 400);
        }

        $now = \Carbon\Carbon::now();

        // Tìm ca thi của giảng viên
        $examSchedules = ExamSchedule::query()
            ->with(['subject', 'supervisors.lecturer'])
            ->whereHas('supervisors', function ($query) use ($lecturer) {
                $query->where('lecturer_code', $lecturer->lecturer_code);
            })
            ->whereDate('exam_date', $now->toDateString())
            ->get();

        $examSchedule = $examSchedules->filter(function ($exam) use ($now) {
            try {
                $examDate = \Carbon\Carbon::parse($exam->exam_date)->format('Y-m-d');
                $examTime = is_string($exam->exam_time) ? $exam->exam_time : $exam->exam_time->format('H:i:s');
                $examDateTimeString = $examDate . ' ' . $examTime;

                $examDateTime = \Carbon\Carbon::parse($examDateTimeString);
                $examEndTime = $examDateTime->copy()->addMinutes($exam->duration ?? 90);

                // Ca thi đang diễn ra
                if ($now->between($examDateTime, $examEndTime)) {
                    return true;
                }

                // Ca thi sắp diễn ra trong 30 phút
                $thirtyMinutesLater = $now->copy()->addMinutes(30);
                if ($examDateTime->between($now, $thirtyMinutesLater)) {
                    return true;
                }

                return false;
            } catch (\Exception $e) {
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

    /**
     * Lấy danh sách sinh viên tham gia ca thi
     */
    public function getStudents($id)
    {
        $examSchedule = ExamSchedule::find($id);
        if (!$examSchedule) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy ca thi',
            ], 404);
        }

        $students = AttendanceRecord::where('exam_schedule_id', $id)
            ->with('student:student_code,full_name,class_code')
            ->get()
            ->map(function ($record) {
                return [
                    'student_code' => $record->student_code,
                    'full_name' => $record->student->full_name ?? '',
                    'class_code' => $record->student->class_code ?? '',
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $students,
        ]);
    }

    /**
     * Thêm sinh viên vào ca thi
     */
    public function addStudent(Request $request, $id)
    {
        $request->validate([
            'student_code' => 'required|string',
        ]);

        $examSchedule = ExamSchedule::find($id);
        if (!$examSchedule) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy ca thi',
            ], 404);
        }

        // Kiểm tra sinh viên có tồn tại
        $student = \App\Models\Student::where('student_code', $request->student_code)->first();
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sinh viên với mã: ' . $request->student_code,
            ], 404);
        }

        // Kiểm tra sinh viên đã tham gia ca thi này chưa
        $exists = AttendanceRecord::where('exam_schedule_id', $id)
            ->where('student_code', $request->student_code)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Sinh viên đã tham gia ca thi này',
            ], 400);
        }

        // Kiểm tra sinh viên có tham gia ca thi khác cùng thời gian không
        $conflictExam = AttendanceRecord::whereHas('examSchedule', function ($query) use ($examSchedule) {
            $query->where('exam_date', $examSchedule->exam_date)
                ->where('exam_time', $examSchedule->exam_time);
        })
            ->where('student_code', $request->student_code)
            ->where('exam_schedule_id', '!=', $id)
            ->exists();

        if ($conflictExam) {
            return response()->json([
                'success' => false,
                'message' => 'Sinh viên đã có ca thi khác cùng thời gian',
            ], 400);
        }

        // Thêm sinh viên
        AttendanceRecord::create([
            'exam_schedule_id' => $id,
            'student_code' => $request->student_code,
            'rekognition_result' => null,
            'attendance_time' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã thêm sinh viên vào ca thi',
        ]);
    }

    /**
     * Xóa sinh viên khỏi ca thi
     */
    public function removeStudent($id, $student_code)
    {
        $deleted = AttendanceRecord::where('exam_schedule_id', $id)
            ->where('student_code', $student_code)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sinh viên trong ca thi này',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa sinh viên khỏi ca thi',
        ]);
    }

    /**
     * Lấy danh sách giám thị ca thi
     */
    public function getSupervisors($id)
    {
        $examSchedule = ExamSchedule::find($id);
        if (!$examSchedule) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy ca thi',
            ], 404);
        }

        $supervisors = \App\Models\ExamSupervisor::where('exam_schedule_id', $id)
            ->with('lecturer:lecturer_code,full_name')
            ->get()
            ->map(function ($supervisor) {
                return [
                    'lecturer_code' => $supervisor->lecturer_code,
                    'full_name' => $supervisor->getLecturerName() ?? '',
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $supervisors,
        ]);
    }

    /**
     * Thêm giám thị vào ca thi
     */
    public function addSupervisor(Request $request, $id)
    {
        $request->validate([
            'lecturer_code' => 'required|string',
        ]);

        $examSchedule = ExamSchedule::find($id);
        if (!$examSchedule) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy ca thi',
            ], 404);
        }

        // Kiểm tra giảng viên có tồn tại
        $lecturer = \App\Models\Lecturer::where('lecturer_code', $request->lecturer_code)->first();
        if (!$lecturer) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy giảng viên với mã: ' . $request->lecturer_code,
            ], 404);
        }

        // Kiểm tra giảng viên đã là giám thị ca thi này chưa
        $exists = \App\Models\ExamSupervisor::where('exam_schedule_id', $id)
            ->where('lecturer_code', $request->lecturer_code)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Giảng viên đã là giám thị ca thi này',
            ], 400);
        }

        // Kiểm tra giảng viên có coi thi ca khác cùng thời gian không
        $conflictExam = \App\Models\ExamSupervisor::whereHas('examSchedule', function ($query) use ($examSchedule) {
            $query->where('exam_date', $examSchedule->exam_date)
                ->where('exam_time', $examSchedule->exam_time);
        })
            ->where('lecturer_code', $request->lecturer_code)
            ->where('exam_schedule_id', '!=', $id)
            ->exists();

        if ($conflictExam) {
            return response()->json([
                'success' => false,
                'message' => 'Giảng viên đã coi thi ca khác cùng thời gian',
            ], 400);
        }

        // Thêm giám thị
        \App\Models\ExamSupervisor::create([
            'exam_schedule_id' => $id,
            'lecturer_code' => $request->lecturer_code,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã thêm giám thị vào ca thi',
        ]);
    }

    /**
     * Xóa giám thị khỏi ca thi
     */
    public function removeSupervisor($id, $supervisor_id)
    {
        $supervisor = \App\Models\ExamSupervisor::where('id', $supervisor_id)
            ->where('exam_schedule_id', $id)
            ->first();

        if (!$supervisor) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy giám thị trong ca thi này',
            ], 404);
        }

        $supervisor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa giám thị khỏi ca thi',
        ]);
    }
}