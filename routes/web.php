<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

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
    Route::get('/product/add', function () {
        return view('admin.add-product');
    })->name('product.add');
    Route::get('/student', function () {
        return view('admin.students.index');
    })->name('student');
      Route::get('/teacher', function () {
        return view('admin.teacher');
    })->name('teacher');
    Route::get('/phancong', function () {
        return view('admin.phancong-teacher');
    })->name('phancong');


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
});

require __DIR__.'/auth.php';
