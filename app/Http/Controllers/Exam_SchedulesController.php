<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Exam_Schedules;

class Exam_SchedulesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $q = request()->query('q');
        $limit = (int) request()->query('limit', 10);

        $exam_schedules = Exam_Schedules::query()
            ->with('subject')
            ->latest();

        if ($q) {
            $exam_schedules->where(function ($query) use ($q) {
                $query->where('id', 'like', "%{$q}%")
                    ->orWhere('subject_code', 'like', "%{$q}%")
                    ->orWhere('exam_date', 'like', "%{$q}%")
                    ->orWhere('exam_time', 'like', "%{$q}%")
                    ->orWhere('room', 'like', "%{$q}%")
                    ->orWhereHas('subject', function ($subjectQuery) use ($q) {
                        $subjectQuery->where('name', 'like', "%{$q}%");
                    });
            });
        }

        return response()->json([
            'success' => true,
            'data' => $exam_schedules->paginate($limit),
            'message' => 'List Exam Schedules',
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
