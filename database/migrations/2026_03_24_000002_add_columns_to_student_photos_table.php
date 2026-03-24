<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('student_photos')) {
            return;
        }

        Schema::table('student_photos', function (Blueprint $table) {
            if (!Schema::hasColumn('student_photos', 'student_code')) {
                $table->string('student_code', 20)->nullable()->after('id');
                $table->index('student_code', 'student_photos_student_code_index');
            }

            if (!Schema::hasColumn('student_photos', 'image_url')) {
                $table->string('image_url', 2048)->nullable()->after('student_code');
            }

            if (!Schema::hasColumn('student_photos', 'uploaded_by_user_id')) {
                $table->unsignedBigInteger('uploaded_by_user_id')->nullable()->after('image_url');
                $table->index('uploaded_by_user_id', 'student_photos_uploaded_by_index');
            }

            if (!Schema::hasColumn('student_photos', 'approved_by_user_id')) {
                $table->unsignedBigInteger('approved_by_user_id')->nullable()->after('uploaded_by_user_id');
                $table->index('approved_by_user_id', 'student_photos_approved_by_index');
            }

            if (!Schema::hasColumn('student_photos', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by_user_id');
            }

            if (!Schema::hasColumn('student_photos', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('approved_at');
                $table->index('is_active', 'student_photos_is_active_index');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('student_photos')) {
            return;
        }

        Schema::table('student_photos', function (Blueprint $table) {
            if (Schema::hasColumn('student_photos', 'is_active')) {
                $table->dropIndex('student_photos_is_active_index');
                $table->dropColumn('is_active');
            }

            if (Schema::hasColumn('student_photos', 'approved_at')) {
                $table->dropColumn('approved_at');
            }

            if (Schema::hasColumn('student_photos', 'approved_by_user_id')) {
                $table->dropIndex('student_photos_approved_by_index');
                $table->dropColumn('approved_by_user_id');
            }

            if (Schema::hasColumn('student_photos', 'uploaded_by_user_id')) {
                $table->dropIndex('student_photos_uploaded_by_index');
                $table->dropColumn('uploaded_by_user_id');
            }

            if (Schema::hasColumn('student_photos', 'image_url')) {
                $table->dropColumn('image_url');
            }

            if (Schema::hasColumn('student_photos', 'student_code')) {
                $table->dropIndex('student_photos_student_code_index');
                $table->dropColumn('student_code');
            }
        });
    }
};
