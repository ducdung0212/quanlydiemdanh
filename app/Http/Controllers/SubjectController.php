<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subject;
use App\Http\Requests\SubjectRequest;
use App\Imports\SubjectImport;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;


class SubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $q=request()->query('q','');
        $limit=request()->query('limit',10);
        $subjects=Subject::latest();
        if($q){
            $subjects->where(function($query) use ($q){
                $query->where('subject_code','like',"%$q%")
                      ->orWhere('name','like',"%$q%");
            });
        }
        return response()->json([
            'success'=>true,
            'data'=>$subjects->paginate($limit),
            'message'=>'List Subjects.'
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
    public function store(SubjectRequest $request)
    {
        try{
            $subject=new Subject();
            $subject->fill($request->all());
            $subject->save();
            return response()->json([
                'success'=>true,
                'data'=>$subject,
                'message'=>'Subject created successfully.'
            ],201);
        }catch(ValidationException $e){
            return response()->json([
                'success'=>false,
                'error'=>$e->errors()
            ],422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
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
    public function update(SubjectRequest $request, string $subject_code)
    {
        try{
            $subject=Subject::find($subject_code);
            if(!$subject){
                return response()->json([
                    'success'=>false,
                    'data'=>null,
                    'message'=>'Subject not found.'
                ],404);
            }
            if($request->subject_code){
                $subject->subject_code=$request->subject_code;
            }
            if($request->name){
                $subject->name=$request->name;
            }
            if($request->credit){
                $subject->credit=$request->credit;
            }
            $subject->save();
            return response()->json([
                'success'=>true,
                'data'=>$subject,
                'message'=>'Subject updated successfully.'
            ]);
        }catch(ValidationException $e){
            return response()->json([
                'success'=>false,
                'error'=>$e->errors()
            ],422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $subject_code)
    {
        $subject=Subject::find($subject_code);
        if(!$subject){
            return response()->json([
                'success'=>false,
                'data'=>null,
                'message'=>'Subject not found.'
            ],404);
        }
        $subject->delete();
        return response()->json([
            'success'=>true,
            'message'=>'Subject deleted successfully.'
        ]);
    }
    public function bulkDelete(Request $request)
    {
        $subjectCodes = $request->input('subject_codes', []);
        if(empty($subjectCodes)){
            return response()->json([
                'success'=>false,
                'message'=>'Không có môn học nào được chọn'
            ],400);
        }
        Subject::whereIn('subject_code', $subjectCodes)->delete();
        return response()->json([
            'success'=>true,
            'message'=>'Subjects deleted successfully.'
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
                'ma-mon-hoc',
                'ma-mon',
                'subject-code',
                'subject_code',
                'ten-mon-hoc',
                'ten-mon',
                'name',
                'so-tin-chi',
                'tin-chi',
                'credit',
                'credits',
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
            'mapping.subject_code' => 'required|string',
            'mapping.name' => 'required|string',
            'mapping.credit' => 'nullable|string',
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
                new SubjectImport($validated['mapping'], $headingRow),
                Storage::path($filePath)
            );

            return response()->json([
                'success' => true,
                'message' => 'Import môn học thành công.',
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
