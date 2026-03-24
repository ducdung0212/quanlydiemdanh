<?php

namespace App\Http\Controllers;

use App\Imports\UsersImport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class UserImportController extends Controller
{
    public function previewImport(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls'
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
                'email',
                'mail',
                'e-mail',
                'ten',
                'ho-va-ten',
                'name',
                'mat-khau',
                'password',
                'mssv',
                'ma-sinh-vien',
                'student-code',
                'student-code',
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
                    return trim($slug, '-');
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
        } catch (\Throwable $e) {
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
            'mapping.email' => 'required|string',
            'mapping.name' => 'nullable|string',
            'mapping.password' => 'nullable|string',
            'mapping.student_code' => 'nullable|string',
            'role' => 'required|in:admin,lecturer,student',
            'use_password_column' => 'boolean',
            'default_password' => 'nullable|string|min:6',
        ]);

        $usePasswordColumn = $validated['use_password_column'] ?? false;
        $defaultPassword = $validated['default_password'] ?? null;

        if (!$usePasswordColumn && empty($defaultPassword)) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng nhập mật khẩu mặc định hoặc chọn sử dụng cột mật khẩu từ file.',
            ], 422);
        }

        if ($usePasswordColumn && empty($validated['mapping']['password'])) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã chọn mật khẩu theo cột nên cần map trường mật khẩu.',
            ], 422);
        }

        if ($validated['role'] === 'student' && empty($validated['mapping']['student_code'])) {
            return response()->json([
                'success' => false,
                'message' => 'Role sinh viên yêu cầu map trường mã sinh viên.',
            ], 422);
        }

        $filePath = $validated['token'];
        $headingRow = (int) ($validated['heading_row'] ?? 1);

        if (!Storage::exists($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'File tạm không tồn tại hoặc đã hết hạn.',
            ], 410);
        }

        try {
            $usersBefore = User::count();

            $import = new UsersImport(
                $validated['mapping'],
                $validated['role'],
                $defaultPassword,
                $usePasswordColumn,
                $headingRow
            );

            Excel::import($import, Storage::path($filePath));

            $usersAfter = User::count();
            $importedCount = $usersAfter - $usersBefore;

            return response()->json([
                'success' => true,
                'message' => "Import thành công! Đã thêm {$importedCount} người dùng mới.",
                'imported_count' => $importedCount,
            ]);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];
            foreach ($failures as $failure) {
                $errorMessages[] = 'Dòng ' . $failure->row() . ': ' . implode(', ', $failure->errors());
            }

            return response()->json([
                'success' => false,
                'message' => 'Lỗi dữ liệu: ' . implode('; ', $errorMessages),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã có lỗi xảy ra trong quá trình import: ' . $e->getMessage(),
            ], 500);
        } finally {
            Storage::delete($filePath);
        }
    }
}
