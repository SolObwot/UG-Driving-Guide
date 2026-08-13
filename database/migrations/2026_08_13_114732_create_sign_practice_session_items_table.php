<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sign_practice_session_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('sign_practice_sessions')->cascadeOnDelete();
            $table->foreignId('road_sign_id')->constrained('road_signs');
            $table->unsignedInteger('order')->default(0);

            $table->unique(['session_id', 'road_sign_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sign_practice_session_items');
    }
};