<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $checks = [
            [
                'table' => 'students',
                'fk_name' => 'fk_students_faculty',
                'sql' => "ALTER TABLE students ADD CONSTRAINT fk_students_faculty FOREIGN KEY (faculty_id) REFERENCES faculties(id) ON DELETE CASCADE;"
            ],
            [
                'table' => 'lecturers',
                'fk_name' => 'fk_lecturers_faculty',
                'sql' => "ALTER TABLE lecturers ADD CONSTRAINT fk_lecturers_faculty FOREIGN KEY (faculty_id) REFERENCES faculties(id) ON DELETE CASCADE;"
            ],
            [
                'table' => 'exam_schedules',
                'fk_name' => 'fk_exam_schedules_subject',
                'sql' => "ALTER TABLE exam_schedules ADD CONSTRAINT fk_exam_schedules_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE;"
            ],
            [
                'table' => 'exam_supervisors',
                'fk_name' => 'fk_exam_supervisors_schedule',
                'sql' => "ALTER TABLE exam_supervisors ADD CONSTRAINT fk_exam_supervisors_schedule FOREIGN KEY (exam_schedule_id) REFERENCES exam_schedules(id) ON DELETE CASCADE;"
            ],
            [
                'table' => 'exam_supervisors',
                'fk_name' => 'fk_exam_supervisors_lecturer',
                'sql' => "ALTER TABLE exam_supervisors ADD CONSTRAINT fk_exam_supervisors_lecturer FOREIGN KEY (lecturer_id) REFERENCES lecturers(id) ON DELETE CASCADE;"
            ],
            [
                'table' => 'attendance_records',
                'fk_name' => 'fk_attendance_schedule',
                'sql' => "ALTER TABLE attendance_records ADD CONSTRAINT fk_attendance_schedule FOREIGN KEY (exam_schedule_id) REFERENCES exam_schedules(id) ON DELETE CASCADE;"
            ],
            [
                'table' => 'attendance_records',
                'fk_name' => 'fk_attendance_student',
                'sql' => "ALTER TABLE attendance_records ADD CONSTRAINT fk_attendance_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE;"
            ],
        ];

        foreach ($checks as $c) {
            $exists = \DB::select("select constraint_name from information_schema.key_column_usage where constraint_schema = database() and constraint_name = ?", [$c['fk_name']]);
            if (empty($exists)) {
                try {
                    \DB::statement($c['sql']);
                } catch (\Exception $e) {
                    // log and continue; some FKs may fail if columns/indexes missing
                    \Log::warning('Could not add FK ' . $c['fk_name'] . ': ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $drops = [
            "ALTER TABLE students DROP FOREIGN KEY fk_students_faculty;",
            "ALTER TABLE lecturers DROP FOREIGN KEY fk_lecturers_faculty;",
            "ALTER TABLE exam_schedules DROP FOREIGN KEY fk_exam_schedules_subject;",
            "ALTER TABLE exam_supervisors DROP FOREIGN KEY fk_exam_supervisors_schedule;",
            "ALTER TABLE exam_supervisors DROP FOREIGN KEY fk_exam_supervisors_lecturer;",
            "ALTER TABLE attendance_records DROP FOREIGN KEY fk_attendance_schedule;",
            "ALTER TABLE attendance_records DROP FOREIGN KEY fk_attendance_student;",
        ];
        foreach ($drops as $sql) {
            try { \DB::statement($sql); } catch (\Exception $e) { /* ignore */ }
        }
    }
};
