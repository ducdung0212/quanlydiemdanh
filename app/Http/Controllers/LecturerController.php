<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lecturer;
use App\Http\Requests\LecturerRequest;
use App\Imports\LecturersImport;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class LecturerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $q = request()->query('q');
        $limit = (int) request()->query('limit', 10);

        $lecturers = Lecturer::query()->latest();

        if ($q) {
            $lecturers->where(function ($query) use ($q) {
                $query->where('full_name', 'like', "%{$q}%")
                    ->orWhere('lecturer_code', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('faculty_code', 'like', "%{$q}%");
            });
        }

        return response()->json([
            'success' => true,
            'data' => $lecturers->paginate($limit),
            'message' => 'List Lecturers',
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
    public function store(LecturerRequest $request)
    {
        try{
            $lecturer=new Lecturer();
            $lecturer->fill($request->all());
            $lecturer->save();
            return response()->json([
                'success'=>true,
                'data'=>$lecturer,
                'message'=>'Lecturer created successfully'
            ], 201);
        } catch(ValidationException $e){
            return response()->json([
                'success'=>false,
                'errors'=>$e->errors()
            ],422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($lecturer_code)
    {
        $lecturer=Lecturer::find($lecturer_code);
        if(!$lecturer){
            return response()->json([
                'success'=>false,
                'data'=>null,
                'message'=>'Lecturer Not Found'
            ],404);
        }
        return response()->json([
            'success'=>true,
            'data'=>$lecturer,
            'message'=>'Detail Lecturer'
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
    public function update(LecturerRequest $request, string $lecturer_code)
    {
        try{
            $lecturer=Lecturer::find($lecturer_code);
            if(!$lecturer){
                return response()->json([
                    'success'=>false,
                    'data'=>null,
                    'message'=>'Lecturer Not Found'
                ],404);
            }
            if($request->lecturer_code){
                $lecturer->lecturer_code=$request->lecturer_code;
            }
            if($request->user_id){
                $lecturer->user_id=$request->user_id;
            }
            if($request->full_name){
                $lecturer->full_name=$request->full_name;
            }
            if($request->email){
                $lecturer->email=$request->email;
            }
            if($request->phone){
                $lecturer->phone=$request->phone;
            }
            if($request->faculty_code){
                $lecturer->faculty_code=$request->faculty_code;
            }
            $lecturer->save();
            return response()->json([
                'success'=>true,
                'data'=>$lecturer,
                'message'=>'Lecturer updated successfully'
            ]);
        }
        catch(ValidationException $e){
            return response()->json([
                'success'=>false,
                'errors'=>$e->errors()
            ],422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $lecturer_code)
    {
        $lecturer=Lecturer::find($lecturer_code);
        if(!$lecturer){
            return response()->json([
                'success'=>false,
                'data'=>null,
                'message'=>'Lecturer Not Found'
            ],404);
        }
        $lecturer->delete();
        return response()->json([
            'success'=>true,
            'data'=>null,
            'message'=>'Lecturer deleted successfully'
        ]);
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
                'magv',
                'ma-giang-vien',
                'lecturer-code',
                'giang-vien',
                'ho-va-ten',
                'ten-giang-vien',
                'faculty-code',
                'khoa',
                'email',
                'so-dien-thoai',
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
            'mapping.lecturer_code' => 'required|string',
            'mapping.faculty_code' => 'required|string',
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
                new LecturersImport($validated['mapping'], $headingRow),
                Storage::path($filePath)
            );

            return response()->json([
                'success' => true,
                'message' => 'Import giảng viên thành công.',
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
