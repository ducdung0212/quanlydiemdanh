<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\LecturerController;
use App\Http\Controllers\Exam_SchedulesController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::apiResource('users', UserController::class);

Route::apiResource('students', StudentController::class);
Route::post('students/bulk-delete', [StudentController::class, 'bulkDelete']);
Route::post('students/import/preview', [StudentController::class, 'previewImport']);
Route::post('students/import', [StudentController::class, 'import']);

Route::apiResource('lecturers', LecturerController::class);
Route::post('lecturers/import/preview', [LecturerController::class, 'previewImport']);
Route::post('lecturers/import', [LecturerController::class, 'import']);

Route::apiResource('exam_schedules', Exam_SchedulesController::class);
Route::post('exam_schedules/import/preview', [Exam_SchedulesController::class, 'previewImport']);
Route::post('exam_schedules/import', [Exam_SchedulesController::class, 'import']);