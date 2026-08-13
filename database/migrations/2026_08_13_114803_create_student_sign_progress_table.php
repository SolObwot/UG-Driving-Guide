<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_sign_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('sign_type', ['warning', 'regulatory', 'road_markings', 'information', 'road_signs', 'traffic_signals', 'hand_signals', 'other',]);
            $table->unsignedInteger('signs_attempted')->default(0);
            $table->unsignedInteger('signs_correct')->default(0);
            $table->unsignedTinyInteger('accuracy_percent')->default(0);
            $table->enum('mastery_level', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->timestamp('last_practiced_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'sign_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_sign_progress');
    }
};