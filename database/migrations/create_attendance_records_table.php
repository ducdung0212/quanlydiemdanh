<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('exam_schedule_id');
            $table->string('student_code', 20);
            $table->enum('rekognition_result', ['match','not_match','unknown'])->nullable();
            $table->decimal('confidence', 5, 2)->nullable();
            $table->timestamp('attendance_time')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamps();

            $table->unique(['student_code', 'exam_schedule_id'], 'unique_student_exam');
            $table->index('exam_schedule_id', 'fk_attendance_schedule');
            $table->foreign('exam_schedule_id', 'fk_attendance_schedule')
                ->references('id')->on('exam_schedules')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('student_code', 'fk_attendance_student')
                ->references('student_code')->on('students')
                ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
