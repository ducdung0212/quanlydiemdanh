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
        $tables = [
            'users','faculties','students','lecturers','subjects','exam_schedules','exam_supervisors','attendance_records'
        ];
        foreach ($tables as $t) {
            \DB::statement("ALTER TABLE `{$t}` ENGINE=InnoDB;");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally convert back to MyISAM if needed. We'll leave this empty to avoid accidental data loss.
    }
};
