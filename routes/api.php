<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AttendanceRecordController;
use App\Http\Controllers\ExamSchedulesController;
use App\Http\Controllers\ExamSupervisorController;
use App\Http\Controllers\LecturerController;

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
Route::post('lecturers/bulk-delete', [LecturerController::class, 'bulkDelete']);
Route::post('lecturers/import/preview', [LecturerController::class, 'previewImport']);
Route::post('lecturers/import', [LecturerController::class, 'import']);

Route::apiResource('exam-schedules', ExamSchedulesController::class);
Route::post('exam-schedules/bulk-delete', [ExamSchedulesController::class, 'bulkDelete']);
Route::post('exam-schedules/import/preview', [ExamSchedulesController::class, 'previewImport']);
Route::post('exam-schedules/import', [ExamSchedulesController::class, 'import']);

Route::delete('exam-supervisors/{examSupervisor}', [ExamSupervisorController::class, 'destroy']);
Route::delete('exam-supervisors', [ExamSupervisorController::class, 'destroyAll']);
Route::post('exam-supervisors/import', [ExamSupervisorController::class, 'import']);

Route::delete('attendance-records/{attendanceRecord}', [AttendanceRecordController::class, 'destroy']);
Route::delete('attendance-records', [AttendanceRecordController::class, 'destroyAll']);
Route::post('attendance-records/import', [AttendanceRecordController::class, 'import']);