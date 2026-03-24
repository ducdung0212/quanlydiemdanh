<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('face_registration_windows', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('opened_by_user_id')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'starts_at', 'ends_at'], 'face_reg_windows_active_time_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('face_registration_windows');
    }
};
