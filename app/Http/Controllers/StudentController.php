<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Requests\StudentRequest;
use App\Imports\StudentsImport;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $q=request()->query('q');
        $limit=request()->query('limit',10);
        $students=Student::latest();
        if($q){
            $students->where(function($query) use ($q){
                $query->where('student_code','LIKE',"%{$q}%")
                      ->orWhere('full_name','LIKE',"%{$q}%")
                      ->orWhere('email','LIKE',"%{$q}%")
                      ->orWhere('phone','LIKE',"%{$q}%")
                      ->orWhere('class_code','LIKE',"%{$q}%");
            });
        }
        return response()->json([
            'success' => true,
            'data' => $students->paginate($limit),
            'message'=>'List Students'
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
    public function store(StudentRequest $request)
    {
        try {
            $student =new Student;
            $student->fill($request->all());
            $student->save();
            return response()->json([
                'success' => true,
                'data' => $student,
                'message'=>'Create Student Successfully'
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($student_code)
    {
        $student=Student::find($student_code);
        if(!$student){
            return response()->json([
                'success' => false,
                'data' => null,
                'message'=>'Student Not Found'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $student,
            'message'=>'Detail Student'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StudentRequest $request,string $student_code)
    {
        try {
            $student=Student::find($student_code);
            if(!$student){
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message'=>'Student Not Found'
                ], 404);
            }
            if($request->student_code){
                $student->student_code=$request->student_code;
            }
            if($request->full_name){
                $student->full_name=$request->full_name;
            }
            if($request->class_code){
                $student->class_code=$request->class_code;
            }
            if($request->email){
                $student->email=$request->email;
            }
            if($request->phone){
                $student->phone=$request->phone;
            }   
            $student->save();
            return response()->json([
                'success' => true,
                'data' => $student,
                'message'=>'Update Student Successfully'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $student_code)
    {
        $student=Student::find($student_code);
        if(!$student){
        return response()->json([
            'success' => false,
            'message'=>'Student Not Found'
        ],
        404);
        }
        $student->delete();
         return response()->json([
            'success' => true,
            'message'=>'Delete Student Successfully'
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $studentCodes = $request->input('student_codes', []);
        if (empty($studentCodes)) {
            return response()->json(['success' => false, 'message' => 'Không có sinh viên nào được chọn'], 400);
        }

        Student::whereIn('student_code', $studentCodes)->delete();

        return response()->json(['success' => true, 'message' => 'Đã xóa ' . count($studentCodes) . ' sinh viên thành công.']);
    }

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
                'mssv',
                'ma-sv',
                'ma-sinh-vien',
                'ma-so-sinh-vien',
                'student-code',
                'ho-va-ten',
                'ho-ten',
                'ho-va-ten-day-du',
                'lop',
                'lop-hoc',
                'class-code',
                'nhom',
                'gvhd',
                'giang-vien',
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

                $values = array_map(static fn ($value) => trim((string) $value), array_values($row));
                $nonEmpty = array_values(array_filter($values, static fn ($value) => $value !== ''));

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
            'mapping.student_code' => 'required|string',
            'mapping.class_code' => 'required|string',
            'mapping.full_name' => 'required|string',
            'mapping.email' => 'nullable|string',
            'mapping.phone' => 'nullable|string',
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
                new StudentsImport($validated['mapping'], $headingRow),
                Storage::path($filePath)
            );

            return response()->json([
                'success' => true,
                'message' => 'Import sinh viên thành công.',
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
