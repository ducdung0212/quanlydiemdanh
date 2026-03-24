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
        if (!Schema::hasTable('students') || Schema::hasColumn('students', 'class_name')) {
            return;
        }

        Schema::table('students', function (Blueprint $table) {
            // In this project, students may use student_code as PK and not have an id column.
            if (Schema::hasColumn('students', 'student_code')) {
                $table->string('class_name')->nullable()->after('student_code');
            } else {
                $table->string('class_name')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('students') || !Schema::hasColumn('students', 'class_name')) {
            return;
        }

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('class_name');
        });
    }
};
