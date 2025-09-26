<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_code', 20)->unique();
            $table->string('full_name', 100);
            $table->string('email', 100)->unique()->nullable();
            $table->string('phone', 15)->nullable();
            $table->foreignId('faculty_id')->constrained('faculties')->onDelete('cascade');
            $table->string('photo_url', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
