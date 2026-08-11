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
        Schema::create('road_signs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['warning', 'regulatory', 'road_markings', 'information', 'road_signs', 'traffic_signals', 'hand_signals', 'other']);
            $table->string('description');
            $table->string('image_path');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('road_signs');
    }
};
