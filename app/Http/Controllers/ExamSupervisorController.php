<?php

namespace App\Http\Controllers;

use App\Imports\ExamSupervisorImport;
use App\Models\ExamSupervisor;
use App\Http\Requests\SupervisorRequest;
use Illuminate\Validation\ValidationException;
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
    public function index()
    {
        $q = request()->query('q');
        $date = request()->query('date');
        $limit = (int) request()->query('limit', 10);

        $examSupervisors = ExamSupervisor::with(['lecturer', 'examSchedule.subject'])->latest();

        if ($q) {
            $examSupervisors->where(function ($query) use ($q) {
                // Tìm theo mã giảng viên
                $query->where('lecturer_code', 'like', "%{$q}%")
                    // Tìm theo tên giảng viên
                    ->orWhereHas('lecturer', function ($lecturerQuery) use ($q) {
                        $lecturerQuery->where('full_name', 'like', "%{$q}%");
                    })
                    // Tìm theo mã ca thi
                    ->orWhere('exam_schedule_id', 'like', "%{$q}%")
                    // Tìm theo phòng thi
                    ->orWhereHas('examSchedule', function ($examScheduleQuery) use ($q) {
                        $examScheduleQuery->where('room', 'like', "%{$q}%");
                    })
                    // Tìm theo mã môn học
                    ->orWhereHas('examSchedule', function ($examScheduleQuery) use ($q) {
                        $examScheduleQuery->where('subject_code', 'like', "%{$q}%");
                    })
                    // Tìm theo tên môn học
                    ->orWhereHas('examSchedule.subject', function ($subjectQuery) use ($q) {
                        $subjectQuery->where('name', 'like', "%{$q}%");
                    })
                    // Tìm theo giờ thi
                    ->orWhereHas('examSchedule', function ($examScheduleQuery) use ($q) {
                        $examScheduleQuery->where('exam_time', 'like', "%{$q}%");
                    })
                    // Tìm theo ngày thi
                    ->orWhereHas('examSchedule', function ($examScheduleQuery) use ($q) {
                        $examScheduleQuery->where('exam_date', 'like', "%{$q}%");
                    });
            });
        }

        if ($date) {
            try {
                $normalizedDate = \Carbon\Carbon::parse($date)->toDateString();
                $examSupervisors->whereHas('examSchedule', function ($examScheduleQuery) use ($normalizedDate) {
                    $examScheduleQuery->whereDate('exam_date', $normalizedDate);
                });
            } catch (\Throwable $e) {
            }
        }

        return response()->json([
            'success' => true,
            'data' => $examSupervisors->paginate($limit),
            'message' => 'List Exam Supervisor',
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
                'lecturer',
                'lecturer-code',
                'lecturer_code',
                'ma-giang-vien',
                'role',
                'note',
                'ghi-chu',
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

    /**
     * Import supervisors using mapping and heading row.
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
            'mapping.lecturer_code' => 'required|string',
            'mapping.role' => 'nullable|string',
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
                new ExamSupervisorImport($validated['mapping'], $headingRow),
                Storage::path($filePath)
            );

            return response()->json([
                'success' => true,
                'message' => 'Import giám thị thành công.',
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
    public function destroy(string $id)
    {
        $examSupervisor = ExamSupervisor::find($id);

        if (!$examSupervisor) {
            return response()->json([
                'success' => false,
                'message' => 'Exam Supervisor not found',
            ], 404);
        }

        $examSupervisor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Exam Supervisor deleted successfully',
        ]);
    }
    public function update(SupervisorRequest $request, string $id)
    {
        try {
            $supervisor = ExamSupervisor::find($id);
            if (!$supervisor) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'Exam Supervisor Not Found'
                ], 404);
            }

            if ($request->filled('lecturer_code')) {
                $supervisor->lecturer_code = $request->input('lecturer_code');
            }

            $supervisor->save();
            return response()->json([
                'success' => true,
                'data' => $supervisor,
                'message' => 'Update Supervisor Successfully'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        }
    }
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'Không có giám thị nào được chọn',
            ], 400);
        }

        ExamSupervisor::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa ' . count($ids) . ' giám thị thành công.',
        ]);
    }
}
