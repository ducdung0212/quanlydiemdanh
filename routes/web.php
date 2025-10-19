<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::get('/', function () {
    return view('auth/login');
});


Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('admin.index');
    })->name('dashboard');

  
    Route::get('/user', function () {
        return view('admin.users.index');
    })->name('user');

    // Routes for loading user modals
    Route::get('/users/modals/create', function () {
        return view('admin.users.create');
    });
    Route::get('/users/modals/edit/{id}', function ($id) {
        $user = \App\Models\User::find($id);
        if (!$user) {
            return response('User not found', 404);
        }
        return view('admin.users.edit', ['user' => $user]);
    });
    Route::get('/student', function () {
        return view('admin.students.index');
    })->name('student');
    Route::get('/lecturer', function () {
        return view('admin.lecturers.index');
    })->name('lecturer');
    Route::get('/exam-schedules', function () {
        return view('admin.exam_schedules.index');
    })->name('exam-schedules.index');


});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/dashboard', function () {
        return view('admin.index');
    })->name('dashboard');

    Route::get('/students', function () {
        return view('admin.students.index');
    })->name('students.index');

    // Routes for loading modals
    Route::get('/students/modals/create', function () {
        $classes = \App\Models\Classes::orderBy('class_code')->get();

        return view('admin.students.create', [
            'classes' => $classes,
        ]);
    });
    Route::get('/students/modals/edit/{student_code}', function ($student_code) {
        $student = \App\Models\Student::find($student_code);
        if (!$student) {
            return response('Student not found', 404);
        }
        $classes = \App\Models\Classes::orderBy('class_code')->get();

        return view('admin.students.edit', [
            'student' => $student,
            'classes' => $classes,
        ]);
    });
    Route::get('/students/modals/view/{student_code}', function ($student_code) {
        $student = \App\Models\Student::find($student_code);
        if (!$student) {
            return response('Student not found', 404);
        }
        return view('admin.students.show', ['student' => $student]);
    });
    Route::get('/students/modals/import', function () {
        return view('admin.students.import');
    });

    Route::get('/lecturers/modals/create', function () {
        $query = DB::table('faculties');
        if (Schema::hasColumn('faculties', 'faculty_code')) {
            $query->orderBy('faculty_code');
        } elseif (Schema::hasColumn('faculties', 'code')) {
            $query->orderBy('code');
        } else {
            $query->orderBy('name');
        }

        return view('admin.lecturers.create', [
            'faculties' => $query->get(),
        ]);
    });

    Route::get('/lecturers/{lecturer_code}/modals/edit', function ($lecturer_code) {
        $lecturer = \App\Models\Lecturer::find($lecturer_code);
        if (!$lecturer) {
            return response('Lecturer not found', 404);
        }

        $query = DB::table('faculties');
        if (Schema::hasColumn('faculties', 'faculty_code')) {
            $query->orderBy('faculty_code');
        } elseif (Schema::hasColumn('faculties', 'code')) {
            $query->orderBy('code');
        } else {
            $query->orderBy('name');
        }

        return view('admin.lecturers.edit', [
            'lecturer' => $lecturer,
            'faculties' => $query->get(),
        ]);
    });

    Route::get('/lecturers/{lecturer_code}/modals/view', function ($lecturer_code) {
        $lecturer = \App\Models\Lecturer::find($lecturer_code);
        if (!$lecturer) {
            return response('Lecturer not found', 404);
        }

        return view('admin.lecturers.show', ['lecturer' => $lecturer]);
    });

    Route::get('/lecturers/modals/import', function () {
        return view('admin.lecturers.import');
    });
});

require __DIR__.'/auth.php';
