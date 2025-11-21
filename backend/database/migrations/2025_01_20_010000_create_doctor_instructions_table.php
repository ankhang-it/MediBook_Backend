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
        Schema::create('doctor_instructions', function (Blueprint $table) {
            $table->uuid('instruction_id')->primary();
            $table->uuid('appointment_id')->unique();
            $table->uuid('doctor_id');
            $table->json('instructions')->nullable();
            $table->json('reminders')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('appointment_id')->references('appointment_id')->on('appointments')->onDelete('cascade');
            $table->foreign('doctor_id')->references('doctor_id')->on('doctor_profiles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_instructions');
    }
};

