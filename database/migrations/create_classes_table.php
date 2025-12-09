<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->string('class_code', 20)->primary();
            $table->string('class_name', 100);
            $table->string('faculty_code', 20);
            $table->timestamps();

            $table->index('faculty_code');
            $table->foreign('faculty_code')
                ->references('faculty_code')->on('faculties')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
