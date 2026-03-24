<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            // Expand role enum to include student.
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','lecturer','student') NOT NULL DEFAULT 'lecturer'");

            if (!Schema::hasColumn('users', 'student_code')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->string('student_code', 20)->nullable()->after('role');
                    $table->index('student_code', 'users_student_code_index');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            if (Schema::hasColumn('users', 'student_code')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropIndex('users_student_code_index');
                    $table->dropColumn('student_code');
                });
            }

            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','lecturer') NOT NULL DEFAULT 'lecturer'");
        }
    }
};
