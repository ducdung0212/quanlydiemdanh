<?php

namespace App\Http\Controllers;

use App\Imports\ExamSupervisorImport;
use App\Models\ExamSupervisor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class ExamSupervisorController extends Controller
{
    public function previewImport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $file = $validated['excel_file'];
        $fileName = 'imports/tmp/' . uniqid('exam_supervisor_') . '.' . $file->getClientOriginalExtension();

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
            'mapping.lecturer_code' => 'required|string',
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
                new ExamSupervisorImport($validated['mapping'], $headingRow),
                Storage::path($filePath)
            );

            return response()->json([
                'success' => true,
                'message' => 'Import danh sách phân công giám thị thành công.',
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

    public function destroy(ExamSupervisor $examSupervisor): JsonResponse
    {
        $examSupervisor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa phân công giám thị.',
        ]);
    }

    public function destroyAll(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'exam_schedule_id' => 'nullable|integer|exists:exam_schedules,id',
            'lecturer_code' => 'nullable|string|exists:lecturers,lecturer_code',
            'delete_all' => 'nullable|boolean',
        ]);

        $query = ExamSupervisor::query();

        if (isset($validated['exam_schedule_id'])) {
            $query->where('exam_schedule_id', $validated['exam_schedule_id']);
        }

        if (isset($validated['lecturer_code'])) {
            $query->where('lecturer_code', $validated['lecturer_code']);
        }

        if (!isset($validated['exam_schedule_id']) && !isset($validated['lecturer_code'])) {
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
            'message' => 'Đã xóa ' . $deleted . ' bản ghi phân công giám thị.',
            'deleted' => $deleted,
        ]);
    }
}
