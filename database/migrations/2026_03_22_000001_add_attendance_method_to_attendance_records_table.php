<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('attendance_records', 'attendance_method')) {
            Schema::table('attendance_records', function (Blueprint $table) {
                $table->enum('attendance_method', ['face', 'qr_code'])
                    ->nullable()
                    ->after('attendance_time');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('attendance_records', 'attendance_method')) {
            Schema::table('attendance_records', function (Blueprint $table) {
                $table->dropColumn('attendance_method');
            });
        }
    }
};
