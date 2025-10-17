<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lecturer;
use App\Http\Requests\LecturerRequest;
use Illuminate\Validation\ValidationException;

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
}
