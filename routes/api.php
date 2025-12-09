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

// Protected routes - require authentication
Route::middleware(['web', 'auth'])->group(function () {
    Route::apiResource('users', UserController::class);

    Route::apiResource('students', StudentController::class);
    Route::post('students/bulk-delete', [StudentController::class, 'bulkDelete'])->name('students.bulk-delete');
    Route::post('students/import/preview', [StudentController::class, 'previewImport']);
    Route::post('students/import', [StudentController::class, 'import']);

    Route::apiResource('lecturers', LecturerController::class);
    Route::post('lecturers/bulk-delete', [LecturerController::class, 'bulkDelete'])->name('lecturers.bulk-delete');
    Route::post('lecturers/import/preview', [LecturerController::class, 'previewImport']);
    Route::post('lecturers/import', [LecturerController::class, 'import']);

    // Các route cụ thể phải đặt TRƯỚC apiResource
    Route::get('exam-schedules/my/schedule', [ExamSchedulesController::class, 'mySchedule']);
    Route::get('exam-schedules/today/all', [ExamSchedulesController::class, 'todayExams']);
    Route::get('exam-schedules/current/exam', [ExamSchedulesController::class, 'currentExam']);
    Route::post('exam-schedules/bulk-delete', [ExamSchedulesController::class, 'bulkDelete'])->name('exam-schedules.bulk-delete');
    Route::post('exam-schedules/import/preview', [ExamSchedulesController::class, 'previewImport']);
    Route::post('exam-schedules/import', [ExamSchedulesController::class, 'import']);
    Route::post('exam-schedules/export/multiple', [ExamSchedulesController::class, 'exportMultipleAttendance'])->name('exam-schedules.export.multiple');
    Route::post('exam-schedules/export/by-date', [ExamSchedulesController::class, 'exportByDate'])->name('exam-schedules.export.by-date');
    
    // Quản lý sinh viên tham gia ca thi
    Route::get('exam-schedules/{id}/students', [ExamSchedulesController::class, 'getStudents']);
    Route::post('exam-schedules/{id}/students', [ExamSchedulesController::class, 'addStudent']);
    Route::delete('exam-schedules/{id}/students/{recordId}', [ExamSchedulesController::class, 'removeStudent']);
    
    // Quản lý giám thị ca thi
    Route::get('exam-schedules/{id}/supervisors', [ExamSchedulesController::class, 'getSupervisors']);
    Route::post('exam-schedules/{id}/supervisors', [ExamSchedulesController::class, 'addSupervisor']);
    Route::delete('exam-schedules/{id}/supervisors/{supervisor_id}', [ExamSchedulesController::class, 'removeSupervisor']);
    
    // apiResource phải đặt SAU các route cụ thể
    Route::apiResource('exam-schedules', ExamSchedulesController::class);

    Route::apiResource('exam-supervisors', ExamSupervisorController::class);
    Route::post('exam-supervisors/bulk-delete', [ExamSupervisorController::class, 'bulkDelete'])->name('exam-supervisors.bulk-delete');
    Route::post('exam-supervisors/import/preview', [ExamSupervisorController::class, 'previewImport']);
    Route::post('exam-supervisors/import', [ExamSupervisorController::class, 'import']);

    Route::apiResource('attendance-records', AttendanceRecordController::class);
    Route::post('attendance-records/bulk-delete', [AttendanceRecordController::class, 'bulkDelete'])->name('attendance-records.bulk-delete');
    Route::post('attendance-records/import/preview', [AttendanceRecordController::class, 'previewImport']);
    Route::post('attendance-records/import', [AttendanceRecordController::class, 'import']);

    Route::apiResource('subjects', SubjectController::class);
    Route::post('subjects/bulk-delete', [SubjectController::class, 'bulkDelete'])->name('subjects.bulk-delete');
    Route::post('subjects/import/preview', [SubjectController::class, 'previewImport']);
    Route::post('subjects/import', [SubjectController::class, 'import']);

    // Face Recognition Attendance
    Route::post('attendance/face-recognition', [FaceAttendanceController::class, 'authenticate']);
    Route::get('attendance/test-lambda', [FaceAttendanceController::class, 'testLambda']);

    // Presigned URLs for bulk face registration
    Route::post('students/generate-upload-urls', [StudentFaceRegistrationController::class, 'generateUploadUrls']);
    Route::post('/students/generate-upload-urls', [StudentFaceRegistrationController::class, 'generateUploadUrls']);
});
