<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Requests\StudentRequest;

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
        $student =new Student;
        $student->fill($request->all());
        $student->save();
        return response()->json([
            'success' => true,
            'data' => $student,
            'message'=>'Create Student Successfully'
        ], 201);
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
            'message'=>'Get Student Successfully'
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
}
