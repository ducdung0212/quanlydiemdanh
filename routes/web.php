<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExamSchedulesController;
use App\Http\Controllers\LecturerExamSchedulesController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentFaceRegistrationController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to login
Route::get('/', function () {
    return view('auth/login');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    // Routes accessible to all authenticated users (including lecturers)
    // ==================== PROFILE ====================
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //attendance - visible/usable by lecturers
    Route::get('/attendance', function () {
        return view('lecturer.attendance.index');
    })->name('attendance');

    // Lecturer-only pages
    Route::middleware('role:lecturer')->group(function () {
        Route::get('/lecturer/exam-schedules', [LecturerExamSchedulesController::class, 'index'])
            ->name('lecturer.exam-schedules');
    });

    // ==================== ADMIN-ONLY ROUTES ====================
    Route::middleware('role:admin')->group(function () {
        // DASHBOARD
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');
        Route::get('/dashboard/face-registration-students', [DashboardController::class, 'faceRegistrationStudents'])->name('dashboard.face-registration-students');

        // USERS
        Route::get('/user', function () {
            return view('admin.users.index');
        })->name('user');

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

        // STUDENTS
        Route::get('/student', function () {
            return view('admin.students.index');
        })->name('student');

        Route::get('/students', function () {
            return view('admin.students.index');
        })->name('students.index');

        Route::get('/students/modals/create', function () {
            $classes = \App\Models\Classes::orderBy('class_code')->get();
            return view('admin.students.create', ['classes' => $classes]);
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

        // LECTURERS
        Route::get('/lecturer', function () {
            return view('admin.lecturers.index');
        })->name('lecturer');

        Route::get('/lecturers/modals/create', function () {
            $query = DB::table('faculties');
            if (Schema::hasColumn('faculties', 'faculty_code')) {
                $query->orderBy('faculty_code');
            } elseif (Schema::hasColumn('faculties', 'code')) {
                $query->orderBy('code');
            } else {
                $query->orderBy('name');
            }
            return view('admin.lecturers.create', ['faculties' => $query->get()]);
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
            $users = \App\Models\User::orderBy('id')->get();
            return view('admin.lecturers.edit', [
                'lecturer' => $lecturer,
                'faculties' => $query->get(),
                'users' => $users,
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

        // SUBJECTS
        Route::get('/subject', function () {
            return view('admin.subjects.index');
        })->name('subject');

        Route::get('/subjects/modals/create', function () {
            return view('admin.subjects.create');
        });

        Route::get('/subjects/modals/edit/{subject_code}', function ($subject_code) {
            $subject = \App\Models\Subject::find($subject_code);
            if (!$subject) {
                return response('Subject not found', 404);
            }
            return view('admin.subjects.edit', ['subject' => $subject]);
        });

        Route::get('/subjects/modals/view/{subject_code}', function ($subject_code) {
            $subject = \App\Models\Subject::find($subject_code);
            if (!$subject) {
                return response('Subject not found', 404);
            }
            return view('admin.subjects.show', ['subject' => $subject]);
        });

        Route::get('/subjects/modals/import', function () {
            return view('admin.subjects.import');
        });

        // EXAM SCHEDULES
        Route::get('/exam-schedules', function () {
            return view('admin.exam-schedules.index');
        })->name('exam-schedules');

        Route::get('exam-schedules/show/{id}', function ($id) {
            return view('admin.exam-schedules.show', ['id' => $id]);
        })->name('exam-schedules.show');

        Route::get('/exam-schedules/modals/import', function () {
            return view('admin.exam-schedules.import');
        });

        Route::get('/exam-schedules/modals/export', function () {
            return view('admin.exam-schedules.export-modal');
        });

        Route::get('/exam-schedules/{id}/modals/students', function ($id) {
            return view('admin.exam-schedules.students-modal');
        });

        Route::get('/exam-schedules/{id}/modals/import-students', function ($id) {
            return view('admin.exam-schedules.import-students-modal');
        });

        Route::get('/exam-schedules/{id}/modals/supervisors', function ($id) {
            return view('admin.exam-schedules.supervisors-modal');
        });

        Route::get('/exam-schedules/{id}/export', [ExamSchedulesController::class, 'exportAttendance'])
            ->name('exam-schedules.export');

        // EXAM SUPERVISORS
        Route::get('/exam-supervisors', function () {
            return view('admin.exam-supervisors.index');
        })->name('exam-supervisors');

        Route::get('/exam-supervisors/modals/import', function () {
            return view('admin.exam-supervisors.import');
        });

        // Edit supervisor modal
        Route::get('/exam-supervisors/modals/edit/{id}', function ($id) {
            $supervisor = \App\Models\ExamSupervisor::find($id);
            if (!$supervisor) {
                return response('Supervisor not found', 404);
            }
            // Provide list of lecturers for selection
            $lecturers = \App\Models\Lecturer::orderBy('full_name')->get();
            return view('admin.exam-supervisors.edit', [
                'supervisor' => $supervisor,
                'lecturers' => $lecturers,
            ]);
        });

        // ATTENDANCE RECORDS (admin)
        Route::get('/attendance-records', function () {
            return view('admin.attendance-records.index');
        })->name('attendance-records');

        Route::get('/attendance-records/modals/import', function () {
            return view('admin.attendance-records.import');
        });

        // Face registration UI (admin)
        Route::get('/face-registration', function () {
            return view('admin.face-registration.index');
        })->name('face-registration');
        Route::post('/students/generate-upload-url', [StudentFaceRegistrationController::class, 'generateUploadUrls']);
        Route::post('/students/confirm-upload', [StudentFaceRegistrationController::class, 'confirmUpload']);
    });
});



require __DIR__ . '/auth.php';
