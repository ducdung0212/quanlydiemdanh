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
    public function previewImport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $file = $validated['excel_file'];
        $fileName = 'imports/tmp/' . uniqid('attendance_record_') . '.' . $file->getClientOriginalExtension();

        Storage::putFileAs('', $file, $fileName);

        try {
            $spreadsheet = IOFactory::load(Storage::path($fileName));
            $worksheet = $spreadsheet->getActiveSheet();
            $headings = [];
            $headingRow = 1;

            foreach ($worksheet->getRowIterator() as $rowIdx => $row) {
                $rowData = [];
                foreach ($row->getCellIterator() as $cell) {
                    $rowData[] = $cell->getValue();
                }

                if (!empty(array_filter($rowData))) {
                    $headings = $rowData;
                    $headingRow = $rowIdx;
                    break;
                }
            }

            $columnHeadings = array_map(fn ($h) => Str::slug((string) $h, '_'), $headings);

            return response()->json([
                'success' => true,
                'token' => $fileName,
                'heading_row' => $headingRow,
                'headings' => $columnHeadings,
            ]);
        } catch (Throwable $e) {
            Storage::delete($fileName);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi đọc file: ' . $e->getMessage(),
            ], 422);
        }
    }

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

    public function destroy(AttendanceRecord $attendanceRecord): JsonResponse
    {
        $attendanceRecord->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa bản ghi điểm danh.',
        ]);
    }

    public function destroyAll(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'exam_schedule_id' => 'nullable|integer|exists:exam_schedules,id',
            'student_code' => 'nullable|string|exists:students,student_code',
            'delete_all' => 'nullable|boolean',
        ]);

        $query = AttendanceRecord::query();

        if (isset($validated['exam_schedule_id'])) {
            $query->where('exam_schedule_id', $validated['exam_schedule_id']);
        }

        if (isset($validated['student_code'])) {
            $query->where('student_code', $validated['student_code']);
        }

        if (!isset($validated['exam_schedule_id']) && !isset($validated['student_code'])) {
            if (empty($validated['delete_all'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng xác nhận delete_all hoặc cung cấp điều kiện lọc.',
                ], 422);
            }
        }

        $deleted = $query->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa ' . $deleted . ' bản ghi điểm danh.',
            'deleted' => $deleted,
        ]);
    }
}
