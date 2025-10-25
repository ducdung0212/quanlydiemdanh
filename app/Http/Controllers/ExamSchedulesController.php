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

        $examSchedules = ExamSchedule::query()
            ->with('subject')
            ->latest();

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
        $examSchedule = ExamSchedule::with('subject')->find($id);

        if (!$examSchedule) {
            return response()->json([
                'success' => false,
                'message' => 'Exam Schedule not found',
            ], 404);
        }

        $attendanceRecords = AttendanceRecord::with('student')
            ->where('exam_schedule_id', $examSchedule->id)
            ->get();

        $examStart = null;

        if (!empty($examSchedule->exam_date) && !empty($examSchedule->exam_time)) {
            // Format date and time properly before parsing
            $dateStr = $examSchedule->exam_date instanceof Carbon 
                ? $examSchedule->exam_date->format('Y-m-d') 
                : $examSchedule->exam_date;
            $timeStr = $examSchedule->exam_time instanceof Carbon 
                ? $examSchedule->exam_time->format('H:i:s') 
                : $examSchedule->exam_time;
            $examStart = Carbon::parse($dateStr . ' ' . $timeStr);
        }

        $students = [];
        $present = 0;
        $late = 0;

        foreach ($attendanceRecords as $record) {
            $student = $record->student;

            $status = 'absent';
            $attendanceTime = null;

            if ($record->attendance_time) {
                $attendanceTime = optional($record->attendance_time)?->toDateTimeString();
                $attendanceMoment = $record->attendance_time instanceof Carbon
                    ? $record->attendance_time
                    : Carbon::parse($record->attendance_time);

                if ($attendanceMoment && $examStart && $attendanceMoment->gt($examStart)) {
                    $status = 'late';
                    $late++;
                } elseif ($record->rekognition_result === 'match') {
                    $status = 'present';
                    $present++;
                } elseif ($record->rekognition_result === 'unknown') {
                    $status = 'late';
                    $late++;
                } else {
                    $status = 'absent';
                }
            } elseif ($record->rekognition_result === 'match') {
                $status = 'present';
                $present++;
            } elseif ($record->rekognition_result === 'unknown') {
                $status = 'late';
                $late++;
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
        $absent = max($total - $present - $late, 0);

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
                    'room' => $examSchedule->room,
                    'note' => $examSchedule->note,
                    'registered_count' => $examSchedule->registered_count,
                    'attended_count' => $examSchedule->attended_count,
                    'attendance_rate' => $examSchedule->attendance_rate,
                ],
                'stats' => [
                    'total_students' => $total,
                    'present' => $present,
                    'late' => $late,
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
            'mapping.room' => 'nullable|string',
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
}
