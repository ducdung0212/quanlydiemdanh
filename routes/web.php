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

    // Products
    Route::get('/user', function () {
        return view('admin.user');
    })->name('user');
    Route::get('/product/add', function () {
        return view('admin.add-product');
    })->name('product.add');
    Route::get('/student', function () {
        return view('admin.student');
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
});

require __DIR__.'/auth.php';
