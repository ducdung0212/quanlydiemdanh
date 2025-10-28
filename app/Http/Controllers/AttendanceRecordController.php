<?php

namespace App\Http\Controllers;

use App\Imports\AttendanceRecordImport;
use App\Models\AttendanceRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class AttendanceRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $q = request()->query('q');
        $session = request()->query('session');
        $limit = (int) request()->query('limit', 10);
        $user = auth()->user();

        $attendanceRecords = AttendanceRecord::with('student')->latest();

        // Nếu là lecturer, chỉ xem điểm danh của ca thi được phân công
        if ($user && $user->role === 'lecturer') {
            $attendanceRecords->whereHas('examSchedule.supervisors', function ($query) use ($user) {
                $query->where('lecturer_code', $user->lecturer_code);
            });
        }

        if ($q) {
            $attendanceRecords->where(function ($query) use ($q) {
                $query->where('student_code', 'like', "%{$q}%")
                    ->orWhereHas('student', function ($studentQuery) use ($q) {
                        $studentQuery->where('full_name', 'like', "%{$q}%");
                    })
                    ->orWhereHas('examSchedule', function ($examScheduleQuery) use ($q) {
                        $examScheduleQuery->where('room', 'like', "%{$q}%")
                            ->orWhere('subject_code', 'like', "%{$q}%");
                    });
            });
        }

        if ($session) {
            $attendanceRecords->where('exam_schedule_id', $session);
        }

        return response()->json([
            'success' => true,
            'data' => $attendanceRecords->paginate($limit),
            'message' => 'List Attendance Records',
        ]);
    }

    /**
     * Preview import: upload file and detect headings + heading row.
     */
    public function previewImport(Request $request): JsonResponse
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
                'subject', 'subject-code', 'subject_code', 'ma-mon', 'ma-hoc-phan',
                'exam-date', 'exam-time', 'exam_date', 'exam_time',
                'room', 'phong',
                'student', 'student-code', 'student_code', 'ma-sinh-vien',
                'attendance-time', 'attendance_time', 'thoi-gian',
                'captured-image', 'captured_image_url',
                'rekognition-result', 'rekognition_result',
                'confidence', 'do-tin-cay',
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

    /**
     * Import attendance records using mapping and heading row.
     */
    public function import(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'heading_row' => 'nullable|integer|min:1',
            'mapping' => 'required|array',
            'mapping.subject_code' => 'required|string',
            'mapping.exam_date' => 'required|string',
            'mapping.exam_time' => 'required|string',
            'mapping.room' => 'required|string',
            'mapping.student_code' => 'required|string',
            'mapping.attendance_time' => 'nullable|string',
            'mapping.captured_image_url' => 'nullable|string',
            'mapping.rekognition_result' => 'nullable|string',
            'mapping.confidence' => 'nullable|string',
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
                new AttendanceRecordImport($validated['mapping'], $headingRow),
                Storage::path($filePath)
            );

            return response()->json([
                'success' => true,
                'message' => 'Import điểm danh thành công.',
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        $user = auth()->user();
        $attendanceRecord = AttendanceRecord::with('examSchedule')->find($id);

        if (!$attendanceRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance Record not found',
            ], 404);
        }

        // Nếu là lecturer, kiểm tra quyền sửa
        if ($user && $user->role === 'lecturer') {
            $hasAccess = $attendanceRecord->examSchedule->supervisors()
                ->where('lecturer_code', $user->lecturer_code)
                ->exists();
            
            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền sửa điểm danh ca thi này',
                ], 403);
            }
        }

        $validated = $request->validate([
            'attendance_time' => 'nullable|date',
            'captured_image_url' => 'nullable|string',
            'rekognition_result' => 'nullable|string',
            'confidence' => 'nullable|numeric',
        ]);

        $attendanceRecord->update($validated);

        return response()->json([
            'success' => true,
            'data' => $attendanceRecord->fresh(),
            'message' => 'Attendance Record updated successfully',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $attendanceRecord = AttendanceRecord::find($id);

        if (!$attendanceRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance Record not found',
            ], 404);
        }

        $attendanceRecord->delete();

        return response()->json([
            'success' => true,
            'message' => 'Attendance Record deleted successfully',
        ]);
    }

    /**
     * Remove multiple attendance records.
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('record_ids', []);

        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'Không có bản ghi nào được chọn',
            ], 400);
        }

        AttendanceRecord::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa ' . count($ids) . ' bản ghi thành công.',
        ]);
    }
}
