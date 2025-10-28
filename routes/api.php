<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AttendanceRecordController;
use App\Http\Controllers\ExamSchedulesController;
use App\Http\Controllers\ExamSupervisorController;
use App\Http\Controllers\LecturerController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\FaceAttendanceController;
use App\Http\Controllers\StudentFaceRegistrationController;

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
Route::get('exam-schedules/my/schedule', [ExamSchedulesController::class, 'mySchedule']);
Route::post('exam-schedules/bulk-delete', [ExamSchedulesController::class, 'bulkDelete']);
Route::post('exam-schedules/import/preview', [ExamSchedulesController::class, 'previewImport']);
Route::post('exam-schedules/import', [ExamSchedulesController::class, 'import']);

Route::apiResource('exam-supervisors', ExamSupervisorController::class);
Route::post('exam-supervisors/bulk-delete', [ExamSupervisorController::class, 'bulkDelete']);
Route::post('exam-supervisors/import/preview', [ExamSupervisorController::class, 'previewImport']);
Route::post('exam-supervisors/import', [ExamSupervisorController::class, 'import']);

Route::apiResource('attendance-records', AttendanceRecordController::class);
Route::post('attendance-records/bulk-delete', [AttendanceRecordController::class, 'bulkDelete']);
Route::post('attendance-records/import/preview', [AttendanceRecordController::class, 'previewImport']);
Route::post('attendance-records/import', [AttendanceRecordController::class, 'import']);

Route::apiResource('subjects', SubjectController::class);
Route::post('subjects/bulk-delete', [SubjectController::class, 'bulkDelete']);
Route::post('subjects/import/preview', [SubjectController::class, 'previewImport']);
Route::post('subjects/import', [SubjectController::class, 'import']);

// Face Recognition Attendance
Route::post('attendance/face-recognition', [FaceAttendanceController::class, 'authenticate']);
Route::get('attendance/test-lambda', [FaceAttendanceController::class, 'testLambda']);

// Presigned URLs for bulk face registration
Route::post('students/generate-upload-urls', [StudentFaceRegistrationController::class, 'generateUploadUrls']);
Route::post('/students/generate-upload-urls', [StudentFaceRegistrationController::class, 'generateUploadUrls']);